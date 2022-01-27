from os import getpid, path, mkdir
from enum import Enum
from importlib import import_module
from json import dumps as to_json
import logging

from grpc import RpcError
from requirements import parse as req_parse
from install import add_python_path, get_package_info, get_site_packages, pip_call
from py_proto.core_pb2 import taskStatus, taskType
from core_proto import ClientCloudPA, CloudLogHandler
from nc_py_api import _ncc


class ExitCodes(Enum):
    CODE_OK = 0
    CODE_CONN_BROKE = 1
    CODE_CONN_IMP = 2
    CODE_INSTALL_ERR = 3
    CODE_INIT_ERR = 4
    CODE_LOAD_ERR = 5
    CODE_EXCEPTION = 6


def check_requirements(req_file_path: str, app_package_dir: str, cc: ClientCloudPA) -> bool:
    try:
        needed_packages = []
        with open(req_file_path, 'r') as requirements_file:
            for requirement in req_parse(requirements_file):
                depends_on = requirement.specs[0] if len(requirement.specs) == 1 else requirement.specs
                needed_packages.append({'name': requirement.name, 'version': to_json(depends_on)})
    except OSError:
        cc.logger.error(f'Error during {req_file_path} read.')
        return False
    installed_packages = []
    not_installed_packages = []
    python_path = get_site_packages()
    for package in needed_packages:
        package_info = get_package_info(package['name'], userbase=app_package_dir, python_path=python_path)
        if package_info:
            package_info['version'] = to_json(package_info['version'])
            installed_packages.append(package_info)
        else:
            not_installed_packages.append(package)
    cc.send_app_info(not_installed=not_installed_packages, installed=installed_packages)
    if not not_installed_packages:
        return True
    return False


def install_requirements(req_file_path: str, app_package_dir: str, cc: ClientCloudPA) -> None:
    _call_result, _message = pip_call(['install', '-r', req_file_path, '--no-warn-script-location', '--prefer-binary'],
                                      userbase=app_package_dir, python_path=get_site_packages(),
                                      user_cache=True)
    if not _call_result:
        cc.logger.error(f'pip_call:{_message}')
    else:
        cc.logger.debug(f'pip_call:{_message}')


def check_install_app(app_package_dir: str, cc: ClientCloudPA, task_data) -> bool:
    requirements_path = path.join(task_data.modPath, 'requirements.txt')
    if not path.isfile(requirements_path):
        cc.logger.debug(f'Requirements missing for {task_data.appName}: {task_data.modPath}')
        return True
    cc.logger.debug(f'Processing {requirements_path}')
    if not path.isdir(app_package_dir):
        cc.logger.info(f'Creating app directory: {app_package_dir}')
        try:
            mkdir(app_package_dir, mode=0o774)
        except OSError:
            cc.logger.error(f'Cant create application directory{app_package_dir} for python packages.')
            return False
    if task_data.cmdType in (taskType.T_INSTALL, taskType.T_DEFAULT):
        install_requirements(requirements_path, app_package_dir, cc)
    return check_requirements(requirements_path, app_package_dir, cc)


def pyfrm_main(framework_data: str, connect_address: str, auth: str = '') -> ExitCodes:
    cloud = None
    exit_code = ExitCodes.CODE_OK
    try:
        cloud = ClientCloudPA(connect_address, auth)
        result = None
        cloud.set_status(taskStatus.ST_INITIALIZING)
        if not cloud.perform_init():
            cloud.set_status(taskStatus.ST_INIT_ERROR)
            return ExitCodes.CODE_INIT_ERR
        _ncc.NCC = cloud
        logging.getLogger('pyfrm.install').setLevel(cloud.nc_to_python_loglvl(cloud.task_init_data.config.log_lvl))
        logging.getLogger('pyfrm.install').addHandler(CloudLogHandler())
        try:
            cloud.logger.debug(f'Started with pid={getpid()}')
            cloud.logger.debug(f'Start loading target app: {cloud.task_init_data.appName}')
            _app_packages = path.abspath(path.join(framework_data, cloud.task_init_data.appName))
            if cloud.task_init_data.cmdType != taskType.T_RUN:
                cloud.set_status(taskStatus.ST_INSTALLING)
                if not check_install_app(_app_packages, cloud, cloud.task_init_data):
                    cloud.set_status(taskStatus.ST_INSTALL_ERROR)
                    return ExitCodes.CODE_INSTALL_ERR
                if cloud.task_init_data.cmdType == taskType.T_CHECK:
                    cloud.set_status(taskStatus.ST_SUCCESS)
                    return ExitCodes.CODE_OK
            cloud.set_status(taskStatus.ST_INITIALIZING)
            if not path.isdir(_app_packages):
                cloud.logger.debug(f'App directory({_app_packages}) with python packages cannot be accessed.')
            else:
                app_site_packages = get_site_packages(_app_packages)
                if app_site_packages:
                    if path.isdir(app_site_packages):
                        add_python_path(app_site_packages, first=True)
                    else:
                        cloud.logger.info(f'App site package directory({app_site_packages}) does not exist.')
                else:
                    cloud.logger.warning(f'App({cloud.task_init_data.appName}) site package directory not found.')
            add_python_path(path.abspath(cloud.mod_folder), first=True)
            try:
                target_module = import_module(cloud.mod_name, None)
                globals()[cloud.mod_name] = target_module
            except (ModuleNotFoundError, AttributeError, ImportError, ValueError):
                cloud.logger.exception(f'Error loading {cloud.mod_name} module.')
                cloud.set_status(taskStatus.ST_INIT_ERROR, f'Error loading {cloud.mod_name} module.')
                return ExitCodes.CODE_LOAD_ERR
            try:
                func_to_call = getattr(target_module, cloud.task_init_data.funcName)
            except AttributeError:
                cloud.logger.error(f'Function {cloud.task_init_data.funcName} not found.')
                cloud.set_status(taskStatus.ST_INIT_ERROR, f'Function {cloud.task_init_data.funcName} not found.')
                return ExitCodes.CODE_LOAD_ERR
            cloud.logger.debug(f'Calling target app entry point({cloud.task_init_data.funcName})')
            cloud.set_status(taskStatus.ST_IN_PROGRESS)
            result = func_to_call(*cloud.task_init_data.args)
            cloud.logger.debug(f'Target app finished.')
            if result is None or isinstance(result, str):
                cloud.logger.debug(f"Result length=`{len(result) if result is not None else 'None'}`")
            else:
                cloud.logger.warning(f'Invalid result type({str(type(result))})')
                result = None
            cloud.set_status(taskStatus.ST_SUCCESS)
        except Exception as exception_info:
            exception_name = type(exception_info).__name__
            if exception_name in ('RpcError',):
                raise exception_info from None
            exit_code = ExitCodes.CODE_EXCEPTION
            cloud.set_status(taskStatus.ST_EXCEPTION, exception_name)
            cloud.logger.exception(f'Exception: {exception_name}')
        finally:
            cloud.exit(result)
    except RpcError as exception_info:
        exit_code = ExitCodes.CODE_CONN_BROKE
        if cloud is None:
            logging.getLogger('pyfrm').error('Cant establish connect to server')
            exit_code = ExitCodes.CODE_CONN_IMP
        logging.getLogger('pyfrm').exception(f'Exception: {type(exception_info).__name__}')
    return exit_code
