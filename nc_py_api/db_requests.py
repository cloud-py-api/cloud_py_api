from typing import Optional

from .config import CONFIG
from .db_api import execute_fetchall
from .db_misc import TABLES

FIELD_NAME_LIST = (
    "fcache.fileid, fcache.storage, fcache.path, fcache.storage, fcache.name, "
    "fcache.mimetype, fcache.mimepart, fcache.parent, "
    "fcache.size, fcache.mtime, fcache.encrypted, fcache.etag, fcache.permissions, fcache.checksum"
)


def get_paths_by_ids(file_ids: list[int]) -> list:
    """For each element of list in file_ids return [path, fileid, storage]. Order of file_ids is not preserved."""

    query = (
        "SELECT fcache.path, fcache.fileid, fcache.storage "
        f"FROM {TABLES.file_cache} AS fcache  "
        f"WHERE fileid IN ({','.join(str(x) for x in file_ids)}) "
        "ORDER BY fcache.fileid ASC;"
    )
    return execute_fetchall(query)


def get_storages_info(num_id: Optional[int] = None) -> list:
    """If num_id is None, return info for all storages.

    Returns list of dicts with: numeric_id,id,available,mount_point,user_id,storage_backend fields."""

    if CONFIG["dbtype"] == "mysql":
        check_ext_mounts_query = f'SHOW TABLES LIKE "{TABLES.ext_mounts}";'
    else:
        check_ext_mounts_query = f"SELECT * FROM pg_catalog.pg_tables WHERE tablename LIKE '{TABLES.ext_mounts}';"
    if execute_fetchall(check_ext_mounts_query):
        query = (
            "SELECT storage.numeric_id, storage.id, storage.available, "
            "mounts.mount_point, mounts.user_id, mounts.root_id, ext_mounts.storage_backend "
            f"FROM {TABLES.storages} AS storage "
            f"LEFT JOIN {TABLES.mounts}  AS mounts "
            "ON storage.numeric_id = mounts.storage_id "
            f"LEFT JOIN {TABLES.ext_mounts} AS ext_mounts "
            "ON mounts.mount_id = ext_mounts.mount_id "
        )
    else:
        query = (
            "SELECT storage.numeric_id, storage.id, storage.available, "
            "mounts.mount_point, mounts.user_id, mounts.root_id "
            f"FROM {TABLES.storages} AS storage "
            f"LEFT JOIN {TABLES.mounts}  AS mounts "
            "ON storage.numeric_id = mounts.storage_id"
        )
    if num_id is None:
        query += " WHERE 1;" if CONFIG["dbtype"] == "mysql" else ";"
    else:
        query += f" WHERE storage.numeric_id = {num_id};"
    return execute_fetchall(query)


def get_mimetype_id(mimetype: str) -> int:
    """For string mimetype returns it number representation."""

    query = f"SELECT id FROM {TABLES.mimetypes} WHERE mimetype = '{mimetype}';"
    result = execute_fetchall(query)
    if not result:
        return 0
    return result[0]["id"]


def get_fileid_info(file_id: int) -> dict:
    """Returns dictionary with information for given file id."""

    query = f"SELECT {FIELD_NAME_LIST} FROM {TABLES.file_cache} AS fcache WHERE fcache.fileid = {file_id};"
    result = execute_fetchall(query)
    if result:
        return result[0]
    return {}


def get_fs_obj_info_by_path(obj_path: str, storage_numeric_id: int) -> dict:
    """Returns dictionary with information for given userid:path."""

    query = (
        f"SELECT {FIELD_NAME_LIST} FROM {TABLES.file_cache} AS fcache "
        f"WHERE fcache.path = '{obj_path}' AND fcache.storage = {storage_numeric_id};"
    )
    result = execute_fetchall(query)
    if result:
        return result[0]
    return {}


def get_fileids_info(file_ids: list[int]) -> list[dict]:
    """Returns dictionaries with information for given file ids."""

    query = (
        f"SELECT {FIELD_NAME_LIST} FROM {TABLES.file_cache} AS fcache "
        f"WHERE fcache.fileid IN ({','.join(str(x) for x in file_ids)});"
    )
    return execute_fetchall(query)


def get_directory_list(dir_id: int, mount_points_ids: list[int]) -> list[dict]:
    """Lists the provided directory

    :param dir_id: directory ``id`` to get list of items.
    :param mount_points_ids: ``id`` of files/directories that are mounted to current.

    :returns: list of dictionaries that contains files/directories info in the provided catalog."""

    mp_query = ""
    if mount_points_ids:
        mp_query = f" OR fcache.fileid IN ({','.join(str(x) for x in mount_points_ids)})"
    query = f"SELECT {FIELD_NAME_LIST} FROM {TABLES.file_cache} AS fcache WHERE (fcache.parent = {dir_id}{mp_query});"
    return execute_fetchall(query)


def get_non_direct_access_filesize_limit() -> int:
    query = f"SELECT value FROM {TABLES.settings} WHERE name='remote_filesize_limit';"
    result = execute_fetchall(query)
    if not result:
        return 256 * 1024 * 1024
    return int(result[0]["value"])
