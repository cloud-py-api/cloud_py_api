"""
File contains class CloudApi to use in applications for working with Nextcloud server.

See the README file for information on usage and redistribution.
"""
from typing import Union
from re import sub, IGNORECASE

from .log_lvl import LogLvl
from .db_api import DbApi
from .fs_api import FsApi
from . import _ncc


class CloudApi:
    db: DbApi
    """Class py:currentmodule::db_api"""
    fs: FsApi

    def __init__(self):
        self.db = DbApi()
        self.fs = FsApi()

    @staticmethod
    def log(log_lvl: Union[int, LogLvl], mod_name: str, content: Union[str, list, tuple]) -> None:
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
