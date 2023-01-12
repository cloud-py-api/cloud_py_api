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

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

use OCA\Cloud_Py_API\Service\UtilsService;

/**
 * @covers \OCA\Cloud_Py_API\Service\UtilsService
 */
class UtilsServiceTest extends TestCase {
	use \phpmock\phpunit\PHPMock;

	/** @var \OCP\IConfig|MockObject */
	private $config;

	/** @var \OCA\Cloud_Py_API\Db\SettingMapper|MockObject */
	private $settingMapper;

	/** @var \OCP\App\IAppManager|MockObject */
	private $appManager;

	/** @var \OCA\ServerInfo\DatabaseStatistics|MockObject */
	private $databaseStatistics;

	/** @var UtilsService|MockObject */
	private $utils;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(\OCP\IConfig::class);
		$this->settingMapper = $this->createMock(\OCA\Cloud_Py_API\Db\SettingMapper::class);
		$this->appManager = $this->createMock(\OCP\App\IAppManager::class);
		$this->databaseStatistics = $this->createMock(\OCA\ServerInfo\DatabaseStatistics::class);

		$this->utils = new UtilsService(
			$this->config,
			$this->settingMapper,
			$this->appManager,
			$this->databaseStatistics
		);
	}

	public function testGetNCLogLevel() {
		$this->config->expects($this->once())
			->method('getSystemValue')
			->with('loglevel', 2)
			->will($this->returnValue(2));
		$result = $this->utils->getNCLogLevel();
		$this->assertEquals('WARNING', $result, 'Should be loglevel from config mapped to string');
	}

	public function testGetCpaLogLevel() {
		$cpaLogLevelSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'cpa_loglevel',
			'value' => '"WARNING"', // string JSON value from DB
		]);
		$this->settingMapper->expects($this->once())
			->method('findByName')
			->with('cpa_loglevel')
			->will($this->returnValue($cpaLogLevelSetting));
		$result = $this->utils->getCpaLogLevel();
		$this->assertEquals('WARNING', $result, 'Should be cpa_loglevel string from settings');
	}

	public function testGetPhpInterpreter() {
		$usePhpPathSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'use_php_path_from_settings',
			'value' => 'false',
		]);
		$settingsMap = [
			['use_php_path_from_settings', $usePhpPathSetting],
		];
		$this->settingMapper->expects($this->any())
			->method('findByName')
			->will($this->returnValueMap($settingsMap));
		$result = $this->utils->getPhpInterpreter();
		$resultCheck = preg_match('/php\d?\.?\d?/s', $result, $matches);
		$this->assertTrue($resultCheck == 1, 'getPhpInterpreter should return php interpreter command');
		$this->assertCount(1, $matches, 'Should be one correct php path regex match');
		$this->assertStringContainsString($matches[0], $result, 'Should return automatically detected php_path');
	}

	public function testGetPhpInterpreterFromSettings() {
		$usePhpPathSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'use_php_path_from_settings',
			'value' => 'true',
		]);
		$phpPathSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 1,
			'name' => 'php_path',
			'value' => '"/usr/bin/php7.4"',
		]);
		$settingsMap = [
			['use_php_path_from_settings', $usePhpPathSetting],
			['php_path', $phpPathSetting],
		];
		$this->settingMapper->expects($this->any())
			->method('findByName')
			->will($this->returnValueMap($settingsMap));
		$result = $this->utils->getPhpInterpreter();
		$this->assertEquals('/usr/bin/php7.4', $result, 'Should return php_path from settings');
	}

	public function testIsFunctionEnabled() {
		$expected = true;
		$result = $this->utils->isFunctionEnabled('exec');
		$this->assertEquals($expected, $result, 'Should return function availability');
	}

	// public function testIsFunctionEnabledwithOverride() {
	// 	$expected = false;
	// 	$function_exists = $this->getFunctionMock('\OCA\Cloud_Py_API\Service', 'function_exists');
	// 	$function_exists->expects($this->once())
	// 		->with('exec')
	// 		->willReturn(false);
	// 	$result = $this->utils->isFunctionEnabled('exec');
	// 	$this->assertEquals($expected, $result, 'Should return false function availability');
	// }

	public function testIsSnapEnv() {
		$expected = false;
		$result = $this->utils->isSnapEnv();
		$this->assertEquals(
			$expected, $result, 'Should be bool value based on system env variable existence'
		);
	}

	public function testIsVideosSupported() {
		$expected = true;
		$exec = $this->getFunctionMock('\OCA\Cloud_Py_API\Service', 'exec');
		$exec->expects($this->any())
			->willReturnCallback(
				function ($command, &$output, &$result_code) {
					$output = ['version x.x.x'];
					$result_code = 0;
				}
			);
		$result = $this->utils->isVideosSupported();
		$this->assertEquals($expected, $result, 'Should return videos processing availability');
	}

	public function testIsVideosSupportedNotInstalled() {
		$expected = false;
		$exec = $this->getFunctionMock('\OCA\Cloud_Py_API\Service', 'exec');
		$exec->expects($this->any())
			->willReturnCallback(
				function ($command, &$output, &$result_code) {
					$output = ['command not found'];
					$result_code = 1;
				}
			);
		$result = $this->utils->isVideosSupported();
		$this->assertEquals($expected, $result, 'Should return videos processing availability');
	}

	public function testIsMusliLinux() {
		$expected = false;
		$exec = $this->getFunctionMock('\OCA\Cloud_Py_API\Service', 'exec');
		$exec->expects($this->any())
			->willReturnCallback(
				function ($command, &$output, &$result_code) {
					$this->assertEquals('ldd --version 2>&1', $command);
					$output = ['manylinux'];
					$result_code = 1;
				}
			);
		$result = $this->utils->isMusliLinux();
		$this->assertEquals($expected, $result, 'Should return bool is musllinux');
	}

	public function testIsMusliLinuxWithOverride() {
		$expected = true;
		$exec = $this->getFunctionMock('\OCA\Cloud_Py_API\Service', 'exec');
		$exec->expects($this->any())
			->willReturnCallback(
				function ($command, &$output, &$result_code) {
					$this->assertEquals('ldd --version 2>&1', $command);
					$output = ['musl linux'];
					$result_code = 1;
				}
			);
		$result = $this->utils->isMusliLinux();
		$this->assertEquals($expected, $result, 'Should return true bool is musllinux');
	}

	public function testGetOsArch() {
		$expected = 'amd64';
		$result = $this->utils->getOsArch();
		$this->assertEquals($expected, $result, 'Should return architecture name');
	}

	public function testGetCustomAppDirectory() {
		$expected = getcwd() . '/apps/';
		$result = $this->utils->getCustomAppsDirectory();
		$this->assertEquals(
			$expected, $result, 'Should return default value as the NC config option not set'
		);
	}

	public function testGetCustomAppDirectoryWithAppsPaths() {
		$appsPaths = [
			[
				// test default apps folder
				"path" => dirname(getcwd()),
				"url" => "/apps",
				"writable" => true
			]
		];
		$this->config->expects($this->once())
			->method('getSystemValue')
			->with('apps_paths')
			->willReturn($this->returnValue($appsPaths));
		$result = $this->utils->getCustomAppsDirectory();
		foreach ($appsPaths as $appsPath) {
			if ($appsPath['writable']) {
				$this->assertEquals($appsPath['path'] . '/', $result);
			} else {
				$this->assertEquals(getcwd() . '/apps/', $result);
			}
		}
	}

	public function testGetSystemInfo() {
		$pythonCommandSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'python_command',
			'value' => '"/usr/bin/python3"'
		]);
		$usePhpPathSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 1,
			'name' => 'use_php_path_from_settings',
			'value' => 'true',
		]);
		$phpPathSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 2,
			'name' => 'php_path',
			'value' => '"/usr/bin/php7.4"',
		]);
		$settingsMap = [
			['python_command', $pythonCommandSetting],
			['use_php_path_from_settings', $usePhpPathSetting],
			['php_path', $phpPathSetting],
		];
		$this->settingMapper->expects($this->any())
			->method('findByName')
			->will($this->returnValueMap($settingsMap));
		$appsVersions = [
			['cloud_py_api', true, '0.1.1'],
			['test_app', true, '0.1.0']
		];
		$this->appManager->expects($this->any())
			->method('getAppVersion')
			->will($this->returnValueMap($appsVersions));
		$this->config->expects($this->once())
			->method('getSystemValue')
			->with('version')
			->will($this->returnValue('25.0.0'));
		$systemInfoKeys = [
			'nextcloud-version',
			'app-versions',
			'is-videos-supported',
			'is-snap',
			'arch',
			'webserver',
			'database',
			'php-version',
			'php-interpreter',
			'python-interpreter-setting',
			'os',
			'os-release',
			'machine-type'
		];
		$result = $this->utils->getSystemInfo('test_app');
		$this->assertArrayHasKey('app-versions', $result);
		$this->assertCount(count($appsVersions), $result['app-versions']);
		$this->assertTrue(isset($result['app-versions']['cloud_py_api-version']));
		$this->assertEquals('0.1.1', $result['app-versions']['cloud_py_api-version']);
		$this->assertTrue(isset($result['app-versions']['test_app-version']));
		$this->assertEquals('0.1.0', $result['app-versions']['test_app-version']);
		foreach ($systemInfoKeys as $infoKey) {
			$this->assertArrayHasKey(
				$infoKey, $result, 'Should contain all described keys with system info'
			);
		}
	}

	public function testDownloadPythonBinary() {
		// TODO
		$this->addToAssertionCount(1);
	}

	public function testCompareBinaryHash() {
		// TODO
		$this->addToAssertionCount(1);
	}

	public function testDownloadBinaryHash() {
		// TODO
		$this->addToAssertionCount(1);
	}

	public function testUnGz() {
		// TODO
		$this->addToAssertionCount(1);
	}

	public function testAddChmodX() {
		// TODO
		$this->addToAssertionCount(1);
	}

	public function testGetBinaryName() {
		$expected = 'manylinux_amd64';
		$result = $this->utils->getBinaryName();
		$this->assertEquals(
			$expected, $result, 'Should return part of binary name based on Linux type and os arch'
		);
	}

	public function testGetBinaryNameMuslLinux() {
		$expected = 'musllinux_amd64';
		$exec = $this->getFunctionMock('\OCA\Cloud_Py_API\Service', 'exec');
		$exec->expects($this->any())
			->willReturnCallback(
				function ($command, &$output, &$result_code) {
					$this->assertEquals('ldd --version 2>&1', $command);
					$output = ['musl linux'];
					$result_code = 0;
				}
			);
		$result = $this->utils->getBinaryName();
		$this->assertEquals(
			$expected, $result, 'Should return part of binary name based on Linux type and os arch'
		);
	}

	public function testCheckForSettingsUpdates() {
		// TODO
		$this->addToAssertionCount(1);
	}

	public function testCheckForNewSettings() {
		// TODO
		$this->addToAssertionCount(1);
	}

	public function testCheckForDeletedSettings() {
		// TODO
		$this->addToAssertionCount(1);
	}

	public function testUpdateSettingsTexts() {
		// TODO
		$this->addToAssertionCount(1);
	}
}
