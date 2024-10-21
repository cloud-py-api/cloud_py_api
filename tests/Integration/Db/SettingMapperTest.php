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

namespace OCA\Cloud_Py_API\Tests\Integration\Db;

use OCA\Cloud_Py_API\Db\SettingMapper;

use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cloud_Py_API\Db\SettingMapper
 */
class SettingMapperTest extends TestCase {
	/** @var \OCP\IDBConnection */
	private $db;

	/** @var SettingMapper */
	private $settingMapper;

	public function setUp(): void {
		parent::setUp();

		$this->db = \OC::$server->getDatabaseConnection();

		$this->settingMapper = new SettingMapper($this->db);
	}

	public function testFind() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'name' => 'test_setting_name',
			'value' => '"test_setting_value"',
			'displayName' => 'test_setting_display_name',
			'title' => 'test_setting_title',
			'description' => 'test_setting_description',
			'helpUrl' => 'test_setting_help_url',
		]);
		$insertedSetting = $this->settingMapper->insert($testSetting);
		$testSetting->setId($insertedSetting->getId());
		$result = $this->settingMapper->find($testSetting->getId());
		$this->settingMapper->delete($testSetting); // cleanup after test
		$this->assertEquals($testSetting->getId(), $result->getId());
	}

	public function testFindAll() {
		$testSettings = [
			new \OCA\Cloud_Py_API\Db\Setting([
				'name' => 'test_setting_name',
				'value' => '"test_setting_value"',
				'displayName' => 'test_setting_display_name',
				'title' => 'test_setting_title',
				'description' => 'test_setting_description',
				'helpUrl' => 'test_setting_help_url',
			]),
		];
		foreach ($testSettings as $testSetting) {
			$insertedSetting = $this->settingMapper->insert($testSetting);
			$testSetting->setId($insertedSetting->getId());
		}
		$result = $this->settingMapper->findAll();
		foreach ($testSettings as $testSetting) {
			$this->settingMapper->delete($testSetting); // cleanup after test
		}
		$this->assertGreaterThan(0, count($result));
	}

	public function testFindByName() {
		$testSetting = new \OCA\Cloud_Py_API\Db\Setting([
			'name' => 'test_setting_name',
			'value' => '"test_setting_value"',
			'displayName' => 'test_setting_display_name',
			'title' => 'test_setting_title',
			'description' => 'test_setting_description',
			'helpUrl' => 'test_setting_help_url',
		]);
		$insertedSetting = $this->settingMapper->insert($testSetting);
		$testSetting->setId($insertedSetting->getId());
		$result = $this->settingMapper->findByName($testSetting->getName());
		$this->settingMapper->delete($testSetting); // cleanup after test
		$this->assertEquals($testSetting->getId(), $result->getId());
	}
}
