"""
File contains class CloudApi to use in applications for working with Nextcloud server.

See the README file for information on usage and redistribution.
"""
from typing import Union
from re import sub, IGNORECASE
from logging import Logger
from enum import Enum

from .db_api import DbApi
from .fs_api import FsApi
from . import _ncc


class LogLvl(Enum):
    """
    Log levels for py:method::`~CloudApi.log`
    See https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/logging_configuration.html#log-level
    """
    DEBUG = 0
    INFO = 1
    WARN = 2
    ERROR = 3
    FATAL = 4


class CloudApi:
    db: DbApi
    """Class py:currentmodule::db_api"""
    fs: FsApi
    log: Logger         # CloudLogHandler

    def __init__(self):
        self.log = _ncc.NCC.logger
        self.db = DbApi()
        self.fs = FsApi()

    @property
    def nc_loglvl(self):
        return _ncc.NCC.task_init_data.config.log_lvl

    @staticmethod
    def to_log(log_lvl: Union[int, LogLvl], mod_name: str, content: Union[str, list, tuple]) -> None:
        """Send logs to Nextcloud server. Log levels are the same as in Nextcloud and described in LogLvl class."""
        if isinstance(log_lvl, LogLvl):
            log_lvl = log_lvl.value
        _ncc.NCC.log(log_lvl, mod_name, content)

    @staticmethod
    def occ_call(occ_task, *params, decode: bool = True) -> [bool, Union[str, bytes]]:
        """Wrapper for occ calls. If decode=False then raw stdout data will be returned from occ."""
        success, result = _ncc.NCC.occ_call('--no-warnings', occ_task, *params)
        if not success:
            return False, result.decode('utf-8').rstrip('\n')
        if decode:
            clear_result = result.decode('utf-8').rstrip('\n')
            clear_result = sub(r'.*app.*require.*upgrade.*\n?', '', clear_result, flags=IGNORECASE)
            clear_result = sub(r'.*occ.*upgrade.*command.*\n?', '', clear_result, flags=IGNORECASE)
            clear_result = clear_result.strip('\n')
            return True, clear_result
        return True, result
