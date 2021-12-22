"""
Run nc_pyfrm and send Initialize, read reply and debug logs, and close pipe to test exit.
"""

import sys
from pyfrm_lib.pp_transport import InterCom
from pyfrm_lib.proto.core_pb2 import Initialize, InitializationResult
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
    init_data = None

    def __init__(self, process=None):
        super().__init__(process)

    def process_init_msg(self) -> bool:
        init_data = Initialize()
        init_data.ModulePath = 'ThisIsModulePath'
        init_data.ModuleName = 'ThisIsModuleName'
        init_data.EP_File = 'ThisIsFileWithEP'
        init_data.EP_Function = 'This func we will call'
        init_data.config.LogLvl = 0
        init_data.config.DataFolder = '/var/www/nextcloud/data'
        init_data.args.append('ArgN1')
        init_data.args.append('ArgN2')
        if not self.send_msg(init_data.SerializeToString()):
            raise f'send_msg fails, error:{self.error}'
        if not self.get_msg():
            raise f'get_msg fails, error:{self.error}'
        reply = InitializationResult()
        reply.ParseFromString(self.packet_data)
        print(f'Status:{reply.Status}')
        print(f'ErrDescription:{reply.ErrDescription}')
        return True


if __name__ == '__main__':
    p_obj = run_python_script('pyfrm/nc_pyfrm.py')
    cloud = TestCloudPP(p_obj)
    cloud.process_init_msg()
    p_obj.wait()
    sys.exit(0)
