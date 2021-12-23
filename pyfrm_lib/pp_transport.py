"""
Transport layer of communication between php and python.
"""

import sys


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
    """Class representing both server(for tests) and client.
        Packet struct:
        8 bytes - size of packet(_PACKET_SIZE), not including this field. Currently it is sizeof(X).
        X bytes - google proto data.

    Return False for get_msg, send_msg if error or communication channel was closed.
    `error` field in that case will contain `ClosedPipe`, `BrokenPipe` or OS text error.
    Client must stop it work and shutdown itself if main channel for communication with server was lost.
    When True returned for get_msg, message itself(proto data) will be in `proto_data`.
    """
    proto_data: bytes
    error: str
    _process = None
    _PACKET_SIZE = 8

    def __init__(self, process=None):
        if process is not None:
            self._process = process

    def get_msg(self) -> bool:
        self.error = ''
        self.proto_data = b''
        try:
            packet_size = self._read(self._PACKET_SIZE)
            if len(packet_size) == self._PACKET_SIZE:
                packet_data_size = int.from_bytes(packet_size, byteorder='big', signed=False)
                self.proto_data = self._read(packet_data_size)
                if len(self.proto_data) == packet_data_size:
                    return True
            self.error = 'ClosedPipe'
        except BrokenPipeError:
            self.error = 'BrokenPipe'
        except OSError as exc:
            self.error = exc.strerror
        return False

    def send_msg(self, data: bytes) -> bool:
        self.error = ''
        packet_size = len(data).to_bytes(self._PACKET_SIZE, byteorder='big', signed=False)
        try:
            if self._write(packet_size):
                if self._write(data):
                    return True
            self.error = 'ClosedPipe'
            return False
        except BrokenPipeError:
            self.error = 'BrokenPipe'
        except OSError as exc:
            self.error = exc.strerror
        return False

    def _read(self, n: int) -> bytes:
        if self._process is None:
            return sys.stdin.buffer.read(n)
        else:
            return self._process.stdout.read(n)

    def _write(self, data: bytes) -> bool:
        if self._process is None:
            written = sys.stdout.buffer.write(data)
        else:
            written = self._process.stdin.write(data)
        return True if written == len(data) else False
