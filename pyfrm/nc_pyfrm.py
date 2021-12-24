"""
Entry point for NC Python Framework module.
"""

import os
import signal
import sys
from pyfrm_lib.helpers import print_err
from pyfrm_lib.pp_proto import CloudPP
from pyfrm_lib.proto.core_pb2 import taskStatus


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


def signal_handler(signum=None, _frame=None):
    """Ideally we want gracefully shutdown via pipe `exit` function."""
    print_err('Got signal:', signum)
    sys.exit(0)


if __name__ == '__main__':
    for sig in [signal.SIGINT, signal.SIGQUIT, signal.SIGTERM, signal.SIGHUP]:
        signal.signal(sig, signal_handler)
    # slog(LogLvl.DEBUG, 'cpa_core', f'Started with pid={os.getpid()}')
    try:
        cloud = CloudPP()
    except RuntimeError:
        sys.exit(1)
    cloud.log(0, 'TEST', ['string1', 'string2'])
    cloud.set_status(taskStatus.ST_IN_PROGRESS, 'ignored!')
    cloud.set_status(taskStatus.ST_SUCCESS)
    sys.exit(0)
