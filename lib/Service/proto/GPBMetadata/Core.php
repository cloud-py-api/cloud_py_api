<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: core.proto

namespace GPBMetadata;

class Core
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(
            '
� 

core.protoCloud_Py_API"
Empty"�
TaskInitReply
appName (	
modName (	
modPath (	
funcName (	
args (	6
config (2&.Cloud_Py_API.TaskInitReply.cfgOptionsa

cfgOptions%
log_lvl (2.Cloud_Py_API.logLvl

dataFolder (	
frameworkAppData (	"P
TaskSetStatusRequest)
st_code (2.Cloud_Py_API.taskStatus
error (	"!
TaskExitRequest
result (	"X
TaskLogRequest%
log_lvl (2.Cloud_Py_API.logLvl
module (	
content (	"&
fsId
userId (	
fileId (	"6
FsGetInfoRequest"
fileId (2.Cloud_Py_API.fsId"�
FsGetInfoReply"
fileId (2.Cloud_Py_API.fsId
is_dir (
is_local (
mimetype (	
name (	
internal_path (	
abs_path (	
size (
permissions	 (
mtime
 (
checksum (	
	encrypted (
etag (	
	ownerName (	
	storageId (
mountId ("2
FsListRequest!
dirId (2.Cloud_Py_API.fsId":
FsListReply+
nodes (2.Cloud_Py_API.FsGetInfoReply"3
FsReadRequest"
fileId (2.Cloud_Py_API.fsId"K
FsReadReply+
resCode (2.Cloud_Py_API.fsResultCode
content ("j
FsCreateRequest\'
parentDirId (2.Cloud_Py_API.fsId
name (	
is_file (
content ("E
FsWriteRequest"
fileId (2.Cloud_Py_API.fsId
content ("5
FsDeleteRequest"
fileId (2.Cloud_Py_API.fsId"V
FsMoveRequest"
fileId (2.Cloud_Py_API.fsId

targetPath (	
bCopy ("6
FsReply+
resCode (2.Cloud_Py_API.fsResultCode"-
	whereExpr
type (	

expression (	"(
	str_alias
name (	
alias (	"�
DbSelectRequest(
columns (2.Cloud_Py_API.str_alias%
from (2.Cloud_Py_API.str_alias5
joins (2&.Cloud_Py_API.DbSelectRequest.joinType(
whereas (2.Cloud_Py_API.whereExpr
groupBy (	9
havings (2(.Cloud_Py_API.DbSelectRequest.havingExpr
orderBy (	

maxResults (
firstResult	 ([
joinType
name (	
	fromAlias (	
join (	
alias (	
	condition (	.

havingExpr
type (	

expression (	"@
DbSelectReply
rowCount (
error (	
handle ("
DbCursorRequest/
cmd (2".Cloud_Py_API.DbCursorRequest.cCmd
handle ("+
cCmd	
FETCH 
	FETCH_ALL	
CLOSE"�
DbCursorReply
error (	
columnsName (	;
columnsData (2&.Cloud_Py_API.DbCursorReply.columnData,

columnData
bPresent (
data ("�
DbExecRequest/
type (2!.Cloud_Py_API.DbExecRequest.rType

table_name (	
columns (	
values ((
whereas (2.Cloud_Py_API.whereExpr"+
rType

INSERT 

UPDATE

DELETE"3
DbExecReply
nAffectedRows (
error (	"j
ServerCommand.
id (2".Cloud_Py_API.ServerCommand.cmd_id")
cmd_id
TASK_NOTHING 
	TASK_STOP*=
logLvl	
DEBUG 
INFO
WARN	
ERROR	
FATAL*s

taskStatus

ST_SUCCESS 
ST_IN_PROGRESS
ST_INIT_ERROR
ST_EXCEPTION
ST_ERROR

ST_UNKNOWN*X
fsResultCode
NO_ERROR 
NOT_PERMITTED

LOCKED
	NOT_FOUND
IO_ERROR2�
CloudPyApiCore>
TaskInit.Cloud_Py_API.Empty.Cloud_Py_API.TaskInitReply" G

TaskStatus".Cloud_Py_API.TaskSetStatusRequest.Cloud_Py_API.Empty" @
TaskExit.Cloud_Py_API.TaskExitRequest.Cloud_Py_API.Empty" >
TaskLog.Cloud_Py_API.TaskLogRequest.Cloud_Py_API.Empty" A
	CmdStream.Cloud_Py_API.Empty.Cloud_Py_API.ServerCommand" 0E
	FsGetInfo.Cloud_Py_API.FsListRequest.Cloud_Py_API.FsListReply" B
FsList.Cloud_Py_API.FsListRequest.Cloud_Py_API.FsListReply" D
FsRead.Cloud_Py_API.FsReadRequest.Cloud_Py_API.FsReadReply" 0B
FsCreate.Cloud_Py_API.FsCreateRequest.Cloud_Py_API.FsReply" B
FsWrite.Cloud_Py_API.FsWriteRequest.Cloud_Py_API.FsReply" (B
FsDelete.Cloud_Py_API.FsDeleteRequest.Cloud_Py_API.FsReply" >
FsMove.Cloud_Py_API.FsMoveRequest.Cloud_Py_API.FsReply" H
DbSelect.Cloud_Py_API.DbSelectRequest.Cloud_Py_API.DbSelectReply" H
DbCursor.Cloud_Py_API.DbCursorRequest.Cloud_Py_API.DbCursorReply" B
DbExec.Cloud_Py_API.DbExecRequest.Cloud_Py_API.DbExecReply" bproto3'
        , true);

        static::$is_initialized = true;
    }
}

