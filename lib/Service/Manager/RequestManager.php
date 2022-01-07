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

namespace OCA\Cloud_Py_API\Service\Manager;


/**
 * GRPC requests manager
 */
class RequestManager {

	/**
	 * List of Requests.
	 */
	private $requests = array(
		// 'appId' => [
		// 	'handler' => '\OCA\MediaDC\Service\Callback\TaskCallback::processResult',
		// 	'result' => null,
		// ],
	);

	public static function getRequest(string $message_id) {
		if (isset(self::$requests[$message_id])) {
			return self::$requests[$message_id];
		}
		return null;
	}

	public static function setRequest(string $message_id, mixed $message) {
		if (!isset(self::$requests[$message_id])) {
			self::$requests[$message_id]['result'] = $message;
			// call_user_func(self::$requests[$message_id]['handler']);
			return true;
		}
		return false;
	}

	public static function removeRequest(string $message_id): bool {
		if (isset(self::$requests[$message_id])) {
			$queryIndex = array_search($message_id, array_keys(self::$requests));
			if ($queryIndex !== false) {
				array_splice(self::$requests, $queryIndex, 1);
				return true;
			}
		}
		return false;
	}

}