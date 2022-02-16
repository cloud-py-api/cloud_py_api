import signal
import sys
import logging
from argparse import ArgumentParser

from install import get_options, get_python_info, get_pip_info, add_python_path, get_site_packages


Log = logging.getLogger('pyfrm')
Log.propagate = False


class PyFrmLogHandler(logging.Handler):
    __log_levels = {'DEBUG': 0, 'INFO': 1, 'WARN': 2, 'ERROR': 3, 'FATAL': 4}

    def emit(self, record):
        self.format(record)
        __content = record.message if record.funcName == '<module>' else record.funcName + ': ' + record.message
        if record.exc_text is not None:
            __content += '\n' + record.exc_text
        __log_lvl = self.__log_levels.get(record.levelname)
        __module = record.module if record.name == 'root' else record.name
        print({'log_lvl': __log_lvl, 'module': __module, 'content': __content}, file=sys.stderr)


def signal_handler(signum=None, _frame=None):
    print(f'Got signal:{signum}', file=sys.stderr)
    sys.exit(0)


if __name__ == '__main__':
    parser = ArgumentParser(description='Module for executing NC python apps.', add_help=True)
    parser.add_argument('appdata', action='store', type=str,
                        help='Absolute path to cloud_py_api folder in appdata_xxx.')
    levels = ('DEBUG', 'INFO', 'WARN', 'ERROR', 'FATAL')
    logging.addLevelName(30, 'WARN')
    logging.addLevelName(50, 'FATAL')
    parser.add_argument('connect_address', action='store', type=str,
                        help='Address[:port] of php server to connect to(network or unix socket).')
    parser.add_argument('--loglvl', default='ERROR', type=str, choices=levels, help='Default=ERROR')
    # parser.add_argument('--taskinit', default='', type=str, help='Light version when grpc for php is not available.')
    args = parser.parse_args()
    Log.setLevel(level=args.loglvl)
    Log.addHandler(PyFrmLogHandler())
    logging.getLogger('pyfrm.install').setLevel(level=args.loglvl)
    logging.getLogger('pyfrm.install').addHandler(PyFrmLogHandler())
    Log.debug('__main__: started')
    for sig in [signal.SIGINT, signal.SIGQUIT, signal.SIGTERM, signal.SIGHUP]:
        signal.signal(sig, signal_handler)
    options = get_options()
    options['app_data'] = args.appdata
    options['python'] = get_python_info()
    options['pip'] = get_pip_info()
    add_python_path(get_site_packages(), first=True)
    from pyfrm import pyfrm_main
    r = pyfrm_main(args.appdata, args.connect_address)
    Log.debug(f'__main__: finished, exit_code = {r.value}:{r.name}')
    sys.exit(0)
