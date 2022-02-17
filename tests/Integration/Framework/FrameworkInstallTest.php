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


namespace OCA\Cloud_Py_API\Tests\Integration\Framework;

use ChristophWurst\Nextcloud\Testing\TestCase;

use OC;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

use OCA\Cloud_Py_API\AppInfo\Application;
use OCA\Cloud_Py_API\Db\SettingMapper;
use OCA\Cloud_Py_API\Proto\logLvl;
use OCA\Cloud_Py_API\Service\AppsService;
use OCA\Cloud_Py_API\Service\PythonService;
use OCA\Cloud_Py_API\Service\UtilsService;


class FrameworkInstallTest extends TestCase {

	/** @var IConfig */
	private $config;

	/** @var PythonService */
	private $pythonService;

	public function setUp(): void {
		parent::setUp();

		$this->config = OC::$server->get(IConfig::class);

		$this->pythonService = new PythonService(
			OC::$server->get(SettingMapper::class),
			$this->config,
			OC::$server->getAppDataDir(Application::APP_ID),
			OC::$server->get(LoggerInterface::class),
			OC::$server->get(UtilsService::class),
			OC::$server->get(AppsService::class),
			true,
		);
	}

	public function testFrameworkInstall() {
		$pythonOutput = $this->pythonService->run('/pyfrm/install.py', [
			'--config' => rawurlencode(json_encode($this->pythonService->getPyFrmConfig(logLvl::DEBUG))),
			'--install' => '',
			'--target' => 'framework'
		]);
		echo PHP_EOL . rawurlencode(json_encode($this->pythonService->getPyFrmConfig(logLvl::DEBUG))) . PHP_EOL;
		echo PHP_EOL. 'LOG: ' . PHP_EOL;
		foreach (json_decode($pythonOutput['output'][0], true)['Logs'] as $logRow) {
			echo PHP_EOL. '[' . logLvl::name($logRow['log_lvl']) . '] ('. $logRow['module'] .'): ' . $logRow['content'] . PHP_EOL;
		}
		echo PHP_EOL. 'LOG NOT FORMATTED: ' . PHP_EOL;
		echo PHP_EOL. json_encode($pythonOutput) . PHP_EOL;
		$this->assertTrue($pythonOutput['result_code'] === 0 && isset($pythonOutput['output'][0]) && json_decode($pythonOutput['output'][0], true)['Result'] === 'true');
	}

}