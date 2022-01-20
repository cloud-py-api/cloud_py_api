"""
File contains class CloudApi to use in applications for working with Nextcloud server.

See the README file for information on usage and redistribution.
"""
from typing import Union

from .log_lvl import LogLvl
from .fs_api import FsApi
from .db_api import DbApi


_NCC: any
"""Private class created by py:module::`pyfrm`"""


def _pyfrm_set_conn(cloud_connector):
    """Private. Used by py:module::`pyfrm` to set class with internal functions implementation."""
    global _NCC                                                 # pylint: disable=global-statement
    _NCC = cloud_connector


class CloudApi:
    fs: FsApi
    """Class py:currentmodule::fs_api"""
    db: DbApi
    """Class py:currentmodule::db_api"""

    def __init__(self, create_file_ex: bool = True):
        """
        Creates and instance of class for python app to use to work wih Nextcloud.
        :param create_file_ex: If py:method::`~FsApi.create` functions must invoke write_file,
         when content to create are greater then maxCreateFileContent.
        :returns: Connected class instance to Nextcloud cloud_py_api app server part.
        """
        self.fs = FsApi(_NCC, create_file_ex)
        self.db = DbApi(_NCC)

    @staticmethod
    def log(log_lvl: Union[int, LogLvl], mod_name: str, content: Union[str, list, tuple]) -> None:
        """Send logs to Nextcloud server. Log levels are the same as in Nextcloud and described in LogLvl class."""
        if isinstance(log_lvl, LogLvl):
            log_lvl = log_lvl.value
        _NCC.log(log_lvl, mod_name, content)

    @staticmethod
    def occ_call():
        pass
