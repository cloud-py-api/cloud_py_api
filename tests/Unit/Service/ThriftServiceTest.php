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

use OCA\Cloud_Py_API\Service\ThriftService;

/**
 * @covers \OCA\Cloud_Py_API\Service\ThriftService
 */
class ThriftServiceTest extends TestCase {
	use \phpmock\phpunit\PHPMock;

	/** @var ThriftService */
	private $tService;

	public function setUp(): void {
		parent::setUp();

		$this->tService = new ThriftService();
	}

	/**
	 * @covers \OCA\Cloud_Py_API\Service\ThriftService::runThriftServer
	 */
	public function testRunThriftClient() {
		$this->tService->runThriftBgServer();
		sleep(1); // wait for server to start
		$expectedResult = ['success' => true, 'recieved_ping_response' => 0];
		$result = $this->tService->runThriftClient();
		$this->assertEquals($expectedResult, $result);
	}

	public function testRunThriftClientError() {
		$expectedResult = ['success' => false, 'error_message' => 'TSocket: Could not connect to 0.0.0.0:7080 (Connection refused [111])'];
		$result = $this->tService->runThriftClient();
		$this->assertEquals($expectedResult, $result);
	}

}
