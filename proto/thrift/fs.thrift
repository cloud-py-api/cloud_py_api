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
namespace py nc_py_api.TProto

enum fsResultCode {
    NO_ERROR = 0,
    NOT_PERMITTED = 1,
    LOCKED = 2,
    NOT_FOUND = 3,
    IO_ERROR = 4
}

struct fsId {
    1: string userId,
    2: i64 fileId
}

struct FsGetInfoRequest {
    1: fsId fileId
}

struct FsNodeInfo {
    1: i64 fileId,
    2: bool is_dir,
    3: bool is_local,
    4: i32 mimetype,
    5: i32 mimepart,
    6: string name,
    7: string internal_path,
    8: string abs_path,
    9: i64 parent_id,
    10: i64 size,
    11: i64 permissions,
    12: i64 mtime,
    13: string checksum,
    14: bool encrypted,
    15: string etag,
    16: string ownerName,
    17: i64 storageId,
    18: i64 mountId
}

struct FsListRequest {
    1: fsId dirId
}

struct FsListReply {
    1: list<FsNodeInfo> nodes
}

struct FsReadRequest {
    1: fsId fileId,
    2: i64 offset,
    3: i64 bytes_to_read
}

struct FsReadReply {
    1: fsResultCode resCode,
    2: bool last,
    3: binary content                      // Present only if resCode is NO_ERROR.
}

struct FsCreateRequest {                 // Reply for this is a FsCreateReply message.
    1: fsId parentDirId,
    2: string name,
    3: bool is_file,
    4: binary content
}

struct FsCreateReply {                   // Reply for FsCreateRequest.
    1: fsResultCode resCode,
    2: fsId fileId
}

struct FsWriteRequest {                  // Reply for this is a FsReply message.
    1: fsId fileId,
    2: bool last,
    3: binary content
}

struct FsDeleteRequest {                 // Reply for this is a FsReply message.
    1: fsId fileId
}

struct FsReply {
    1: fsResultCode resCode
}

struct FsMoveRequest {                   // Reply for this is a FsMoveReply message.
    1: fsId fileId,
    2: string targetPath,                  // Absolute path relative to MountPoint.
    3: bool copy
}

struct FsMoveReply {
    1: fsResultCode resCode,
    2: fsId fileId
}