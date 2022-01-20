"""
Cloud_Py_Api self install module.
"""

import sys
import platform
from subprocess import run, PIPE, DEVNULL, TimeoutExpired, CalledProcessError
from os import chdir, path, mkdir, environ, remove
from argparse import ArgumentParser
from json import dumps as to_json
from traceback import format_exc
from typing import Union
from enum import Enum
from re import search, sub, MULTILINE, IGNORECASE
from importlib import invalidate_caches, import_module


EXTRA_PIP_ARGS = []


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
    DEBUG = 0
    INFO = 1
    WARN = 2
    ERROR = 3


Options = {}

LogsContainer = []


RequiredPackagesList = {'google.protobuf': 'protobuf',
                        'grpc': {'grpc': 'grpcio',
                                 'grpclib': 'grpclib'},
                        'pipdeptree': 'pipdeptree'
                        }
#                         'nc_py_api': 'nc_py_api',
ExtraPackagesList = {'numpy': 'numpy',
                     'PIL': 'pillow',
                     'scipy': 'scipy',
                     'pywt': 'pywavelets',
                     'pg8000': 'pg8000',
                     'pymysql': 'PyMySQL[rsa,ed25519]',
                     'sqlalchemy': 'SQLAlchemy'
                     }


def log(log_lvl: Union[int, LogLvl], *msgs: str, help_code: int = 0):
    """Adds logs(s) to global LogsContainer variable."""
    if isinstance(log_lvl, LogLvl):
        log_lvl = log_lvl.value
    if Options['log_lvl'] <= log_lvl:
        for msg in msgs:
            if help_code:
                LogsContainer.append({log_lvl: msg, 'help_code': help_code})
            else:
                LogsContainer.append({log_lvl: msg})


def get_python_info() -> dict:
    _interpreter = sys.executable
    _local = _interpreter.startswith(Options['app_data'])
    return {'local': _local, 'path': _interpreter}


def get_module_location(module_name: str) -> str:
    if module_name:
        _call_result, _message = pip_call(['show', module_name], add_user_cache=False)
        if _call_result:
            _line_start = 'Location:'
            for line in _message.splitlines():
                if line.startswith(_line_start):
                    return line[len(_line_start):].strip()
    return ''


def get_pip_info() -> dict:
    _local = False
    _version = check_pip()
    _pip = True if _version[0] > 20 else False
    if _pip:
        _location = get_module_location('pip')
        if _location:
            if _location.startswith(Options['app_data']):
                _local = True
        else:
            log(LogLvl.WARN, f"Cant determine pip location, assume that it is global.")
    return {'present': _pip, 'version': _version, 'local': _local}


def get_local_dir_path() -> str:
    """Returns abs path to local dir. Tt is: .../appdata_xxx/cloud_py_api/local"""
    return path.join(Options['app_data'], 'local')


def check_local_dir(create_if_absent: bool = False) -> bool:
    """Returns True if local dir exists or was created(if create_if_absent=True), False otherwise."""
    dir_exists = path.isdir(get_local_dir_path())
    if not dir_exists and create_if_absent:
        try:
            mkdir(get_local_dir_path(), mode=0o774)
        except OSError:
            return False
        return path.isdir(get_local_dir_path())
    return dir_exists


def get_core_userbase() -> str:
    if Options['python']['local']:
        return path.dirname(path.dirname(Options['python']['path']))
    if check_local_dir():
        return get_local_dir_path()
    return ''


def get_modified_env(spec_userbase: str = ''):
    modified_env = environ
    if spec_userbase:
        modified_env['PYTHONUSERBASE'] = spec_userbase
    else:
        modified_env['PYTHONUSERBASE'] = get_core_userbase()
        if not modified_env['PYTHONUSERBASE']:
            return None, ''
    modified_env['_PIP_LOCATIONS_NO_WARN_ON_MISMATCH'] = '1'
    return modified_env, modified_env['PYTHONUSERBASE']


def get_core_site_packages() -> str:
    _env, _userbase = get_modified_env()
    try:
        _result = run([Options['python']['path'], '-m', 'site', '--user-site'],
                      stderr=PIPE, stdout=PIPE, check=True, env=_env)
        return _result.stdout.decode('utf-8').rstrip('\n')
    except (OSError, ValueError, TypeError, TimeoutExpired) as _exception_info:
        log(LogLvl.ERROR, f'python -m site raised {type(_exception_info).__name__}: {str(_exception_info)}')
        return ''


def check_pip() -> tuple:
    _ret = (0, 0, 0)
    _call_result, _message = pip_call(['--version'], add_user_cache=False)
    log(LogLvl.DEBUG, 'Pip version:', _message)
    if _call_result:
        m_groups = search(r'pip\s*(\d+(\.\d+){0,2})', _message, flags=MULTILINE + IGNORECASE)
        if m_groups is None:
            return _ret
        pip_version = tuple(map(int, str(m_groups.groups()[0]).split('.')))
        return pip_version
    return _ret


def pip_call(parameters, python_userbase: str = '', add_user_cache: bool = False) -> [bool, str]:
    log(LogLvl.DEBUG, f"pip_call(userbase={python_userbase}):\n{str(parameters)}")
    try:
        etc = ['--disable-pip-version-check']
        etc += EXTRA_PIP_ARGS
        _env, _userbase = get_modified_env(spec_userbase=python_userbase)
        if _userbase:
            if add_user_cache:
                etc += ['--user', '--cache-dir', _userbase]
        _result = run([Options['python']['path'], '-m', 'pip'] + parameters + etc,
                      stderr=PIPE, stdout=PIPE, check=False, env=_env)
        log(LogLvl.DEBUG, f"pip_call(stderr):\n{_result.stderr.decode('utf-8')}")
        log(LogLvl.DEBUG, f"pip_call(stdout):\n{_result.stdout.decode('utf-8')}")
        full_reply = _result.stderr.decode('utf-8')
        reply = sub(r'^\s*WARNING:.*\n?', '', full_reply, flags=MULTILINE + IGNORECASE)
        if len(reply) == 0:
            return True, _result.stdout.decode('utf-8')
        return False, _result.stderr.decode('utf-8')
    except (OSError, ValueError, TypeError, TimeoutExpired) as _exception_info:
        return False, f'pip_call raised {type(_exception_info).__name__}: {str(_exception_info)}'


def add_python_path(_path: str, first: bool = False):
    if not _path:
        return
    try:
        sys.path.pop(sys.path.index(_path))
    except (ValueError, IndexError):
        pass
    if first:
        sys.path.insert(0, _path)
    else:
        sys.path.append(_path)
    invalidate_caches()


def import_package(name: str, dest_sym_table=None, package=None) -> bool:
    try:
        if dest_sym_table is None:
            import_module(name, package)
        else:
            dest_sym_table[name] = import_module(name, package)
        return True
    except (ModuleNotFoundError, AttributeError, ImportError, ValueError) as e:
        print(e)
        pass
    return False


def get_missing_packages(packages_info: dict, any_of: bool = False) -> dict:
    missing = {}
    for package_name, install_info in packages_info.items():
        if isinstance(install_info, dict):
            _result = get_missing_packages(install_info, any_of=True)
            if _result:
                missing[package_name] = install_info
        else:
            _result = import_package(package_name)
            if _result:
                if any_of:
                    return {}
            else:
                missing[package_name] = install_info
    return missing


def check() -> [bool, int]:
    if not Options['pip']['present']:
        log(LogLvl.ERROR, 'Python pip not found or has too low version.')
        return False, 1
    add_python_path(get_core_site_packages(), first=True)
    _missing_required = get_missing_packages(RequiredPackagesList)
    if _missing_required:
        for package_name, install_info in _missing_required.items():
            log(LogLvl.ERROR, f'Missing {package_name}:{install_info}')
        return False, 1
    _missing_extra = get_missing_packages(ExtraPackagesList)
    for package_name, install_info in _missing_extra.items():
        log(LogLvl.WARN, f'Missing {package_name}:{install_info}')
    return True, 0


def download_pip(url: str, out_path: str) -> bool:
    n_download_clients = 2
    if not check_local_dir(create_if_absent=True):
        log(LogLvl.ERROR, 'Cant create local dir.')
        return False

    for _ in range(2):
        try:
            run(['curl', url, '-o', out_path], timeout=90, stderr=DEVNULL, stdout=DEVNULL, check=True)
            log(LogLvl.DEBUG, f'`{out_path}` finished downloading.')
            return True
        except CalledProcessError:
            break
        except FileNotFoundError:
            n_download_clients -= 1
            break
        except TimeoutExpired:
            pass
    for _ in range(2):
        try:
            run(['wget', url, '-O', out_path], timeout=90, stderr=DEVNULL, stdout=DEVNULL, check=True)
            log(LogLvl.DEBUG, f'`{out_path}` finished downloading.')
            return True
        except CalledProcessError:
            break
        except FileNotFoundError:
            n_download_clients -= 1
            break
        except TimeoutExpired:
            pass
    if not n_download_clients:
        log(LogLvl.ERROR, 'Both curl and wget cannot be found.')
    return False


def install_pip() -> bool:
    log(LogLvl.INFO, 'Start installing local pip.')
    get_pip_path = str(path.join(get_local_dir_path(), 'get-pip.py'))
    if not download_pip('https://bootstrap.pypa.io/get-pip.py', get_pip_path):
        log(LogLvl.ERROR, 'Cant download pip installer.')
        return False
    try:
        log(LogLvl.INFO, 'Running get-pip.py...')
        _env, _userbase = get_modified_env(get_local_dir_path())
        _result = run([Options['python']['path'], get_pip_path,
                       '--user', '--cache-dir', get_local_dir_path(), '--no-warn-script-location'],
                      stderr=PIPE, stdout=PIPE, check=False, env=_env
                      )
        log(LogLvl.DEBUG, f"get-pip.py(stderr):\n{_result.stderr.decode('utf-8')}")
        log(LogLvl.DEBUG, f"get-pip.py(stdout):\n{_result.stdout.decode('utf-8')}")
        full_reply = _result.stderr.decode('utf-8')
        reply = sub(r'^\s*WARNING:.*\n?', '', full_reply, flags=MULTILINE + IGNORECASE)
        if len(reply) == 0:
            return True
        log(LogLvl.ERROR, f'get-pip returned:\n{full_reply}')
    except (OSError, ValueError, TypeError, TimeoutExpired) as _exception_info:
        log(LogLvl.ERROR, f'install_pip raised {type(_exception_info).__name__}: {str(_exception_info)}')
    finally:
        try:
            remove(get_pip_path)
        except OSError:
            log(LogLvl.WARN, f'Cant remove `{get_pip_path}`')
    return False


def install_package(package_name, install_name, to_log: bool = False) -> bool:
    _call_result, _message = pip_call(['install', install_name, '--no-warn-script-location'], add_user_cache=True)
    if not _call_result:
        if to_log:
            log(LogLvl.WARN, f'Error during install {package_name}:{install_name}:\n', _message)
    return _call_result


def install_packages(packages_info: dict, any_of: bool = False) -> bool:
    _result = True
    _last_package_name = ''
    for package_name, install_info in packages_info.items():
        _last_package_name = package_name
        if isinstance(install_info, dict):
            _result = install_packages(install_info, any_of=True)
        else:
            _result = install_package(package_name, install_info, to_log=False if any_of else True)
        if any_of:
            if _result:
                return _result
        elif not _result:
            break
    if not _result:
        log(LogLvl.ERROR, f'Cant install {_last_package_name}')
    return _result


def install() -> [bool, int]:
    if not Options['pip']['present']:
        if not install_pip():
            log(LogLvl.ERROR, 'Cant install local pip.')
            return False, 1
        Options['pip'] = get_pip_info()
        if not Options['pip']['present']:
            log(LogLvl.ERROR, 'Cant run pip after local install.')
            return False, 1
    if not install_packages(RequiredPackagesList):
        return False, 1
    return True, 0


def install_extra() -> [bool, int]:
    if not Options['pip']['present']:
        log(LogLvl.ERROR, 'Pip required for packages install.')
        return False, 1
    if not install_packages(ExtraPackagesList):
        return False, 1
    return True, 0


def update_pip() -> [bool, int]:
    if Options['pip']['present']:
        if Options['pip']['local']:
            _call_result, _message = pip_call(['install', '--upgrade', 'pip', '--no-warn-script-location'],
                                              add_user_cache=True)
            if _call_result:
                return True, 0
    else:
        log(LogLvl.ERROR, 'No local compatible pip found to make update.')
    return False, 1


if __name__ == '__main__':
    chdir(path.dirname(path.abspath(__file__)))
    parser = ArgumentParser(description='Module for checking/installing packages for NC pyfrm.',
                            add_help=True)
    parser.add_argument('appdata', action='store', type=str,
                        help='Absolute path to cloud_py_api folder in appdata_xxx.')
    parser.add_argument('--loglvl', action='store', type=int, default=2,
                        help='Log level for output from 0(debug) to 3(errors). Default = 2.')
    group = parser.add_mutually_exclusive_group()
    group.add_argument('--check', dest='check', action='store_true',
                       help='Check installation.')
    group.add_argument('--install', dest='install', action='store_true',
                       help='Perform installation of basic modules for pyfrm.')
    group.add_argument('--install-extra', dest='install_extra', action='store_true',
                       help='Perform installation of extra modules in shared core folder.')
    group.add_argument('--update-pip', dest='update_pip', action='store_true',
                       help='Perform built-in or local pip update.')
    args = parser.parse_args()
    Options['app_data'] = args.appdata
    Options['log_lvl'] = args.loglvl
    Options['dry_run'] = args.check
    exit_code = 0
    result = False
    try:
        log(LogLvl.DEBUG, f'Path to python: {sys.executable}')
        log(LogLvl.DEBUG, f'Python version: {sys.version}')
        log(LogLvl.DEBUG, f'Platform: {platform.system(), platform.release(), platform.version(), platform.machine()}')
        Options['python'] = get_python_info()
        Options['pip'] = get_pip_info()
        log(LogLvl.INFO, f"Python info: {Options.get('python')}")
        log(LogLvl.INFO, f"Pip info: {Options.get('pip')}")
        if args.check:
            result, exit_code = check()
        elif args.install:
            result, exit_code = install()
        elif args.install_extra:
            result, exit_code = install_extra()
        elif args.update_pip:
            result, exit_code = update_pip()
    except Exception as exception_info:
        exit_code = 2
        exception_name = type(exception_info).__name__
        log(LogLvl.ERROR, f'Unexpected Exception: {exception_name}')
        exception_info_str = str(format_exc())
        log(LogLvl.ERROR, exception_info_str)
    print(to_json({'Installed': result,
                   'Logs': LogsContainer}))
    sys.exit(exit_code)
