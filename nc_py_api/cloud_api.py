# Version of python API 0.1.0
from typing import Union
from enum import Enum


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
    def log(log_lvl: int, mod_name: str, content: Union[str, list]) -> None:
        _ncc.log(log_lvl, mod_name, content)
