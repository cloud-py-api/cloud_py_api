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

namespace OCA\Cloud_Py_API\Tests\Unit\Settings;

use PHPUnit\Framework\TestCase;

use OCA\Cloud_Py_API\Settings\AdminSettings;
use OCP\AppFramework\Http\TemplateResponse;

/**
 * @covers \OCA\Cloud_Py_API\Settings\AdminSettings
 */
class AdminSettingsTest extends TestCase {
	/** @var AdminSettings */
	private $settings;

	public function setUp(): void {
		parent::setUp();

		$this->settings = new AdminSettings();
	}

	public function testGetForm() {
		$expected = new TemplateResponse('cloud_py_api', 'admin');
		$result = $this->settings->getForm();
		$this->assertEquals($expected, $result);
	}

	public function testGetSection() {
		$result = $this->settings->getSection();
		$this->assertEquals('cloud_py_api', $result);
	}

	public function testGetPriority() {
		$result = $this->settings->getPriority();
		$this->assertIsInt($result);
	}
}
