import signal
import sys
from os import getpid, path
from enum import Enum
from importlib import invalidate_caches, import_module
from traceback import format_exc

from grpc import RpcError
from py_proto.core_pb2 import logLvl, taskStatus
from helpers import print_err, debug_msg
from core_proto import ClientCloudPA
from nc_py_api import cloud_api, _ncc


class ExitCodes(Enum):
    CODE_OK = 0
    CODE_CONN_BROKE = 1
    CODE_CONN_IMP = 2
    CODE_INIT_ERR = 3
    CODE_LOAD_ERR = 4
    CODE_EXCEPTION = 5


def signal_handler(signum=None, _frame=None):
    print_err('Got signal:', signum)
    sys.exit(0)


def check_task_init(cloud: ClientCloudPA) -> bool:
    _task_data = cloud.task_init_data
    if not _task_data.appName:
        cloud.log(logLvl.FATAL, 'cpa_core', 'invalid task`s appName')
        return False
    if not _task_data.modName:
        cloud.log(logLvl.FATAL, 'cpa_core', 'invalid task`s modName')
        return False
    if not _task_data.modPath:
        cloud.log(logLvl.FATAL, 'cpa_core', 'invalid task`s modPath')
        return False
    if not _task_data.funcName:
        cloud.log(logLvl.FATAL, 'cpa_core', 'invalid task`s funcName')
        return False
    if not _task_data.config.frameworkAppData:
        cloud.log(logLvl.FATAL, 'cpa_core', 'invalid task`s frameworkAppData')
        return False
    return True


def true_main(connect_address: str, auth: str = '') -> ExitCodes:
    cloud = None
    exit_code = ExitCodes.CODE_OK
    try:
        cloud = ClientCloudPA(connect_address, auth)
        result = None
        try:
            cloud.set_status(taskStatus.ST_IN_PROGRESS)
            cloud.log(logLvl.DEBUG, 'cpa_core', f'Started with pid={getpid()}')
            if not check_task_init(cloud):
                cloud.set_status(taskStatus.ST_INIT_ERROR)
                return ExitCodes.CODE_INIT_ERR
            cloud.log(logLvl.DEBUG, 'cpa_core', f'Start loading target app: {cloud.task_init_data.appName}')
            _app_packages = path.abspath(
                path.join(cloud.task_init_data.config.frameworkAppData, cloud.task_init_data.appName))
            _app_packages_exists = path.isdir(_app_packages)
            # TODO: if directory does not exist run from default path.
            if not _app_packages_exists:
                cloud.log(logLvl.FATAL, 'cpa_core',
                          f'App directory({_app_packages}) with python packages cannot be accessed.')
                cloud.set_status(taskStatus.ST_INIT_ERROR, 'Directory with python packages for app cannot be accessed.')
                return ExitCodes.CODE_INIT_ERR
            # TODO: expand site_path to frameworkAppData + appName   -> as first element?
            sys.path.append(path.dirname(path.abspath(cloud.task_init_data.modPath)))
            invalidate_caches()
            _ncc.NCC = cloud
            try:
                target_module = import_module(cloud.task_init_data.modName, None)
                globals()[cloud.task_init_data.modName] = target_module
            except (ModuleNotFoundError, AttributeError, ImportError, ValueError):
                cloud.log(logLvl.FATAL, 'cpa_core', f'Error loading {cloud.task_init_data.modName} module.')
                cloud.set_status(taskStatus.ST_ERROR, f'Error loading {cloud.task_init_data.modName} module.')
                return ExitCodes.CODE_LOAD_ERR
            try:
                func_to_call = getattr(target_module, cloud.task_init_data.funcName)
            except AttributeError:
                cloud.log(logLvl.FATAL, 'cpa_core', f'Function {cloud.task_init_data.funcName} not found.')
                cloud.set_status(taskStatus.ST_ERROR, f'Function {cloud.task_init_data.funcName} not found.')
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


if __name__ == '__main__':
    debug_msg('__main__: started')
    for sig in [signal.SIGINT, signal.SIGQUIT, signal.SIGTERM, signal.SIGHUP]:
        signal.signal(sig, signal_handler)
    r = true_main(sys.argv[1:2][0])
    debug_msg(f'__main__: finished, exit_code = {r.value}:{r.name}')
    sys.exit(r.value)
