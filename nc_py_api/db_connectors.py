"""
File contains logic for establishing connection to DB.
"""

from os import environ

from pg8000.dbapi import connect as pgsql_connect
from pymysql import connect as mysql_connect

from .log import cpa_logger as log


def create_connection(config: dict, log_errors=True):
    connection = None
    try:
        if config["dbtype"] == "mysql":
            if config.get("usock", None):
                connection = mysql_connect(
                    unix_socket=config["usock"],
                    user=config["dbuser"],
                    password=config["dbpassword"],
                    database=config["dbname"],
                    charset="utf8mb4",
                )
            else:
                port = int(config["dbport"]) if config.get("dbport", None) else 3306
                host = config["dbhost"] if config.get("dbhost", None) else "localhost"
                connection = mysql_connect(
                    host=host,
                    port=port,
                    user=config["dbuser"],
                    password=config["dbpassword"],
                    database=config["dbname"],
                    charset="utf8mb4",
                )
        elif config["dbtype"] == "pgsql":
            if config.get("usock", None):
                connection = pgsql_connect(
                    unix_sock=config["usock"],
                    user=config["dbuser"],
                    password=config["dbpassword"],
                    database=config["dbname"],
                )
            else:
                port = int(config["dbport"]) if config.get("dbport", None) else 5432
                host = config["dbhost"] if config.get("dbhost", None) else "localhost"
                connection = pgsql_connect(
                    host=host,
                    port=port,
                    user=config["dbuser"],
                    password=config["dbpassword"],
                    database=config["dbname"],
                )
    except Exception:  # noqa # pylint: disable=broad-except
        if log_errors:
            log.exception("create_connection exception:")
    return connection


def connection_test(config: dict, log_errors=False) -> bool:
    if environ.get("CPA_LOGLEVEL", "").upper() == "DEBUG":
        log_errors = True
    connection = create_connection(config, log_errors=log_errors)
    if connection is not None:
        connection.close()
        return True
    return False
