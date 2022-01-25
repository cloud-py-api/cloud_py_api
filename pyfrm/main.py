import signal
import sys


from helpers import print_err, debug_msg
from install import get_options, get_python_info, get_pip_info, add_python_path, get_python_site_packages


def signal_handler(signum=None, _frame=None):
    print_err('Got signal:', signum)
    sys.exit(0)


if __name__ == '__main__':
    # import subprocess
    # import os
    # print('main')
    # import site
    # site.main()
    # print(site.getsitepackages())
    # print(site.getusersitepackages())
    # print(sys.prefix)
    # print(sys.exec_prefix)
    # print(sys.path)
    # sys.path.insert(0, '/var/www/nextcloud/data/appdata_ocs30ydgi7y8/cloud_py_api/local/bin/')
    # sys.path.insert(0, '/var/www/nextcloud/data/appdata_ocs30ydgi7y8/cloud_py_api/local/lib/python3.7/site-packages/')
    # print('A:', sys.path)
    # child_env = dict(os.environ)
    # child_env["PYTHONPATH"] = '/var/www/nextcloud/data/appdata_ocs30ydgi7y8/cloud_py_api/local/lib/python3.7/site-packages'
    # print(child_env)
    # try:
    #     _result = subprocess.run([sys.executable, '-m', 'pip', '--version'],
    #                              stderr=subprocess.PIPE, stdout=subprocess.PIPE, check=True, env=child_env)
    #     print(_result.stdout.decode('utf-8').rstrip('\n'))
    # except (OSError, ValueError, TypeError) as _exception_info:
    #     print(_exception_info)
    # sys.exit(0)
    debug_msg('__main__: started')
    for sig in [signal.SIGINT, signal.SIGQUIT, signal.SIGTERM, signal.SIGHUP]:
        signal.signal(sig, signal_handler)
    options = get_options()
    options['app_data'] = sys.argv[1:2][0]
    options['python'] = get_python_info()
    options['pip'] = get_pip_info()
    add_python_path(get_python_site_packages(), first=True)
    from pyfrm import pyfrm_main
    r = pyfrm_main(sys.argv[1:2][0], sys.argv[2:3][0])
    debug_msg(f'__main__(pyfrm): finished, exit_code = {r.value}:{r.name}')
    sys.exit(0)
