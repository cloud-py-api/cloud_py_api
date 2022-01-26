"""
File contains class CloudApi to use in applications for working with Nextcloud server.

See the README file for information on usage and redistribution.
"""
from typing import Union
from re import sub, IGNORECASE
from logging import Logger

from .db_api import DbApi
from .fs_api import FsApi
from . import _ncc


class CloudApi:
    db: DbApi
    """Class py:currentmodule::db_api"""
    fs: FsApi
    log: Logger

    def __init__(self):
        self.db = DbApi()
        self.fs = FsApi()
        self.log = _ncc.NCC.logger

    @property
    def nc_loglvl(self):
        return _ncc.NCC.task_init_data.config.log_lvl

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
