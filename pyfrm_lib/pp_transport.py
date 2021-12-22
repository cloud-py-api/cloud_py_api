"""
Transport layer of communication between php and python.
"""

import sys
from typing import Union


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


class InterCom:
    packet_len: int
    packet_data: bytes
    error: str
    _process = None

    def __init__(self, process=None):
        self._init_v()
        if process is not None:
            self._process = process

    def get_msg(self) -> bool:
        self._init_v()
        try:
            _packet_size: bytes = b''
            while len(_packet_size) < 8:
                new_data = self._read_nbytes(8 - len(_packet_size))
                if new_data is not None:
                    _packet_size += new_data
            self.packet_len = int.from_bytes(_packet_size, byteorder='big', signed=False)
            while self.packet_len - len(self.packet_data) > 0:
                new_data = self._read_nbytes(self.packet_len - len(self.packet_data))
                if new_data is not None:
                    self.packet_data += new_data
            return True
        except BrokenPipeError:
            self.error = 'BrokenPipe'
        except OSError as exc:
            self.error = exc.strerror
        return False

    def send_msg(self, data: bytes) -> bool:
        packet_size = len(data).to_bytes(8, byteorder='big', signed=False)
        try:
            if self._process is None:
                sys.stdout.buffer.write(packet_size)
                sys.stdout.buffer.write(data)
            else:
                self._process.stdin.write(packet_size)
                self._process.stdin.write(data)
            return True
        except BrokenPipeError:
            self.error = 'BrokenPipe'
        except OSError as exc:
            self.error = exc.strerror
        return False

    def _init_v(self):
        self.packet_len = 0
        self.packet_data = b''
        self.error = ''

    def _read_nbytes(self, nbytes: int) -> Union[bytes, None]:
        if self._process is None:
            return sys.stdin.buffer.read(nbytes)
        else:
            return self._process.stdout.read(nbytes)
