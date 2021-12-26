# -*- coding: utf-8 -*-
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: core.proto
"""Generated protocol buffer code."""
from google.protobuf.internal import enum_type_wrapper
from google.protobuf import descriptor as _descriptor
from google.protobuf import descriptor_pool as _descriptor_pool
from google.protobuf import message as _message
from google.protobuf import reflection as _reflection
from google.protobuf import symbol_database as _symbol_database
# @@protoc_insertion_point(imports)

_sym_db = _symbol_database.Default()




DESCRIPTOR = _descriptor_pool.Default().AddSerializedFile(b'\n\ncore.proto\"%\n\x07Request\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\"\xb1\x01\n\rTaskInitReply\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x0f\n\x07\x61ppPath\x18\x02 \x01(\t\x12\x0c\n\x04\x61rgs\x18\x06 \x03(\t\x12)\n\x06\x63onfig\x18\x07 \x01(\x0b\x32\x19.TaskInitReply.cfgOptions\x1a:\n\ncfgOptions\x12\x18\n\x07log_lvl\x18\x01 \x01(\x0e\x32\x07.logLvl\x12\x12\n\ndataFolder\x18\x02 \x01(\t\"U\n\nTaskStatus\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x1c\n\x07st_code\x18\x02 \x01(\x0e\x32\x0b.taskStatus\x12\r\n\x05\x65rror\x18\x03 \x01(\t\"7\n\x08TaskExit\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x0f\n\x07msgText\x18\x02 \x01(\t\">\n\x11TaskGetStateReply\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\r\n\x05\x62Stop\x18\x02 \x01(\x08\"a\n\x07TaskLog\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x18\n\x07log_lvl\x18\x02 \x01(\x0e\x32\x07.logLvl\x12\x0f\n\x07sModule\x18\x03 \x01(\t\x12\x0f\n\x07\x63ontent\x18\x04 \x03(\t\")\n\x0bOpenChannel\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\".\n\x10OpenChannelReply\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\"$\n\x06\x46sList\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\")\n\x0b\x46sListReply\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\"\'\n\tFsGetInfo\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\",\n\x0e\x46sGetInfoReply\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\"D\n\x06\x46sRead\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x0e\n\x06userID\x18\x02 \x01(\t\x12\x0e\n\x06\x66ileID\x18\x03 \x01(\t\"p\n\x0b\x46sReadReply\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x1e\n\x07resCode\x18\x02 \x01(\x0e\x32\r.fsResultCode\x12\x10\n\x08\x62NotLast\x18\x03 \x01(\x08\x12\x13\n\x0b\x66ileContent\x18\x04 \x01(\x0c\"&\n\x08\x46sCreate\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\"%\n\x07\x46sWrite\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\"&\n\x08\x46sDelete\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\"$\n\x06\x46sMove\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\"E\n\x07\x46sReply\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x1e\n\x07resCode\x18\x02 \x01(\x0e\x32\r.fsResultCode\"-\n\twhereExpr\x12\x0c\n\x04type\x18\x01 \x01(\t\x12\x12\n\nexpression\x18\x02 \x01(\t\"\x94\x03\n\x08\x44\x62Select\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x0c\n\x04what\x18\x02 \x03(\t\x12\x0c\n\x04\x66rom\x18\x03 \x01(\t\x12\x11\n\tfromAlias\x18\x04 \x01(\t\x12!\n\x05joins\x18\x05 \x03(\x0b\x32\x12.DbSelect.joinType\x12\x1b\n\x07whereas\x18\x06 \x03(\x0b\x32\n.whereExpr\x12\x0f\n\x07groupBy\x18\x07 \x03(\t\x12%\n\x07havings\x18\x08 \x03(\x0b\x32\x14.DbSelect.havingExpr\x12\x0f\n\x07orderBy\x18\t \x03(\t\x12\x12\n\nmaxResults\x18\n \x01(\x03\x12\x13\n\x0b\x66irstResult\x18\x0b \x01(\x03\x1a[\n\x08joinType\x12\x0c\n\x04name\x18\x01 \x01(\t\x12\x11\n\tfromAlias\x18\x02 \x01(\t\x12\x0c\n\x04join\x18\x03 \x01(\t\x12\r\n\x05\x61lias\x18\x04 \x01(\t\x12\x11\n\tcondition\x18\x05 \x01(\t\x1a.\n\nhavingExpr\x12\x0c\n\x04type\x18\x01 \x01(\t\x12\x12\n\nexpression\x18\x02 \x01(\t\"\\\n\rDbSelectReply\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x10\n\x08rowCount\x18\x02 \x01(\x03\x12\r\n\x05\x65rror\x18\x03 \x01(\t\x12\x0e\n\x06handle\x18\x04 \x01(\x03\"Q\n\x08\x44\x62\x43ursor\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x0e\n\x06handle\x18\x02 \x01(\x03\x12\x19\n\x03\x63md\x18\x03 \x01(\x0e\x32\x0c.dbCursorCmd\"\xad\x01\n\rDbCursorReply\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\r\n\x05\x65rror\x18\x02 \x01(\t\x12\x13\n\x0b\x63olumnsName\x18\x03 \x03(\t\x12.\n\x0b\x63olumnsData\x18\x04 \x03(\x0b\x32\x19.DbCursorReply.columnData\x1a,\n\ncolumnData\x12\x10\n\x08\x62Present\x18\x01 \x01(\x08\x12\x0c\n\x04\x64\x61ta\x18\x02 \x01(\x0c\"j\n\x06\x44\x62\x45xec\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x19\n\x04type\x18\x02 \x01(\x0e\x32\x0b.dbExecType\x12\x0c\n\x04what\x18\x03 \x03(\t\x12\x1b\n\x07whereas\x18\x06 \x03(\x0b\x32\n.whereExpr\"O\n\x0b\x44\x62\x45xecReply\x12\x1a\n\x07\x63lassId\x18\x01 \x01(\x0e\x32\t.msgClass\x12\x15\n\rnAffectedRows\x18\x02 \x01(\x03\x12\r\n\x05\x65rror\x18\x03 \x01(\t*\x8a\x02\n\x08msgClass\x12\x0c\n\x08RESERVED\x10\x00\x12\r\n\tTASK_INIT\x10\x01\x12\x0f\n\x0bTASK_STATUS\x10\x02\x12\r\n\tTASK_EXIT\x10\x03\x12\x12\n\x0eTASK_GET_STATE\x10\x04\x12\x0c\n\x08TASK_LOG\x10\x05\x12\x10\n\x0cOPEN_CHANNEL\x10\x06\x12\x0b\n\x07\x46S_LIST\x10\x07\x12\x0f\n\x0b\x46S_GET_INFO\x10\x08\x12\x0b\n\x07\x46S_READ\x10\t\x12\r\n\tFS_CREATE\x10\n\x12\x0c\n\x08\x46S_WRITE\x10\x0b\x12\r\n\tFS_DELETE\x10\x0c\x12\x0b\n\x07\x46S_MOVE\x10\r\x12\r\n\tDB_SELECT\x10\x0e\x12\r\n\tDB_CURSOR\x10\x0f\x12\x0b\n\x07\x44\x42_EXEC\x10\x10*=\n\x06logLvl\x12\t\n\x05\x44\x45\x42UG\x10\x00\x12\x08\n\x04INFO\x10\x01\x12\x08\n\x04WARN\x10\x02\x12\t\n\x05\x45RROR\x10\x03\x12\t\n\x05\x46\x41TAL\x10\x04*s\n\ntaskStatus\x12\x0e\n\nST_SUCCESS\x10\x00\x12\x12\n\x0eST_IN_PROGRESS\x10\x01\x12\x11\n\rST_INIT_ERROR\x10\x02\x12\x10\n\x0cST_EXCEPTION\x10\x03\x12\x0c\n\x08ST_ERROR\x10\x04\x12\x0e\n\nST_UNKNOWN\x10\x05*X\n\x0c\x66sResultCode\x12\x0c\n\x08NO_ERROR\x10\x00\x12\x11\n\rNOT_PERMITTED\x10\x01\x12\n\n\x06LOCKED\x10\x02\x12\r\n\tNOT_FOUND\x10\x03\x12\x0c\n\x08IO_ERROR\x10\x04*2\n\x0b\x64\x62\x43ursorCmd\x12\t\n\x05\x46\x45TCH\x10\x00\x12\r\n\tFETCH_ALL\x10\x01\x12\t\n\x05\x43LOSE\x10\x02*0\n\ndbExecType\x12\n\n\x06INSERT\x10\x00\x12\n\n\x06UPDATE\x10\x01\x12\n\n\x06\x44\x45LETE\x10\x02\x62\x06proto3')

_MSGCLASS = DESCRIPTOR.enum_types_by_name['msgClass']
msgClass = enum_type_wrapper.EnumTypeWrapper(_MSGCLASS)
_LOGLVL = DESCRIPTOR.enum_types_by_name['logLvl']
logLvl = enum_type_wrapper.EnumTypeWrapper(_LOGLVL)
_TASKSTATUS = DESCRIPTOR.enum_types_by_name['taskStatus']
taskStatus = enum_type_wrapper.EnumTypeWrapper(_TASKSTATUS)
_FSRESULTCODE = DESCRIPTOR.enum_types_by_name['fsResultCode']
fsResultCode = enum_type_wrapper.EnumTypeWrapper(_FSRESULTCODE)
_DBCURSORCMD = DESCRIPTOR.enum_types_by_name['dbCursorCmd']
dbCursorCmd = enum_type_wrapper.EnumTypeWrapper(_DBCURSORCMD)
_DBEXECTYPE = DESCRIPTOR.enum_types_by_name['dbExecType']
dbExecType = enum_type_wrapper.EnumTypeWrapper(_DBEXECTYPE)
RESERVED = 0
TASK_INIT = 1
TASK_STATUS = 2
TASK_EXIT = 3
TASK_GET_STATE = 4
TASK_LOG = 5
OPEN_CHANNEL = 6
FS_LIST = 7
FS_GET_INFO = 8
FS_READ = 9
FS_CREATE = 10
FS_WRITE = 11
FS_DELETE = 12
FS_MOVE = 13
DB_SELECT = 14
DB_CURSOR = 15
DB_EXEC = 16
DEBUG = 0
INFO = 1
WARN = 2
ERROR = 3
FATAL = 4
ST_SUCCESS = 0
ST_IN_PROGRESS = 1
ST_INIT_ERROR = 2
ST_EXCEPTION = 3
ST_ERROR = 4
ST_UNKNOWN = 5
NO_ERROR = 0
NOT_PERMITTED = 1
LOCKED = 2
NOT_FOUND = 3
IO_ERROR = 4
FETCH = 0
FETCH_ALL = 1
CLOSE = 2
INSERT = 0
UPDATE = 1
DELETE = 2


_REQUEST = DESCRIPTOR.message_types_by_name['Request']
_TASKINITREPLY = DESCRIPTOR.message_types_by_name['TaskInitReply']
_TASKINITREPLY_CFGOPTIONS = _TASKINITREPLY.nested_types_by_name['cfgOptions']
_TASKSTATUS = DESCRIPTOR.message_types_by_name['TaskStatus']
_TASKEXIT = DESCRIPTOR.message_types_by_name['TaskExit']
_TASKGETSTATEREPLY = DESCRIPTOR.message_types_by_name['TaskGetStateReply']
_TASKLOG = DESCRIPTOR.message_types_by_name['TaskLog']
_OPENCHANNEL = DESCRIPTOR.message_types_by_name['OpenChannel']
_OPENCHANNELREPLY = DESCRIPTOR.message_types_by_name['OpenChannelReply']
_FSLIST = DESCRIPTOR.message_types_by_name['FsList']
_FSLISTREPLY = DESCRIPTOR.message_types_by_name['FsListReply']
_FSGETINFO = DESCRIPTOR.message_types_by_name['FsGetInfo']
_FSGETINFOREPLY = DESCRIPTOR.message_types_by_name['FsGetInfoReply']
_FSREAD = DESCRIPTOR.message_types_by_name['FsRead']
_FSREADREPLY = DESCRIPTOR.message_types_by_name['FsReadReply']
_FSCREATE = DESCRIPTOR.message_types_by_name['FsCreate']
_FSWRITE = DESCRIPTOR.message_types_by_name['FsWrite']
_FSDELETE = DESCRIPTOR.message_types_by_name['FsDelete']
_FSMOVE = DESCRIPTOR.message_types_by_name['FsMove']
_FSREPLY = DESCRIPTOR.message_types_by_name['FsReply']
_WHEREEXPR = DESCRIPTOR.message_types_by_name['whereExpr']
_DBSELECT = DESCRIPTOR.message_types_by_name['DbSelect']
_DBSELECT_JOINTYPE = _DBSELECT.nested_types_by_name['joinType']
_DBSELECT_HAVINGEXPR = _DBSELECT.nested_types_by_name['havingExpr']
_DBSELECTREPLY = DESCRIPTOR.message_types_by_name['DbSelectReply']
_DBCURSOR = DESCRIPTOR.message_types_by_name['DbCursor']
_DBCURSORREPLY = DESCRIPTOR.message_types_by_name['DbCursorReply']
_DBCURSORREPLY_COLUMNDATA = _DBCURSORREPLY.nested_types_by_name['columnData']
_DBEXEC = DESCRIPTOR.message_types_by_name['DbExec']
_DBEXECREPLY = DESCRIPTOR.message_types_by_name['DbExecReply']
Request = _reflection.GeneratedProtocolMessageType('Request', (_message.Message,), {
  'DESCRIPTOR' : _REQUEST,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:Request)
  })
_sym_db.RegisterMessage(Request)

TaskInitReply = _reflection.GeneratedProtocolMessageType('TaskInitReply', (_message.Message,), {

  'cfgOptions' : _reflection.GeneratedProtocolMessageType('cfgOptions', (_message.Message,), {
    'DESCRIPTOR' : _TASKINITREPLY_CFGOPTIONS,
    '__module__' : 'core_pb2'
    # @@protoc_insertion_point(class_scope:TaskInitReply.cfgOptions)
    })
  ,
  'DESCRIPTOR' : _TASKINITREPLY,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:TaskInitReply)
  })
_sym_db.RegisterMessage(TaskInitReply)
_sym_db.RegisterMessage(TaskInitReply.cfgOptions)

TaskStatus = _reflection.GeneratedProtocolMessageType('TaskStatus', (_message.Message,), {
  'DESCRIPTOR' : _TASKSTATUS,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:TaskStatus)
  })
_sym_db.RegisterMessage(TaskStatus)

TaskExit = _reflection.GeneratedProtocolMessageType('TaskExit', (_message.Message,), {
  'DESCRIPTOR' : _TASKEXIT,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:TaskExit)
  })
_sym_db.RegisterMessage(TaskExit)

TaskGetStateReply = _reflection.GeneratedProtocolMessageType('TaskGetStateReply', (_message.Message,), {
  'DESCRIPTOR' : _TASKGETSTATEREPLY,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:TaskGetStateReply)
  })
_sym_db.RegisterMessage(TaskGetStateReply)

TaskLog = _reflection.GeneratedProtocolMessageType('TaskLog', (_message.Message,), {
  'DESCRIPTOR' : _TASKLOG,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:TaskLog)
  })
_sym_db.RegisterMessage(TaskLog)

OpenChannel = _reflection.GeneratedProtocolMessageType('OpenChannel', (_message.Message,), {
  'DESCRIPTOR' : _OPENCHANNEL,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:OpenChannel)
  })
_sym_db.RegisterMessage(OpenChannel)

OpenChannelReply = _reflection.GeneratedProtocolMessageType('OpenChannelReply', (_message.Message,), {
  'DESCRIPTOR' : _OPENCHANNELREPLY,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:OpenChannelReply)
  })
_sym_db.RegisterMessage(OpenChannelReply)

FsList = _reflection.GeneratedProtocolMessageType('FsList', (_message.Message,), {
  'DESCRIPTOR' : _FSLIST,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsList)
  })
_sym_db.RegisterMessage(FsList)

FsListReply = _reflection.GeneratedProtocolMessageType('FsListReply', (_message.Message,), {
  'DESCRIPTOR' : _FSLISTREPLY,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsListReply)
  })
_sym_db.RegisterMessage(FsListReply)

FsGetInfo = _reflection.GeneratedProtocolMessageType('FsGetInfo', (_message.Message,), {
  'DESCRIPTOR' : _FSGETINFO,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsGetInfo)
  })
_sym_db.RegisterMessage(FsGetInfo)

FsGetInfoReply = _reflection.GeneratedProtocolMessageType('FsGetInfoReply', (_message.Message,), {
  'DESCRIPTOR' : _FSGETINFOREPLY,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsGetInfoReply)
  })
_sym_db.RegisterMessage(FsGetInfoReply)

FsRead = _reflection.GeneratedProtocolMessageType('FsRead', (_message.Message,), {
  'DESCRIPTOR' : _FSREAD,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsRead)
  })
_sym_db.RegisterMessage(FsRead)

FsReadReply = _reflection.GeneratedProtocolMessageType('FsReadReply', (_message.Message,), {
  'DESCRIPTOR' : _FSREADREPLY,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsReadReply)
  })
_sym_db.RegisterMessage(FsReadReply)

FsCreate = _reflection.GeneratedProtocolMessageType('FsCreate', (_message.Message,), {
  'DESCRIPTOR' : _FSCREATE,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsCreate)
  })
_sym_db.RegisterMessage(FsCreate)

FsWrite = _reflection.GeneratedProtocolMessageType('FsWrite', (_message.Message,), {
  'DESCRIPTOR' : _FSWRITE,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsWrite)
  })
_sym_db.RegisterMessage(FsWrite)

FsDelete = _reflection.GeneratedProtocolMessageType('FsDelete', (_message.Message,), {
  'DESCRIPTOR' : _FSDELETE,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsDelete)
  })
_sym_db.RegisterMessage(FsDelete)

FsMove = _reflection.GeneratedProtocolMessageType('FsMove', (_message.Message,), {
  'DESCRIPTOR' : _FSMOVE,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsMove)
  })
_sym_db.RegisterMessage(FsMove)

FsReply = _reflection.GeneratedProtocolMessageType('FsReply', (_message.Message,), {
  'DESCRIPTOR' : _FSREPLY,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:FsReply)
  })
_sym_db.RegisterMessage(FsReply)

whereExpr = _reflection.GeneratedProtocolMessageType('whereExpr', (_message.Message,), {
  'DESCRIPTOR' : _WHEREEXPR,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:whereExpr)
  })
_sym_db.RegisterMessage(whereExpr)

DbSelect = _reflection.GeneratedProtocolMessageType('DbSelect', (_message.Message,), {

  'joinType' : _reflection.GeneratedProtocolMessageType('joinType', (_message.Message,), {
    'DESCRIPTOR' : _DBSELECT_JOINTYPE,
    '__module__' : 'core_pb2'
    # @@protoc_insertion_point(class_scope:DbSelect.joinType)
    })
  ,

  'havingExpr' : _reflection.GeneratedProtocolMessageType('havingExpr', (_message.Message,), {
    'DESCRIPTOR' : _DBSELECT_HAVINGEXPR,
    '__module__' : 'core_pb2'
    # @@protoc_insertion_point(class_scope:DbSelect.havingExpr)
    })
  ,
  'DESCRIPTOR' : _DBSELECT,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:DbSelect)
  })
_sym_db.RegisterMessage(DbSelect)
_sym_db.RegisterMessage(DbSelect.joinType)
_sym_db.RegisterMessage(DbSelect.havingExpr)

DbSelectReply = _reflection.GeneratedProtocolMessageType('DbSelectReply', (_message.Message,), {
  'DESCRIPTOR' : _DBSELECTREPLY,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:DbSelectReply)
  })
_sym_db.RegisterMessage(DbSelectReply)

DbCursor = _reflection.GeneratedProtocolMessageType('DbCursor', (_message.Message,), {
  'DESCRIPTOR' : _DBCURSOR,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:DbCursor)
  })
_sym_db.RegisterMessage(DbCursor)

DbCursorReply = _reflection.GeneratedProtocolMessageType('DbCursorReply', (_message.Message,), {

  'columnData' : _reflection.GeneratedProtocolMessageType('columnData', (_message.Message,), {
    'DESCRIPTOR' : _DBCURSORREPLY_COLUMNDATA,
    '__module__' : 'core_pb2'
    # @@protoc_insertion_point(class_scope:DbCursorReply.columnData)
    })
  ,
  'DESCRIPTOR' : _DBCURSORREPLY,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:DbCursorReply)
  })
_sym_db.RegisterMessage(DbCursorReply)
_sym_db.RegisterMessage(DbCursorReply.columnData)

DbExec = _reflection.GeneratedProtocolMessageType('DbExec', (_message.Message,), {
  'DESCRIPTOR' : _DBEXEC,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:DbExec)
  })
_sym_db.RegisterMessage(DbExec)

DbExecReply = _reflection.GeneratedProtocolMessageType('DbExecReply', (_message.Message,), {
  'DESCRIPTOR' : _DBEXECREPLY,
  '__module__' : 'core_pb2'
  # @@protoc_insertion_point(class_scope:DbExecReply)
  })
_sym_db.RegisterMessage(DbExecReply)

if _descriptor._USE_C_DESCRIPTORS == False:

  DESCRIPTOR._options = None
  _MSGCLASS._serialized_start=2208
  _MSGCLASS._serialized_end=2474
  _LOGLVL._serialized_start=2476
  _LOGLVL._serialized_end=2537
  _TASKSTATUS._serialized_start=2539
  _TASKSTATUS._serialized_end=2654
  _FSRESULTCODE._serialized_start=2656
  _FSRESULTCODE._serialized_end=2744
  _DBCURSORCMD._serialized_start=2746
  _DBCURSORCMD._serialized_end=2796
  _DBEXECTYPE._serialized_start=2798
  _DBEXECTYPE._serialized_end=2846
  _REQUEST._serialized_start=14
  _REQUEST._serialized_end=51
  _TASKINITREPLY._serialized_start=54
  _TASKINITREPLY._serialized_end=231
  _TASKINITREPLY_CFGOPTIONS._serialized_start=173
  _TASKINITREPLY_CFGOPTIONS._serialized_end=231
  _TASKSTATUS._serialized_start=233
  _TASKSTATUS._serialized_end=318
  _TASKEXIT._serialized_start=320
  _TASKEXIT._serialized_end=375
  _TASKGETSTATEREPLY._serialized_start=377
  _TASKGETSTATEREPLY._serialized_end=439
  _TASKLOG._serialized_start=441
  _TASKLOG._serialized_end=538
  _OPENCHANNEL._serialized_start=540
  _OPENCHANNEL._serialized_end=581
  _OPENCHANNELREPLY._serialized_start=583
  _OPENCHANNELREPLY._serialized_end=629
  _FSLIST._serialized_start=631
  _FSLIST._serialized_end=667
  _FSLISTREPLY._serialized_start=669
  _FSLISTREPLY._serialized_end=710
  _FSGETINFO._serialized_start=712
  _FSGETINFO._serialized_end=751
  _FSGETINFOREPLY._serialized_start=753
  _FSGETINFOREPLY._serialized_end=797
  _FSREAD._serialized_start=799
  _FSREAD._serialized_end=867
  _FSREADREPLY._serialized_start=869
  _FSREADREPLY._serialized_end=981
  _FSCREATE._serialized_start=983
  _FSCREATE._serialized_end=1021
  _FSWRITE._serialized_start=1023
  _FSWRITE._serialized_end=1060
  _FSDELETE._serialized_start=1062
  _FSDELETE._serialized_end=1100
  _FSMOVE._serialized_start=1102
  _FSMOVE._serialized_end=1138
  _FSREPLY._serialized_start=1140
  _FSREPLY._serialized_end=1209
  _WHEREEXPR._serialized_start=1211
  _WHEREEXPR._serialized_end=1256
  _DBSELECT._serialized_start=1259
  _DBSELECT._serialized_end=1663
  _DBSELECT_JOINTYPE._serialized_start=1524
  _DBSELECT_JOINTYPE._serialized_end=1615
  _DBSELECT_HAVINGEXPR._serialized_start=1617
  _DBSELECT_HAVINGEXPR._serialized_end=1663
  _DBSELECTREPLY._serialized_start=1665
  _DBSELECTREPLY._serialized_end=1757
  _DBCURSOR._serialized_start=1759
  _DBCURSOR._serialized_end=1840
  _DBCURSORREPLY._serialized_start=1843
  _DBCURSORREPLY._serialized_end=2016
  _DBCURSORREPLY_COLUMNDATA._serialized_start=1972
  _DBCURSORREPLY_COLUMNDATA._serialized_end=2016
  _DBEXEC._serialized_start=2018
  _DBEXEC._serialized_end=2124
  _DBEXECREPLY._serialized_start=2126
  _DBEXECREPLY._serialized_end=2205
# @@protoc_insertion_point(module_scope)
