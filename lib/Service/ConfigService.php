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

use OCP\Files\IAppData;
use OCP\IConfig;

use OCA\Cloud_Py_API\Db\AppMapper;
use OCA\Cloud_Py_API\AppInfo\Application;


class ConfigService {

	/** @var AppMapper */
	private $mapper;

	/** @var IAppData */
	private $appData;

	/** @var IConfig */
	private $config;

	public function __construct(AppMapper $appMapper, IAppData $appData, IConfig $config)
	{
		$this->mapper = $appMapper;
		$this->appData = $appData;
		$this->config = $config;
	}

	public function scanAppsForConfig() {
		// TODO Scan all apps directories (custom_apps, apps, etc.) for appinfo/cloud_py_api_config
	}

	public function validateConfig(string $appId, string $config) {
		// TODO
	}

	public function syncAppConfig(string $appId, array $config) {
		// TODO
	}

	public function writeAppConfig(string $appId, array $config) {
		// TODO Write updated config to PHP config file
	}

}