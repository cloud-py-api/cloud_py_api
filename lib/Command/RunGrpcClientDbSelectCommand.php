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
use Symfony\Component\Console\Input\InputArgument;

use OCA\Cloud_Py_API\Service\ServerService;


class RunGrpcClientDbSelectCommand extends Command {

	public const ARGUMENT_HOSTNAME = 'hostname';
	public const ARGUMENT_PORT = 'port';

	public const ARGUMENT_COLUMNS = 'columns';
	public const ARGUMENT_FROM = 'from';
	public const ARGUMENT_JOINS = 'joins';
	public const ARGUMENT_WHEREAS = 'whereas';
	public const ARGUMENT_GROUP_BY = 'groupBy';
	public const ARGUMENT_HAVINGS = 'havings';
	public const ARGUMENT_ORDER_BY = 'orderBy';
	public const ARGUMENT_MAX_RESULTS = 'maxResults';
	public const ARGUMENT_FIRST_RESULT = 'firstResult';

	/** @var ServerService */
	private $serverService;

	public function __construct(ServerService $serverService) {
		parent::__construct();

		$this->serverService = $serverService;
	}

	protected function configure(): void {
		$this->setName("cloud_py_api:grpc:client:db:select");
		$this->setDescription("Run GRPC client DbSelect request");
		$this->addArgument(self::ARGUMENT_HOSTNAME, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_PORT, InputArgument::REQUIRED);
		// $this->addArgument(self::ARGUMENT_COLUMNS, InputArgument::REQUIRED);
		// $this->addArgument(self::ARGUMENT_FROM, InputArgument::REQUIRED);
		// $this->addArgument(self::ARGUMENT_JOINS, InputArgument::OPTIONAL);
		// $this->addArgument(self::ARGUMENT_WHEREAS, InputArgument::OPTIONAL);
		// $this->addArgument(self::ARGUMENT_GROUP_BY, InputArgument::OPTIONAL);
		// $this->addArgument(self::ARGUMENT_HAVINGS, InputArgument::OPTIONAL);
		// $this->addArgument(self::ARGUMENT_ORDER_BY, InputArgument::OPTIONAL);
		// $this->addArgument(self::ARGUMENT_MAX_RESULTS, InputArgument::OPTIONAL);
		// $this->addArgument(self::ARGUMENT_FIRST_RESULT, InputArgument::OPTIONAL);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->serverService->testDbSelect($input, $output);
		return 0;
	}

}
