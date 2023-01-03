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
use OCP\Files\NotPermittedException;
use OCP\Lock\LockedException;

use OCA\Cloud_Py_API\Command\GetFileContentsCommand;

/**
 * @covers \OCA\Cloud_Py_API\Command\GetFileContentsCommand
 */
class GetFileContentsCommandTest extends TestCase {
	/** @var \OCP\Files\IRootFolder|MockObject */
	private $rootFolder;

	/** @var \Psr\Log\LoggerInterface|MockObject */
	private $logger;

	/** @var GetFileContentsCommand */
	private $command;

	private $args = [
		'fileid',
		'userid'
	];

	public function setUp(): void {
		parent::setUp();

		$this->rootFolder = $this->createMock(\OCP\Files\IRootFolder::class);
		$this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);

		$this->command = new GetFileContentsCommand($this->rootFolder, $this->logger);
	}

	public function testName() {
		$expected = 'cloud_py_api:getfilecontents';
		$result = $this->command->getName();
		$this->assertSame($expected, $result);
	}

	public function testDescription() {
		$expected = 'Returns file binary data';
		$result = $this->command->getDescription();
		$this->assertSame($expected, $result);
	}

	public function testArguments() {
		$commandArgs = $this->command->getDefinition()->getArguments();

		foreach ($commandArgs as $cArg) {
			$this->assertTrue($cArg->isRequired());
			$this->assertTrue(in_array($cArg->getName(), $this->args));
		}
	}

	public function testExecute() {
		$expectedResultCode = 0;
		/** @var InputInterface|MockObject */
		$input = $this->createMock(InputInterface::class);
		$inputMap = [
			['fileid', 123],
			['userid', 'admin']
		];
		$input->expects($this->any())
			->method('getArgument')
			->willReturnMap($inputMap);

		/** @var OutputInterface|MockObject */
		$output = $this->createMock(OutputInterface::class);
		$output->expects($this->once())
			->method('write')
			->with('Test file contents', false, OutputInterface::OUTPUT_RAW);

		/** @var \OCP\Files\IRootFolder|MockObject */
		$testRootFolder = $this->createMock(\OCP\Files\IRootFolder::class);
		$this->rootFolder->expects($this->once())
			->method('getUserFolder')
			->with('admin')
			->will($this->returnValue($testRootFolder));

		/** @var \OCP\Files\File|MockObject */
		$testFileNode = $this->createMock(\OCP\Files\File::class);
		$testFileNode->expects($this->once())
			->method('getContent')
			->will($this->returnValue('Test file contents'));
		$testRootFolder->expects($this->once())
			->method('getById')
			->with(123)
			->will($this->returnValue([$testFileNode]));
		$result = $this->command->run($input, $output);
		$this->assertEquals($expectedResultCode, $result);
	}

	public function testExecuteNotPermited() {
		$expectedResultCode = -1;
		/** @var InputInterface|MockObject */
		$input = $this->createMock(InputInterface::class);
		$inputMap = [
			['fileid', 123],
			['userid', 'admin']
		];
		$input->expects($this->any())
			->method('getArgument')
			->willReturnMap($inputMap);

		/** @var OutputInterface|MockObject */
		$output = $this->createMock(OutputInterface::class);

		/** @var \OCP\Files\IRootFolder|MockObject */
		$testRootFolder = $this->createMock(\OCP\Files\IRootFolder::class);
		$this->rootFolder->expects($this->once())
			->method('getUserFolder')
			->with('admin')
			->will($this->returnValue($testRootFolder));

		$notPermitedException = new NotPermittedException('Not permited');
		/** @var \OCP\Files\File|MockObject */
		$testFileNode = $this->createMock(\OCP\Files\File::class);
		$testFileNode->expects($this->once())
			->method('getContent')
			->will($this->throwException($notPermitedException));
		$testRootFolder->expects($this->once())
			->method('getById')
			->with(123)
			->will($this->returnValue([$testFileNode]));
		$this->logger->expects($this->once())
			->method('error')
			->with('Not permited');
		$result = $this->command->run($input, $output);
		$this->assertEquals($expectedResultCode, $result);
	}

	public function testExecuteLocked() {
		$expectedResultCode = -1;
		/** @var InputInterface|MockObject */
		$input = $this->createMock(InputInterface::class);
		$inputMap = [
			['fileid', 123],
			['userid', 'admin']
		];
		$input->expects($this->any())
			->method('getArgument')
			->willReturnMap($inputMap);

		/** @var OutputInterface|MockObject */
		$output = $this->createMock(OutputInterface::class);

		/** @var \OCP\Files\IRootFolder|MockObject */
		$testRootFolder = $this->createMock(\OCP\Files\IRootFolder::class);
		$this->rootFolder->expects($this->once())
			->method('getUserFolder')
			->with('admin')
			->will($this->returnValue($testRootFolder));

		$lockedException = new LockedException('/path/to/file');
		/** @var \OCP\Files\File|MockObject */
		$testFileNode = $this->createMock(\OCP\Files\File::class);
		$testFileNode->expects($this->once())
			->method('getContent')
			->will($this->throwException($lockedException));
		$testRootFolder->expects($this->once())
			->method('getById')
			->with(123)
			->will($this->returnValue([$testFileNode]));
		$this->logger->expects($this->once())
			->method('error')
			->with('"/path/to/file" is locked');
		$result = $this->command->run($input, $output);
		$this->assertEquals($expectedResultCode, $result);
	}

	public function testExecuteNotFound() {
		$expectedResultCode = 1;
		/** @var InputInterface|MockObject */
		$input = $this->createMock(InputInterface::class);
		$inputMap = [
			['fileid', 123],
			['userid', 'admin']
		];
		$input->expects($this->any())
			->method('getArgument')
			->willReturnMap($inputMap);

		/** @var OutputInterface|MockObject */
		$output = $this->createMock(OutputInterface::class);

		/** @var \OCP\Files\IRootFolder|MockObject */
		$testRootFolder = $this->createMock(\OCP\Files\IRootFolder::class);
		$this->rootFolder->expects($this->once())
			->method('getUserFolder')
			->with('admin')
			->will($this->returnValue($testRootFolder));

		$testRootFolder->expects($this->once())
			->method('getById')
			->with(123)
			->will($this->returnValue([]));
		$result = $this->command->run($input, $output);
		$this->assertEquals($expectedResultCode, $result);
	}
}
