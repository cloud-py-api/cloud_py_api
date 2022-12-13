import logging

import pytest

from nc_py_api import fs_node_info, get_mimetype_id


@pytest.mark.parametrize("mimetype", ["httpd", "httpd/unix-directory", "application", "text", "image", "video"])
def test_get_mimetype_id(mimetype):
    assert get_mimetype_id(mimetype)


@pytest.mark.parametrize("mimetype", ["", "invalid_mime", None, "'invalid_mime'"])
def test_get_mimetype_id_invalid(mimetype):
    logging.disable(logging.CRITICAL)
    assert not get_mimetype_id(mimetype)
    logging.disable(logging.NOTSET)


def test_mimetype_other():
    for key, value in {
        "test_dir/empty_dir/empty_file.bin": "application/x-bin",
        "test_dir/hopper.png": "image/png",
        "test_dir/test.txt": "text/plain",
    }.items():
        assert fs_node_info(key, user_id="admin")["mimetype"] == get_mimetype_id(value)
