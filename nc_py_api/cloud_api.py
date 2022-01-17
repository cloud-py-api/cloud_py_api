# Version of python API 0.1.0
from typing import Union
from enum import Enum


from .fs_api import FsApi
# from .db_api import DbApi


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
    fs: FsApi
#    db: DbApi

    def __init__(self, create_file_ex: bool = True):
        self.fs = FsApi(_ncc, create_file_ex)
#        self.db = DbApi(_ncc)

    @staticmethod
    def log(log_lvl: Union[int, LogLvl], mod_name: str, content: Union[str, list, tuple]) -> None:
        if isinstance(log_lvl, LogLvl):
            log_lvl = log_lvl.value
        _ncc.log(log_lvl, mod_name, content)
