<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021 Andrey Borysenko <andrey18106x@gmail.com>
 * 
 * @copyright Copyright (c) 2021 Alexander Piskun <bigcat88@icloud.com>
 * 
 * @author 2021 Andrey Borysenko <andrey18106x@gmail.com>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Cloud_Py_API\Service;

use OCA\Cloud_Py_API\Proto\CloudPyApiCoreStub;


class CloudPyApiCoreService extends CloudPyApiCoreStub {

	/** @var TaskService */
	private $tasks;

	/** @var FsService */
	private $fs;

	/** @var DbService */
	private $db;

	public function __construct(TaskService $tasks, FsService $fs, DbService $db)
	{
		$this->tasks = $tasks;
		$this->fs = $fs;
		$this->db = $db;
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\PBEmpty $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\TaskInitReply for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function TaskInit(
		\OCA\Cloud_Py_API\Proto\PBEmpty $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\TaskInitReply {
		$context->setStatus(\Grpc\Status::ok());
		return $this->tasks->init($request);
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\TaskSetStatusRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\PBEmpty for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function TaskStatus(
		\OCA\Cloud_Py_API\Proto\TaskSetStatusRequest $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\PBEmpty {
		$context->setStatus(\Grpc\Status::ok());
		return $this->tasks->status($request);
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\TaskExitRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\PBEmpty for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function TaskExit(
		\OCA\Cloud_Py_API\Proto\TaskExitRequest $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\PBEmpty {
		$context->setStatus(\Grpc\Status::ok());
		return $this->tasks->exit($request);
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\TaskLogRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\PBEmpty for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function TaskLog(
		\OCA\Cloud_Py_API\Proto\TaskLogRequest $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\PBEmpty {
		$context->setStatus(\Grpc\Status::ok());
		return $this->tasks->log($request);
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\FsGetInfoRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\FsListReply for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function FsGetInfo(
		\OCA\Cloud_Py_API\Proto\FsGetInfoRequest $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\FsListReply {
		$context->setStatus(\Grpc\Status::ok());
		return $this->fs->info($request);
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\FsListRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\FsListReply for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function FsList(
		\OCA\Cloud_Py_API\Proto\FsListRequest $request,
		\Grpc\ServerContext $context
		): ?\OCA\Cloud_Py_API\Proto\FsListReply {
		$context->setStatus(\Grpc\Status::ok());
		return $this->fs->list($request);
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\FsReadRequest $request client request
	 * @param \Grpc\ServerCallWriter $writer write response data of \OCA\Cloud_Py_API\Proto\FsReadReply
	 * @param \Grpc\ServerContext $context server request context
	 * @return void
	 */
	public function FsRead(
		\OCA\Cloud_Py_API\Proto\FsReadRequest $request,
		\Grpc\ServerCallWriter $writer,
		\Grpc\ServerContext $context
	): void {
		$context->setStatus(\Grpc\Status::unimplemented());
		$writer->finish();
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\FsCreateRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\FsReply for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function FsCreate(
		\OCA\Cloud_Py_API\Proto\FsCreateRequest $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\FsReply {
		$context->setStatus(\Grpc\Status::unimplemented());
		return $this->fs->create($request);
	}

	/**
	 * @param \Grpc\ServerCallReader $reader read client request data of \OCA\Cloud_Py_API\Proto\FsWriteRequest
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\FsReply for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function FsWrite(
		\Grpc\ServerCallReader $reader,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\FsReply {
		$context->setStatus(\Grpc\Status::unimplemented());
		return null;
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\FsDeleteRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\FsReply for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function FsDelete(
		\OCA\Cloud_Py_API\Proto\FsDeleteRequest $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\FsReply {
		$context->setStatus(\Grpc\Status::unimplemented());
		return null;
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\FsMoveRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\FsReply for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function FsMove(
		\OCA\Cloud_Py_API\Proto\FsMoveRequest $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\FsReply {
		$context->setStatus(\Grpc\Status::unimplemented());
		return null;
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\DbSelectRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\DbSelectReply for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function DbSelect(
		\OCA\Cloud_Py_API\Proto\DbSelectRequest $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\DbSelectReply {
		$context->setStatus(\Grpc\Status::unimplemented());
		return $this->db->select($request);
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\DbCursorRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\DbCursorReply for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function DbCursor(
		\OCA\Cloud_Py_API\Proto\DbCursorRequest $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\DbCursorReply {
		$context->setStatus(\Grpc\Status::unimplemented());
		return $this->db->cursor($request);
	}

	/**
	 * @param \OCA\Cloud_Py_API\Proto\DbExecRequest $request client request
	 * @param \Grpc\ServerContext $context server request context
	 * @return \OCA\Cloud_Py_API\Proto\DbExecReply for response data, null if if error occured
	 *     initial metadata (if any) and status (if not ok) should be set to $context
	 */
	public function DbExec(
		\OCA\Cloud_Py_API\Proto\DbExecRequest $request,
		\Grpc\ServerContext $context
	): ?\OCA\Cloud_Py_API\Proto\DbExecReply {
		$context->setStatus(\Grpc\Status::unimplemented());
		return $this->db->exec($request);
	}

}