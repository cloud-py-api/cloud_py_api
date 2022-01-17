from typing import Union
from threading import Event
from io import BytesIO
from os import SEEK_SET

import grpc
from core_pb2 import taskStatus, Empty, \
    TaskSetStatusRequest, TaskExitRequest, TaskLogRequest, \
    fsId, FsListRequest, FsGetInfoRequest, FsNodeInfo, FsReadRequest, \
    FsCreateRequest, FsWriteRequest, FsDeleteRequest, FsMoveRequest
import core_pb2_grpc
from helpers import debug_msg
from nc_py_api.fs_api import FsObjInfo, FsResultCode


class ClientCloudPA:
    task_init_data = None
    _main_channel = None
    _main_stub = None
    _exit_sent: bool = False
    _connected_event = Event()

    def __wait_for_server_connect(self, channel_connectivity):
        if channel_connectivity in (grpc.ChannelConnectivity.READY, grpc.ChannelConnectivity.IDLE):
            self._connected_event.set()

    def __init__(self, connect_address: str, auth: str):
        self._main_channel = grpc.insecure_channel(target=connect_address,
                                                   options=[('grpc.enable_retries', 1),
                                                            ('grpc.keepalive_timeout_ms', 10000)
                                                            ])
        self._main_channel.subscribe(self.__wait_for_server_connect)
        if not self._connected_event.wait(timeout=5.0):
            raise grpc.RpcError('Timeout connecting to the server')
        self._main_channel.unsubscribe(self.__wait_for_server_connect)
        self._main_stub = core_pb2_grpc.CloudPyApiCoreStub(self._main_channel)
        self.task_init_data = self._main_stub.TaskInit(Empty())
        debug_msg('connected')

    def __del__(self):
        debug_msg('destructor')
        if not self._exit_sent:
            self.exit()

    def set_status(self, status: taskStatus, error: str = '') -> None:
        self._main_stub.TaskStatus(TaskSetStatusRequest(st_code=status,
                                                        error=error))

    def exit(self, result=None) -> None:
        debug_msg('exit()')
        self._exit_sent = True
        try:
            self._main_stub.TaskExit(TaskExitRequest(result=result))
            self._main_channel.close()
        except grpc.RpcError as exc:
            debug_msg(str(exc))

    def log(self, log_lvl: int, mod_name: str, content: Union[str, list, tuple]) -> None:
        if content is None:
            raise ValueError('no log content')
        if self.task_init_data.config.log_lvl <= log_lvl:
            _log_content = []
            if isinstance(content, str):
                _log_content.append(content)
            else:
                for elem in content:
                    _log_content.append(elem)
            self._main_stub.TaskLog(TaskLogRequest(log_lvl=log_lvl,
                                                   module=mod_name if mod_name is not None else '',
                                                   content=_log_content))

    @staticmethod
    def __node_to_fs_obj_info(fs_info_reply: FsNodeInfo) -> FsObjInfo:
        obj_info = FsObjInfo()
        obj_info.user_id = fs_info_reply.fileId.userId
        obj_info.file_id = fs_info_reply.fileId.fileId
        obj_info.is_dir = fs_info_reply.is_dir
        obj_info.is_local = fs_info_reply.is_local
        obj_info.encrypted = fs_info_reply.encrypted
        obj_info.mimetype = fs_info_reply.mimetype
        obj_info.name = fs_info_reply.name
        obj_info.internal_path = fs_info_reply.internal_path
        obj_info.abs_path = fs_info_reply.abs_path
        obj_info.size = fs_info_reply.size
        obj_info.permissions = fs_info_reply.permissions
        obj_info.mtime = fs_info_reply.mtime
        obj_info.checksum = fs_info_reply.checksum
        obj_info.etag = fs_info_reply.etag
        obj_info.owner_name = fs_info_reply.ownerName
        obj_info.storage_id = fs_info_reply.storageId
        obj_info.mount_id = fs_info_reply.mountId
        return obj_info

    def fs_list(self, user_id: str = '', file_id: int = 0) -> list:
        if not user_id:
            user_id = self.task_init_data.config.userId
        if self.task_init_data.config.useFileDirect:
            raise Exception('Not implemented.')
        _fs_reply = self._main_stub.FsList(FsListRequest(dirId=fsId(userId=user_id, fileId=file_id)))
        _dir_list = []
        for each_obj in _fs_reply.nodes:
            _dir_list.append(self.__node_to_fs_obj_info(each_obj))
        return _dir_list

    def fs_info(self, user_id: str = '', file_id: int = 0) -> Union[FsObjInfo, None]:
        if not user_id:
            user_id = self.task_init_data.config.userId
        if self.task_init_data.config.useFileDirect:
            raise Exception('Not implemented.')
        fs_reply = self._main_stub.FsGetInfo(FsGetInfoRequest(fileId=fsId(userId=user_id, fileId=file_id)))
        if not len(fs_reply.nodes):
            return None
        return self.__node_to_fs_obj_info(fs_reply.nodes[0])

    def fs_read(self, user_id: str, file_id: int, out_obj: BytesIO, offset: int, bytes_to_read: int) -> FsResultCode:
        if self.task_init_data.config.useFileDirect:
            raise Exception('Not implemented.')
        fs_read_response_iterator = self._main_stub.FsRead(FsReadRequest(fileId=fsId(userId=user_id, fileId=file_id),
                                                                         offset=offset, bytes_to_read=bytes_to_read))
        res_code = FsResultCode.NO_ERROR.value
        for fs_read_response in fs_read_response_iterator:
            res_code = fs_read_response.resCode
            if len(fs_read_response.content):
                out_obj.write(fs_read_response.content)
            if fs_read_response.last:
                break
        out_obj.seek(0, SEEK_SET)
        return FsResultCode(res_code)

    def fs_create(self, parent_dir_user_id: str, parent_dir_id: int, name: str,
                  is_file: bool, content: bytes = b'') -> [FsResultCode, str, int]:
        if not parent_dir_user_id:
            parent_dir_user_id = self.task_init_data.config.userId
        fs_reply = self._main_stub.FsCreate(FsCreateRequest(parentDirId=fsId(userId=parent_dir_user_id,
                                                                             fileId=parent_dir_id),
                                                            name=name, is_file=is_file, content=content))
        return FsResultCode(fs_reply.resCode), fs_reply.fileId.userId, fs_reply.fileId.fileId

    def fs_write(self, user_id: str, file_id: int, content: BytesIO) -> FsResultCode:
        def fs_write_request_generator():
            _last = False
            while not _last:
                data = content.read(self.task_init_data.config.maxChunkSize)
                _last = True if len(data) < self.task_init_data.config.maxChunkSize else False
                _request = FsWriteRequest(fileId=fsId(userId=user_id, fileId=file_id), last=_last, content=data)
                yield _request

        fs_reply = self._main_stub.FsWrite(fs_write_request_generator())
        return FsResultCode(fs_reply.resCode)

    def fs_delete(self, user_id: str, file_id: int) -> FsResultCode:
        fs_reply = self._main_stub.FsDelete(FsDeleteRequest(fileId=fsId(userId=user_id, fileId=file_id)))
        return FsResultCode(fs_reply.resCode)

    def fs_move(self, user_id: str, file_id: int, target_path: str, copy: bool = False) -> [FsResultCode, str, int]:
        fs_reply = self._main_stub.FsMove(FsMoveRequest(fileId=fsId(userId=user_id, fileId=file_id),
                                                        targetPath=target_path, copy=copy))
        return FsResultCode(fs_reply.resCode), fs_reply.fileId.userId, fs_reply.fileId.fileId
