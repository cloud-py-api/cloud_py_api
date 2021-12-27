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
	private $queries = array();

	public static function getQueryResult(string $query_id) {
		if (isset(self::$queries[$query_id])) {
			return self::$queries[$query_id];
		}
		return null;
	}

	/**
	 * Saves exectued query result for further usage
	 * 
	 * @param string $query SQL query string
	 * 
	 * @param mixed $query_result Exectued SQL query results (array, string, int, null)
	 * 
	 * @return string|null Returns $queryId - generated unique id based on query string, 
	 * or null if such instance already exists
	 */
	public static function setQueryResult(string $query, mixed $query_result) {
		$queryId = sha1($query . time());
		if (!isset(self::$queries[$queryId])) {
			self::$queries[$queryId] = $query_result;
			return $queryId;
		}
		return null;
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