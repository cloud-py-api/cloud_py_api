import sys
from platform import machine
from subprocess import run, PIPE, STDOUT, DEVNULL
from shutil import copytree, rmtree
from pwd import getpwnam
from os import path, environ, listdir, mkdir, remove
from urllib import parse
from json import dumps as to_json
from getpass import getuser
import tarfile


FRM_APP_DATA = "/cloud_py_api"
FRM_CONFIG = ""
SYS_PYTHON = "python3"
ST_PYTHON_DIR = path.join(FRM_APP_DATA, "st_python")
ST_PYTHON = path.join(ST_PYTHON_DIR, "bin/python3")
ST_PYTHON_CLONE_DIR = path.join(FRM_APP_DATA, "st_python_clone")
ST_PYTHON_CLONE = path.join(ST_PYTHON_CLONE_DIR, "bin/python3")
AS_USER = ["sudo", "-u"]
PRJ_PATH = environ.get("PRJ_PATH", "/host")
PY_FRM_PATH = path.join(PRJ_PATH, "pyfrm")
CURRENT_USER = getuser()


def get_cmd(cmd):
    return [
        path.join(PY_FRM_PATH, "install.py"),
        "--config",
        FRM_CONFIG,
        f"--{cmd}",
        "--target",
        "framework",
        "--dev",
    ]


def clean_fs():
    rmtree(path.join(FRM_APP_DATA, ".local"), ignore_errors=True)


def check_fs(log_head, process_result):
    _dir_list = listdir(FRM_APP_DATA)
    frm_allowed_names = [
        ".local",
        path.basename(ST_PYTHON_DIR),
        path.basename(ST_PYTHON_CLONE_DIR),
    ]
    _dir_list = [i for i in _dir_list if i not in frm_allowed_names]
    if _dir_list:
        print(f"{log_head} LOGS:", flush=True)
        print(process_result.stdout.decode("utf-8"), flush=True)
        print(
            f"Unexpected files in {FRM_APP_DATA} directory:\n{str(_dir_list)}",
            flush=True,
        )
        raise Exception("check_fs error.")
    try:
        _dir = path.expanduser(f"~")
        _dir_list = listdir(_dir)
        _must_not_be = [i for i in _dir_list if i in (".local", ".cache")]
        if _must_not_be:
            print(f"{log_head} LOGS:", flush=True)
            print(process_result.stdout.decode("utf-8"), flush=True)
            print(
                f"Unexpected files in {_dir} directory:\n{str(_must_not_be)}",
                flush=True,
            )
            raise Exception("check_fs error.")
        if AS_USER:
            try:
                _dir = path.expanduser(f"~{AS_USER[2]}")
                _dir_list = listdir(_dir)
                _must_not_be = [i for i in _dir_list if i in (".local", ".cache")]
                if _must_not_be:
                    print(f"{log_head} LOGS:", flush=True)
                    print(process_result.stdout.decode("utf-8"), flush=True)
                    print(
                        f"Unexpected files in {_dir} directory:\n{str(_must_not_be)}",
                        flush=True,
                    )
                    raise Exception("check_fs error.")
            except FileNotFoundError:
                pass
    except (KeyError, RuntimeError):
        pass


def python_test(python_interpreter=None, as_user: bool = False):
    _py_intp = python_interpreter if python_interpreter else sys.executable
    _whom = AS_USER[2] if as_user and AS_USER else CURRENT_USER
    _as = AS_USER if as_user and AS_USER else []
    _log_head = f"{_py_intp} ({_whom}): "
    print(_log_head + "CHECKING:..", end="", flush=True)
    _ = run(
        _as + [_py_intp] + get_cmd("check"),
        stdout=PIPE,
        stderr=STDOUT,
        check=False,
    )
    if _.returncode == 2:
        print(f". FAILED\n\n{_log_head} LOGS:", flush=True)
        print(_.stdout.decode("utf-8"), flush=True)
        raise Exception(f"TEST FAILED. {_log_head}: CHECK")
    print(". OK", flush=True)
    check_fs(_log_head, _)
    if _.returncode == 1:
        print(_log_head + "INSTALLING:..", end="", flush=True)
        _ = run(
            _as + [_py_intp] + get_cmd("install"),
            stdout=PIPE,
            stderr=STDOUT,
            check=False,
        )
        if _.returncode:
            print(f". FAILED\n\n{_log_head} LOGS:", flush=True)
            print(_.stdout.decode("utf-8"), flush=True)
            raise Exception(f"TEST FAILED. {_log_head}: INSTALL")
        print(". OK", flush=True)
        check_fs(_log_head, _)
    print(_log_head + "UPDATING:..", end="", flush=True)
    _ = run(
        _as + [_py_intp] + get_cmd("update"),
        stdout=PIPE,
        stderr=STDOUT,
        check=False,
    )
    if _.returncode:
        print(f". FAILED\n\n{_log_head} LOGS:", flush=True)
        print(_.stdout.decode("utf-8"), flush=True)
        raise Exception(f"TEST FAILED. {_log_head}: UPDATE")
    print(". OK", flush=True)
    check_fs(_log_head, _)
    clean_fs()
    print(_log_head + "PASSED", flush=True)


def python_tests(python_interpreter=None):
    if AS_USER:
        chown(FRM_APP_DATA, AS_USER[2])
        python_test(python_interpreter, as_user=True)
    chown(FRM_APP_DATA, CURRENT_USER)
    python_test(python_interpreter)


def init_frm_cfg():
    global FRM_CONFIG
    test_cfg = {"loglvl": "DEBUG", "frmAppData": FRM_APP_DATA, "dbConfig": {}}
    test_cfg["dbConfig"]["dbType"] = "pgsql"
    test_cfg["dbConfig"]["dbUser"] = ""
    # test_cfg["dbConfig"]["dbPass"] = ""
    # test_cfg["dbConfig"]["dbHost"] = ""
    test_cfg["dbConfig"]["dbName"] = "nextcloud"
    # test_cfg["dbConfig"]["dbPrefix"] = "oc_"
    # test_cfg["dbConfig"]["iniDbSocket"] = ""
    # test_cfg["dbConfig"]["iniDbHost"] = ""
    # test_cfg["dbConfig"]["iniDbPort"] = ""
    # test_cfg["dbConfig"]["dbDriverSslKey"] = ""
    # test_cfg["dbConfig"]["dbDriverSslCert"] = ""
    # test_cfg["dbConfig"]["dbDriverSslCa"] = ""
    # test_cfg["dbConfig"]["dbDriverSslVerifyCrt"] = ""
    FRM_CONFIG = parse.quote_plus(to_json(test_cfg, separators=(",", ":")))


def init_web_username():
    global AS_USER
    try:
        getpwnam("www-data")
        AS_USER += ["www-data"]
        return
    except KeyError:
        pass
    try:
        getpwnam("apache")
        AS_USER += ["apache"]
        return
    except KeyError:
        pass
    AS_USER = []


def chown(target_dir: str, username: str) -> None:
    run(["chown", "-R", f"{username}:{username}", target_dir], check=True)


def init():
    if sys.version_info[0] != 3:
        raise Exception(
            f"Unsupported python version({sys.version_info[0]}.{sys.version_info[1]})."
        )
    init_frm_cfg()
    init_web_username()
    # Delete .cache and .local folders if any in ~ and ~USER
    try:
        _dir = path.expanduser(f"~")
        rmtree(path.join(_dir, ".cache"), ignore_errors=True)
        rmtree(path.join(_dir, ".local"), ignore_errors=True)
        if AS_USER:
            try:
                _dir = path.expanduser(f"~{AS_USER[2]}")
                rmtree(path.join(_dir, ".cache"), ignore_errors=True)
                rmtree(path.join(_dir, ".local"), ignore_errors=True)
            except FileNotFoundError:
                pass
    except (KeyError, RuntimeError):
        pass
    # Create cloud_py_api folder.
    rmtree(FRM_APP_DATA, ignore_errors=True)
    mkdir(FRM_APP_DATA)
    # Download standalone python.
    if environ.get("SKIP_ST_PY_TESTS", "0") != "0":
        return
    _st_py_tag = environ.get("REL_TAG")
    if not _st_py_tag:
        return
    _st_type = "amd64"
    if machine().lower() in ("arm64", "aarch64"):
        _st_type = "arm64"
    _st_os = "manylinux"
    _ = run("ldd --version".split(), stdout=PIPE, stderr=STDOUT, check=False)
    if _.stdout and _.stdout.decode("utf-8").find("musl") != -1:
        _st_os = "musllinux"
    _url = (
        "https://github.com/bigcat88/cloud_py_api/releases/download/"
        + f"{_st_py_tag}/st_python_{_st_type}_{_st_os}.tar.zst"
    )
    zst_path = path.join(FRM_APP_DATA, "standalone.tar.zst")
    _ = run(f"wget -q --no-check-certificate -O {zst_path} {_url}".split(), check=False)
    if _.returncode:
        print("WARNING: Standalone Python not found.", flush=True)
        if path.isfile(zst_path):
            remove(zst_path)
        return
    run(f"zstd -d {zst_path}".split(), stderr=DEVNULL, stdout=DEVNULL, check=True)
    remove(zst_path)
    tar_path = path.join(FRM_APP_DATA, "standalone.tar")
    with tarfile.open(tar_path) as tar:
        tar.extractall(FRM_APP_DATA)
    remove(tar_path)
    if path.isdir(ST_PYTHON_DIR) and environ.get("SKIP_ST_PY_CLONED_TESTS", "0") == "0":
        # Clone standalone python.
        rmtree(ST_PYTHON_CLONE_DIR, ignore_errors=True)
        copytree(ST_PYTHON_DIR, ST_PYTHON_CLONE_DIR)
        chown(FRM_APP_DATA, AS_USER[2] if AS_USER else CURRENT_USER)
        # Install old packages for update test.
        run(
            AS_USER
            + [ST_PYTHON_CLONE]
            + ["-m", "pip", "install", "--no-cache-dir"]
            + ["pg8000==1.23.0", "PyMySQL==1.0.1", "protobuf==3.19.1"],
            check=True,
        )


if __name__ == "__main__":
    init()
    if path.isdir(ST_PYTHON_CLONE_DIR):
        python_tests(ST_PYTHON_CLONE)
    if path.isdir(ST_PYTHON_DIR):
        python_tests(ST_PYTHON)
    if sys.version_info[1] > 6 and environ.get("SKIP_SYS_PY_TESTS", "0") == "0":
        # Temporary till photon will be updated to use newer pip without that bug with `xml`.
        # if environ.get("M_OS_NAME", "") == "photon":
        #     _install_cmd = environ.get("INSTALL_CMD")
        #     _pip_name = environ.get("PIP_NAME", "")
        #     if _pip_name:
        #         run(_install_cmd.split() + [_pip_name], check=True)
        #         run(
        #             [sys.executable]
        #             + "-m pip install --no-cache-dir --upgrade pip".split(),
        #             check=True,
        #         )
        # -----------------------------------------------------------------------------------
        python_tests()
        _install_cmd = environ.get("INSTALL_CMD")
        _pip_name = environ.get("PIP_NAME", "")
        if _pip_name:
            run(_install_cmd.split() + [_pip_name], check=True)
            run(
                [sys.executable]
                + "-m pip install --no-cache-dir --upgrade pip".split(),
                check=True,
            )
            run(
                [sys.executable]
                + "-m pip install --no-cache-dir pipdeptree pg8000 PyMySQL protobuf SQLAlchemy".split(),
                check=True,
            )
            python_tests()
    sys.exit(0)
