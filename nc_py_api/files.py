"""
Helper functions related to get files content or storages info.
"""
from fnmatch import fnmatch
from os import environ, path
from pathlib import Path
from typing import Literal, Optional, TypedDict

from . import mimetype
from .config import CONFIG
from .db_requests import (
    get_directory_list,
    get_fileid_info,
    get_fileids_info,
    get_non_direct_access_filesize_limit,
    get_paths_by_ids,
    get_storages_info,
)
from .log import cpa_logger as log
from .occ import occ_call


class FsNodeInfo(TypedDict):
    id: int
    is_dir: bool
    is_local: bool
    mimetype: int
    mimepart: int
    name: str
    internal_path: str
    abs_path: str
    size: int
    permissions: int
    mtime: int
    checksum: str
    encrypted: bool
    etag: str
    ownerName: str
    storageId: int
    mountId: int
    direct_access: bool


FsNodeInfoField = Literal["is_dir", "is_local", "mimetype", "mimepart", "name", "direct_access"]


USER_ID = environ.get("USER_ID", "")
STORAGES_INFO = get_storages_info()
ND_ACCESS_LIMIT = get_non_direct_access_filesize_limit()
"""A value from the config that defines the maximum file size allowed to be requested from php."""


def fs_get_obj_info(file_id: int) -> Optional[FsNodeInfo]:
    raw_result = get_fileid_info(file_id)
    if raw_result:
        return db_record_to_fs_node(raw_result)
    return None


def fs_get_objs_info(file_ids: list[int]) -> list[FsNodeInfo]:
    raw_result = get_fileids_info(file_ids)
    return [db_record_to_fs_node(i) for i in raw_result]


def fs_list_directory(file_id: int, user_id=USER_ID) -> list[FsNodeInfo]:
    _ = user_id  # noqa # will be used in 0.4.0 version
    dir_info = get_paths_by_ids([file_id])
    file_mounts = []
    if dir_info:
        file_mounts = get_mounts_to(dir_info[0]["storage"], dir_info[0]["path"])
    raw_result = get_directory_list(file_id, file_mounts)
    return [db_record_to_fs_node(i) for i in raw_result]


def fs_apply_exclude_lists(fs_objs: list[FsNodeInfo], excl_file_ids: list[int], excl_mask: list[str]) -> None:
    """Purge all records according to exclude_(mask/fileid) from `where_to_purge`(or from fs_records)."""

    indexes_to_purge = []
    for index, fs_obj in enumerate(fs_objs):
        if fs_obj["id"] in excl_file_ids:
            indexes_to_purge.append(index)
        elif is_path_in_exclude(fs_obj["internal_path"], excl_mask):
            indexes_to_purge.append(index)
    for index in reversed(indexes_to_purge):
        del fs_objs[index]


def fs_extract_sub_dirs(fs_objs: list[FsNodeInfo]) -> list[FsNodeInfo]:
    sub_dirs = []
    indexes_to_purge = []
    for index, fs_obj in enumerate(fs_objs):
        if fs_obj["mimetype"] == mimetype.DIR:
            sub_dirs.append(fs_obj)
            indexes_to_purge.append(index)
    for index in reversed(indexes_to_purge):
        del fs_objs[index]
    return sub_dirs


def fs_apply_ignore_flags(fs_objs: list[FsNodeInfo]) -> None:
    ignore_flag = any(fs_obj["name"] in (".noimage", ".nomedia") for fs_obj in fs_objs)
    if ignore_flag:
        fs_filter_by(fs_objs, "mimepart", [mimetype.IMAGE, mimetype.VIDEO], reverse_filter=True)
        fs_apply_exclude_lists(fs_objs, [], [".noimage", ".nomedia"])


def fs_filter_by(fs_objs: list[FsNodeInfo], field: FsNodeInfoField, values: list, reverse_filter=False) -> None:
    indexes_to_purge = []
    if reverse_filter:
        for index, fs_obj in enumerate(fs_objs):
            if fs_obj[field] in values:
                indexes_to_purge.append(index)
    else:
        for index, fs_obj in enumerate(fs_objs):
            if fs_obj[field] not in values:
                indexes_to_purge.append(index)
    for index in reversed(indexes_to_purge):
        del fs_objs[index]


def fs_sort_by_id(fs_objs: list[FsNodeInfo]) -> list[FsNodeInfo]:
    return sorted(fs_objs, key=lambda i: i["id"])


def fs_get_file_data(file_info: FsNodeInfo) -> bytes:
    if file_info["direct_access"]:
        try:
            with open(file_info["abs_path"], "rb") as h_file:
                return h_file.read()
        except Exception:  # noqa # pylint: disable=broad-except
            log.exception("Exception during reading %s", file_info["abs_path"])
    return request_file_from_php(file_info)


def get_storage_info(storage_id: int) -> dict:
    for storage_info in STORAGES_INFO:
        if storage_info["numeric_id"] == storage_id:
            return storage_info
    return {}


def get_storage_mount_point(storage_id: int) -> str:
    storage_info = get_storage_info(storage_id)
    if storage_info:
        return storage_info["mount_point"]
    return ""


def get_storage_user_id(storage_id: int) -> str:
    storage_info = get_storage_info(storage_id)
    if storage_info:
        return storage_info["user_id"]
    return ""


def get_storage_root_id(storage_id: int) -> int:
    storage_info = get_storage_info(storage_id)
    if storage_info:
        return storage_info["root_id"]
    return 0


def request_file_from_php(file_info: FsNodeInfo) -> bytes:
    if file_info["size"] >= ND_ACCESS_LIMIT:
        return b""
    file_data = occ_call("cloud_py_api:getfilecontents", str(file_info["id"]), file_info["ownerName"])
    if file_data is not None:
        return file_data
    log.warning("request fails for %d %s", str(file_info["id"]), file_info["ownerName"])
    return b""


def get_file_full_path(storage_id: int, relative_path: str) -> str:
    storage_info = get_storage_info(storage_id)
    if not storage_info:
        return ""
    path_data = storage_info["id"].split(sep="::", maxsplit=1)
    if len(path_data) != 2:
        log.warning("get_file_full_path: cant parse: %d", storage_info["id"])
        return ""
    if path_data[0] not in ["local", "home"]:
        return ""
    if path_data[1].startswith("/"):
        return path.join(path_data[1], relative_path)
    return path.join(CONFIG["datadir"], path_data[1], relative_path)


def is_local_storage(storage_id: int) -> bool:
    storage_info = get_storage_info(storage_id)
    if not storage_info:
        return False
    if storage_info["available"] == 0:
        return False
    if storage_info.get("storage_backend") is None or storage_info.get("storage_backend") == "local":
        storage_txt_id = storage_info["id"]
        supported_start_list = ("local::", "home::")
        if storage_txt_id.startswith(supported_start_list):
            return True
    return False


def can_directly_access_file(file_info: dict) -> bool:
    if file_info["encrypted"] == 1:
        return False
    return is_local_storage(file_info["storage"])


def get_mounts_to(storage_id: int, dir_path: str) -> list[int]:
    return_list: list[int] = []
    mount_to = get_storage_mount_point(storage_id)
    if not mount_to:
        return return_list
    mount_point_with_dir_path = path.join(mount_to, dir_path)
    for storage_info in STORAGES_INFO:
        if storage_info["mount_point"]:
            if mount_point_with_dir_path == str(Path(storage_info["mount_point"]).parent):
                return_list.append(storage_info["root_id"])
    return return_list


def db_record_to_fs_node(fs_record: dict) -> FsNodeInfo:
    return {
        "id": fs_record["fileid"],
        "is_dir": fs_record["mimetype"] == mimetype.DIR,
        "is_local": is_local_storage(fs_record["storage"]),
        "mimetype": fs_record["mimetype"],
        "mimepart": fs_record["mimepart"],
        "name": fs_record["name"],
        "internal_path": fs_record["path"],
        "abs_path": get_file_full_path(fs_record["storage"], fs_record["path"]),
        "size": fs_record["size"],
        "permissions": fs_record["permissions"],
        "mtime": fs_record["mtime"],
        "checksum": fs_record["checksum"],
        "encrypted": fs_record["encrypted"],
        "etag": fs_record["etag"],
        "ownerName": get_storage_user_id(fs_record["storage"]),
        "storageId": fs_record["storage"],
        "mountId": get_storage_root_id(fs_record["storage"]),
        "direct_access": can_directly_access_file(fs_record),
    }


def is_path_in_exclude(fs_path: str, exclude_patterns: list[str]) -> bool:
    """Checks with fnmatch if `path` is in `exclude_patterns`. Returns ``True`` if yes."""

    name = path.basename(fs_path)
    for pattern in exclude_patterns:
        if fnmatch(name, pattern):
            return True
    return False
