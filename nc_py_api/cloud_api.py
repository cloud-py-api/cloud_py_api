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
    __create_file_ex: bool

    def __init__(self, create_file_ex: bool = True):
        self.__create_file_ex = create_file_ex

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

    def create_file(self, name: str, is_dir: bool = False, parent_dir: FsObjId = None,
                    content: bytes = b'') -> [FsResultCode, FsObjId]:
        if is_dir and len(content) > 0:
            raise ValueError('Content can be specified only for files.')
        __write_after = False
        if len(content) > _ncc.task_init_data.config.maxCreateFileContent:
            if not self.__create_file_ex:
                raise ValueError(f'length of content({len(content)}) exceeds config.maxCreateFileContent.')
            __write_after = True
        _result, _user_id, _file_id = _ncc.fs_create(parent_dir.user_id if parent_dir is not None else '',
                                                     parent_dir.file_id if parent_dir is not None else 0,
                                                     name, not is_dir, content if not __write_after else b'')
        created_object = FsObjId(user_id=_user_id, file_id=_file_id)
        if _result == FsResultCode.NO_ERROR:
            if __write_after:
                _result = self.write_file(created_object, BytesIO(content))
        return _result, created_object

    @staticmethod
    def write_file(fs_id: FsObjId, content: BytesIO) -> FsResultCode:
        return _ncc.fs_write(fs_id.user_id, fs_id.file_id, content)

    @staticmethod
    def delete_file(fs_id: FsObjId) -> FsResultCode:
        if fs_id is None:
            raise ValueError('FsObjId must be specified.')
        return _ncc.fs_delete(fs_id.user_id, fs_id.file_id)

    @staticmethod
    def move_file(fs_id: FsObjId, target_path: str, copy: bool = False) -> FsResultCode:
        if fs_id is None:
            raise ValueError('FsObjId must be specified.')
        if target_path:
            raise ValueError('target_path must be specified.')
        return _ncc.fs_move(fs_id.user_id, fs_id.file_id, target_path, copy)
