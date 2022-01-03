import sys
import os
import time
import re
from subprocess import PIPE, Popen, TimeoutExpired
from concurrent import futures

import grpc
from core_pb2 import taskStatus, logLvl, Empty, \
    ServerCommand, TaskInitReply
import core_pb2_grpc as core_pb2_grpc


def run_python_script(python_script_path, *args):
    fd = os.open('./../tmp/errors.log', os.O_WRONLY + os.O_CREAT + os.O_TRUNC)
    process = Popen([sys.executable, python_script_path, *args],
                    bufsize=0, stdin=PIPE, stdout=PIPE, stderr=fd, text=False)
    os.close(fd)
    return process


class TaskParameters:
    def __init__(self, log_lvl, data_folder, frm_app_data,
                 app_name, mod_name, mod_path, func_name, args):
        self.app_name = app_name
        self.mod_name = mod_name
        self.mod_path = mod_path
        self.func_name = func_name
        self.args = args
        self.log_lvl = log_lvl
        self.data_folder = data_folder
        self.frm_app_data = frm_app_data


class ServerCloudPA(core_pb2_grpc.CloudPyApiCoreServicer, TaskParameters):
    stop_flag: bool = False
    connection_alive: bool = False
    task_status: taskStatus = taskStatus.ST_UNKNOWN
    task_error: str = ''
    result: str
    logs_storage: list = []

    def TaskInit(self, request, context):
        _reply = TaskInitReply(appName=self.app_name,
                               modName=self.mod_name,
                               modPath=self.mod_path,
                               funcName=self.func_name,
                               config=TaskInitReply.cfgOptions(
                                   log_lvl=self.log_lvl,
                                   dataFolder=self.data_folder,
                                   frameworkAppData=self.frm_app_data))
        if self.args is not None:
            if isinstance(self.args, (list, tuple)):
                for _each_arg in self.args:
                    _reply.args.append(_each_arg)
            elif isinstance(self.args, str):
                _reply.args.append(self.args)
            else:
                raise TypeError('Only str, tuple of str and list of str types are supported.')
        return _reply

    def TaskStatus(self, request, context):
        if self.task_status != request.st_code:
            print(f'Server: pyfrm status changed from '
                  f'{taskStatus.Name(self.task_status)} to {taskStatus.Name(request.st_code)}')
        if self.task_error != request.error:
            print(f'Server: pyfrm error changed from `{self.task_error}` to `{request.error}`')
        self.task_status = request.st_code
        self.task_error = request.error
        return Empty()

    def TaskLog(self, request, context):
        mod_name = request.module if len(request.module) else 'Unknown'
        for record in request.content:
            print(f'Client: {mod_name} : {logLvl.Name(request.log_lvl)} : {record}')
            self.logs_storage.append(f'{mod_name}:{record}')
        return Empty()

    def TaskExit(self, request, context):
        self.result = request.result
        print('Server: receive TaskExit')
        print(f'Server: result length = {len(self.result)}, result: {self.result}')
        self.__stop()
        return Empty()

    def CmdStream(self, request_iterator, context):
        print('Server: starting cmd stream')
        self.connection_alive = True
        context.add_callback(self.__rpc_termination_callback)
        while not self.stop_flag:
            time.sleep(0.1)
        if self.connection_alive:
            print('Server: sending stop command')
            yield ServerCommand(id=ServerCommand.TASK_STOP)

    def __rpc_termination_callback(self):
        print('Server: connection closed')
        self.__stop()

    def __stop(self):
        self.connection_alive = False
        self.stop_flag = True


def srv_example(address, port, app_name, module_name, module_path, function_to_call, args=None):
    print('')
    server = grpc.server(futures.ThreadPoolExecutor(max_workers=2))
    servicer = ServerCloudPA(log_lvl=logLvl.DEBUG,
                             data_folder='./../tmp/data_folder',
                             frm_app_data='./../tmp/frm_app_data',
                             app_name=app_name,
                             mod_name=module_name,
                             mod_path=module_path,
                             func_name=function_to_call,
                             args=args
                             )
    core_pb2_grpc.add_CloudPyApiCoreServicer_to_server(servicer, server)
    connect_address = address
    if not address.startswith('unix:'):
        if not port:
            port = '0'
        listen_port = server.add_insecure_port(f'{address}:{port}')
        connect_address += ':' + str(listen_port)
    else:
        server.add_insecure_port(address)
    print(f'Server: connect address = {connect_address}')
    p_obj = run_python_script('pyfrm.py', connect_address)
    server.start()
    if server.wait_for_termination(timeout=5.0):
        if servicer.connection_alive:
            while True:
                if not server.wait_for_termination(timeout=0.5):
                    break
                if not servicer.connection_alive:
                    break
        elif servicer.task_status == taskStatus.ST_UNKNOWN:
            print('Server: timeout, client did not connected')
    server.stop(grace=0.5)
    try:
        p_obj.wait(timeout=3.0)
    except TimeoutExpired:
        print('Server: timeout waiting child process')
    print(f'Server: task finished with status {taskStatus.Name(servicer.task_status)}')
    print('Server: exiting')
    return servicer.task_status, servicer.task_error, servicer.result, servicer.logs_storage


if __name__ == '__main__':
    status, error, result, logs = srv_example('localhost', '0', 'hello_world', 'hello_world2',
                                              '../tests/python/apps_example/hello_world', 'func_hello_world')
    sys.exit(0)


import pytest


@pytest.mark.parametrize(
    "address, port, app_name, module_name, module_path, function_to_call, args,"
    "expected_status, expected_error, expected_result, logs_must_contain",
    (
        ('unix:./../tmp/test.sock', '', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_hello_world', None,
         taskStatus.ST_SUCCESS, '', 'OK', r'HelloWorld'),
        ('localhost', '0', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_hello_world', None,
         taskStatus.ST_SUCCESS, '', 'OK', r'HelloWorld'),
        ('0.0.0.0', '', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_hello_world', None,
         taskStatus.ST_SUCCESS, '', 'OK', r'HelloWorld'),
        ('[::]', '60051', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_hello_world', None,
         taskStatus.ST_SUCCESS, '', 'OK', r'HelloWorld'),
    ),
    ids=(
        'unix',
        'local_random',
        'all_random',
        'ip6_port',
    ),
)
def test_hello_world(address, port, app_name, module_name, module_path, function_to_call, args,
                     expected_status, expected_error, expected_result, logs_must_contain):
    _status, _error, _result, _logs = srv_example(address, port,
                                                  app_name, module_name, module_path,
                                                  function_to_call, args)
    assert _status == expected_status
    assert _error == expected_error
    assert _result == expected_result
    if logs_must_contain:
        assert len([s for s in _logs if re.search(logs_must_contain, s) is not None]) > 0


@pytest.mark.parametrize(
    "address, port, app_name, module_name, module_path, function_to_call, args,"
    "expected_status, expected_error, expected_result, logs_must_contain",
    (
        ('unix:./../tmp/test.sock', '', 'invalid_app_name', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_hello_world', None,
         taskStatus.ST_INIT_ERROR, 'Directory with python packages for app cannot be accessed.', '', r''),
        ('localhost', '0', 'hello_world', 'invalid_module_name',
         '../tests/python/apps_example/hello_world', 'func_hello_world', None,
         taskStatus.ST_ERROR, 'Error loading invalid_module_name module.', '', r'cpa_core:Error.*module'),
        ('0.0.0.0', '', 'hello_world', 'hello_world',
         '../tests/python/invalid_path', 'func_hello_world', None,
         taskStatus.ST_ERROR, 'Error loading hello_world module.', '', r'cpa_core:Error.*module'),
        ('[::]', '60051', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'invalid_function', None,
         taskStatus.ST_ERROR, 'Function invalid_function not found.', '', r'cpa_core:Function.*not\sfound'),
        ('[::]', '0', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_hello_world', 'args_error',
         taskStatus.ST_EXCEPTION, 'TypeError', '', r'cpa_core:Exception\(TypeError\)'),
    ),
    ids=(
        'unix__invalid_app_name',
        'local_random__invalid_module_name',
        'all_random__invalid_path',
        'ip6_port__invalid_function',
        'ip6_random__args_error',
    ),
)
def test_error_handling(address, port, app_name, module_name, module_path, function_to_call, args,
                        expected_status, expected_error, expected_result, logs_must_contain):
    _status, _error, _result, _logs = srv_example(address, port,
                                                  app_name, module_name, module_path,
                                                  function_to_call, args)
    assert _status == expected_status
    assert _error == expected_error
    assert _result == expected_result
    if logs_must_contain:
        assert len([s for s in _logs if re.search(logs_must_contain, s) is not None]) > 0


@pytest.mark.parametrize(
    "address, port, app_name, module_name, module_path, function_to_call, args,"
    "expected_status, expected_error, expected_result, logs_must_contain",
    (
        ('unix:./../tmp/test.sock', '', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_hello_world_fixed_two_args', ('one_', 'two'),
         taskStatus.ST_SUCCESS, '', 'one_two', r''),
        ('unix:./../tmp/test.sock', '', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_hello_world_args', None,
         taskStatus.ST_SUCCESS, '', 'get 0 argument(s)', r''),
        ('localhost', '0', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_hello_world_args', '1',
         taskStatus.ST_SUCCESS, '', 'get 1 argument(s)', r'hello_world_args:\(\'1\',\)'),
        ('0.0.0.0', '', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_hello_world_args', ('1', '2'),
         taskStatus.ST_SUCCESS, '', 'get 2 argument(s)', r'hello_world_args:\(\'1\',\s\'2\'\)'),
        ('[::]', '60051', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_no_result', None,
         taskStatus.ST_SUCCESS, '', '', r'cpa_core:Result length=`None`'),
        ('[::]', '0', 'hello_world', 'hello_world',
         '../tests/python/apps_example/hello_world', 'func_exception', None,
         taskStatus.ST_EXCEPTION, 'ValueError', '', r'cpa_core:Exception\(ValueError\):`TEST`'),
    ),
    ids=(
        'unix__fixed_two_args',
        'unix__none_args',
        'local_random__one_arg',
        'all_random__two_args',
        'ip6_port__no_result',
        'ip6_random__exception',
    ),
)
def test_other_basics(address, port, app_name, module_name, module_path, function_to_call, args,
                      expected_status, expected_error, expected_result, logs_must_contain):
    _status, _error, _result, _logs = srv_example(address, port,
                                                  app_name, module_name, module_path,
                                                  function_to_call, args)
    assert _status == expected_status
    assert _error == expected_error
    assert _result == expected_result
    if logs_must_contain:
        assert len([s for s in _logs if re.search(logs_must_contain, s) is not None]) > 0
