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

use OCA\Cloud_Py_API\Service\UtilsService;

use OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient;
use OCA\Cloud_Py_API\Proto\PBEmpty;
use OCA\Cloud_Py_API\Proto\TaskExitRequest;
use OCA\Cloud_Py_API\Proto\TaskLogRequest;
use OCA\Cloud_Py_API\Proto\TaskSetStatusRequest;
use OCA\Cloud_Py_API\Proto\taskStatus;


/**
 * Cloud_Py_API Framework Core API
 */
class Core {

	/** @var CloudPyApiCore */
	private $cpa;

	/** @var UtilsService */
	private $utils;

	public function __construct(CloudPyApiCore $cpa, UtilsService $utils)
	{
		$this->cpa = $cpa;
		$this->utils = $utils;
	}

	/**
	 * Run non-blocking GRPC server
	 * 
	 * @param array $params hostname, port, userid, appname, handler,
	 *                      modname, modpath, funcname, args
	 * 
	 * @return void
	 */
	public function runBgGrpcServer(array $params = []): int {
		if (isset($params['hostname'])) {
			$hostname = $params['hostname'];
		}
		if (isset($params['port'])) {
			$port = $params['port'];
		}
		if (isset($params['userid'])) {
			$userid = $params['userid'];
		}
		if (isset($params['appname'])) {
			$appname = $params['appname'];
		}
		if (isset($params['handler'])) {
			$handler = $params['handler'];
		}
		if (isset($params['modname'])) {
			$modname = $params['modname'];
		}
		if (isset($params['modpath'])) {
			$modpath = $params['modpath'];
		}
		if (isset($params['funcname'])) {
			$funcname = $params['funcname'];
		}
		$pathToOcc = getcwd() . '/occ';
		$cloudPyApiCommand = 'cloud_py_api:grpc:server:bg:run ' . $hostname . ' ' . $port
			. ' ' . $userid . ' ' . $appname . ' ' . $handler . ' ' . $modname . ' ' . $modpath 
			. ' ' . $funcname;
		if (isset($params['args'])) {
			$args = $params['args'];
			$cloudPyApiCommand += array_reduce(json_decode($args), function ($carry, $argument) {
				return $carry += ' ' . $argument;
			});
		}
		$command = $this->utils->getPhpInterpreter() . ' ' . $pathToOcc . ' ' . $cloudPyApiCommand 
			. ' > /dev/null 2>&1 & echo $!';
		exec($command, $cmdOut);
		if (isset($cmdOut[0]) && intval($cmdOut[0]) > 0) {
			return intval($cmdOut[0]);
		}
		return -1;
	}

	/**
	 * Create GRPC server
	 * 
	 * @param array $params hostname and port
	 * 
	 * @return \Grpc\RpcServer
	 */
	public function createServer(array $params = []): \Grpc\RpcServer {
		$server = new \Grpc\RpcServer();
		$hostname = '0.0.0.0';
		if (isset($params['hostname'])) {
			$hostname = $params['hostname'];
		}
		if (isset($params['port'])) {
			$hostname .= ':' . $params['port'];
		}
		$server->addHttp2Port($hostname);
		$server->handle($this->cpa);
		return $server;
	}

	/**
	 * Create GRPC client
	 * 
	 * @param array $params
	 * 
	 * @return CloudPyApiCoreClient
	 */
	public function createClient(array $params = []): CloudPyApiCoreClient {
		if (isset($params['hostname'])) {
			$hostname = $params['hostname'];
		}
		if (isset($params['port'])) {
			$port = $params['port'];
		}
		$client = new CloudPyApiCoreClient($hostname . ':' . $port, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);
		return $client;
	}

	/**
	 * Send TaskInit request from given client
	 * 
	 * @param CloudPyApiCoreClient $client
	 * 
	 * @return array [
	 * 	'response' => OCA\Cloud_Py_API\Proto\TaskInitReply,
	 * 	'status' => ['metadata', 'code', 'details']
	 * ]
	 */
	public function TaskInit($client): array {
		return $client->TaskInit(new PBEmpty())->wait();
	}

	/**
	 * Send TaskLog request
	 * 
	 * @param CloudPyApiCoreClient $client
	 * @param array $params
	 * 
	 * @return void
	 */
	public function TaskStatus($client, $params = []): void {
		$request = new TaskSetStatusRequest();
		if (isset($params['stCode'])) {
			$request->setStCode(taskStatus::value(taskStatus::name($params['stCode'])));
		}
		$client->TaskStatus($request);
	}

	/**
	 * Send TaskLog
	 * 
	 * @param CloudPyApiCoreClient $client
	 * @param array $params
	 * 
	 * @return void
	 */
	public function TaskLog($client, $params = []): void {
		$request = new TaskLogRequest();
		if (isset($params['logLvl'])) {
			$request->setLogLvl($params['logLvl']);
		}
		if (isset($params['module'])) {
			$request->setModule($params['module']);
		}
		if (isset($params['content'])) {
			$request->setContent($params['content']);
		}
		$client->TaskLog($request)->wait();
	}

	/**
	 * Send TaskExit request with passing $result to initiator callback
	 * and closing server process
	 * 
	 * @param CloudPyApiCoreClient $client
	 * @param array $params
	 * 
	 * @return void
	 */
	public function TaskExit($client, $params = []): void {
		$request = new TaskExitRequest();
		if (isset($params['result'])) {
			$request->setResult($params['result']);
		}
		$client->TaskExit($request);
	}

}
