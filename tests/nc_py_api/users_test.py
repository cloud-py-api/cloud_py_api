import pytest

from nc_py_api import current_user_info, user_info, users_info


def test_user_info():
    info = current_user_info()
    assert info
    assert info["uid"]
    assert info["password"]
    assert info["uid_lower"]
    assert "display_name" in info.keys()
    assert info == user_info("admin")


def test_users_info():
    info = users_info()
    assert len(info) == 2
    for i in info:
        assert i["uid"]
        assert i["password"]
        assert i["uid_lower"]
        assert "display_name" in i.keys()


@pytest.mark.parametrize("uid", ["", "invalid user"])
def test_invalid_user(uid):
    assert user_info(uid) is None
