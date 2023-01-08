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

namespace OCA\Cloud_Py_API\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use OCA\Cloud_Py_API\Service\ThriftService;
use OCA\Cloud_Py_API\Command\ThriftClientCommand;

/**
 * @covers \OCA\Cloud_Py_API\Command\ThriftClientCommand
 */
class ThriftClientCommandTest extends TestCase {
	/** @var ThriftService|MockObject */
	private $tService;

	/** @var ThriftClientCommand */
	private $command;

	public function setUp(): void {
		parent::setUp();

		$this->tService = $this->createMock(ThriftService::class);

		$this->command = new ThriftClientCommand($this->tService);
	}

	public function testName() {
		$expected = 'cloud_py_api:thrift:client';
		$result = $this->command->getName();
		$this->assertSame($expected, $result);
	}

	public function testDescription() {
		$expected = 'Check cloud_py_api thrift status';
		$result = $this->command->getDescription();
		$this->assertSame($expected, $result);
	}

	public function testExecute() {
		$expectedResultCode = 0;
		/** @var InputInterface|MockObject */
		$input = $this->createMock(InputInterface::class);

		$this->tService->expects($this->once())
			->method('runThriftClient')
			->willReturn(['success' => true, 'recieved_ping_response' => 0]);
		/** @var OutputInterface|MockObject */
		$output = $this->createMock(OutputInterface::class);
		$output->expects($this->once())
			->method('writeln')
			->with('{"success":true,"recieved_ping_response":0}');

		$result = $this->command->run($input, $output);
		$this->assertEquals($expectedResultCode, $result);
	}
}
