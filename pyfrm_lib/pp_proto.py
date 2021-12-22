"""
Wrappers around `protobuf` for communication between php cli module and python. Works on top of transport layer.
"""

import sys
from typing import AnyStr, Union
from enum import Enum
from pyfrm_lib.pp_transport import InterCom
from pyfrm_lib.proto.core_pb2 import Initialize, InitializationResult


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


class LogLvl(Enum):
    """Possible log level values."""
    DEBUG = 0
    INFO = 1
    WARN = 2
    ERROR = 3
    FATAL = 4


def slog(log_level: LogLvl, app_name: str, *args, **kwargs):
    print(f'log_level={log_level}, app_name={app_name}', *args, **kwargs)


class CloudPP(InterCom):
    init_data = None

    def __init__(self, process=None):
        super().__init__(process)

    def process_init_msg(self) -> bool:
        if not self.get_msg():
            raise "TODO"
        self.init_data = Initialize()
        self.init_data.ParseFromString(self.packet_data)
        reply = InitializationResult()
        reply.Status = 0
        reply.ErrDescription = 'TEST'
        if not self.send_msg(reply.SerializeToString()):
            raise "TODO"
        return True
