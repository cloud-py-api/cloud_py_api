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
    task_status: taskStatus = taskStatus.ST_UNKNOWN     # in php this will be in DB
    task_error: str = ''                                # this field will be in DB too.
    stop_msg_cycle: bool = False                        # this is only to make code beautiful and for tests.
    states_updates_before_exit: int = 15                # for tests, count of states replies before signal to exit.

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
            print(f'Server: Process {msgClass.Name(msg_id)} request.')
            if msg_id == msgClass.INIT_TASK:
                self.process_init_task()
            elif msg_id == msgClass.TASK_STATUS:
                self.process_task_status()
            elif msg_id == msgClass.TASK_EXIT:
                self.process_task_exit()
            elif msg_id == msgClass.GET_STATE:
                self.process_get_state()
            elif msg_id == msgClass.LOG:
                self.process_log()
            elif msg_id == msgClass.GET_FILE_CONTENT:
                self.process_get_file_content()
            elif msg_id == msgClass.SELECT:
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
        init_data.classId = msgClass.INIT_TASK
        init_data.AppPath = 'PathToTargetApp'
        init_data.args.append('ArgN1')
        init_data.args.append('ArgN2')
        init_data.config.log_lvl = logLvl.DEBUG
        init_data.config.DataFolder = '/var/www/nextcloud/data'
        self.reply = init_data.SerializeToString()

    def process_task_status(self):
        new_status = TaskStatus()
        new_status.ParseFromString(self.proto_data)
        if self.task_status != new_status.st_code:
            print(f'Server: pyfrm status changed from '
                  f'{taskStatus.Name(self.task_status)} to {taskStatus.Name(new_status.st_code)}')
        if self.task_error != new_status.errDescription:
            print(f'Server: pyfrm error changed from `{self.task_error}` to `{new_status.errDescription}`')
        self.task_status = new_status.st_code
        self.task_error = new_status.errDescription

    def process_task_exit(self):
        exit_status = TaskExit()
        exit_status.ParseFromString(self.proto_data)
        print(f'Server: pyfrm exited. OptMessage:`{exit_status.msgText}`')
        self.stop_msg_cycle = True

    def process_get_state(self):
        self.states_updates_before_exit -= 1
        task_state = GetState()
        task_state.classId = msgClass.GET_STATE
        task_state.bStop = True if self.states_updates_before_exit == 0 else False
        self.reply = task_state.SerializeToString()

    def process_log(self):
        log_data = Log()
        log_data.ParseFromString(self.proto_data)
        mod_name = log_data.sModule if len(log_data.sModule) else 'Unknown'
        for record in log_data.Content:
            print(f'Client: {mod_name} : {logLvl.Name(log_data.log_lvl)} : {record}')

    def process_get_file_content(self):
        get_file = GetFileContent()
        get_file.ParseFromString(self.proto_data)
        print(f'Server: request for file with userID={get_file.UserID} and fileID={get_file.FileID}.')
        # for tests: send random data as reply for userID=1. for userID=2 send error, no such file.


if __name__ == '__main__':
    p_obj = run_python_script('pyfrm/nc_pyfrm.py')
    cloud = TestCloudPP(p_obj)
    cloud.process_msgs(n_count=-1)
    p_obj.wait()
    sys.exit(0)
