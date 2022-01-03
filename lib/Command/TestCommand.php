<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021 Andrey Borysenko <andrey18106x@gmail.com>
 * 
 * @copyright Copyright (c) 2021 Alexander Piskun <bigcat88@icloud.com>
 * 
 * @author 2021 Andrey Borysenko <andrey18106x@gmail.com>
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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OCP\Files\SimpleFS\ISimpleFolder;

use OCA\Cloud_Py_API\Service\AppsService;


class TestCommand extends Command {

	/** @var AppsService */
	private $appsService;

	public function __construct(AppsService $appsService) {
		parent::__construct();

		$this->appsService = $appsService;
	}

	protected function configure(): void {
		$this->setName("cloud_py_api:test");
		$this->setDescription("Test command");
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		// $this->appsService->createAppDataFolder('cloud_py_api');
		/** @var ISimpleFolder */
		$appDataFolder = $this->appsService->getAppDataFolder('cloud_py_api');
		$output->writeln($appDataFolder->getName());
		$appDataFolder->newFile('test_file.txt', 'Some text');
		$appDataFolderNodes = $appDataFolder->getDirectoryListing();
		$output->writeln(json_encode($appDataFolderNodes));

		$output->writeln('cloud_py_api config: ' . json_encode($this->appsService->getAppConfig('cloud_py_api')));
		$output->writeln('mediadc config: ' . json_encode($this->appsService->getAppConfig('mediadc')));
		$output->writeln('non-existed config: ' . json_encode($this->appsService->getAppConfig('123')));

		$this->appsService->registerApp('mediadc');
		return 0;
	}

}
