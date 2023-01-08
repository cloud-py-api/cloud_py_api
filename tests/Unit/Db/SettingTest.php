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

namespace OCA\Cloud_Py_API\Tests\Unit\Db;

use OCA\Cloud_Py_API\Db\Setting;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cloud_Py_API\Db\Setting
 */
class SettingTest extends TestCase {
	/** @var Setting */
	private $setting;

	public function setUp(): void {
		parent::setUp();

		$this->setting = new Setting([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value',
			'displayName' => 'test_setting_display_name',
			'title' => 'test_setting_title',
			'description' => 'test_setting_description',
			'helpUrl' => 'test_setting_help_url',
		]);
	}

	public function testSettingKeys() {
		$this->assertEquals(0, $this->setting->getId());
		$this->assertEquals('test_setting_name', $this->setting->getName());
		$this->assertEquals('test_setting_value', $this->setting->getValue());
		$this->assertEquals('test_setting_display_name', $this->setting->getDisplayName());
		$this->assertEquals('test_setting_title', $this->setting->getTitle());
		$this->assertEquals('test_setting_description', $this->setting->getDescription());
		$this->assertEquals('test_setting_help_url', $this->setting->getHelpUrl());
	}

	public function testJsonSerialize() {
		$this->assertEquals([
			'id' => 0,
			'name' => 'test_setting_name',
			'value' => 'test_setting_value',
			'display_name' => 'test_setting_display_name',
			'title' => 'test_setting_title',
			'description' => 'test_setting_description',
			'help_url' => 'test_setting_help_url',
		], $this->setting->jsonSerialize());
	}
}
