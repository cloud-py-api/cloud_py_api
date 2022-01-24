import signal
import sys


from helpers import print_err, debug_msg
from install import get_options, get_python_info, get_pip_info, add_python_path, get_core_site_packages


def signal_handler(signum=None, _frame=None):
    print_err('Got signal:', signum)
    sys.exit(0)


if __name__ == '__main__':
    debug_msg('__main__: started')
    for sig in [signal.SIGINT, signal.SIGQUIT, signal.SIGTERM, signal.SIGHUP]:
        signal.signal(sig, signal_handler)
    options = get_options()
    options['app_data'] = sys.argv[1:2][0]
    options['python'] = get_python_info()
    options['pip'] = get_pip_info()
    add_python_path(get_core_site_packages(), first=True)
    from pyfrm import pyfrm_main
    r = pyfrm_main(sys.argv[1:2][0], sys.argv[2:3][0])
    debug_msg(f'__main__(pyfrm): finished, exit_code = {r.value}:{r.name}')
    sys.exit(0)
