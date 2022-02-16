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

use OCA\Cloud_Py_API\Service\PythonService;


class FrameworkCheckCommand extends Command {

	/** @var PythonService */
	private $pythonService;

	public function __construct(PythonService $pythonService) {
		parent::__construct();

		$this->pythonService = $pythonService;
	}

	protected function configure(): void {
		$this->setName("cloud_py_api:framework:check");
		$this->setDescription("Check cloud_py_api python framework installation");
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$pythonOutput = $this->pythonService->run('/pyfrm/install.py', [
			'--config' => rawurlencode(json_encode($this->pythonService->getPyFrmConfig())),
			'--check' => '',
			'--target' => 'framework',
		]);
		$output->writeln(json_encode($pythonOutput));
		return 0;
	}

}
