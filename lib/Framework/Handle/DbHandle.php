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

namespace OCA\Cloud_Py_API\Framework\Handle;

use OCP\IDBConnection;

use OCA\Cloud_Py_API\Proto\DbCursorReply;
use OCA\Cloud_Py_API\Proto\DbCursorRequest;
use OCA\Cloud_Py_API\Proto\DbExecReply;
use OCA\Cloud_Py_API\Proto\DbExecRequest;
use OCA\Cloud_Py_API\Proto\DbSelectReply;
use OCA\Cloud_Py_API\Proto\DbSelectRequest;


class DbHandle {

	/** @var IDBConnection */
	private $dbConnection;

	public function __construct(IDBConnection $dbConnection)
	{
		$this->dbConnection = $dbConnection;
	}

	/**
	 * Database Select request
	 * 
	 * @param DbSelectRequest $request
	 * 
	 * @return DbSelectReply|null DB Select results
	 */
	public function select(DbSelectRequest $request): ?DbSelectReply {
		return new DbSelectReply(null);
	}

	/** 
	 * Database Exec statement
	 * 
	 * @param DbExecRequest $request
	 * 
	 * @return DbExecReply|null DB Exec statements results
	 */
	public function exec(DbExecRequest $request): ?DbExecReply {
		return new DbExecReply(null);
	}

	/**
	 * Get Database Cursor
	 * 
	 * @param DbCursorRequest $request
	 * 
	 * @return DbCursorReply DB Cursor
	 */
	public function cursor(DbCursorRequest $request): ?DbCursorReply {
		return new DbCursorReply(null);
	}

}