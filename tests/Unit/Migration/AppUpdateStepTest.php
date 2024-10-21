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

namespace OCA\Cloud_Py_API\Tests\Unit\Migration;

use OCA\Cloud_Py_API\Migration\AppUpdateStep;
use OCA\Cloud_Py_API\Migration\data\AppInitialData;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cloud_Py_API\Migration\AppUpdateStep
 */
class AppUpdateStepTest extends TestCase {
	/** @var \OCA\Cloud_Py_API\Service\UtilsService|MockObject */
	private $utils;

	/** @var AppUpdateStep */
	private $repairStep;

	public function setUp(): void {
		parent::setUp();

		$this->utils = $this->createMock(\OCA\Cloud_Py_API\Service\UtilsService::class);

		$this->repairStep = new AppUpdateStep($this->utils);
	}

	public function testName() {
		$this->assertEquals('Updating Cloud_Py_API data', $this->repairStep->getName());
	}

	public function testRun() {
		/** @var \OCP\Migration\IOutput|MockObject */
		$output = $this->createMock(\OCP\Migration\IOutput::class);
		$this->utils->expects($this->once())
			->method('checkForSettingsUpdates')
			->with(AppInitialData::$INITIAL_DATA);

		$this->repairStep->run($output);
	}
}
