"""
Cloud_Py_Api self install module.
"""

# TODO: https://docs.python.org/3/library/subprocess.html#subprocess.CompletedProcess.stdout
import sys
import platform
from subprocess import run, PIPE, DEVNULL, TimeoutExpired, CalledProcessError
from os import chdir, path, mkdir, environ, remove
from argparse import ArgumentParser
from json import dumps as to_json, loads as from_json
from re import search, sub, MULTILINE, IGNORECASE
from importlib import invalidate_caches, import_module
from urllib.parse import unquote_plus
from getpass import getuser
import logging

from exceptions import FrmProgrammingError


EXTRA_PIP_ARGS = []
Options = {}
RequiredPackagesList = {
    "pipdeptree": "pipdeptree",
    "nc_py_api": "nc_py_api",
    "pg8000": "pg8000",
    "pymysql": "PyMySQL[rsa,ed25519]",
    "sqlalchemy": "SQLAlchemy",
    "requirements": "requirements-parser",
    "google.protobuf": "protobuf",
}
OptionalPackagesList = {
    "grpc": "grpcio",
}
LogsContainer = []
Log = logging.getLogger("pyfrm.install")
Log.propagate = False


class InstallLogHandler(logging.Handler):
    __log_levels = {"DEBUG": 0, "INFO": 1, "WARN": 2, "ERROR": 3, "FATAL": 4}

    def emit(self, record):
        self.format(record)
        __content = record.message if record.funcName == "<module>" else record.funcName + ": " + record.message
        if record.exc_text is not None:
            __content += "\n" + record.exc_text
        __log_lvl = self.__log_levels.get(record.levelname)
        __module = record.module if record.name == "root" else record.name
        if Options["dev"]:
            LogsContainer.append(
                {"log_lvl": __log_lvl, "module": f"{record.filename}:{record.lineno}", "content": __content}
            )
        else:
            LogsContainer.append({"log_lvl": __log_lvl, "module": __module, "content": __content})


def get_options() -> dict:
    return Options


def get_python_info() -> dict:
    _interpreter = sys.executable
    _local = _interpreter.startswith(Options["app_data"])
    return {"local": _local, "path": _interpreter}


def get_pip_info() -> dict:
    _local = False
    _version = check_pip()
    _pip = True if _version[0] > 20 else False
    if _pip:
        _location = get_package_info("pip").get("location", "")
        if _location:
            if _location.startswith(Options["app_data"]):
                _local = True
        else:
            Log.warning("Cant determine pip location, assume that it is global.")
    return {"present": _pip, "version": _version, "local": _local}


def get_local_dir_path() -> str:
    """Returns abs path to local dir. Tt is: .../appdata_xxx/cloud_py_api/local"""
    return path.join(Options["app_data"], "local")


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
    if Options["python"]["local"]:
        return path.dirname(path.dirname(Options["python"]["path"]))
    if check_local_dir():
        return get_local_dir_path()
    return ""


def get_modified_env(userbase: str = "", python_path: str = ""):
    modified_env = dict(environ)
    if userbase:
        modified_env["PYTHONUSERBASE"] = userbase
    else:
        def_userbase = get_core_userbase()
        if def_userbase:
            modified_env["PYTHONUSERBASE"] = def_userbase
    if python_path:
        modified_env["PYTHONPATH"] = python_path
    modified_env["_PIP_LOCATIONS_NO_WARN_ON_MISMATCH"] = "1"
    return modified_env, modified_env.get("PYTHONUSERBASE", "")


def get_site_packages(userbase: str = "") -> str:
    _env, _userbase = get_modified_env(userbase=userbase)
    try:
        _result = run(
            [Options["python"]["path"], "-m", "site", "--user-site"], stderr=PIPE, stdout=PIPE, check=True, env=_env
        )
        return _result.stdout.decode("utf-8").rstrip("\n")
    except (OSError, ValueError, TypeError, TimeoutExpired, CalledProcessError) as _exception_info:
        Log.exception(f"Exception {type(_exception_info).__name__}:")
        return ""


def check_pip() -> tuple:
    _ret = (0, 0, 0)
    _call_result, _message = pip_call(["--version"], user_cache=False)
    if _call_result:
        m_groups = search(r"pip\s*(\d+(\.\d+){0,2})", _message, flags=MULTILINE + IGNORECASE)
        if m_groups is None:
            return _ret
        pip_version = tuple(map(int, str(m_groups.groups()[0]).split(".")))
        return pip_version
    return _ret


def pip_call(parameters, userbase: str = "", python_path: str = "", user_cache: bool = False) -> [bool, str]:
    Log.debug(f"(USERBASE<{userbase}> PATH<{python_path}>): {str(parameters)}")
    try:
        etc = ["--disable-pip-version-check"]
        etc += EXTRA_PIP_ARGS
        _env, _userbase = get_modified_env(userbase=userbase, python_path=python_path)
        if _userbase:
            if user_cache:
                etc += ["--user", "--cache-dir", _userbase]
        Log.debug(f"_env=<{_env}>")
        _result = run(
            [Options["python"]["path"], "-m", "pip"] + parameters + etc, stderr=PIPE, stdout=PIPE, check=False, env=_env
        )
        _stderr = _result.stderr.decode("utf-8")
        _stdout = _result.stdout.decode("utf-8")
        if _stderr:
            Log.debug(f"pip.stderr:\n{_stderr}".rstrip("\n"))
        if _stdout:
            Log.debug(f"pip.stdout:\n{_stdout}".rstrip("\n"))
        reply = sub(r"^\s*WARNING:.*\n?", "", _stderr, flags=MULTILINE + IGNORECASE)
        if len(reply) == 0:
            return True, _stdout
        return False, _stderr
    except (OSError, ValueError, TypeError, TimeoutExpired) as _exception_info:
        return False, f"Exception {type(_exception_info).__name__}: {str(_exception_info)}"


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


def get_package_info(name: str, userbase: str = "", python_path: str = "") -> dict:
    package_info = {}
    if name:
        _call_result, _message = pip_call(["show", name], userbase=userbase, python_path=python_path, user_cache=False)
        if _call_result:
            _pip_show_map = {
                "Name:": "name",
                "Version:": "version",
                "Location:": "location",
                "Summary:": "summary",
                "Requires:": "requires",
            }
            for _line in _message.splitlines():
                for _map_key in _pip_show_map:
                    if _line.startswith(_map_key):
                        package_info[_pip_show_map[_map_key]] = _line[len(_map_key) :].strip()
    return package_info


def import_package(name: str, dest_sym_table=None, package=None) -> bool:
    try:
        if dest_sym_table is None:
            import_module(name, package)
        else:
            dest_sym_table[name] = import_module(name, package)
        return True
    except (ModuleNotFoundError, AttributeError, ImportError, ValueError):
        pass
    return False


def frm_check_item(import_name: str, install_name: str) -> dict:
    _modules = {}
    _result = import_package(import_name, dest_sym_table=_modules)
    if _result:
        location = ""
        if hasattr(_modules[import_name], "__version__"):
            version = _modules[import_name].__version__
        else:
            version = get_package_info(install_name).get("version", "")
        if hasattr(_modules[import_name], "__spec__"):
            __spec = _modules[import_name].__spec__
            if __spec is not None and hasattr(__spec, "has_location"):
                if __spec.has_location:
                    location = __spec.origin
        if not location and hasattr(_modules[import_name], "__path__"):
            location = _modules[import_name].__path__
        if location and not path.isdir(location):
            location = path.dirname(location)
        return {"package": install_name, "location": location, "version": version}
    return {"package": install_name, "location": "", "version": ""}


def frm_check() -> [dict, dict, dict]:
    if not Options["pip"]["present"]:
        Log.error("Python pip not found or has too low version.")
        return {}, {"package": "pip3", "location": "", "version": ""}, {}
    add_python_path(get_site_packages(), first=True)
    installed_list = {}
    not_installed_list = {}
    not_installed_opt_list = {}
    for import_name, install_name in RequiredPackagesList.items():
        _result = frm_check_item(import_name, install_name)
        if _result.get("location", ""):
            installed_list[import_name] = _result
        else:
            not_installed_list[import_name] = _result
            Log.error(f"Missing {import_name}:{install_name}")
    for import_name, install_name in OptionalPackagesList.items():
        _result = frm_check_item(import_name, install_name)
        if _result.get("location", ""):
            installed_list[import_name] = _result
        else:
            not_installed_opt_list[import_name] = _result
            Log.warning(f"Missing {import_name}:{install_name}")
    return installed_list, not_installed_list, not_installed_opt_list


def download_pip(url: str, out_path: str) -> bool:
    n_download_clients = 2
    if not check_local_dir(create_if_absent=True):
        Log.error("Cant create local dir.")
        return False
    for _ in range(2):
        try:
            run(["curl", url, "-o", out_path], timeout=90, stderr=DEVNULL, stdout=DEVNULL, check=True)
            Log.debug(f"`{out_path}` finished downloading.")
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
            run(["wget", url, "-O", out_path], timeout=90, stderr=DEVNULL, stdout=DEVNULL, check=True)
            Log.debug(f"`{out_path}` finished downloading.")
            return True
        except CalledProcessError:
            break
        except FileNotFoundError:
            n_download_clients -= 1
            break
        except TimeoutExpired:
            pass
    if not n_download_clients:
        Log.error("Both curl and wget cannot be found.")
    return False


def install_pip() -> bool:
    Log.info("Start installing local pip.")
    get_pip_path = str(path.join(get_local_dir_path(), "get-pip.py"))
    if not download_pip("https://bootstrap.pypa.io/get-pip.py", get_pip_path):
        Log.error("Cant download pip installer.")
        return False
    try:
        Log.info("Running get-pip.py...")
        _env, _userbase = get_modified_env(get_local_dir_path())
        _result = run(
            [
                Options["python"]["path"],
                get_pip_path,
                "--user",
                "--cache-dir",
                get_local_dir_path(),
                "--no-warn-script-location",
            ],
            stderr=PIPE,
            stdout=PIPE,
            check=False,
            env=_env,
        )
        Log.debug(f"get-pip.stderr:\n{_result.stderr.decode('utf-8')}")
        Log.debug(f"get-pip.stdout:\n{_result.stdout.decode('utf-8')}")
        full_reply = _result.stderr.decode("utf-8")
        reply = sub(r"^\s*WARNING:.*\n?", "", full_reply, flags=MULTILINE + IGNORECASE)
        if len(reply) == 0:
            return True
        Log.error(f"get-pip returned:\n{full_reply}")
    except (OSError, ValueError, TypeError, TimeoutExpired) as _exception_info:
        Log.exception(f"Exception {type(_exception_info).__name__}:")
    finally:
        try:
            remove(get_pip_path)
        except OSError:
            Log.warning(f"Cant remove `{get_pip_path}`")
    return False


def install() -> bool:
    if not Options["pip"]["present"]:
        if not install_pip():
            Log.error("Cant install local pip.")
            return False
        Options["pip"] = get_pip_info()
        if not Options["pip"]["present"]:
            Log.error("Cant run pip after local install.")
            return False
    for install_name in RequiredPackagesList.values():
        _result, _message = pip_call(
            ["install", install_name, "--no-warn-script-location", "--prefer-binary"], user_cache=True
        )
        if not _result:
            Log.error(f"Cant install {install_name}. Pip output:\n{_message}")
            return False
    for install_name in OptionalPackagesList.values():
        _result, _message = pip_call(
            ["install", install_name, "--no-warn-script-location", "--prefer-binary"], user_cache=True
        )
        if not _result:
            Log.warning(f"Cant install {install_name}. Pip output:\n{_message}")
    return True


def update_pip() -> bool:
    if Options["pip"]["present"]:
        Log.error("No local compatible pip found.")
        return False
    if Options["pip"]["local"]:
        _call_result, _message = pip_call(["install", "--upgrade", "pip", "--no-warn-script-location"], user_cache=True)
        if not _call_result:
            return False
    return True


def check_target(target: str) -> [dict, dict, dict]:
    if target == "framework":
        return frm_check()
    return {}, {}, {}


def frm_perform(action: str) -> bool:
    if action == "delete":
        raise FrmProgrammingError("Target `framework` can not be specified for delete operation.")
    if action == "install":
        return install()
    if action == "update":
        if not update_pip():
            return False
        for import_name, install_name in RequiredPackagesList.items():
            _result, _message = pip_call(
                ["install", "--upgrade", install_name, "--no-warn-script-location", "--prefer-binary"], user_cache=True
            )
            if not _result:
                Log.error(f"Cant update {install_name}. Pip output:\n{_message}")
                return False
        return True
    raise FrmProgrammingError(f"Unknown action: {action}.")


def app_perform(app_id: str, action: str) -> bool:
    return False


def perform_action(target: str, action: str) -> bool:
    if target == "framework":
        return frm_perform(action)
    return app_perform(target, action)


if __name__ == "__main__":
    chdir(path.dirname(path.abspath(__file__)))
    parser = ArgumentParser(description="Module for checking/installing packages for NC pyfrm.", add_help=True)
    parser.add_argument("--config", dest="config", type=str, help="JSON with loglvl, frmAppData and dbConfig.")
    parser.add_argument(
        "--target",
        dest="target",
        type=str,
        help="'framework' or 'AppId' from table `cloud_py_api`(for app).",
    )
    parser.add_argument("--dev", dest="dev", action="store_true", default=False)
    group = parser.add_mutually_exclusive_group()
    group.add_argument("--check", dest="check", action="store_true", help="Check installation of specified target.")
    group.add_argument(
        "--install", dest="install", action="store_true", help="Perform installation of specified target's packages."
    )
    group.add_argument(
        "--update", dest="update", action="store_true", help="Perform update of specified target's packages."
    )
    group.add_argument(
        "--delete",
        dest="delete",
        action="store_true",
        help="Perform delete of specified target's packages.",
    )
    args = parser.parse_args()
    Options["dev"] = args.dev
    args.target = str(args.target).lower()
    config = from_json(unquote_plus(args.config))
    Options["app_data"] = config["frmAppData"]
    Options["db_config"] = config["dbConfig"]
    levels = ("DEBUG", "INFO", "WARN", "ERROR", "FATAL")
    logging.addLevelName(30, "WARN")
    logging.addLevelName(50, "FATAL")
    Log.setLevel(level=config["loglvl"])
    Log.addHandler(InstallLogHandler())
    exit_code = 0
    result = False
    r_installed_list = {}
    r_not_installed_list = {}
    r_not_installed_opt_list = {}
    try:
        try:
            Log.debug(f"User name: {getuser()}")
        except Exception as _exception:
            Log.warning(f"Exception during `getuser`:\n{str(_exception)}")
        Log.debug(f"target: {args.target}")
        Log.debug(f"app_data: {Options['app_data']}")
        Log.debug(f"Path to python: {sys.executable}")
        Log.debug(f"Python version: {sys.version}")
        Log.debug(f"Platform: {platform.system(), platform.release(), platform.version(), platform.machine()}")
        Options["python"] = get_python_info()
        Options["pip"] = get_pip_info()
        Log.info(f"Python info: {Options.get('python')}")
        Log.info(f"Pip info: {Options.get('pip')}")
        if args.target != "framework":
            r_installed_list, r_not_installed_list, r_not_installed_opt_list = check_target("framework")
            if r_not_installed_list:
                raise FrmProgrammingError("Install framework before targeting app.")
            r_installed_list.clear()
            r_not_installed_list.clear()
            r_not_installed_opt_list.clear()
        if args.install:
            result = perform_action(args.target, "install")
        elif args.update:
            result = perform_action(args.target, "update")
        elif args.delete:
            result = perform_action(args.target, "delete")
        r_installed_list, r_not_installed_list, r_not_installed_opt_list = check_target(args.target)
        if args.check and not r_not_installed_list:
            result = True
        if not result:
            exit_code = 1
    except Exception as exception_info:
        if type(exception_info) is FrmProgrammingError:
            Log.error(str(exception_info))
        else:
            Log.exception(f"Unexpected Exception: {type(exception_info).__name__}")
        exit_code = 2
    Log.debug(f"ExitCode: {exit_code}")
    if Options["dev"]:
        print("Logs:")
        for log_record in LogsContainer:
            print(str(log_record["log_lvl"]) + " : " + log_record["module"] + " : " + log_record["content"])
        print(f"Installed:\n{r_installed_list}")
        print(f"NotInstalled:\n{r_not_installed_list}")
        print(f"NotInstalledOpt:\n{r_not_installed_opt_list}")
        print(f"Result: {result}")
    else:
        print(
            to_json(
                {
                    "Result": result,
                    "Installed": r_installed_list,
                    "NotInstalled": r_not_installed_list,
                    "NotInstalledOpt": r_not_installed_opt_list,
                    "Logs": LogsContainer,
                }
            )
        )
    sys.exit(exit_code)
