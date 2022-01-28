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

use Grpc\ServerStreamingCall;

use OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient;
use OCA\Cloud_Py_API\Proto\PBEmpty;
use OCA\Cloud_Py_API\Proto\TaskExitRequest;
use OCA\Cloud_Py_API\Proto\TaskLogRequest;
use OCA\Cloud_Py_API\Proto\TaskSetStatusRequest;
use OCA\Cloud_Py_API\Proto\taskStatus;
use OCA\Cloud_Py_API\Proto\CheckDataRequest;
use OCA\Cloud_Py_API\Proto\CheckDataRequest\installed_pckg;
use OCA\Cloud_Py_API\Proto\CheckDataRequest\missing_pckg;
use OCA\Cloud_Py_API\Proto\OccRequest;
use OCA\Cloud_Py_API\Service\PythonService;
use Psr\Log\LoggerInterface;

/**
 * Cloud_Py_API Framework Core API
 */
class Core {

	/** @var CloudPyApiCore */
	private $cpa;

	/** @var PythonService */
	private $pythonService;

	/** @var UtilsService */
	private $utils;

	public function __construct(CloudPyApiCore $cpa, PythonService $pythonService,
								UtilsService $utils, LoggerInterface $logger) {
		$this->cpa = $cpa;
		$this->pythonService = $pythonService;
		$this->utils = $utils;
		$this->logger = $logger;
	}

	/**
	 * Run non-blocking gRPC server
	 * 
	 * @param array $params hostname, port, userid, appname, handler,
	 *                      modname, modpath, funcname, args
	 * 
	 * @return int gRPC server PID or `-1` on failure
	 */
	public function runBgGrpcServer(array $params = []): int {
		if (isset($params['hostname'])) {
			$hostname = $params['hostname'];
		}
		if (isset($params['port'])) {
			$port = $params['port'];
		}
		if (isset($params['cmd'])) {
			$cmd = $params['cmd'];
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
		if (isset($params['modpath'])) {
			$modpath = $params['modpath'];
		}
		if (isset($params['funcname'])) {
			$funcname = $params['funcname'];
		}
		$pathToOcc = getcwd() . '/occ';
		$cloudPyApiCommand = 'cloud_py_api:grpc:server:bg:run ' . $hostname . ' ' . $port 
			. ' ' . $cmd . ' ' . $userid . ' ' . $appname . ' ' . $handler  . ' ' . $modpath . ' ' 
			. $funcname;
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
	 * @param array $params hostname and port
	 * 
	 * @return \OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient
	 */
	public function createClient(array $params = []): CloudPyApiCoreClient {
		if (isset($params['hostname'])) {
			$hostname = $params['hostname'];
		}
		if (isset($params['port'])) {
			$hostname .= ':' . $params['port'];
		}
		$client = new CloudPyApiCoreClient($hostname, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);
		return $client;
	}

	public function runPyfrm(): array {
		// TODO Run Pyfrm to handle task
		return $this->pythonService->run('/pyfrm/main.py');
	}

	/**
	 * Send TaskInit request from given client
	 * 
	 * @param \OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient $client
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
	 * @param \OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient $client
	 * @param array $params
	 * 
	 * @return array [
	 * 	'response' => OCA\Cloud_Py_API\Proto\PBEmpty,
	 * 	'status' => ['metadata', 'code', 'details']
	 * ]
	 */
	public function TaskStatus($client, $params = []): array {
		$request = new TaskSetStatusRequest();
		if (isset($params['stCode'])) {
			$request->setStCode(taskStatus::value(taskStatus::name($params['stCode'])));
		}
		return $client->TaskStatus($request)->wait();
	}

	/**
	 * Send TaskLog request
	 * 
	 * @param \OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient $client
	 * @param array $params
	 * 
	 * @return array [
	 * 	'response' => OCA\Cloud_Py_API\Proto\PBEmpty,
	 * 	'status' => ['metadata', 'code', 'details']
	 * ]
	 */
	public function TaskLog($client, $params = []): array {
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
		return $client->TaskLog($request)->wait();
	}

	/**
	 * Send TaskExit request with passing $result to initiator callback
	 * and closing server process
	 * 
	 * @param \OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient $client
	 * @param array $params
	 * 
	 * @return array [
	 * 	'response' => OCA\Cloud_Py_API\Proto\PBEmpty,
	 * 	'status' => ['metadata', 'code', 'details']
	 * ]
	 */
	public function TaskExit($client, $params = []): array {
		$request = new TaskExitRequest();
		if (isset($params['result'])) {
			$request->setResult($params['result']);
		}
		return $client->TaskExit($request)->wait();
	}

	/**
	 * Send AppCheck request for checking python requirements installation
	 * 
	 * @param \OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient $client
	 * @param array $params not_installed and installed packages lists
	 * 
	 * @return array [
	 * 	'response' => OCA\Cloud_Py_API\Proto\PBEmpty,
	 * 	'status' => ['metadata', 'code', 'details']
	 * ]
	 */
	public function AppCheck($client, $params = []): array {
		$request = new CheckDataRequest();
		if (isset($params['not_installed'])) {
			$not_installed = array_map(function(array $pckg) {
				$missing_pckg = new missing_pckg();
				if (isset($pckg['name'])) {
					$missing_pckg->setName($pckg['name']);
				}
				if (isset($pckg['version'])) {
					$missing_pckg->setVersion($pckg['version']);
				}
				return $missing_pckg;
			}, $params['not_installed']);
			$request->setNotInstalled($not_installed);
		}
		if (isset($params['installed'])) {
			$installed = array_map(function(array $pckg) {
				$installed_pckg = new installed_pckg();
				if (isset($pckg['name'])) {
					$installed_pckg->setName($pckg['name']);
				}
				if (isset($pckg['version'])) {
					$installed_pckg->setVersion($pckg['version']);
				}
				if (isset($pckg['location'])) {
					$installed_pckg->setLocation($pckg['location']);
				}
				if (isset($pckg['summary'])) {
					$installed_pckg->setSummary($pckg['summary']);
				}
				if (isset($pckg['requires'])) {
					$installed_pckg->setRequires($pckg['requires']);
				}
				return $installed_pckg;
			}, $params['installed']);
			$request->setInstalled($installed);
		}
		return $client->AppCheck($request)->wait();
	}

	/**
	 * Send OccCall request for executing Nextcloud OCC CLI command
	 * 
	 * @param \OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient $client
	 * @param array $params
	 * 
	 * @return \Grpc\ServerStreamingCall
	 */
	public function OccCall($client, $params = []): ServerStreamingCall {
		$request = new OccRequest();
		if (isset($params['arguments'])) {
			$request->setArguments($params['arguments']);
		}
		return $client->OccCall($request);
	}

}
