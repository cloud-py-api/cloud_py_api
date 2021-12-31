import signal
import sys
import time
from os import getpid

from grpc import RpcError
from core_pb2 import logLvl, taskStatus
from helpers import print_err, debug_msg
from core_proto import ClientCloudPA


def signal_handler(signum=None, _frame=None):
    print_err('Got signal:', signum)
    sys.exit(0)


def true_main(connect_address: str, auth: str = '') -> int:
    cloud = None
    try:
        cloud = ClientCloudPA(connect_address, auth)
        cloud.set_status(taskStatus.ST_IN_PROGRESS)
        cloud.log(logLvl.DEBUG, 'cpa_core', f'Started with pid={getpid()}')

        cloud.log(logLvl.DEBUG, 'TEST', 'sleeping')
        time.sleep(5.0)
        cloud.log(logLvl.DEBUG, 'TEST', 'waking up')

        cloud.set_status(taskStatus.ST_SUCCESS)
        cloud.exit('result_ok')
    except RpcError as exc:
        ret_val = 1
        if cloud is None:
            print_err('Cant establish connect to server')
            ret_val = 2
        print_err(str(exc))
        return ret_val
    return 0


if __name__ == '__main__':
    for sig in [signal.SIGINT, signal.SIGQUIT, signal.SIGTERM, signal.SIGHUP]:
        signal.signal(sig, signal_handler)
    debug_msg('started')
    sys.exit(true_main(sys.argv[1:2][0]))
