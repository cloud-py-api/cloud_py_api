import sys
import os
import time
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
    def __init__(self, log_lvl, data_folder, frm_app_data, file_path, func_name, *args):
        self.file_path = file_path
        self.func_name = func_name
        self.args = args
        self.log_lvl = log_lvl
        self.data_folder = data_folder
        self.frm_app_data = frm_app_data


class ServerCloudPA(core_pb2_grpc.CloudPyApiCoreServicer, TaskParameters):
    stop_flag: bool = False
    connection_alive: bool = False
    result: str = ''
    task_status: taskStatus = taskStatus.ST_UNKNOWN
    task_error: str = ''

    def TaskInit(self, request, context):
        return TaskInitReply(filePath=self.file_path,
                             funcName=self.func_name,
                             args=self.args,
                             config=TaskInitReply.cfgOptions(
                                 log_lvl=self.log_lvl,
                                 dataFolder=self.data_folder,
                                 frameworkAppData=self.frm_app_data))

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
        return Empty()

    def TaskExit(self, request, context):
        self.result = request.result
        print(f'Server: receive TaskExit, with result = <{self.result}>')
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


# @pytest.mark.parametrize(
#     "address, port, app_file, app_func",
#     (
#         ('unix:./../tmp/test.sock', '', 'hello_world.py', ''),
#         ('localhost', '', 'hello_world.py', ''),
#         ('0.0.0.0', '', 'hello_world.py', ''),
#         ('[::]', '60051', 'hello_world.py', ''),
#     ),
#     ids=(
#         'unix',
#         'local_random',
#         'all_random',
#         'port',
#     ),
# )
# def xxx_t_est_basic(address: str = 'localhost', port: str = '0'):
#     pass

if __name__ == '__main__':
    address = 'unix:./../tmp/test.sock'
    port = '0'
    app_file = 'hello_word.py'
    app_func = ''
    server = grpc.server(futures.ThreadPoolExecutor(max_workers=2))
    servicer = ServerCloudPA(log_lvl=logLvl.DEBUG,
                             data_folder='./../tmp/data_folder',
                             frm_app_data='./../tmp/frm_app_data',
                             file_path=f'../tests/python/apps_example/{app_file}',
                             func_name=app_func,
                             )
    core_pb2_grpc.add_CloudPyApiCoreServicer_to_server(servicer, server)
    connect_address = address
    if port and port != '0':
        listen_port = server.add_insecure_port(f'{address}:{port}')
    else:
        listen_port = server.add_insecure_port(address)
    if not address.startswith('unix:'):
        connect_address += ':' + listen_port
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
        else:
            print('Server: timeout, client did not connected')
    server.stop(grace=0.5)
    try:
        p_obj.wait(timeout=3.0)
    except TimeoutExpired:
        print('Server: timeout waiting child process')
    print('Server: exiting')
    sys.exit(0)
