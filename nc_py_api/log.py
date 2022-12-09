import logging
from os import environ

logging.basicConfig(format="%(levelname)s:%(name)s:%(module)s:%(funcName)s:%(message)s")
cpa_logger = logging.getLogger("nc_py_api")
cpa_logger.setLevel(level=environ.get("CPA_LOGLEVEL", "INFO").upper())

cpa_logger.debug("Logger initialized.")
