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
use PHPUnit\Framework\MockObject\MockObject;

use OCA\Cloud_Py_API\Settings\AdminSection;

/**
 * @covers \OCA\Cloud_Py_API\Settings\AdminSection
 */
class AdminSectionTest extends TestCase {
	/** @var \OCP\IL10N|MockObject */
	private $l;

	/** @var \OC\URLGenerator|MockObject */
	private $urlGenerator;

	/** @var AdminSection */
	private $section;

	public function setUp(): void {
		parent::setUp();

		$this->l = $this->createMock(\OCP\IL10N::class);
		$this->urlGenerator = $this->createMock(\OC\URLGenerator::class);

		$this->section = new AdminSection($this->l, $this->urlGenerator);
	}

	public function testGetId() {
		$expected = 'cloud_py_api';
		$result = $this->section->getId();
		$this->assertEquals($expected, $result);
	}

	public function testGetName() {
		$expected = 'Cloud Python API';
		$this->l->expects($this->once())
			->method('t')
			->with('Cloud Python API')
			->will($this->returnValue('Cloud Python API'));
		$result = $this->section->getName();
		$this->assertEquals($expected, $result);
	}

	public function testGetPriority() {
		$result = $this->section->getPriority();
		$this->assertIsInt($result);
	}

	public function testGetIcon() {
		$expected = '/nextcloud/apps/cloud_py_api/img/settings.svg';
		$this->urlGenerator->expects($this->once())
			->method('imagePath')
			->with('cloud_py_api', 'settings.svg')
			->will($this->returnValue('/nextcloud/apps/cloud_py_api/img/settings.svg'));
		$result = $this->section->getIcon();
		$this->assertEquals($expected, $result);
	}
}
