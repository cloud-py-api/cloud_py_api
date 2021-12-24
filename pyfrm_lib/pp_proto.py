"""
Wrappers around `protobuf` for communication between php cli module and python. Works on top of transport layer.
"""

from enum import Enum
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


# def slog(log_level: LogLvl, app_name: str, *args, **kwargs):
#     print(f'log_level={log_level}, app_name={app_name}', *args, **kwargs)

class CloudPP(InterCom):
    init_data = None
    _req = None
    _exit_done: bool = False

    def __init__(self, process=None):
        super().__init__(process)
        if not self._get_init_task():
            raise RuntimeError('Cannot establish connect with server.')

    def __del__(self):
        if not self._exit_done:
            self.exit()

    def _get_init_task(self) -> bool:
        self._req = Request()
        self._req.classId = msgClass.INIT_TASK
        if not self._send():
            return False
        if not self._get():
            return False
        self.init_data = InitTask()
        self.init_data.ParseFromString(self.proto_data)
        return True

    def set_status(self, status: taskStatus, error: str = '') -> bool:
        self._req = TaskStatus()
        self._req.classId = msgClass.TASK_STATUS
        self._req.st_code = status
        self._req.errDescription = error
        return self._send()

    def exit(self, msg: str = '') -> None:
        self._exit_done = True
        self._req = TaskExit()
        self._req.classId = msgClass.TASK_EXIT
        self._req.msgText = msg
        self._send()

    def log(self, log_lvl: int, mod_name: str, content: list) -> None:
        if content is None:
            raise ValueError('no log content')
        if self.init_data.config.log_lvl <= log_lvl:
            self._req = Log()
            self._req.classId = msgClass.LOG
            self._req.log_lvl = log_lvl
            self._req.sModule = mod_name if mod_name is not None else ''
            for elem in content:
                self._req.Content.append(elem)
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
