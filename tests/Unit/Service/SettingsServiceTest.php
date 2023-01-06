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

use OCA\Cloud_Py_API\Service\SettingsService;

/**
 * @covers \OCA\Cloud_Py_API\Service\SettingsService
 */
class SettingsServiceTest extends TestCase {
	use \phpmock\phpunit\PHPMock;

	/** @var \OCA\Cloud_Py_API\Db\SettingMapper|MockObject */
	private $settingMapper;

	/** @var ThriftService */
	private $settingsService;

	public function setUp(): void {
		parent::setUp();

		$this->settingMapper = $this->createMock(\OCA\Cloud_Py_API\Db\SettingMapper::class);

		$this->settingsService = new SettingsService($this->settingMapper);
	}

	public function testGetSettings() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value'
		]);
		$expectedResult = [$testSetting];
		$this->settingMapper->expects($this->once())
			->method('findAll')
			->willReturn($expectedResult);
		$result = $this->settingsService->getSettings();
		$this->assertEquals($expectedResult, $result);
	}

	public function testGetSettingById() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value'
		]);
		$expectedResult = $testSetting;
		$this->settingMapper->expects($this->once())
			->method('find')
			->with(0)
			->willReturn($testSetting);
		$result = $this->settingsService->getSettingById(0);
		$this->assertEquals($expectedResult, $result);
	}

	public function testGetSettingByIdNotFound() {
		$expectedResult = ['success' => false, 'message' => 'Not found'];
		$this->settingMapper->expects($this->once())
			->method('find')
			->with(0)
			->willThrowException(new \OCP\AppFramework\Db\DoesNotExistException(''));
		$result = $this->settingsService->getSettingById(0);
		$this->assertEquals($expectedResult, $result);
	}

	public function testGetSettingByName() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value'
		]);
		$expectedResult = ['success' => true, 'setting' => $testSetting];
		$this->settingMapper->expects($this->once())
			->method('findByName')
			->with('test_setting_name')
			->willReturn($testSetting);
		$result = $this->settingsService->getSettingByName('test_setting_name');
		$this->assertEquals($expectedResult, $result);
	}

	public function testGetSettingByNameNotFound() {
		$expectedResult = ['success' => false, 'message' => 'Not found'];
		$this->settingMapper->expects($this->once())
			->method('findByName')
			->with('test_setting_name')
			->willThrowException(new \OCP\AppFramework\Db\DoesNotExistException(''));
		$result = $this->settingsService->getSettingByName('test_setting_name');
		$this->assertEquals($expectedResult, $result);
	}

	public function testUpdateSetting() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value',
			'displayName' => 'testDisplayName',
			'title' => 'testTitle',
			'description' => 'testDescription',
			'helpUrl' => 'testHelpUrl',
		]);
		$expectedUpdatedSetting = $testSetting;
		$expectedUpdatedSetting->setValue('test_setting_value_updated');
		$expectedResult = ['success' => true, 'updated_setting' => $expectedUpdatedSetting];
		$this->settingMapper->expects($this->once())
			->method('update')
			->with($expectedUpdatedSetting)
			->willReturn($expectedUpdatedSetting);
		$result = $this->settingsService->updateSetting($testSetting);
		$this->assertEquals($expectedResult, $result);
	}

	public function testUpdateSettingJson() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value',
			'displayName' => 'testDisplayName',
			'title' => 'testTitle',
			'description' => 'testDescription',
			'helpUrl' => 'testHelpUrl',
		]);
		$expectedUpdatedSetting = $testSetting;
		$testSettingJson = $expectedUpdatedSetting->jsonSerialize();
		$expectedUpdatedSetting->setValue('test_setting_value_updated');
		$expectedResult = ['success' => true, 'updated_setting' => $expectedUpdatedSetting];
		$this->settingMapper->expects($this->once())
			->method('update')
			->with(new \OCA\Cloud_Py_API\Db\Setting([
				'id' => $testSettingJson['id'],
				'name' => $testSettingJson['name'],
				'value' => is_array($testSettingJson['value']) ? json_encode($testSettingJson['value']) : $testSettingJson['value'],
				'displayName' => $testSettingJson['display_name'],
				'title' => $testSettingJson['title'],
				'description' => $testSettingJson['description'],
				'helpUrl' => $testSettingJson['help_url']
			]))
			->willReturn($expectedUpdatedSetting);
		$result = $this->settingsService->updateSetting($testSettingJson);
		$this->assertEquals($expectedResult, $result);
	}

	public function testUpdateSettingError() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value',
			'displayName' => 'testDisplayName',
			'title' => 'testTitle',
			'description' => 'testDescription',
			'helpUrl' => 'testHelpUrl',
		]);
		$expectedUpdatedSetting = $testSetting;
		$testSettingJson = $expectedUpdatedSetting->jsonSerialize();
		$expectedUpdatedSetting->setValue('test_setting_value_updated');
		$expectedResult = [
			'success' => false,
			'message' => 'An error occured while updating setting',
			'setting' => $testSettingJson,
			'error' => 'test exception'
		];
		$this->settingMapper->expects($this->once())
			->method('update')
			->with(new \OCA\Cloud_Py_API\Db\Setting([
				'id' => $testSettingJson['id'],
				'name' => $testSettingJson['name'],
				'value' => is_array($testSettingJson['value']) ? json_encode($testSettingJson['value']) : $testSettingJson['value'],
				'displayName' => $testSettingJson['display_name'],
				'title' => $testSettingJson['title'],
				'description' => $testSettingJson['description'],
				'helpUrl' => $testSettingJson['help_url']
			]))
			->will($this->throwException(new \Exception('test exception')));
		$result = $this->settingsService->updateSetting($testSettingJson);
		$this->assertEquals($expectedResult, $result);
	}

	public function testUpdateSettings() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value',
			'displayName' => 'testDisplayName',
			'title' => 'testTitle',
			'description' => 'testDescription',
			'helpUrl' => 'testHelpUrl',
		]);
		$testSettingJson = $testSetting->jsonSerialize();
		$testSettingJson['value'] = 'test_setting_value_updated';
		$expectedUpdatedSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'id' => $testSettingJson['id'],
			'name' => $testSettingJson['name'],
			'value' => is_array($testSettingJson['value']) ? json_encode($testSettingJson['value']) : $testSettingJson['value'],
			'displayName' => $testSettingJson['display_name'],
			'title' => $testSettingJson['title'],
			'description' => $testSettingJson['description'],
			'helpUrl' => $testSettingJson['help_url']
		]);
		$expectedResult = ['success' => true, 'updated_settings' => [$expectedUpdatedSetting]];
		$this->settingMapper->expects($this->once())
			->method('update')
			->with($expectedUpdatedSetting)
			->willReturn($expectedUpdatedSetting);
		$result = $this->settingsService->updateSettings([$testSettingJson]);
		$this->assertEquals($expectedResult, $result);
	}

}
