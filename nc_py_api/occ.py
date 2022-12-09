""" Functions wrappers around OCC utility """

import os
import re
import subprocess
from typing import Union

from .log import cpa_logger as log


def get_cloud_config_value(value_name: str, default=None) -> Union[str, None]:
    """Returns decoded utf8 output of `occ config:system:get {value}` command."""

    _ = occ_call_decode("config:system:get", value_name, log_error=default is None)
    return _ if _ is not None else default


def get_cloud_app_config_value(app_name: str, value_name: str, default=None) -> Union[str, None]:
    """Returns decoded utf8 output of `occ config:app:get {app} {value}` command."""

    _ = occ_call_decode("config:app:get", app_name, value_name, log_error=default is None)
    return _ if _ is not None else default


def occ_call(occ_task, *params, log_error=True) -> Union[bytes, None]:
    """Wrapper for occ calls that returns raw bytes."""

    return php_call(_OCC_PATH, "--no-warnings", occ_task, *params, log_error=log_error)


def occ_call_decode(occ_task, *params, log_error=True) -> Union[str, None]:
    """Wrapper for occ calls that returns string."""

    result = php_call(_OCC_PATH, "--no-warnings", occ_task, *params, log_error=log_error)
    if result is None:
        return result
    str_result = result.decode("utf-8").rstrip("\n")
    str_result = re.sub(r".*app.*require.*upgrade.*\n?", "", str_result, flags=re.IGNORECASE)
    str_result = re.sub(r".*occ.*upgrade.*command.*\n?", "", str_result, flags=re.IGNORECASE)
    return str_result.strip("\n")


def php_call(*params, log_error=True) -> Union[bytes, None]:
    """Calls PHP interpreter with the specified `params`.

    :param log_error: boolean value indicating should be exception info logged or not. Default=``True``

    :returns: output from executing PHP interpreter."""

    try:
        if _SNAP and params[0] == _OCC_PATH:
            result = subprocess.run([*params], stdout=subprocess.PIPE, stderr=subprocess.DEVNULL, check=True)
        else:
            result = subprocess.run([_PHP_PATH, *params], stdout=subprocess.PIPE, stderr=subprocess.DEVNULL, check=True)
    except Exception:  # noqa # pylint: disable=broad-except
        if log_error:
            log.exception("php_call exception:")
        return None
    return result.stdout


_OCC_PATH = os.path.join(os.environ.get("SERVER_ROOT", "/var/www/nextcloud"), "occ")
_PHP_PATH = os.environ.get("PHP_PATH", "php")
_SNAP = os.environ.get("IS_SNAP_ENV", None)
