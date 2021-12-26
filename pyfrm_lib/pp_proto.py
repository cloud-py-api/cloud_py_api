"""
Wrappers around `protobuf` for communication between php cli module and python. Works on top of transport layer.
"""

import os
import signal
import time
from typing import Union
from threading import Event, Thread, Lock
from pyfrm_lib.pp_transport import InterCom
from pyfrm_lib.proto.core_pb2 import *
from pyfrm_lib.helpers import print_err


# @copyright Copyright (c) 2022 Andrey Borysenko <andrey18106x@gmail.com>
#
# @copyright Copyright (c) 2022 Alexander Piskun <bigcat88@icloud.com>
#
# @author 2022 Alexander Piskun <bigcat88@icloud.com>
#
# @license AGPL-3.0-or-later
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.


_STATE_INTERVAL = 4.5


def _signal_exit():
    time.sleep(0.8)
    os.kill(os.getpid(), signal.SIGTERM)


class CloudPP(InterCom):
    init_data = None
    _req = None
    _exit_sent: bool = False
    _thread = None
    _exit_event = Event()
    _comm_lock = Lock()

    def __init__(self, process=None):
        super().__init__(process)
        if not self._get_task_init():
            raise RuntimeError('Cannot establish connect with server.')
        self._create_background_thread()   # destructor don't called due to this...

    def __del__(self):
        if not self._exit_sent:
            self.exit()

    def _get_task_init(self) -> bool:
        self._req = Request()
        self._req.classId = msgClass.TASK_INIT
        if not self._send():
            return False
        if not self._get():
            return False
        self.init_data = TaskInitReply()
        self.init_data.ParseFromString(self.proto_data)
        return True

    def set_status(self, status: taskStatus, error: str = '') -> bool:
        with self._comm_lock:
            self._req = TaskStatus()
            self._req.classId = msgClass.TASK_STATUS
            self._req.st_code = status
            self._req.error = error
            return self._send()

    def exit(self, msg: str = '') -> None:
        self._exit_sent = True
        self._exit_event.set()
        self._thread.join(timeout=1.0)
        self._req = TaskExit()
        self._req.classId = msgClass.TASK_EXIT
        self._req.msgText = msg
        self._send()

    def log(self, log_lvl: int, mod_name: str, content: Union[str, list]) -> None:
        if content is None:
            raise ValueError('no log content')
        if self.init_data.config.log_lvl <= log_lvl:
            with self._comm_lock:
                self._req = TaskLog()
                self._req.classId = msgClass.TASK_LOG
                self._req.log_lvl = log_lvl
                self._req.sModule = mod_name if mod_name is not None else ''
                if isinstance(content, str):
                    self._req.content.append(content)
                else:
                    for elem in content:
                        self._req.content.append(elem)
                self._send()

    def _get(self) -> bool:
        if not self.get_msg():
            req_id = self._req.classId if self._req.classId is not None else -1
            print_err(f'Receive reply for {req_id} failed. {self.error}')
            return False
        return True

    def _send(self) -> bool:
        if self._req is None:
            raise ValueError('[DEBUG]:No request.')
        if self.send_msg(self._req.SerializeToString()):
            return True
        print_err(f'Send {self._req.classId} failed. {self.error}')
        return False

    def _background_thread(self):
        while True:
            if self._exit_event.wait(timeout=float(_STATE_INTERVAL)):
                break
            with self._comm_lock:
                if self._exit_event.is_set():
                    break
                _get_state = Request()
                _get_state.classId = msgClass.TASK_GET_STATE
                if not self.send_msg(_get_state.SerializeToString()):
                    print_err('Cant request state. Exit.')
                    self._exit_event.set()
                    break
                reply, err = self._get_msg_to_buf()
                if err:
                    print_err('Cant receive state. Exit.')
                    self._exit_event.set()
                    break
                new_state = TaskGetStateReply()
                new_state.ParseFromString(reply)
                if new_state.bStop:
                    self._exit_event.set()
                    Thread(target=_signal_exit, daemon=True).start()
                    break

    def _create_background_thread(self):
        self._thread = Thread(target=self._background_thread, daemon=True)
        self._thread.start()
