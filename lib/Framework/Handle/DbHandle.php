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
use OCA\Cloud_Py_API\Proto\DbSelectRequest\havingExpr;
use OCA\Cloud_Py_API\Proto\DbSelectRequest\joinType;
use OCA\Cloud_Py_API\Proto\exprType;
use OCA\Cloud_Py_API\Proto\pType;
use OCA\Cloud_Py_API\Proto\whereExpr;
use OCP\DB\QueryBuilder\IParameter;
use Psr\Log\LoggerInterface;


class DbHandle {

	public static $QUERIES = [];

	/** @var IDBConnection */
	private $dbConnection;

	public function __construct(IDBConnection $dbConnection, LoggerInterface $logger) {
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
		$qb->select(...$columns);

		/** @var RepeatedField */
		$strFroms = $request->getFrom();
		if (isset($strFroms[0])) {
			if ($strFroms[0]->getAlias() !== '') {
				$qb->from($strFroms[0]->getName(), $strFroms[0]->getAlias());
			} else {
				$qb->from($strFroms[0]->getName());
			}
		}

		/** @var joinType */
		foreach ($request->getJoins() as $join) {
			$qb->join($join->getFromAlias(), $join->getJoin(), $join->getAlias(), $join->getCondition());
		}

		/** @var whereExpr $whereas */
		foreach ($request->getWhereas() as $whereas) {
			$whereExpr = $this->buildWhereExpr($qb, $whereas);
			if ($whereas->getType() === 'where') {
				$qb->where($whereExpr);
			}
			if ($whereas->getType() === 'andWhere') {
				$qb->orWhere($whereExpr);
			}
			if ($whereas->getType() === 'orWhere') {
				$qb->andWhere($whereExpr);
			}
		}

		$groupBys = [];
		foreach ($request->getGroupBy() as $groupBy) {
			array_push($groupBys, json_decode($groupBy));
		}
		if (count($groupBys) > 0) {
			$qb->groupBy(...$groupBys);
		}

		/** @var havingExpr */
		foreach ($request->getHavings() as $having) {
			$qb->having($having->getExpression());
		}

		if (isset($request->getOrderBy()[0])) {
			$qb->orderBy($request->getOrderBy()[0]);
		}

		$maxResults = $request->getMaxResults();
		$qb->setMaxResults($maxResults);

		$firstResult = $request->getFirstResult();
		$qb->setFirstResult($firstResult);

		try {
			/** @var IResult $result */
			$result = $qb->executeQuery();
			$handle = QueriesManager::setQueryResult($qb->getSQL(), $result);
			$this->logger->info('[' . self::class . '] saved result id: ' . $handle);
			$this->logger->info('[' . self::class . '] SQL: ' . $qb->getSQL());
			$response->setRowCount($result->rowCount());
			$response->setHandle($handle);
		} catch (\RuntimeException $e) {
			$response->setError($e->getCode());
		}

		return $response;
	}

	private function buildWhereExpr(IQueryBuilder $qb, whereExpr $whereas): ?string {
		$expr = null;
		$expression = json_decode($whereas->getExpression(), true);
		if ($expression['type'] === exprType::EQ) {
			$expr = $qb->expr()->eq($expression['column'], $this->buildExprParam($qb, $expression['param']));
		}
		if ($expression['type'] === exprType::NEQ) {
			$expr = $qb->expr()->neq($expression['column'], $this->buildExprParam($qb, $expression['param']));
		}
		if ($expression['type'] === exprType::LT) {
			$expr = $qb->expr()->lt($expression['column'], $this->buildExprParam($qb, $expression['param']));
		}
		if ($expression['type'] === exprType::LTE) {
			$expr = $qb->expr()->lte($expression['column'], $this->buildExprParam($qb, $expression['param']));
		}
		if ($expression['type'] === exprType::GT) {
			$expr = $qb->expr()->gt($expression['column'], $this->buildExprParam($qb, $expression['param']));
		}
		if ($expression['type'] === exprType::GTE) {
			$expr = $qb->expr()->gt($expression['column'], $this->buildExprParam($qb, $expression['param']));
		}
		return $expr;
	}

	private function buildExprParam(IQueryBuilder $qb, array $param): ?IParameter {
		/** @var IParameter */
		$parameter = null;
		if ($param['param_type'] === pType::NAMED) {
			$parameter =$qb->createNamedParameter($param['value'], $param['value_type']);
		}
		if ($param['param_type'] === pType::POSITIONAL) {
			$parameter = $qb->createPositionalParameter($param['value'], $param['value_type']);
		}
		if ($param['param_type'] === pType::PBDEFAULT) {
			$parameter = $qb->createParameter($param['name']);
			$qb->setParameter($param['name'], $param['value'], $param['type']);
		}
		return $parameter;
	}

	/** 
	 * Database Exec statement
	 * 
	 * @param DbExecRequest $request
	 * 
	 * @return DbExecReply|null DB Exec statements results
	 */
	public function exec(DbExecRequest $request): ?DbExecReply {
		$response = new DbExecReply();
		$type = $request->getType();
		$tableName = $request->getTableName();
		$columns = $request->getColumns();
		$values = $request->getValues();
		$whereAs = $request->getWhereas();
		return $response;
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
		$columnData->setPresent(false);
		if (isset($params['data'])) {
			$columnData->setData($params['data']);
			$columnData->setPresent(true);
		}
		return $columnData;
	}

}