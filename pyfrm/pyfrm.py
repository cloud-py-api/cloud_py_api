from os import getpid, path
from enum import Enum
from importlib import import_module
from traceback import format_exc

from grpc import RpcError
from install import add_python_path
from py_proto.core_pb2 import logLvl, taskStatus, taskType
from helpers import print_err
from core_proto import ClientCloudPA
from nc_py_api import _ncc


class ExitCodes(Enum):
    CODE_OK = 0
    CODE_CONN_BROKE = 1
    CODE_CONN_IMP = 2
    CODE_INSTALL_ERR = 3
    CODE_INIT_ERR = 4
    CODE_LOAD_ERR = 5
    CODE_EXCEPTION = 6


def check_task_init(cloud: ClientCloudPA) -> [bool, str, str]:
    _task_data = cloud.task_init_data
    if not _task_data.appName:
        cloud.log(logLvl.FATAL, 'cpa_core', 'invalid task`s appName')
        return False, '', ''
    if not _task_data.modPath:
        cloud.log(logLvl.FATAL, 'cpa_core', 'invalid task`s modPath')
        return False, '', ''
    if not _task_data.funcName:
        cloud.log(logLvl.FATAL, 'cpa_core', 'invalid task`s funcName')
        return False, '', ''
    mod_folder, mod_name = path.split(_task_data.modPath)
    if not mod_name:
        cloud.log(logLvl.FATAL, 'cpa_core', 'invalid task`s modPath, extracted module name is empty.')
        return False, '', ''
    return True, mod_folder, mod_name


def check_install_app(options: dict, app_package_dir: str, cc: ClientCloudPA, task_data) -> bool:
    requirements_path = path.join(task_data.modPath, 'requirements.txt')
    if not path.isfile(requirements_path):
        cc.log(logLvl.DEBUG, 'cpa_core', f'Requirements missing for {task_data.appName}, {task_data.modPath}.')
        return True
    return False


def pyfrm_main(options: dict, framework_data: str, connect_address: str, auth: str = '') -> ExitCodes:
    cloud = None
    exit_code = ExitCodes.CODE_OK
    try:
        cloud = ClientCloudPA(connect_address, auth)
        result = None
        try:
            cloud.set_status(taskStatus.ST_IN_PROGRESS)
            cloud.log(logLvl.DEBUG, 'cpa_core', f'Started with pid={getpid()}')
            _result, mod_folder, mod_name = check_task_init(cloud)
            if not _result:
                cloud.set_status(taskStatus.ST_INIT_ERROR)
                return ExitCodes.CODE_INIT_ERR
            cloud.log(logLvl.DEBUG, 'cpa_core', f'Start loading target app: {cloud.task_init_data.appName}')
            _app_packages = path.abspath(path.join(framework_data, cloud.task_init_data.appName))
            if cloud.task_init_data.cmdType != taskType.T_RUN:
                cloud.set_status(taskStatus.ST_INSTALLING)
                if not check_install_app(options, _app_packages, cloud, cloud.task_init_data):
                    cloud.set_status(taskStatus.ST_INSTALL_ERROR)
                    return ExitCodes.CODE_INSTALL_ERR
            if not path.isdir(_app_packages):
                cloud.log(logLvl.DEBUG, 'cpa_core',
                          f'App directory({_app_packages}) with python packages cannot be accessed.')
            else:
                add_python_path(_app_packages, first=True)
            add_python_path(path.abspath(mod_folder), first=True)
            _ncc.NCC = cloud
            try:
                target_module = import_module(mod_name, None)
                globals()[mod_name] = target_module
            except (ModuleNotFoundError, AttributeError, ImportError, ValueError):
                cloud.log(logLvl.FATAL, 'cpa_core', f'Error loading {mod_name} module.')
                cloud.set_status(taskStatus.ST_INIT_ERROR, f'Error loading {mod_name} module.')
                return ExitCodes.CODE_LOAD_ERR
            try:
                func_to_call = getattr(target_module, cloud.task_init_data.funcName)
            except AttributeError:
                cloud.log(logLvl.FATAL, 'cpa_core', f'Function {cloud.task_init_data.funcName} not found.')
                cloud.set_status(taskStatus.ST_INIT_ERROR, f'Function {cloud.task_init_data.funcName} not found.')
                return ExitCodes.CODE_LOAD_ERR
            cloud.log(logLvl.DEBUG, 'cpa_core', f'Calling target app entry point({cloud.task_init_data.funcName})')
            result = func_to_call(*cloud.task_init_data.args)
            cloud.log(logLvl.DEBUG, 'cpa_core', f'Target app finished.')
            if result is None or isinstance(result, str):
                cloud.log(logLvl.DEBUG, 'cpa_core', f"Result length=`{len(result) if result is not None else 'None'}`")
            else:
                cloud.log(logLvl.ERROR, 'cpa_core', f'Invalid result type({str(type(result))})')
                result = None
            cloud.set_status(taskStatus.ST_SUCCESS)
        except Exception as exception_info:
            exception_name = str(type(exception_info).__name__)
            if exception_name in ('RpcError',):
                raise exception_info from None
            exit_code = ExitCodes.CODE_EXCEPTION
            cloud.set_status(taskStatus.ST_EXCEPTION, exception_name)
            exception_info_str = str(format_exc())
            cloud.log(logLvl.ERROR, 'cpa_core', f'Exception({exception_name}):`{exception_info_str}`')
        finally:
            cloud.exit(result)
    except RpcError as exception_info:
        exit_code = ExitCodes.CODE_CONN_BROKE
        if cloud is None:
            print_err('Cant establish connect to server')
            exit_code = ExitCodes.CODE_CONN_IMP
        print_err(str(exception_info))
    return exit_code
