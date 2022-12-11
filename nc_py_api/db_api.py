from time import sleep
from typing import Any

from .config import CONFIG
from .db_connectors import create_connection
from .log import cpa_logger as log

CONNECTIONS: list[Any] = [None, None]


def internal_get_connection(connection_id: int) -> Any:
    if CONNECTIONS[connection_id]:
        return CONNECTIONS[connection_id]
    CONNECTIONS[connection_id] = create_connection(CONFIG)
    return CONNECTIONS[connection_id]


def close_connection(connection_id: int = 0):
    if CONNECTIONS[connection_id]:
        try:
            CONNECTIONS[connection_id].close()
        except Exception:  # noqa # pylint: disable=broad-except
            log.exception("DB: Exception during closing connection.")
        CONNECTIONS[connection_id] = None


def internal_execute_fetchall(query: str, connection: Any, args=None) -> list:
    if CONFIG["dbtype"] == "pgsql":
        if args is None:
            args = ()
    result = []
    cur = connection.cursor()
    try:
        cur.execute(query, args)
        if cur.rowcount is not None:
            if cur.rowcount > 0:
                result = cur.fetchall()
                keys = [k[0] for k in cur.description]
                result = [dict(zip(keys, row)) for row in result]
    finally:
        cur.close()
    return result


def execute_fetchall(query: str, args=None, connection_id: int = 0) -> list:
    result = []
    for _ in range(3):
        connection = internal_get_connection(connection_id)
        if not connection:
            sleep(1)
            continue
        try:
            result = internal_execute_fetchall(query=query, connection=connection, args=args)
            break
        except Exception:  # noqa # pylint: disable=broad-except
            log.exception("DB: Exception during executing fetchall.")
            log.debug(query)
        close_connection(connection_id)
    return result


def internal_execute_commit(query: str, connection: Any, args=None) -> int:
    if CONFIG["dbtype"] == "pgsql":
        if args is None:
            args = ()
    result = 0
    cur = connection.cursor()
    try:
        cur.execute(query, args)
        if cur.rowcount is not None:
            if cur.rowcount > 0:
                result = cur.rowcount
                connection.commit()
    finally:
        cur.close()
    return result


def execute_commit(query: str, args=None, connection_id: int = 0) -> int:
    result = 0
    for _ in range(3):
        connection = internal_get_connection(connection_id)
        if not connection:
            sleep(1)
            continue
        try:
            result = internal_execute_commit(query=query, connection=connection, args=args)
            break
        except Exception:  # noqa # pylint: disable=broad-except
            log.exception("DB: Exception during executing commit.")
            log.debug(query)
        close_connection(connection_id)
    return result
