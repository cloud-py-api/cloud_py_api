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

namespace OCA\Cloud_Py_API\Framework;

use OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient;
use OCA\Cloud_Py_API\Proto\DbCursorRequest;
use OCA\Cloud_Py_API\Proto\DbExecRequest;
use OCA\Cloud_Py_API\Proto\DbSelectRequest;


/**
 * Cloud_Py_API Framework DB API
 */
class Db {

	/**
	 * Send DbSelect request
	 * 
	 * @param CloudPyApiCoreClient $client
	 * @param array $params
	 * 
	 * @return array [
	 * 	'response' => OCA\Cloud_Py_API\Proto\DbSelectReply,
	 * 	'status' => ['metadata', 'code', 'details']
	 * ]
	 */
	public function DbSelect($client, $params = []): array {
		$request = new DbSelectRequest();
		return $client->DbSelect($request)->wait();
	}

	/**
	 * Send DbCursor request
	 * 
	 * @param CloudPyApiCoreClient $client
	 * @param array $params
	 * 
	 * @return array [
	 * 	'response' => OCA\Cloud_Py_API\Proto\DbExecReply,
	 * 	'status' => ['metadata', 'code', 'details']
	 * ]
	 */
	public function DbExec($client, $params = []): array {
		$request = new DbExecRequest();
		return $client->DbExec($request)->wait();
	}

	/**
	 * Send DbCursor request
	 * 
	 * @param CloudPyApiCoreClient $client
	 * @param array $params
	 * 
	 * @return array [
	 * 	'response' => OCA\Cloud_Py_API\Proto\DbCursorReply,
	 * 	'status' => ['metadata', 'code', 'details']
	 * ]
	 */
	public function DbCursor($client, $params = []): array {
		$request = new DbCursorRequest();
		return $client->DbCursor($request)->wait();
	}

}
