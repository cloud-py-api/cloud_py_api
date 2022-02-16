from typing import Union
from enum import Enum
from io import BytesIO
import json

from . import _ncc
from .exceptions import NcValueError, FsNotFound, FsNotPermitted, FsLocked, FsIOError


class FsResultCode(Enum):
    NO_ERROR = 0
    NOT_PERMITTED = 1
    LOCKED = 2
    NOT_FOUND = 3
    IO_ERROR = 4


class FsObj:
    id = {}
    info = {}

    def __init__(self, data=None, load: bool = False):
        if data is None:
            self.id['user'] = ''
            self.id['file'] = 0
        else:
            self.init_from(data)
        if load:
            self.load()

    def init_from(self, data) -> None:
        if isinstance(data, str):
            data = json.loads(data)
        if data.get('id') is not None:
            self.id = data.get('id')
            self.info = data.get('info', {})
        else:
            self.id['user'] = data.get('user', '')
            self.id['file'] = data.get('file', 0)

    def __repr__(self):
        return json.dumps({'id': str(self.id), 'info': str(self.info)})

    def load(self) -> None:
        _info = FsApi().info(self)
        if not _info:
            raise FsNotFound(f'No info for:{str(self.id)}')
        self.init_from(_info)

    def list(self) -> list:
        _r = []
        _is_dir = self.info.get('is_dir')
        if _is_dir is not None:
            if not _is_dir:
                return _r
        _objs = FsApi().list(self)
        for _obj in _objs:
            _r.append(FsObj(_obj))
        return _r

    def read(self, offset: int = 0, bytes_to_read: int = 0) -> BytesIO:
        pass

    def write(self, content: BytesIO):
        pass

    def create(self, name: str, is_dir: bool = False, content: bytes = b''):
        pass

    def delete(self) -> None:
        res_code = FsApi().delete(self)
        self.__code_to_exception(res_code)

    def move(self, target, copy: bool = False):
        pass

    def __code_to_exception(self, code: FsResultCode) -> None:
        if code == FsResultCode.NOT_PERMITTED:
            raise FsNotPermitted(f'Operation on {str(self.id)} is not permitted.')
        if code == FsResultCode.LOCKED:
            raise FsLocked(f'FsObject {str(self.id)} is locked.')
        if code == FsResultCode.NOT_FOUND:
            raise FsNotFound(f'FsObject {str(self.id)} can not be found.')
        if code == FsResultCode.IO_ERROR:
            raise FsIOError(f'IO error on operation with {str(self.id)}.')


class FsApi:
    def list(self, fs_obj: Union[None, dict, FsObj] = None) -> list:
        return _ncc.NCC.fs_list(*self.__arg_to_fs_id(fs_obj))

    def info(self, fs_obj: Union[None, dict, FsObj] = None) -> dict:
        return _ncc.NCC.fs_info(*self.__arg_to_fs_id(fs_obj))

    def create(self, name: str, is_dir: bool = False, parent_dir: Union[None, dict, FsObj] = None,
               content: bytes = b'') -> [FsResultCode, dict]:
        if is_dir and len(content) > 0:
            raise NcValueError('Content can be specified only for files.')
        __write_after = False
        if len(content) > _ncc.NCC.task_init_data.config.maxCreateFileContent:
            __write_after = True
        _result, _user_id, _file_id = _ncc.NCC.fs_create(*self.__arg_to_fs_id(parent_dir),
                                                         name, not is_dir, content if not __write_after else b'')
        if _result != FsResultCode.NO_ERROR:
            return _result, {}
        _created_object = {'user': _user_id, 'file': _file_id}
        if __write_after:
            _result = self.write_file(_created_object, BytesIO(content))
        return _result, _created_object

    def read_file(self, fs_obj: Union[dict, FsObj], output_obj: BytesIO,
                  offset: int = 0, bytes_to_read: int = 0) -> FsResultCode:
        return _ncc.NCC.fs_read(*self.__arg_to_fs_id(fs_obj, deny_root=True), output_obj, offset, bytes_to_read)

    def write_file(self, fs_obj: Union[dict, FsObj], content: BytesIO) -> FsResultCode:
        return _ncc.NCC.fs_write(*self.__arg_to_fs_id(fs_obj, deny_root=True), content)

    def delete(self, fs_obj: Union[dict, FsObj]) -> FsResultCode:
        return _ncc.NCC.fs_delete(*self.__arg_to_fs_id(fs_obj, deny_root=True))

    def move(self, fs_obj: Union[dict, FsObj], target_path: str, copy: bool = False) -> [FsResultCode, dict]:
        if target_path:
            raise NcValueError('target_path must be specified.')
        _result, _user_id, _file_id = _ncc.NCC.fs_move(*self.__arg_to_fs_id(fs_obj, deny_root=True), target_path, copy)
        if _result != FsResultCode.NO_ERROR:
            return _result, {}
        return _result, {'user': _user_id, 'file': _file_id}

    @staticmethod
    def __arg_to_fs_id(arg: Union[None, dict, FsObj], deny_root: bool = False) -> [str, int]:
        if arg is None:
            if deny_root:
                raise FsNotPermitted('fs_id can not be None for this method.')
            return '', 0
        if not isinstance(arg, (dict, FsObj)):
            raise NcValueError('fs_id can be None, dict or FsObj only.')
        if isinstance(arg, FsObj):
            arg = arg.id
        elif arg.get('id') is not None:
            arg = arg.get('id')
        if deny_root:
            return arg['user'], arg['file']
        return arg.get('user', ''), arg.get('file', 0)
