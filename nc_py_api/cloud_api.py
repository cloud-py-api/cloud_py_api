# Version of python API 0.1.0
from typing import Union
from enum import Enum
from io import BytesIO

from .fs import FsResultCode, FsObjId, FsObjInfo


class LogLvl(Enum):
    DEBUG = 0
    INFO = 1
    WARN = 2
    ERROR = 3
    FATAL = 4


_ncc: any


def _pyfrm_set_conn(cloud_connector):
    global _ncc
    _ncc = cloud_connector


class CloudApi:
    @staticmethod
    def log(log_lvl: Union[int, LogLvl], mod_name: str, content: Union[str, list, tuple]) -> None:
        if isinstance(log_lvl, LogLvl):
            log_lvl = log_lvl.value
        _ncc.log(log_lvl, mod_name, content)

    @staticmethod
    def dir_list(fs_id: FsObjId = None) -> list:
        if fs_id is not None:
            return _ncc.fs_list(fs_id.user_id, fs_id.file_id)
        return _ncc.fs_list()

    @staticmethod
    def file_info(fs_id: FsObjId = None) -> Union[FsObjInfo, None]:
        if fs_id is not None:
            return _ncc.fs_info(fs_id.user_id, fs_id.file_id)
        return _ncc.fs_info()

    @staticmethod
    def read_file(fs_id: FsObjId, output_obj: BytesIO, offset: int = 0, bytes_to_read: int = 0) -> FsResultCode:
        return _ncc.fs_read(fs_id.user_id, fs_id.file_id, output_obj, offset, bytes_to_read)

    @staticmethod
    def create_file(name: str, is_dir: bool = False, parent_dir: FsObjId = None, content: bytes = b'') -> FsResultCode:
        return _ncc.fs_create(parent_dir.user_id if parent_dir is not None else '',
                              parent_dir.file_id if parent_dir is not None else 0,
                              name, not is_dir, content)

    @staticmethod
    def write_file(fs_id: FsObjId, content: BytesIO) -> FsResultCode:
        return _ncc.fs_write(fs_id.user_id, fs_id.file_id, content)

    @staticmethod
    def delete_file(fs_id: FsObjId = None) -> FsResultCode:
        if fs_id is None:
            raise ValueError('FsObjId must be specified.')
        return _ncc.fs_delete(fs_id.user_id, fs_id.file_id)
