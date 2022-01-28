from os import path
from urllib.parse import quote_plus
from sqlalchemy import create_engine, event

from .exceptions import NcException, NcNotImplementedError
from . import _ncc


class DbApi:
    __connect_params = {}
    __connect_url = {}

    def __init__(self):
        if _ncc.NCC.task_init_data.config.useDBDirect:
            self.__format_direct_connect_values()
        else:
            # self.__format_tunnel_connect_values()
            raise NcNotImplementedError('useDBDirect = False')

    def create_engine(self, auto_table_prefix: bool = True):
        _exec_options = {}
        if auto_table_prefix and self.table_prefix:
            _exec_options['table_prefix'] = self.table_prefix
        engine = create_engine(**self.connect_data, future=True, execution_options=_exec_options)
        if engine:
            @event.listens_for(engine, "before_cursor_execute", retval=True)
            def before_cursor_execute(_conn, _cursor, statement, parameters, context, _executemany):
                __table_prefix = context.execution_options.get("table_prefix", None)
                if __table_prefix:
                    statement = statement.replace("*PREFIX*", __table_prefix)
                return statement, parameters
        return engine

    @property
    def connect_data(self) -> dict:
        if not _ncc.NCC.task_init_data.config.useDBDirect:
            raise NcException('useDBDirect is False, you must use create_engine().')
        return {'url': self.__connect_url, 'connect_args': self.__connect_params}

    @property
    def table_prefix(self) -> str:
        return _ncc.NCC.task_init_data.dbCfg.dbPrefix

    def __format_direct_connect_values(self):
        __socket_dict_name = 'unix_socket'
        __spike_socket_end_value = ''
        if _ncc.NCC.task_init_data.dbCfg.dbType == 'mysql':
            self.__connect_url = 'mysql+pymysql'
            self.__connect_params['charset'] = 'utf8mb4'
        elif _ncc.NCC.task_init_data.dbCfg.dbType == 'pgsql':
            self.__connect_url = 'postgresql+pg8000'
            __socket_dict_name = 'unix_sock'
            __spike_socket_end_value = '.s.PGSQL.5432'
        elif _ncc.NCC.task_init_data.dbCfg.dbType == 'oci':
            self.__connect_url = 'oracle+cx_oracle'
        else:
            raise NcNotImplementedError(f'Unknown database provider:{_ncc.NCC.task_init_data.dbCfg.dbType}')
        __host, __socket = self.__parse_host_value(_ncc.NCC.task_init_data.dbCfg.dbHost)
        if not __host and not __socket:
            if _ncc.NCC.task_init_data.dbCfg.iniDbSocket:
                __socket = _ncc.NCC.task_init_data.dbCfg.iniDbSocket
            elif _ncc.NCC.task_init_data.dbCfg.iniDbHost:
                __host = _ncc.NCC.task_init_data.dbCfg.iniDbHost
                if _ncc.NCC.task_init_data.dbCfg.iniDbPort:
                    __host += ':' + _ncc.NCC.task_init_data.dbCfg.iniDbPort
        self.__connect_url += '://' + _ncc.NCC.task_init_data.dbCfg.dbUser + \
                              ':' + quote_plus(_ncc.NCC.task_init_data.dbCfg.dbPass) + \
                              '@' + __host + '/' + _ncc.NCC.task_init_data.dbCfg.dbName
        if __socket:
            if __spike_socket_end_value:
                path.join(__socket, __spike_socket_end_value)
            self.__connect_params[__socket_dict_name] = __socket

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
