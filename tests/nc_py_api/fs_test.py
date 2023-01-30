import logging
from pathlib import Path

import pytest

from nc_py_api import (
    fs_apply_exclude_lists,
    fs_apply_ignore_flags,
    fs_extract_sub_dirs,
    fs_file_data,
    fs_filter_by,
    fs_list_directory,
    fs_node_info,
    fs_nodes_info,
    fs_sort_by_id,
    get_mimetype_id,
    mimetype,
)

INVALID_FILEID = 18446744073709551610


@pytest.mark.parametrize("test_path", ["", "/"])
@pytest.mark.parametrize("user_id", ["admin"])
def test_node_info_root(test_path, user_id):
    root_dir_info = fs_node_info(test_path, user_id=user_id)
    assert root_dir_info
    assert root_dir_info["mimetype"] == mimetype.DIR
    assert root_dir_info["is_dir"]
    assert root_dir_info["name"] == "files"
    assert root_dir_info["ownerName"] == user_id


@pytest.mark.parametrize("test_path", ["Documents", "Photos", "Templates", "test_dir"])
def test_node_info_path_dirs(test_path):
    dir_info = fs_node_info(test_path)
    assert dir_info
    assert dir_info["mimetype"] == mimetype.DIR
    assert dir_info["is_dir"]
    assert dir_info["name"] == test_path


@pytest.mark.parametrize("test_path", ["test_dir/hopper.png", "/test_dir/复杂 目录 Í/empty_file.bin"])
def test_node_info_path_files(test_path):
    file_info = fs_node_info(test_path)
    assert file_info
    assert not file_info["is_dir"]
    assert file_info["name"] == Path(test_path).parts[-1:][0]


@pytest.mark.parametrize("test_path", ["/test_dir/empty_dir/", "test_dir/empty_dir/", "/test_dir/empty_dir"])
def test_node_info_path_slashes(test_path):
    dir_info = fs_node_info(test_path)
    assert dir_info
    assert dir_info["mimetype"] == mimetype.DIR
    assert dir_info["is_dir"]
    assert dir_info["name"] == "empty_dir"


@pytest.mark.parametrize("test_path", ["/test_dir/复杂 目录 Í/", "test_dir/复杂 目录 Í/", "/test_dir/复杂 目录 Í"])
def test_node_info_path_diff_symbols(test_path):
    dir_info = fs_node_info(test_path)
    assert dir_info
    assert dir_info["mimetype"] == mimetype.DIR
    assert dir_info["is_dir"]
    assert dir_info["name"] == "复杂 目录 Í"


@pytest.mark.parametrize("test_path", ["*-1", "no path", "/no path", "no path/", INVALID_FILEID])
@pytest.mark.parametrize("user_id", ["", None, "non_exist"])
def test_node_info_invalid_input(test_path, user_id):
    logging.disable(logging.CRITICAL)
    path_info = fs_node_info(test_path, user_id=user_id)
    logging.disable(logging.NOTSET)
    assert path_info is None


@pytest.mark.parametrize("test_ids", [[], [0], [INVALID_FILEID], [0, INVALID_FILEID]])
def test_nodes_info_invalid_input(test_ids):
    logging.disable(logging.CRITICAL)
    path_info = fs_nodes_info(test_ids)
    logging.disable(logging.NOTSET)
    assert len(path_info) == 0


@pytest.mark.parametrize("user_id", ["admin"])
def test_list_directory_root(user_id):
    root_dir_listing = fs_list_directory(user_id=user_id)
    # `Documents`, `Photos`, `Templates`, `test_files` folders
    assert len(root_dir_listing) >= 4
    assert any(fs_obj["name"] == "Documents" for fs_obj in root_dir_listing)
    assert any(fs_obj["name"] == "Photos" for fs_obj in root_dir_listing)
    assert any(fs_obj["name"] == "Templates" for fs_obj in root_dir_listing)
    assert any(fs_obj["name"] == "test_dir" for fs_obj in root_dir_listing)


def test_list_directory_by_fileid():
    root_dir_listing = fs_list_directory()
    test_dir_id = [fs_obj["id"] for fs_obj in root_dir_listing if fs_obj["name"] == "test_dir"][0]
    assert fs_list_directory(test_dir_id) == fs_list_directory(fs_node_info("test_dir"))


@pytest.mark.parametrize("file_id", [None, 0, INVALID_FILEID])
@pytest.mark.parametrize("user_id", ["", None, "non_exist"])
def test_list_directory_invalid_input(file_id, user_id):
    logging.disable(logging.CRITICAL)
    root_dir_listing = fs_list_directory(file_id=file_id, user_id=user_id)
    logging.disable(logging.NOTSET)
    assert isinstance(root_dir_listing, list)
    assert not root_dir_listing


def test_list_directory_test_dir():
    test_dir_listing = fs_list_directory(fs_node_info("test_dir"))
    n = len(test_dir_listing)
    assert n == 5
    assert any(fs_obj["name"] == "empty_dir" for fs_obj in test_dir_listing)
    assert any(fs_obj["name"] == "复杂 目录 Í" for fs_obj in test_dir_listing)
    assert any(fs_obj["name"] == "hopper.png" for fs_obj in test_dir_listing)
    assert any(fs_obj["name"] == "test.txt" for fs_obj in test_dir_listing)
    assert any(fs_obj["name"] == "відео та картинки" for fs_obj in test_dir_listing)


@pytest.mark.parametrize("test_path", ["test_dir", "test_dir/hopper.png", "/test_dir/复杂 目录 Í/empty_file.bin"])
def test_node_info_id(test_path):
    file_info1 = fs_node_info(test_path)
    file_info2 = fs_node_info(file_info1["id"])
    assert isinstance(file_info1, dict)
    assert file_info1 == file_info2


def test_nodes_info_ids():
    test_dir_listing = fs_list_directory(fs_node_info("test_dir"))
    ids = [i["id"] for i in test_dir_listing]
    objs_info = fs_nodes_info(ids)
    assert isinstance(objs_info, list)
    n = len(objs_info)
    assert n == 5
    assert all(obj["id"] for obj in objs_info)


def test_parent_field():
    dir_info = fs_node_info("test_dir/empty_dir")
    parent_info = fs_node_info(dir_info["parent_id"])
    assert parent_info["name"] == "test_dir"
    dir_info = fs_node_info("test_dir/hopper.png")
    parent_info = fs_node_info(dir_info["parent_id"])
    assert parent_info["name"] == "test_dir"
    dir_info = fs_node_info("test_dir/empty_dir/empty_file.bin")
    parent_info = fs_node_info(dir_info["parent_id"])
    assert parent_info["name"] == "empty_dir"


def test_node_dir_fields():
    dir_info = fs_node_info("test_dir/empty_dir")
    _ = fs_list_directory(fs_node_info("test_dir"))
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
    file_info = fs_node_info("test_dir/hopper.png")
    _ = fs_list_directory(fs_node_info("test_dir"))
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
    node_info = fs_node_info(file)
    file_data = fs_file_data(node_info)
    assert isinstance(file_data, bytes)
    if file.find("empty") == -1:
        assert file_data
    else:
        assert not len(file_data)


def test_fs_filter_by():
    def len_after_filter_by(field, values, reverse=False) -> int:
        _ = fs_list_directory(fs_node_info("test_dir"))
        fs_filter_by(_, field, values, reverse_filter=reverse)
        return len(_)

    # mimepart
    assert len_after_filter_by("mimepart", [mimetype.IMAGE]) == 1
    assert len_after_filter_by("mimetype", [mimetype.DIR], True) == 2
    # is_dir
    assert len_after_filter_by("is_dir", [True]) == 3
    # is_local
    assert len_after_filter_by("is_local", [True]) == 5
    # name
    assert len_after_filter_by("name", ["test.txt"]) == 1
    assert len_after_filter_by("name", ["test.txt"], True) == 4
    # direct_access
    assert len_after_filter_by("direct_access", [True]) == 5


def test_fs_apply_exclude_lists():
    dir_list = fs_list_directory()
    assert any(fs_obj["name"] == "test_dir" for fs_obj in dir_list)
    fileid = dir_list[0]["id"]
    ids = [i["id"] for i in dir_list]
    assert fileid in ids
    fs_apply_exclude_lists(dir_list, [fileid], [])
    ids = [i["id"] for i in dir_list]
    assert fileid not in ids
    assert any(fs_obj["name"] == "test_dir" for fs_obj in dir_list)
    fs_apply_exclude_lists(dir_list, [], ["test_dir"])
    assert not any(fs_obj["name"] == "test_dir" for fs_obj in dir_list)


def test_fs_apply_ignore_flags():
    dir_list = fs_list_directory(fs_node_info("test_dir/відео та картинки"))
    assert len(dir_list) == 3
    fs_apply_ignore_flags(dir_list)
    assert not len(dir_list)


def test_not_initialized_user_get_files_root_node():
    import nc_py_api

    logging.disable(logging.CRITICAL)
    assert nc_py_api.files.get_files_root_node(user_id="user") is None
    logging.disable(logging.NOTSET)


def test_fs_extract_sub_dirs():
    dir_list = fs_list_directory()
    assert any(fs_obj["is_dir"] for fs_obj in dir_list)
    sub_dirs = fs_extract_sub_dirs(dir_list)
    assert not any(fs_obj["is_dir"] for fs_obj in dir_list)
    assert all(fs_obj["is_dir"] for fs_obj in sub_dirs)
    dir_list = fs_list_directory(fs_node_info("test_dir/empty_dir"))
    sub_dirs = fs_extract_sub_dirs(dir_list)
    assert not sub_dirs


def test_fs_sort_by_id():
    dir1_list = fs_list_directory()
    dir2_list = fs_list_directory(fs_node_info("test_dir"))
    dir3_list = fs_list_directory(fs_node_info("test_dir/відео та картинки"))
    united_dir_list = [*dir2_list, *dir3_list, *dir1_list]
    sorted_dir_list = fs_sort_by_id(united_dir_list)
    assert united_dir_list != sorted_dir_list
    assert sorted_dir_list == fs_sort_by_id(sorted_dir_list)
