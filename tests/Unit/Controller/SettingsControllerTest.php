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

namespace OCA\Cloud_Py_API\Tests\Unit\Controller;

use OCA\Cloud_Py_API\Controller\SettingsController;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cloud_Py_API\Controller\SettingsController
 */
class SettingsControllerTest extends TestCase {
	/** @var \OCP\IRequest|MockObject */
	private $request;

	/** @var \OCA\Cloud_Py_API\Service\SettingsService|MockObject */
	private $settingsService;

	/** @var \OCA\Cloud_Py_API\Service\UtilsService|MockObject */
	private $utils;

	/** @var SettingsController */
	private $controller;

	public function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(\OCP\IRequest::class);
		$this->settingsService = $this->createMock(\OCA\Cloud_Py_API\Service\SettingsService::class);
		$this->utils = $this->createMock(\OCA\Cloud_Py_API\Service\UtilsService::class);

		$this->controller = new SettingsController($this->request, $this->settingsService, $this->utils);
	}

	public function testIndex() {
		$testSettings = [
			new \OCA\Cloud_Py_API\Db\Setting([
				'id' => 0,
				'name' => 'test_setting_name',
				'value' => 'test_setting_value'
			])
		];

		$this->settingsService->expects($this->once())
			->method('getSettings')
			->willReturn($testSettings);

		$expected = new \OCP\AppFramework\Http\JSONResponse($testSettings, \OCP\AppFramework\Http::STATUS_OK);
		$result = $this->controller->index();

		$this->assertEquals($expected, $result);
	}

	public function testUpdate() {
		$testSettings = [
			new \OCA\Cloud_Py_API\Db\Setting([
				'id' => 0,
				'name' => 'test_setting_name',
				'value' => 'test_setting_value'
			])
		];

		$this->settingsService->expects($this->once())
			->method('updateSettings')
			->willReturn($testSettings);

		$expected = new \OCP\AppFramework\Http\JSONResponse($testSettings, \OCP\AppFramework\Http::STATUS_OK);
		$result = $this->controller->update($testSettings);

		$this->assertEquals($expected, $result);
	}

	public function testUpdateSetting() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value'
		]);

		$this->settingsService->expects($this->once())
			->method('updateSetting')
			->willReturn($testSetting);

		$expected = new \OCP\AppFramework\Http\JSONResponse($testSetting, \OCP\AppFramework\Http::STATUS_OK);
		$result = $this->controller->updateSetting($testSetting);

		$this->assertEquals($expected, $result);
	}

	public function testGetSettingById() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value'
		]);

		$this->settingsService->expects($this->once())
			->method('getSettingById')
			->willReturn($testSetting);

		$expected = new \OCP\AppFramework\Http\JSONResponse($testSetting, \OCP\AppFramework\Http::STATUS_OK);
		$result = $this->controller->getSettingById(0);

		$this->assertEquals($expected, $result);
	}

	public function testGetSettingByName() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value'
		]);

		$this->settingsService->expects($this->once())
			->method('getSettingByName')
			->willReturn($testSetting);

		$expected = new \OCP\AppFramework\Http\JSONResponse($testSetting, \OCP\AppFramework\Http::STATUS_OK);
		$result = $this->controller->getSettingByName('test_setting_name');

		$this->assertEquals($expected, $result);
	}

	public function testSystemInfo() {
		$testSystemInfo = [
			'test_system_info' => 'test_system_info_value'
		];
		$expected = new \OCP\AppFramework\Http\JSONResponse($testSystemInfo, \OCP\AppFramework\Http::STATUS_OK);
		$this->utils->expects($this->once())
			->method('getSystemInfo')
			->will($this->returnValue(['test_system_info' => 'test_system_info_value']));
		$result = $this->controller->systemInfo();
		$this->assertEquals($expected, $result);
	}
}
