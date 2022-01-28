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

use RuntimeException;
use OCP\IConfig;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Files\NotPermittedException;

use OCA\Cloud_Py_API\AppInfo\Application;
use OCA\Cloud_Py_API\Db\App;
use OCA\Cloud_Py_API\Db\AppMapper;


class AppsService {

	/** @var AppMapper */
	private $mapper;

	/** @var IAppData */
	private $appData;

	/** @var IConfig */
	private $config;

	public function __construct(AppMapper $appMapper, IAppData $appData, IConfig $config) {
		$this->mapper = $appMapper;
		$this->appData = $appData;
		$this->config = $config;
	}

	/**
	 * Register an app in database
	 * 
	 * @param string $appId
	 * 
	 * @return Entity
	 */
	public function registerApp(string $appId): Entity {
		try {
			/** @var App */
			$app = $this->mapper->findByAppId($appId);
			if ($app->getAppId() === $appId) {
				return $app;
			} else {
				$app = new App([
					'appId' => $appId,
					'token' => sha1($appId)
				]);
				return $this->mapper->insert($app);
			}
		} catch (DoesNotExistException $e) {
			$app = new App([
				'appId' => $appId,
				'token' => sha1($appId)
			]);
			return $this->mapper->insert($app);
		}
	}

	public function getApps() {
		return $this->mapper->findAll();
	}

	public function getApp($appId) {
		return $this->mapper->find($appId);
	}

	/**
	 * Create application's appdata folder
	 * 
	 * @param string $appId
	 * 
	 * @return bool
	 */
	public function createAppDataFolder(string $appId): bool {
		$ncInstanceId = $this->config->getSystemValue('instanceid');
		$ncDataFolder = $this->config->getSystemValue('datadirectory');
		$appDataFolder = $ncDataFolder . '/appdata_' . $ncInstanceId . '/' . Application::APP_ID . '/' . $appId;
		if (!file_exists($appDataFolder)) {
			try	{
				$this->appData->newFolder($appId);
				return true;
			} catch (NotPermittedException $e) {
				return false;
			}
		}
		return false;
	}

	/**
	 * Get app's appdata folder
	 * 
	 * @param string $appId
	 * 
	 * @return ISimpleFolder|false
	 */
	public function getAppDataFolder(string $appId) {
		try {
			return $this->appData->getFolder($appId);
		} catch (NotFoundException | RuntimeException $e) {
			return ['success' => false, 'error' => $e->getMessage()];
		}
	}

	/**
	 * Get app's appdata folder
	 * 
	 * @param string $appId
	 * 
	 * @return string|null
	 */
	public function getAppDataFolderAbsPath(string $appId): ?string {
		try {
			$appDataFolderName = $this->appData->getFolder($appId)->getName();
			$ncInstanceId = $this->config->getSystemValue('instanceid');
			$ncDataFolder = $this->config->getSystemValue('datadirectory');
			if ($appId !== Application::APP_ID) {
				$appDataFolder = $ncDataFolder . '/appdata_' . $ncInstanceId . '/' . Application::APP_ID . '/' . $appId;
			} else {
				$appDataFolder = $ncDataFolder . '/appdata_' . $ncInstanceId . '/' . Application::APP_ID;
			}
			if (file_exists($appDataFolder) && $appDataFolderName === $appId) {
				return $appDataFolder;
			} else {
				return null;
			}
		} catch (NotFoundException | RuntimeException $e) {
			return null;
		}
	}

	/**
	 * Create cloud_py_api appdata folder
	 * 
	 * @return bool
	 */
	public function createFrameworkAppDataFolder(): bool {
		$ncInstanceId = $this->config->getSystemValue('instanceid');
		$ncDataFolder = $this->config->getSystemValue('datadirectory');
		$appDataFolder = $ncDataFolder . '/appdata_' . $ncInstanceId . '/' . Application::APP_ID;
		if (!file_exists($appDataFolder)) {
			try	{
				$this->appData->newFolder(Application::APP_ID);
				return true;
			} catch (NotPermittedException $e) {
				return false;
			}
		}
		return false;
	}

}