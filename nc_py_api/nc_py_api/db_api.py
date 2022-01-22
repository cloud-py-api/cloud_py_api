import logging
from os import path
from urllib.parse import quote_plus
from sqlalchemy import create_engine, event

from .log_lvl import LogLvl
from . import _ncc


class DbLogHandler(logging.Handler):
    def __init__(self):
        super().__init__()

    def emit(self, record):
        self.format(record)
        _ncc.NCC.log(LogLvl.DEBUG.value, 'db_api', f'{record.module}:{record.message}')


class DbApi:
    def __init__(self):
        if _ncc.NCC.task_init_data.config.log_lvl == LogLvl.DEBUG.value:
            logging.basicConfig()
            sql_logger = logging.getLogger('sqlalchemy')
            sql_logger.setLevel(logging.INFO)
            sql_logger.addHandler(DbLogHandler())

    def create_engine(self, auto_table_prefix: bool = True):
        _exec_options = {}
        if auto_table_prefix:
            nc_table_prefix = self.get_table_prefix()
            if nc_table_prefix:
                _exec_options['table_prefix'] = nc_table_prefix
        if _ncc.NCC.task_init_data.config.useDBDirect:
            connect_params = {}
            socket_dict_name = 'unix_socket'
            spike_socket_end_value = ''
            if _ncc.NCC.task_init_data.dbCfg.dbType == 'mysql':
                connect_string = 'mysql+pymysql'
                connect_params['charset'] = 'utf8mb4'
            elif _ncc.NCC.task_init_data.dbCfg.dbType == 'pgsql':
                connect_string = 'postgresql+pg8000'
                socket_dict_name = 'unix_sock'
                spike_socket_end_value = '.s.PGSQL.5432'
            elif _ncc.NCC.task_init_data.dbCfg.dbType == 'oci':
                connect_string = 'oracle+cx_oracle'
            else:
                raise NotImplementedError(f'Unknown database provider:{_ncc.NCC.task_init_data.dbCfg.dbType}')
            _host, _socket = self.__parse_host_value(_ncc.NCC.task_init_data.dbCfg.dbHost)
            if not _host and not _socket:
                if _ncc.NCC.task_init_data.dbCfg.iniDbSocket:
                    _socket = _ncc.NCC.task_init_data.dbCfg.iniDbSocket
                elif _ncc.NCC.task_init_data.dbCfg.iniDbHost:
                    _host = _ncc.NCC.task_init_data.dbCfg.iniDbHost
                    if _ncc.NCC.task_init_data.dbCfg.iniDbPort:
                        _host += ':' + _ncc.NCC.task_init_data.dbCfg.iniDbPort
            connect_string += '://' + _ncc.NCC.task_init_data.dbCfg.dbUser + \
                              ':' + quote_plus(_ncc.NCC.task_init_data.dbCfg.dbPass) + \
                              '@' + _host + '/' + _ncc.NCC.task_init_data.dbCfg.dbName
            if _socket:
                if spike_socket_end_value:
                    path.join(_socket, spike_socket_end_value)
                connect_params[socket_dict_name] = _socket
            engine = create_engine(connect_string, connect_args=connect_params,
                                   future=True, execution_options=_exec_options)
            if engine:
                @event.listens_for(engine, "before_cursor_execute", retval=True)
                def before_cursor_execute(_conn, _cursor, statement, parameters, context, _executemany):
                    __table_prefix = context.execution_options.get("table_prefix", None)
                    if __table_prefix:
                        statement = statement.replace("*PREFIX*", __table_prefix)
                    return statement, parameters
            return engine
        raise NotImplementedError()

    @staticmethod
    def get_connect_string() -> str:
        return ''

    @staticmethod
    def get_table_prefix() -> str:
        return _ncc.NCC.task_init_data.dbCfg.dbPrefix

    @staticmethod
    def __parse_host_value(host_port_socket: str) -> [str, str]:
        _host = ''
        _socket = ''
        _host_port_socket = host_port_socket.split(":", maxsplit=1)
        if len(_host_port_socket) != 1:
            if _host_port_socket[1].isdigit():
                _host = host_port_socket
            else:
                _host = _host_port_socket[0]
                _socket = _host_port_socket[1]
        else:
            if host_port_socket.startswith('/'):
                _socket = host_port_socket
            else:
                _host = host_port_socket
        return _host, _socket
