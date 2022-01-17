from enum import Enum
from typing import Union
from io import BytesIO


class FsObjId:
    user_id: str
    file_id: int

    def __init__(self, user_id: str = '', file_id: int = 0):
        self.user_id = user_id
        self.file_id = file_id


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


class FsApi:
    __create_file_ex: bool
    __ncc: any

    def __init__(self, ncc, create_file_ex):
        self.__ncc = ncc
        self.__create_file_ex = create_file_ex

    def list(self, fs_id: FsObjId = None) -> list:
        if fs_id is not None:
            return self.__ncc.fs_list(fs_id.user_id, fs_id.file_id)
        return self.__ncc.fs_list()

    def info(self, fs_id: FsObjId = None) -> Union[FsObjInfo, None]:
        if fs_id is not None:
            return self.__ncc.fs_info(fs_id.user_id, fs_id.file_id)
        return self.__ncc.fs_info()

    def create(self, name: str, is_dir: bool = False, parent_dir: FsObjId = None,
               content: bytes = b'') -> [FsResultCode, Union[FsObjId, None]]:
        if is_dir and len(content) > 0:
            raise ValueError('Content can be specified only for files.')
        __write_after = False
        if len(content) > self.__ncc.task_init_data.config.maxCreateFileContent:
            if not self.__create_file_ex:
                raise ValueError(f'length of content({len(content)}) exceeds config.maxCreateFileContent.')
            __write_after = True
        _result, _user_id, _file_id = self.__ncc.fs_create(parent_dir.user_id if parent_dir is not None else '',
                                                           parent_dir.file_id if parent_dir is not None else 0,
                                                           name, not is_dir, content if not __write_after else b'')
        if _result != FsResultCode.NO_ERROR:
            return _result, None
        _created_object = FsObjId(user_id=_user_id, file_id=_file_id)
        if __write_after:
            _result = self.write_file(_created_object, BytesIO(content))
        return _result, _created_object

    def read_file(self, fs_id: FsObjId, output_obj: BytesIO, offset: int = 0, bytes_to_read: int = 0) -> FsResultCode:
        return self.__ncc.fs_read(fs_id.user_id, fs_id.file_id, output_obj, offset, bytes_to_read)

    def write_file(self, fs_id: FsObjId, content: BytesIO) -> FsResultCode:
        return self.__ncc.fs_write(fs_id.user_id, fs_id.file_id, content)

    def delete(self, fs_id: FsObjId) -> FsResultCode:
        if fs_id is None:
            raise ValueError('FsObjId must be specified.')
        return self.__ncc.fs_delete(fs_id.user_id, fs_id.file_id)

    def move(self, fs_id: FsObjId, target_path: str, copy: bool = False) -> [FsResultCode, Union[FsObjId, None]]:
        if fs_id is None:
            raise ValueError('FsObjId must be specified.')
        if target_path:
            raise ValueError('target_path must be specified.')
        _result, _user_id, _file_id = self.__ncc.fs_move(fs_id.user_id, fs_id.file_id, target_path, copy)
        if _result != FsResultCode.NO_ERROR:
            return _result, None
        _moved_object = FsObjId(user_id=_user_id, file_id=_file_id)
        return _result, _moved_object
