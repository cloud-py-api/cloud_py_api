import signal
import sys
import time

from core_pb2 import logLvl, taskStatus
from helpers import print_err, debug_msg
from core_proto import *


def signal_handler(signum=None, _frame=None):
    print_err('Got signal:', signum)
    sys.exit(0)


def main(connect_address: str, auth: str = '') -> int:
    try:
        cloud = ClientCloudPA(connect_address, auth)
    except RuntimeError:
        return 1
    cloud.log(logLvl.DEBUG, 'cpa_core', f'Started with pid={os.getpid()}')
    cloud.set_status(taskStatus.ST_IN_PROGRESS, 'ignored!')
    cloud.log(logLvl.DEBUG, 'TEST', 'sleeping')
    time.sleep(5.0)
    cloud.log(logLvl.DEBUG, 'TEST', 'waking up')
    cloud.set_status(taskStatus.ST_SUCCESS)
    cloud.exit('result_ok')
    time.sleep(2.0)
    return 0


if __name__ == '__main__':
    for sig in [signal.SIGINT, signal.SIGQUIT, signal.SIGTERM, signal.SIGHUP]:
        signal.signal(sig, signal_handler)
    debug_msg('started')
    sys.exit(main(sys.argv[1:2][0]))
