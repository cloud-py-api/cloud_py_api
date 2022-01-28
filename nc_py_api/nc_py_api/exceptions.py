class NcException(Exception):
    """Exception related to all operations with NC."""


class NcNotImplementedError(NcException, NotImplementedError):
    """Exception related to not yet implemented functionality."""


class FsException(NcException):
    """Exception related to operation with FS."""


class FsNotFound(FsException):
    """Exception raises when invalid file or user id specified."""


class FsNotPermitted(FsException):
    """Exception raises when operation not permitted."""


class FsLocked(FsException):
    """Exception raises when file is in locked state."""


class FsIOError(FsException):
    """Exception raises when some unexpected hardware problem occurs."""
