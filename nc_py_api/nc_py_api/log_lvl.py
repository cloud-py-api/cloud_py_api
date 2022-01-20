"""
See https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/logging_configuration.html#log-level
"""
from enum import Enum


class LogLvl(Enum):
    """
    Log levels for py:method::`~CloudApi.log`
    """
    DEBUG = 0
    INFO = 1
    WARN = 2
    ERROR = 3
    FATAL = 4
