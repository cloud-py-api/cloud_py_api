"""
Run nc_pyfrm and send Initialize, read reply and debug logs, and close pipe to test exit.
"""

import sys
from pyfrm_lib.pp_transport import InterCom
from pyfrm_lib.proto.core_pb2 import *
from t_helpers import run_python_script


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


class TestCloudPP(InterCom):
    req = Request()
    reply: bytes
    task_status: int = -1
    task_error: str = ''
    stop_msg_cycle: bool = False

    def __init__(self, process=None):
        super().__init__(process)

    def process_msgs(self, n_count: int = -1):
        while True:
            if not self.get_msg():
                print(f'Server: get_msg fails, error:{self.error}')
                break
            self.reply = b''
            self.req.ParseFromString(self.proto_data)
            msg_id = self.req.classId
            print(f'Server: Process request with id = {msg_id}')
            if msg_id == INIT_TASK:
                self.process_init_task()
            elif msg_id == TASK_STATUS:
                self.process_task_status()
            elif msg_id == TASK_EXIT:
                self.process_task_exit()
            elif msg_id == GET_STATE:
                self.process_get_state()
            elif msg_id == LOG:
                self.process_log()
            elif msg_id == GET_FILE_CONTENT:
                self.process_get_file_content()
            elif msg_id == SELECT:
                self.process_select()
            else:
                raise KeyError('Unknown request id.')
            if len(self.reply):
                if not self.send_msg(self.reply):
                    print(f'Server: send_msg fails, error:{self.error}')
                    break
            if self.stop_msg_cycle:
                break
            if n_count != -1:
                n_count -= 1
                if not n_count:
                    break

    def process_init_task(self):
        init_data = InitTask()
        init_data.classId = INIT_TASK
        init_data.AppPath = 'PathToTargetApp'
        init_data.args.append('ArgN1')
        init_data.args.append('ArgN2')
        init_data.config.LogLvl = 0
        init_data.config.DataFolder = '/var/www/nextcloud/data'
        self.reply = init_data.SerializeToString()

    def process_task_status(self):
        new_status = TaskStatus()
        new_status.ParseFromString(self.proto_data)
        if self.task_status != new_status.st_code:
            print(f'Server: pyfrm status changed from {self.task_status} to {new_status.st_code}')
        if self.task_error != new_status.errDescription:
            print(f'Server: pyfrm error changed from `{self.task_error}` to `{new_status.errDescription}`')
        self.task_status = new_status.st_code
        self.task_error = new_status.errDescription

    def process_task_exit(self):
        exit_status = TaskExit()
        exit_status.ParseFromString(self.proto_data)
        print(f'Server: pyfrm exited. OptMessage:`{exit_status.msgText}`')
        self.stop_msg_cycle = True


if __name__ == '__main__':
    p_obj = run_python_script('pyfrm/nc_pyfrm.py')
    cloud = TestCloudPP(p_obj)
    cloud.process_msgs(n_count=-1)
    p_obj.wait()
    sys.exit(0)
