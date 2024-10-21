<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Copyright (c) 2022-2023 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
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

namespace OCA\Cloud_Py_API\Command;

use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\Files\NotPermittedException;
use OCP\Lock\LockedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Temporal command to get file contents
 */
class GetFileContentsCommand extends Command {
	public const ARGUMENT_FILE_ID = 'fileid';
	public const ARGUMENT_USER_ID = 'userid';

	public function __construct(
		private readonly IRootFolder $rootFolder,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('cloud_py_api:getfilecontents');
		$this->setDescription('Returns file binary data');
		$this->addArgument(self::ARGUMENT_FILE_ID, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_USER_ID, InputArgument::REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$fileid = $input->getArgument(self::ARGUMENT_FILE_ID);
		$userid = $input->getArgument(self::ARGUMENT_USER_ID);

		$userFolder = $this->rootFolder->getUserFolder($userid);
		$nodes = $userFolder->getById($fileid);

		if (count($nodes) === 1) {
			$file = $nodes[0];
			if ($file instanceof File) {
				try {
					$output->write($file->getContent(), false, OutputInterface::OUTPUT_RAW);
					return 0;
				} catch (NotPermittedException|LockedException $e) {
					$this->logger->error($e->getMessage());
					return -1;
				}
			}
		}

		return 1;
	}
}
