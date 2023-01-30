from unittest.mock import patch

import nc_py_api


def test_remote_filesize_limit():
    assert nc_py_api.db_requests.get_non_direct_access_filesize_limit() == 512 * 1024 * 1024


@patch("nc_py_api.db_requests.execute_fetchall", autospec=True)
def test_no_remote_filesize_limit(mock_execute_fetchall):
    def execute_fetchall_side_effect(*args, **kwargs):
        query = str(args[0]).replace("remote_filesize_limit", "invalid_remote_filesize_limit")
        return nc_py_api.db_api.execute_fetchall(query, *args[1:], **kwargs)

    mock_execute_fetchall.side_effect = execute_fetchall_side_effect
    assert nc_py_api.db_requests.get_non_direct_access_filesize_limit() == 256 * 1024 * 1024
