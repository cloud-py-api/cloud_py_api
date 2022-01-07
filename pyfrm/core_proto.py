from typing import Union

import grpc
from core_pb2 import taskStatus, Empty, \
    TaskSetStatusRequest, TaskExitRequest, TaskLogRequest, \
    fsId, FsListRequest, FsGetInfoRequest, FsNodeInfo, FsReadRequest, \
    FsCreateRequest, FsWriteRequest, FsDeleteRequest, FsMoveRequest
import core_pb2_grpc
from helpers import debug_msg
from nc_py_api.fs import FsObjInfo, FsResultCode


class ClientCloudPA:
    task_init_data = None
    _main_channel = None
    _main_stub = None
    _exit_sent: bool = False

    def __init__(self, connect_address: str, auth: str):
        self._main_channel = grpc.insecure_channel(target=connect_address,
                                                   options=[('grpc.enable_retries', 1),
                                                            ('grpc.keepalive_timeout_ms', 10000)
                                                            ])
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
        _fs_id = fsId(userId=user_id, fileId=file_id)
        _fs_reply = self._main_stub.FsList(FsListRequest(dirId=_fs_id))
        _dir_list = []
        for each_obj in _fs_reply.nodes:
            _dir_list.append(self.__node_to_fs_obj_info(each_obj))
        return _dir_list

    def fs_info(self, user_id: str = '', file_id: int = 0) -> Union[FsObjInfo, None]:
        if not user_id:
            user_id = self.task_init_data.config.userId
        if self.task_init_data.config.useFileDirect:
            raise Exception('Not implemented.')
        fs_reply = self._main_stub.FsGetInfo(FsGetInfoRequest(userId=user_id, fileId=file_id))
        if len(fs_reply.nodes):
            return None
        return self.__node_to_fs_obj_info(fs_reply.nodes[0])

    def fs_read(self, user_id: str, file_id: int) -> [FsResultCode, bytes]:
        if self.task_init_data.config.useFileDirect:
            raise Exception('Not implemented.')
        fs_reply = self._main_stub.FsRead(FsReadRequest(userId=user_id, fileId=file_id))
        return FsResultCode(fs_reply.resCode), fs_reply.content

    def fs_create(self, parent_dir_user_id: str, parent_dir_id: int, name: str,
                  is_file: bool, content: bytes = b'') -> FsResultCode:
        if self.task_init_data.config.useFileDirect:
            raise Exception('Not implemented.')
        if not is_file and len(content) > 0:
            raise ValueError('Content can be specified only for files.')
        fs_reply = self._main_stub.FsCreate(FsCreateRequest(userId=parent_dir_user_id, fileId=parent_dir_id,
                                                            name=name, is_file=is_file, content=content))
        return FsResultCode(fs_reply.resCode)

    def fs_write(self, user_id: str, file_id: int, content: bytes) -> FsResultCode:
        if self.task_init_data.config.useFileDirect:
            raise Exception('Not implemented.')
        fs_reply = self._main_stub.FsWrite(FsWriteRequest(userId=user_id, fileId=file_id, content=content))
        return FsResultCode(fs_reply.resCode)

    def fs_delete(self, user_id: str, file_id: int) -> FsResultCode:
        if self.task_init_data.config.useFileDirect:
            raise Exception('Not implemented.')
        fs_reply = self._main_stub.FsDelete(FsDeleteRequest(userId=user_id, fileId=file_id))
        return FsResultCode(fs_reply.resCode)

    def fs_move(self, user_id: str, file_id: int, target_path: str, copy: bool = False) -> FsResultCode:
        if self.task_init_data.config.useFileDirect:
            raise Exception('Not implemented.')
        fs_reply = self._main_stub.FsMove(FsMoveRequest(userId=user_id, fileId=file_id,
                                                        targetPath=target_path, copy=copy))
        return FsResultCode(fs_reply.resCode)
