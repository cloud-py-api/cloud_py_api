from enum import Enum


class FsObjId:
    user_id: str
    file_id: int


class FsObjInfo(FsObjId):
    is_dir: bool
    is_local: bool
    encrypted: bool
    mimetype: str
    name: str
    internal_path: str
    abs_path: str
    size: int
    permissions: int
    mtime: int
    checksum: str
    etag: str
    owner_name: str
    storage_id: str
    mount_id: int

    def __str__(self):
        return f'name:{self.name}, dir:{self.is_dir}, size:{self.size}, owner_name:{self.owner_name}, ' \
               f'mtime:{self.mtime}, permissions:{self.permissions}, ' \
               f'internal:{self.internal_path}, abs:{self.abs_path}'


class FsResultCode(Enum):
    NO_ERROR = 0
    NOT_PERMITTED = 1
    LOCKED = 2
    NOT_FOUND = 3
    IO_ERROR = 4
