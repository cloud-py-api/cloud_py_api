import sys
from subprocess import run
from shutil import copytree, rmtree
from pwd import getpwnam
from os import chown, path, environ, stat, mkdir
from urllib import parse
from json import dumps as to_json


FRM_APP_DATA = "/cloud_py_api"
FRM_CONFIG = ""
SYS_PYTHON = "python3"
ST_PYTHON_DIR = path.join(FRM_APP_DATA, "st_python")
ST_PYTHON = path.join(ST_PYTHON_DIR, "bin/python3")
ST_PYTHON_CLONE_DIR = path.join(FRM_APP_DATA, "st_python_clone")
ST_PYTHON_CLONE = path.join(ST_PYTHON_DIR, "bin/python3")
AS_USER = ["sudo", "-u"]
PRJ_PATH = environ.get("PRJ_PATH", "/host/home/runner/work/cloud_py_api/cloud_py_api")
PY_FRM_PATH = environ.get("PY_FRM_PATH", "/")


def my_print(data):
    print("<<<----------------------------------------------->>>")
    print(data)
    print("<<<----------------------------------------------->>>")


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


def check_fs():
    _dir_list = [
        FRM_APP_DATA,
        path.join(FRM_APP_DATA, ".local"),
        ST_PYTHON_DIR,
        "/var/www",
    ]
    run(["ls", "-la"] + _dir_list, check=False)


def python_test(as_user=None, st_python=None):
    _py_intp = st_python if st_python else sys.executable
    _whom = "USER" if as_user else "ROOT"
    _as = AS_USER if as_user else []
    my_print(f"{_py_intp} ({_whom}): CHECKING.")
    _ = run(_as + [_py_intp] + get_cmd("check"), check=False)
    if _.returncode == 2:
        raise Exception(f"TEST FAILED. ({_py_intp}, CHECK, {_whom})")
    if _.returncode == 1:
        my_print(f"{_py_intp} ({_whom}): INSTALLING.")
        _ = run(_as + [_py_intp] + get_cmd("install"), check=False)
        if _.returncode:
            raise Exception(f"TEST FAILED. ({_py_intp}, INSTALL, {_whom})")
    check_fs()
    my_print(f"{_py_intp} ({_whom}): UPDATING.")
    _ = run(_as + [_py_intp] + get_cmd("update"), check=False)
    if _.returncode:
        raise Exception(f"TEST FAILED. ({_py_intp}, UPDATE, {_whom})")
    check_fs()
    clean_fs()
    my_print(f"{_py_intp} ({_whom}): PASSED.")


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


if __name__ == "__main__":
    if sys.version_info[0] != 3:
        raise Exception("Unsupported python version.")
    init_frm_cfg()
    init_web_username()
    mkdir(FRM_APP_DATA)
    if AS_USER:
        run(["chown", "-R", f"{AS_USER[2]}:{AS_USER[2]}", FRM_APP_DATA], check=True)
    rmtree(PY_FRM_PATH, ignore_errors=True)
    copytree(path.join(PRJ_PATH, "pyfrm"), PY_FRM_PATH)
    if (
        AS_USER
        and path.isdir(ST_PYTHON_DIR)
        and environ.get("SKIP_ST_PY_CLONED_TESTS", "0") == "0"
    ):
        rmtree(ST_PYTHON_CLONE_DIR, ignore_errors=True)
        copytree(ST_PYTHON_DIR, ST_PYTHON_CLONE_DIR)
        _st = stat(ST_PYTHON_DIR)
        chown(ST_PYTHON_CLONE_DIR, _st.st_uid, _st.st_gid)
        run(
            AS_USER
            + [ST_PYTHON_CLONE]
            + ["-m", "pip", "install", "--no-cache-dir"]
            + ["pg8000==1.23.0", "PyMySQL==1.0.1", "protobuf==3.19.1"],
            check=True,
        )
        python_test(AS_USER, ST_PYTHON_CLONE)
    if path.isdir(ST_PYTHON_DIR) and environ.get("SKIP_ST_PY_TESTS", "0") == "0":
        if AS_USER:
            python_test(AS_USER, ST_PYTHON)
        python_test(st_python=ST_PYTHON)
    if sys.version_info[1] > 6 and environ.get("SKIP_SYS_PY_TESTS", "0") == "0":
        if AS_USER:
            python_test(AS_USER)
        python_test()
        _os_pckg_cmd = environ.get("OS_PCKG_INSTALL_CMD", "")
        if _os_pckg_cmd:
            run(_os_pckg_cmd.split(), check=True)
            _os_pckg_py_install = environ.get("OS_PCKG_PY_INSTALL", "")
            if _os_pckg_py_install:
                run(_os_pckg_py_install.split(), check=True)
            python_test(AS_USER if AS_USER else None)
    sys.exit(0)
