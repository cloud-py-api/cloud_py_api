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
use OCA\Cloud_Py_API\Proto\DbSelectRequest\joinType;
use OCA\Cloud_Py_API\Proto\str_alias;
use OCA\Cloud_Py_API\Proto\whereExpr;
use Psr\Log\LoggerInterface;

/**
 * Cloud_Py_API Framework DB API
 */
class Db {

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

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
		if (isset($params['columns'])) {
			/** @var str_alias[] $columns */
			$columns = array_reduce($params['columns'], function (array $carry, array $column) {
				array_push($carry, $this->createStrAlias($column));
				return $carry;
			}, []);
			$this->logger->info('[' . self::class . '] columns: ' . json_encode($columns));
			$request->setColumns($columns);
		}
		if (isset($params['from'])) {
			/** @var str_alias[] $froms */
			$froms = array_reduce($params['from'], function (array $carry, array $from) {
				array_push($carry, $this->createStrAlias($from));
				return $carry;
			}, []);
			$request->setFrom($froms);
		}
		if (isset($params['joins'])) {
			/** @var joinType[] */
			$joins = [];
			foreach ($params['joins'] as $join) {
				array_push($joins, $this->createJoinType($join));
			}
			$request->setJoins($joins);
		}
		if (isset($params['whereas'])) {
			/** @var whereExpr[] */
			$whereExpressions = [];
			foreach ($params['whereas'] as $whereas) {
				$whereExpr = $this->createWhereExpr($whereas);
				array_push($whereExpressions, $whereExpr);
			}
			$request->setWhereas($whereExpressions);
		}
		if (isset($params['groupBy'])) {
			$request->setGroupBy($params['groupBy']);
		}
		if (isset($params['havings'])) {
			$request->setHavings([$params['havings']]);
		}
		if (isset($params['orderBy'])) {
			$request->setOrderBy($params['orderBy']);
		}
		if (isset($params['maxResults'])) {
			$request->setMaxResults($params['maxResults']);
		}
		if (isset($params['firstResult'])) {
			$request->setFirstResult($params['firstResult']);
		}
		return $client->DbSelect($request)->wait();
	}

	private function createStrAlias(array $params = []): str_alias {
		$strAlias = new str_alias();
		if (isset($params['name'])) {
			$strAlias->setName($params['name']);
		}
		if (isset($params['alias'])) {
			$strAlias->setAlias($params['alias']);
		}
		return $strAlias;
	}

	private function createJoinType(array $params = []): joinType {
		$joinType = new joinType();
		if (isset($params['name'])) {
			$joinType->setName($params['name']);
		}
		if (isset($params['fromAlias'])) {
			$joinType->setFromAlias($params['fromAlias']);
		}
		if (isset($params['join'])) {
			$joinType->setJoin($params['join']);
		}
		if (isset($params['alias'])) {
			$joinType->setAlias($params['alias']);
		}
		if (isset($params['condition'])) {
			$joinType->setCondition($params['condition']);
		}
		return $joinType;
	}

	private function createWhereExpr(array $params = []): whereExpr {
		$whereExpr = new whereExpr();
		if (isset($params['type'])) {
			$whereExpr->setType($params['type']);
		}
		if (isset($params['expression'])) {
			$whereExpr->setExpression(json_encode($params['expression']));
		}
		return $whereExpr;
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
		if (isset($params['type'])) {
			$request->setType($params['type']);
		}
		if (isset($params['tableName'])) {
			$request->setTableName($params['tableName']);
		}
		if (isset($params['columns'])) {
			$request->setTableName($params['columns']);
		}
		if (isset($params['values'])) {
			$request->setValues($params['values']);
		}
		if (isset($params['whereas'])) {
			$request->setWhereas($params['whereas']);
		}
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
		if (isset($params['cmd'])) {
			$request->setCmd($params['cmd']);
		}
		if (isset($params['handle'])) {
			$request->setHandle($params['handle']);
		}
		return $client->DbCursor($request)->wait();
	}

}
