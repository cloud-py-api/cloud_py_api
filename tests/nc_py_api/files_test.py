import pytest

import nc_py_api


@pytest.mark.parametrize("user_id", ["admin"])
def test_fs_list_directory(user_id):
    root_dir_listing = nc_py_api.fs_list_directory(user_id=user_id)
    # `Documents`, `Photos`, `Templates`, `test_files` folders
    assert len(root_dir_listing) >= 4
    assert any(fs_obj["name"] == "Documents" for fs_obj in root_dir_listing)
    assert any(fs_obj["name"] == "Photos" for fs_obj in root_dir_listing)
    assert any(fs_obj["name"] == "Templates" for fs_obj in root_dir_listing)
    assert any(fs_obj["name"] == "test_dir" for fs_obj in root_dir_listing)
    test_dir = [fs_obj for fs_obj in root_dir_listing if fs_obj["name"] == "test_dir"][0]
    assert test_dir["is_dir"]
    assert test_dir["is_local"]
    assert test_dir["mimetype"] == nc_py_api.mimetype.DIR
    assert test_dir["mimepart"] == nc_py_api.get_mimetype_id("httpd")
    assert test_dir["internal_path"] == "files/test_dir"
    assert test_dir["permissions"] == 31
    assert test_dir["ownerName"] == user_id
    test_dir_listing = nc_py_api.fs_list_directory(test_dir["id"])
    assert test_dir_listing == nc_py_api.fs_list_directory(test_dir)  # results should be the same
    empty_dir = [fs_obj for fs_obj in test_dir_listing if fs_obj["name"] == "empty_dir"][0]
    assert empty_dir["size"] == 0
    # directory should be with one empty file
    assert len(nc_py_api.fs_list_directory(empty_dir)) == 1  # pass FsNodeInfo as fileid
    assert len(nc_py_api.fs_list_directory(empty_dir["id"])) == 1  # pass fileid as fileid
    hopper_img = [fs_obj for fs_obj in test_dir_listing if fs_obj["name"] == "hopper.png"][0]
    assert not hopper_img["is_dir"]
    assert hopper_img["is_local"]
    assert hopper_img["mimetype"] == nc_py_api.get_mimetype_id("image/png")
    assert hopper_img["mimepart"] == nc_py_api.mimetype.IMAGE
    assert hopper_img["internal_path"] == "files/test_dir/hopper.png"
    assert hopper_img["permissions"] == 27
    assert hopper_img["ownerName"] == user_id
    # probably tests should be divided into smaller parts, need api for getting FsNode by internal_path + user_id...
