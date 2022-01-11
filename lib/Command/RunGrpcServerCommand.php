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

use OCA\Cloud_Py_API\Service\UtilsService;


class RunGrpcServerCommand extends Command {

	public const ARGUMENT_HOSTNAME = 'hostname';
	public const ARGUMENT_PORT = 'port';

	public const ARGUMENT_USERID = 'userid';
	public const ARGUMENT_APPNAME = 'appname';
	public const ARGUMENT_MODNAME = 'modname';
	public const ARGUMENT_MODPATH = 'modpath';
	public const ARGUMENT_FUNCNAME = 'funcname';
	public const ARGUMENT_ARGS = 'args';

	/** @var UtilsService */
	private $utils;

	public function __construct(UtilsService $utils) {
		parent::__construct();

		$this->utils = $utils;
	}

	protected function configure(): void {
		$this->setName("cloud_py_api:grpc:server:run");
		$this->setDescription("Run GRPC server in background");
		$this->addArgument(self::ARGUMENT_HOSTNAME, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_PORT, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_USERID, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_APPNAME, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_MODNAME, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_MODPATH, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_FUNCNAME, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_ARGS, InputArgument::OPTIONAL);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$hostname = $input->getArgument(self::ARGUMENT_HOSTNAME);
		$port = $input->getArgument(self::ARGUMENT_PORT);
		$userid = $input->getArgument(self::ARGUMENT_USERID);
		$appname = $input->getArgument(self::ARGUMENT_APPNAME);
		$modname = $input->getArgument(self::ARGUMENT_MODNAME);
		$modpath = $input->getArgument(self::ARGUMENT_MODPATH);
		$funcname = $input->getArgument(self::ARGUMENT_FUNCNAME);
		$args = $input->getArgument(self::ARGUMENT_ARGS);

		$pathToOcc = getcwd() . '/occ';
		$cloudPyApiCommand = 'cloud_py_api:grpc:server:bg:run ' . $hostname . ' ' . $port
			. $userid . ' ' . $appname . ' ' . $modname . ' ' . $modpath . ' ' . $funcname;
		if ($args !== null) {
			$cloudPyApiCommand += array_reduce(json_decode($args), function ($carry, $argument) {
				return $carry += ' ' . $argument;
			});
		}
		$command = $this->utils->getPhpInterpreter() . ' ' . $pathToOcc . ' ' . $cloudPyApiCommand 
			. ' > /dev/null 2>&1 & echo $!';
		exec($command, $cmdOut);
		if (isset($cmdOut[0]) && intval($cmdOut[0]) > 0) {
			$pid = $cmdOut[0];
			$output->writeln($pid);
			return 0;
		}
		$output->writeln('Seems like server not started..');
		return 1;
	}

}
