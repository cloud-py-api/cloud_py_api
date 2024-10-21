<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Copyright (c) 2022-2023 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author 2021-2023 Andrey Borysenko <andrey18106x@gmail.com>
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

namespace OCA\Cloud_Py_API\Tests\Unit\Service;

use OCA\Cloud_Py_API\Service\PythonService;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cloud_Py_API\Service\PythonService
 */
class PythonServiceTest extends TestCase {
	use \phpmock\phpunit\PHPMock;

	/** @var \OCP\IConfig|MockObject */
	private $config;

	/** @var \OCA\Cloud_Py_API\Db\SettingMapper|MockObject */
	private $settingMapper;

	/** @var \OCA\Cloud_Py_API\Service\UtilsService|MockObject */
	private $utils;

	/** @var PythonService|MockObject */
	private $pythonService;

	public function setUp(): void {
		parent::setUp();

		/** @var \OCP\IConfig|MockObject */
		$this->config = $this->createMock(\OCP\IConfig::class);
		/** @var \OCA\Cloud_Py_API\Db\SettingMapper|MockObject */
		$this->settingMapper = $this->createMock(\OCA\Cloud_Py_API\Db\SettingMapper::class);
		$this->utils = $this->createMock(\OCA\Cloud_Py_API\Service\UtilsService::class);

		$pythonSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'python_command',
			'value' => '"/usr/bin/python3"'
		]);
		$this->settingMapper->expects($this->once())
			->method('findByName')
			->with('python_command')
			->will($this->returnValue($pythonSetting));

		$datadir = dirname(dirname(getcwd())) . '/data';
		$configMap = [
			['instanceid', '0123456789ab'],
			['datadirectory', $datadir]
		];

		$this->config->expects($this->any())
			->method('getSystemValue')
			->will($this->returnValueMap($configMap));

		$this->pythonService = new PythonService(
			$this->config,
			$this->settingMapper,
			$this->utils
		);
	}

	public function testRun() {
		// TODO
		$this->addToAssertionCount(1);
	}
}
