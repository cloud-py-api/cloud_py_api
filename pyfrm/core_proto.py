import time
import signal
import os
from threading import Thread
from typing import Union
from subprocess import TimeoutExpired

import grpc
from core_pb2 import taskStatus, Empty, \
    ServerCommand, TaskSetStatusRequest, TaskExitRequest, TaskLogRequest
import core_pb2_grpc
from helpers import debug_msg


def _signal_exit():
    time.sleep(1.6)
    os.kill(os.getpid(), signal.SIGTERM)


class ClientCloudPA:
    task_init_data = None
    _main_channel = None
    _main_stub = None
    _exit_sent: bool = False
    _cmd_thread = None

    def __init__(self, connect_address: str, auth: str):
        self._main_channel = grpc.insecure_channel(target=connect_address,
                                                   options=[('grpc.enable_retries', 1),
                                                            ('grpc.keepalive_timeout_ms', 10000)
                                                            ])
        self._main_stub = core_pb2_grpc.CloudPyApiCoreStub(self._main_channel)
        self.task_init_data = self._main_stub.TaskInit(Empty())
        debug_msg('connected')
        self._cmd_thread = Thread(target=self.__listen_for_commands__, daemon=False)
        self._cmd_thread.start()

    def __del__(self):
        debug_msg('destructor')
        if not self._exit_sent:
            self.exit()

    def __listen_for_commands__(self):
        debug_msg('back_thread: waiting for server commands')
        try:
            for cmd in self._main_stub.CmdStream(Empty()):
                debug_msg(f'cmd.id = {ServerCommand.cmd_id.Name(cmd.id)}')
                if cmd.id == ServerCommand.cmd_id.TASK_STOP:
                    break
        except (grpc.RpcError, ValueError) as exc:
            debug_msg(str(exc))
        if not self._exit_sent:
            Thread(target=_signal_exit, daemon=True).start()

    def set_status(self, status: taskStatus, error: str = '') -> None:
        self._main_stub.TaskStatus(TaskSetStatusRequest(st_code=status,
                                                        error=error))

    def exit(self, result=None) -> None:
        debug_msg('exit()')
        self._exit_sent = True
        try:
            self._main_stub.TaskExit(TaskExitRequest(result=result))
            self._main_channel.close()
        except grpc.RpcError as exc:
            debug_msg(str(exc))
        if self._cmd_thread is not None:
            try:
                self._cmd_thread.join(timeout=1.0)
            except TimeoutExpired as exc:
                debug_msg(str(exc))

    def log(self, log_lvl: int, mod_name: str, content: Union[str, list]) -> None:
        if content is None:
            raise ValueError('no log content')
        if self.task_init_data.config.log_lvl <= log_lvl:
            _log_content = []
            if isinstance(content, str):
                _log_content.append(content)
            else:
                for elem in content:
                    _log_content.append(elem)
            self._main_stub.TaskLog(TaskLogRequest(log_lvl=log_lvl,
                                                   module=mod_name if mod_name is not None else '',
                                                   content=_log_content))
