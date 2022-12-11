import pytest

from nc_py_api import get_mimetype_id


@pytest.mark.parametrize("mimetype", ["httpd", "httpd/unix-directory", "application", "text", "image", "video"])
def test_get_mimetype_id(mimetype):
    assert get_mimetype_id(mimetype)


@pytest.mark.parametrize("mimetype", ["", "invalid_mime", None, "'invalid_mime'"])
def test_get_mimetype_id_invalid(mimetype):
    assert not get_mimetype_id(mimetype)
