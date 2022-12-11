from unittest import mock

import nc_py_api


def test_occ_call():
    assert nc_py_api.occ_call("--version").decode("utf-8").lower().find("nextcloud ") != -1


def test_occ_call_invalid_command():
    assert nc_py_api.occ_call("invalid command") is None


def test_occ_call_with_param():
    assert nc_py_api.occ_call("config:system:get", "installed").decode("utf-8").lower() == "true\n"


def test_occ_call_decode():
    assert nc_py_api.occ_call_decode("--version").lower().find("nextcloud ") != -1


def test_occ_call_decode_invalid_command():
    assert nc_py_api.occ_call_decode("invalid command") is None


def test_occ_call_decode_with_param():
    assert nc_py_api.occ_call_decode("config:system:get", "installed").lower() == "true"


def test_get_cloud_app_config_value():
    assert nc_py_api.get_cloud_app_config_value("core", "vendor") == "nextcloud"


def test_get_cloud_app_config_invalid_name():
    assert nc_py_api.get_cloud_app_config_value("core", "invalid_name") is None


def test_get_cloud_app_config_default_value():
    assert nc_py_api.get_cloud_app_config_value("core", "invalid_name", default=3) == 3


@mock.patch("nc_py_api.occ._PHP_PATH", "no_php")
@mock.patch("nc_py_api.log.cpa_logger.exception")
def test_no_php_log_on(log_exception_mock):
    assert nc_py_api.occ_call("--version") is None
    log_exception_mock.assert_called_once_with("php_call exception:")


@mock.patch("nc_py_api.occ._PHP_PATH", "no_php")
@mock.patch("nc_py_api.log.cpa_logger.exception")
def test_no_php_log_off(log_exception_mock):
    assert nc_py_api.occ_call("--version", log_error=False) is None
    log_exception_mock.assert_not_called()
