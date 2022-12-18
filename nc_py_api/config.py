import os
import re
from typing import Any

from .db_connectors import connection_test
from .log import cpa_logger as log
from .occ import get_cloud_config_value, php_call

MAP_SCHEME: dict[str, list] = {
    "dbname": ["dbname", None],
    "dbtprefix": ["dbtableprefix", "oc_"],
    "dbuser": ["dbuser", None],
    "dbpassword": ["dbpassword", None],
    "dbhost": ["dbhost", None],
    "dbtype": ["dbtype", None],
    "datadir": ["datadirectory", None],
}

CONFIG: dict[str, Any] = {}


for key, value in MAP_SCHEME.items():
    CONFIG[key] = get_cloud_config_value(value[0], value[1])
    if CONFIG[key] is None:
        log.error("Cant find {%s} value in NC config.", value[0])

# Here we need to postprocess `dbhost` value and fill `usock` or `dbport` from `dbhost`
if CONFIG["dbhost"]:
    host_port_socket = CONFIG["dbhost"].split(":", maxsplit=1)
    if len(host_port_socket) > 1:
        # when dbhost = host:port or host:socket
        CONFIG["dbhost"] = host_port_socket[0]
        if os.path.exists(host_port_socket[1]):
            CONFIG["usock"] = host_port_socket[1]
        elif host_port_socket[1].isdigit():
            CONFIG["dbport"] = host_port_socket[1]
        else:
            log.warning("Unknown socket or port value. ({%s})", CONFIG["dbhost"])
    elif os.path.exists(CONFIG["dbhost"]):
        # when dbhost = socket
        CONFIG["usock"] = CONFIG["dbhost"]
        # config['dbhost'] = ''  # removed to fix this: https://github.com/andrey18106/mediadc/issues/45
    if CONFIG["dbtype"] == "pgsql":
        # Don't know currently how to handle this situation properly. Using default port value for socket name.
        if CONFIG.get("usock", None):
            CONFIG["usock"] = os.path.join(CONFIG["usock"], ".s.PGSQL.5432")


def finish_db_configuration() -> bool:
    """Finding working way to connect to database, and change CONFIG according to it."""

    # Trying what we parsed from cloud config.
    if connection_test(CONFIG):
        return True
    # Trying without socket.
    if CONFIG.get("usock", None):
        usock = CONFIG["usock"]
        CONFIG["usock"] = ""
        if connection_test(CONFIG):
            return True
        CONFIG["usock"] = usock
    if CONFIG["dbtype"] == "mysql":
        # when no `dbport` or `usock` found in NC config, trying php socket configuration.
        php_info = php_call("-r", "phpinfo();")
        if php_info:
            m_groups = re.search(
                r"pdo_mysql\.default_socket\s*=>\s*(.*)\s*=>\s*(.*)",
                php_info.decode("utf-8").rstrip("\n"),
                flags=re.MULTILINE + re.IGNORECASE,
            )
            if m_groups is None:
                log.warning("Cant parse php info.")
            else:
                socket_path = m_groups.groups()[-1].strip()
                if os.path.exists(socket_path):
                    usock = CONFIG.get("usock", None)
                    CONFIG["usock"] = socket_path
                    if connection_test(CONFIG):
                        return True
                    CONFIG["usock"] = usock
        else:
            log.warning("Cant get php info.")
    # If we got here then all is not so good, as it can be. Last try with default host and port.
    host = CONFIG["dbhost"]
    port = CONFIG.get("dbport", None)
    CONFIG["dbhost"] = ""
    CONFIG["dbport"] = ""
    if connection_test(CONFIG):
        return True
    CONFIG["dbhost"] = host
    if port:
        CONFIG["dbport"] = port
    return False


CONFIG["valid"] = finish_db_configuration() and CONFIG["datadir"]
