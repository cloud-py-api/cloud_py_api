/**
 * @copyright Copyright (c) 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Copyright (c) 2022-2023 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace php OCA.Cloud_Py_API.TProto
namespace py TProto

include "core.thrift"
include "fs.thrift"
include "db.thrift"

service CloudPyApi {
  core.TaskInitReply TaskInit(),
  core.Empty TaskStatus(1: core.TaskSetStatusRequest request),
  core.Empty AppCheck(1: core.CheckDataRequest request),
  core.Empty TaskExit(1: core.TaskExitRequest request),
  core.Empty TaskLog(1: core.TaskLogRequest request),
  core.OccReply OccCall(1: core.OccRequest request),
  fs.FsListReply FsGetInfo(1: fs.FsGetInfoRequest request),
  fs.FsListReply FsList(1: fs.FsListRequest request),
  fs.FsReadReply FsRead(1: fs.FsReadRequest request),
  fs.FsCreateReply FsCreate(1: fs.FsCreateRequest request),
  fs.FsReply FsWrite(1: fs.FsWriteRequest request),
  fs.FsReply FsDelete(1: fs.FsDeleteRequest request),
  fs.FsMoveReply FsMove(1: fs.FsMoveRequest request),
  db.DbSelectReply DbSelect(1: db.DbSelectRequest request),
  db.DbCursorReply DbCursor(1: db.DbCursorRequest request),
  db.DbExecReply DbExec(1: db.DbExecRequest request)
}
