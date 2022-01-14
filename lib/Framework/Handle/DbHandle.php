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
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\DB\IResult;

use OCA\Cloud_Py_API\Framework\Manager\QueriesManager;

use Google\Protobuf\Internal\RepeatedField;
use OCA\Cloud_Py_API\Proto\DbCursorReply;
use OCA\Cloud_Py_API\Proto\DbCursorReply\columnData;
use OCA\Cloud_Py_API\Proto\DbCursorRequest;
use OCA\Cloud_Py_API\Proto\DbCursorRequest\cCmd;
use OCA\Cloud_Py_API\Proto\DbExecReply;
use OCA\Cloud_Py_API\Proto\DbExecRequest;
use OCA\Cloud_Py_API\Proto\DbSelectReply;
use OCA\Cloud_Py_API\Proto\DbSelectRequest;

use Psr\Log\LoggerInterface;


class DbHandle {

	public static $QUERIES = [];

	/** @var IDBConnection */
	private $dbConnection;

	public function __construct(IDBConnection $dbConnection, LoggerInterface $logger)
	{
		$this->dbConnection = $dbConnection;
		$this->logger = $logger;
	}

	/**
	 * Database Select request
	 * 
	 * @param DbSelectRequest $request
	 * 
	 * @return DbSelectReply|null DB Select results
	 */
	public function select(DbSelectRequest $request): ?DbSelectReply {
		/** @var IQueryBuilder */
		$qb = $this->dbConnection->getQueryBuilder();
		$response = new DbSelectReply();

		$columns = [];
		foreach ($request->getColumns() as $column) {
			if ($column->getAlias() !== '') {
				array_push($columns, [$column->getName(), $column->getAlias()]);
			} else {
				array_push($columns, [$column->getName()]);
			}
		}
		/** @var RepeatedField */
		$strFroms = $request->getFrom();
		if (isset($strFroms[0])) {
			$from = $strFroms[0]->getName();
		}
		$joins = $request->getJoins();
		$whereas = $request->getWhereas();
		$groupBy = $request->getGroupBy();
		$havings = $request->getHavings();
		$orderBy = $request->getOrderBy();
		$maxResults = $request->getMaxResults();
		$firstResult = $request->getFirstResult();

		$qb->select(...$columns)
			->from($from)
			->setMaxResults($maxResults)
			->setFirstResult($firstResult);

		try {
			/** @var IResult $result */
			$result = $qb->executeQuery();
			$handle = QueriesManager::setQueryResult($qb->getSQL(), $result);
			$this->logger->info('[' . self::class . '] saved result id: ' . $handle);
			$response->setRowCount($result->rowCount());
			$response->setHandle($handle);
		} catch (\RuntimeException $e) {
			$response->setError($e->getCode());
		}

		return $response;
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
		$response = new DbCursorReply();
		$cCmd = $request->getCmd();
		$handle = $request->getHandle();
		/** @var IResult $result */
		$result = QueriesManager::getQueryResult($handle);
		if (isset($result)) {
			$rows = [];
			$columnsName = [];
			$columnsData = [];
			if ($cCmd === cCmd::FETCH_ALL) {
				$rows = $result->fetchAll();
			}
			if ($cCmd === cCmd::FETCH) {
				$row = $result->fetch();
				array_push($rows, $row);
			}
			if ($cCmd === cCmd::CLOSE) {
				$result->closeCursor();
				QueriesManager::removeQueryResult($handle);
			}
			foreach($rows as $row) {
				foreach($row as $columnName => $columnValue) {
					array_push($columnsName, $columnName);
					array_push($columnsData, $this->createColumnData(['data' => $columnValue]));
				}
			}
			$response->setColumnsName($columnsName);
			$response->setColumnsData($columnsData);
		}
		return $response;
	}

	private function createColumnData(array $params = []): columnData {
		$columnData = new columnData();
		$columnData->setBPresent(false);
		if (isset($params['data'])) {
			$columnData->setData($params['data']);
			$columnData->setBPresent(true);
		}
		return $columnData;
	}

}