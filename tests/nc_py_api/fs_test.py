import logging
from pathlib import Path

import pytest

from nc_py_api import (
    fs_file_data,
    fs_list_directory,
    fs_node_info,
    get_mimetype_id,
    mimetype,
)


@pytest.mark.parametrize("test_path", ["", "/"])
@pytest.mark.parametrize("user_id", ["admin"])
def test_node_info_root(test_path, user_id):
    root_dir_info = fs_node_info(test_path, user_id=user_id)
    assert root_dir_info
    assert root_dir_info["mimetype"] == mimetype.DIR
    assert root_dir_info["is_dir"]
    assert root_dir_info["name"] == "files"


@pytest.mark.parametrize("test_path", ["Documents", "Photos", "Templates", "test_dir"])
def test_node_info_path_dirs(test_path):
    dir_info = fs_node_info(test_path, user_id="admin")
    assert dir_info
    assert dir_info["mimetype"] == mimetype.DIR
    assert dir_info["is_dir"]
    assert dir_info["name"] == test_path


@pytest.mark.parametrize("test_path", ["test_dir/hopper.png", "/test_dir/复杂 目录 Í/empty_file.bin"])
def test_node_info_path_files(test_path):
    file_info = fs_node_info(test_path, user_id="admin")
    assert file_info
    assert not file_info["is_dir"]
    assert file_info["name"] == Path(test_path).parts[-1:][0]


@pytest.mark.parametrize("test_path", ["/test_dir/empty_dir/", "test_dir/empty_dir/", "/test_dir/empty_dir"])
def test_node_info_path_slashes(test_path):
    dir_info = fs_node_info(test_path, user_id="admin")
    assert dir_info
    assert dir_info["mimetype"] == mimetype.DIR
    assert dir_info["is_dir"]
    assert dir_info["name"] == "empty_dir"


@pytest.mark.parametrize("test_path", ["/test_dir/复杂 目录 Í/", "test_dir/复杂 目录 Í/", "/test_dir/复杂 目录 Í"])
def test_node_info_path_diff_symbols(test_path):
    dir_info = fs_node_info(test_path, user_id="admin")
    assert dir_info
    assert dir_info["mimetype"] == mimetype.DIR
    assert dir_info["is_dir"]
    assert dir_info["name"] == "复杂 目录 Í"


@pytest.mark.parametrize("test_path", ["*-1", "no path", "/no path", "no path/"])
@pytest.mark.parametrize("user_id", ["", None, "non_exist"])
def test_node_info_invalid_input(test_path, user_id):
    logging.disable(logging.CRITICAL)
    path_info = fs_node_info(test_path, user_id=user_id)
    logging.disable(logging.NOTSET)
    assert path_info is None


@pytest.mark.parametrize("user_id", ["admin"])
def test_list_directory_root(user_id):
    root_dir_listing = fs_list_directory(user_id=user_id)
    # `Documents`, `Photos`, `Templates`, `test_files` folders
    assert len(root_dir_listing) >= 4
    assert any(fs_obj["name"] == "Documents" for fs_obj in root_dir_listing)
    assert any(fs_obj["name"] == "Photos" for fs_obj in root_dir_listing)
    assert any(fs_obj["name"] == "Templates" for fs_obj in root_dir_listing)
    assert any(fs_obj["name"] == "test_dir" for fs_obj in root_dir_listing)


@pytest.mark.parametrize("file_id", [None, 0, 18446744073709551610])
@pytest.mark.parametrize("user_id", ["", None, "non_exist"])
def test_list_directory_invalid_input(file_id, user_id):
    logging.disable(logging.CRITICAL)
    root_dir_listing = fs_list_directory(file_id=file_id, user_id=user_id)
    logging.disable(logging.NOTSET)
    assert isinstance(root_dir_listing, list)
    assert not root_dir_listing


def test_list_directory_test_dir():
    test_dir_listing = fs_list_directory(fs_node_info("test_dir", user_id="admin"), user_id="admin")
    assert len(test_dir_listing) == 4
    assert any(fs_obj["name"] == "empty_dir" for fs_obj in test_dir_listing)
    assert any(fs_obj["name"] == "复杂 目录 Í" for fs_obj in test_dir_listing)
    assert any(fs_obj["name"] == "hopper.png" for fs_obj in test_dir_listing)
    assert any(fs_obj["name"] == "test.txt" for fs_obj in test_dir_listing)


@pytest.mark.parametrize("test_path", ["test_dir", "test_dir/hopper.png", "/test_dir/复杂 目录 Í/empty_file.bin"])
def test_node_info_id(test_path):
    file_info1 = fs_node_info(test_path, user_id="admin")
    file_info2 = fs_node_info(file_info1["id"])
    assert isinstance(file_info1, dict)
    assert file_info1 == file_info2


def test_node_info_ids():
    test_dir_listing = fs_list_directory(fs_node_info("test_dir", user_id="admin"), user_id="admin")
    ids = [i["id"] for i in test_dir_listing]
    objs_info = fs_node_info(ids)
    assert isinstance(objs_info, list)
    assert len(objs_info) == 4
    assert all(obj["id"] for obj in objs_info)


def test_parent_field():
    dir_info = fs_node_info("test_dir/empty_dir", user_id="admin")
    parent_info = fs_node_info(dir_info["parent_id"])
    assert parent_info["name"] == "test_dir"
    dir_info = fs_node_info("test_dir/hopper.png", user_id="admin")
    parent_info = fs_node_info(dir_info["parent_id"])
    assert parent_info["name"] == "test_dir"
    dir_info = fs_node_info("test_dir/empty_dir/empty_file.bin", user_id="admin")
    parent_info = fs_node_info(dir_info["parent_id"])
    assert parent_info["name"] == "empty_dir"


def test_node_dir_fields():
    dir_info = fs_node_info("test_dir/empty_dir", user_id="admin")
    _ = fs_list_directory(fs_node_info("test_dir", user_id="admin"), user_id="admin")
    dir_info2 = [i for i in _ if i["name"] == "empty_dir"][0]
    assert dir_info == dir_info2
    assert dir_info["is_dir"]
    assert dir_info["is_local"]
    assert dir_info["mimetype"] == mimetype.DIR
    assert dir_info["mimepart"] == get_mimetype_id("httpd")
    assert dir_info["internal_path"] == "files/test_dir/empty_dir"
    assert dir_info["permissions"] == 31
    assert dir_info["ownerName"] == "admin"
    assert dir_info["size"] == 0


def test_node_file_fields():
    file_info = fs_node_info("test_dir/hopper.png", user_id="admin")
    _ = fs_list_directory(fs_node_info("test_dir", user_id="admin"), user_id="admin")
    file_info2 = [i for i in _ if i["name"] == "hopper.png"][0]
    assert file_info == file_info2
    assert not file_info["is_dir"]
    assert file_info["is_local"]
    assert file_info["mimetype"] == get_mimetype_id("image/png")
    assert file_info["mimepart"] == mimetype.IMAGE
    assert file_info["internal_path"] == "files/test_dir/hopper.png"
    assert file_info["permissions"] == 27
    assert file_info["ownerName"] == "admin"
    assert file_info["size"] == 30605


@pytest.mark.parametrize("file", ["test_dir/hopper.png", "test_dir/test.txt", "/test_dir/复杂 目录 Í/empty_file.bin"])
def test_fs_file_data(file):
    node_info = fs_node_info(file, user_id="admin")
    file_data = fs_file_data(node_info)
    assert isinstance(file_data, bytes)
    if file.find("empty") == -1:
        assert file_data
    else:
        assert not len(file_data)
