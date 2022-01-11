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

namespace OCA\Cloud_Py_API\Service;

use OCP\IConfig;

use OCA\Cloud_Py_API\Proto\PBEmpty;
use OCA\Cloud_Py_API\Proto\TaskExitRequest;
use OCA\Cloud_Py_API\Proto\TaskInitReply;
use OCA\Cloud_Py_API\Proto\TaskInitReply\cfgOptions;
use OCA\Cloud_Py_API\Proto\TaskLogRequest;
use OCA\Cloud_Py_API\Proto\TaskSetStatusRequest;

use OCA\Cloud_Py_API\AppInfo\Application;
use OCA\Cloud_Py_API\Proto\logLvl;
use Psr\Log\LoggerInterface;


class TaskService {

	/** @var string */
	private $userId;

	/** @var IConfig */
	private $config;

	/** @var AppsService */
	private $appsService;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(?string $userId, IConfig $config, AppsService $appsService,
								LoggerInterface $logger) {
		$this->userId = $userId;
		$this->config = $config;
		$this->appsService = $appsService;
		$this->logger = $logger;
	}

	/**
	 * Task initialization
	 * 
	 * @param PBEmpty $request
	 * 
	 * @return TaskInitReply|null
	 */
	public function init(PBEmpty $request): ?TaskInitReply {
		$taskInitReply = new TaskInitReply();
		if (ServerService::$APP !== null) {
			if (isset(ServerService::$APP['appname'])) {
				$taskInitReply->setAppName(ServerService::$APP['appname']);
			}
			if (isset(ServerService::$APP['modname'])) {
				$taskInitReply->setModName(ServerService::$APP['modname']);
			}
			if (isset(ServerService::$APP['modpath'])) {
				$taskInitReply->setModPath(ServerService::$APP['modpath']);
			}
			if (isset(ServerService::$APP['funcname'])) {
				$taskInitReply->setFuncName(ServerService::$APP['funcname']);
			}
			if (isset(ServerService::$APP['args'])) {
				$taskInitReply->setArgs(ServerService::$APP['args']);
			}
			$cfg = new cfgOptions();
			$cfg->setLogLvl(logLvl::value(logLvl::name($this->config->getSystemValue('loglevel'))));
			$cfg->setDataFolder($this->config->getSystemValue('datadirectory'));
			$cfg->setFrameworkAppData($this->appsService->getAppDataFolderAbsPath(Application::APP_ID));
			$cfg->setUserId($this->userId);
			$cfg->setUseDBDirect(false);
			$cfg->setUseFileDirect(false);
			$taskInitReply->setConfig($cfg);
		}
		return $taskInitReply;
	}

	/**
	 * Set task status
	 * 
	 * @param TaskSetStatusRequest $request
	 * 
	 * @return PBEmpty|null
	 */
	public function status(TaskSetStatusRequest $request): ?PBEmpty {
		ServerService::$APP['status'] = $request->getStCode();
		return new PBEmpty(null);
	}

	/**
	 * Task exit
	 * 
	 * @param TaskExitRequest $request
	 * 
	 * @return PBEmpty|null
	 */
	public function exit(TaskExitRequest $request): ?PBEmpty {
		// TODO Shutdown server by PID
		// TODO Return result to request initiator (exec_user_func in RequestsManager)
		return new PBEmpty(null);
	}

	/**
	 * Task logging
	 * 
	 * @param TaskLogRequest $request
	 * 
	 * @return PBEmpty|null
	 */
	public function log(TaskLogRequest $request): ?PBEmpty {
		$logLvl = $request->getLogLvl();
		if ($logLvl === logLvl::DEBUG) {
			$this->logger->debug('[' . $request->getModule() . '] ' . $request->getContent());
		}
		if ($logLvl === logLvl::INFO) {
			$this->logger->info('[' . $request->getModule() . '] ' . $request->getContent());
		}
		if ($logLvl === logLvl::WARN) {
			$this->logger->warning('[' . $request->getModule() . '] ' . $request->getContent());
		}
		if ($logLvl === logLvl::ERROR) {
			$this->logger->error('[' . $request->getModule() . '] ' . $request->getContent());
		}
		if ($logLvl === logLvl::FATAL) {
			$this->logger->emergency('[' . $request->getModule() . '] ' . $request->getContent());
		}
		return new PBEmpty(null);
	}

}