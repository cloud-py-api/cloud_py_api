"""
Helper functions related to get files content or storages info.
"""
from os import environ, path
from pathlib import Path
from typing import TypedDict

from .config import CONFIG
from .db_requests import (
    get_directory_list,
    get_mimetype_id,
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
    mimetype: str
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


USER_ID = environ.get("USER_ID", "")
DIR_MIMETYPE = get_mimetype_id("'httpd/unix-directory'")
STORAGES_INFO = get_storages_info()
ND_ACCESS_LIMIT = get_non_direct_access_filesize_limit()
"""A value from the config that defines the maximum file size allowed to be requested from php."""


def list_directory(file_id: int, user_id=USER_ID) -> list[FsNodeInfo]:
    _ = user_id  # noqa # will be used in 0.4.0 version
    dir_info = get_paths_by_ids([file_id])
    file_mounts = []
    if dir_info:
        file_mounts = get_mounts_to(dir_info[0]["storage"], dir_info[0]["path"])
    raw_result = get_directory_list(file_id, file_mounts)
    result: list[FsNodeInfo] = []
    for i in raw_result:
        result.append(
            {
                "id": i["fileid"],
                "is_dir": i["mimetype"] == DIR_MIMETYPE,
                "is_local": is_local_storage(i["storage"]),
                "mimetype": i["mimetype"],
                "name": i["name"],
                "internal_path": i["path"],
                "abs_path": get_file_full_path(i["storage"], i["path"]),
                "size": i["size"],
                "permissions": i["permissions"],
                "mtime": i["mtime"],
                "checksum": i["checksum"],
                "encrypted": i["encrypted"],
                "etag": i["etag"],
                "ownerName": get_storage_user_id(i["storage"]),
                "storageId": i["storage"],
                "mountId": get_storage_root_id(i["storage"]),
                "direct_access": can_directly_access_file(i),
            }
        )
    return result


def get_file_data(file_info: FsNodeInfo) -> bytes:
    if file_info["direct_access"]:
        try:
            with open(file_info["abs_path"], "rb") as h_file:
                data = h_file.read()
                return data
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
