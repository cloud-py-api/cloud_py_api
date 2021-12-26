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

namespace OCA\Cloud_Py_API\Service\Database;


/**
 * Queries manager for saving database queries results in runtime for multiple processes
 */
class QueriesManager {

	/**
	 * Queries in work.
	 */
	public static $queries = array();

	public static function getQueryResult(string $query_id) {
		if (isset(self::$queries[$query_id])) {
			return self::$queries[$query_id];
		}
	}

	public static function setQueryResult(string $query_id, mixed $query_result) {
		if (!isset(self::$queries[$query_id])) {
			self::$queries[$query_id] = $query_result;
		}
	}

	public static function removeQueryResult(string $query_id): bool {
		if (isset(self::$queries[$query_id])) {
			$queryIndex = array_search($query_id, array_keys(self::$queries));
			if ($queryIndex !== false) {
				array_splice(self::$queries, $queryIndex, 1);
				return true;
			}
		}
		return false;
	}

}