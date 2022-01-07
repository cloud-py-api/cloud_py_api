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

namespace OCA\Cloud_Py_API\Service;

use OCP\Files\IAppData;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Grpc\RpcServer;

use OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient;
use OCA\Cloud_Py_API\Proto\FsGetInfoRequest;
use OCA\Cloud_Py_API\Proto\FsNodeInfo;
use OCA\Cloud_Py_API\Proto\fsId;
use OCA\Cloud_Py_API\Proto\FsListReply;
use OCA\Cloud_Py_API\Proto\FsListRequest;
use OCA\Cloud_Py_API\Proto\PBEmpty;
use OCA\Cloud_Py_API\Proto\TaskInitReply;
use OCA\Cloud_Py_API\Proto\TaskInitReply\cfgOptions;


class ServerService {

	public static $APP = null;

	/** @var CloudPyApiCoreService */
	private $service;

	public function __construct(IAppData $appData, CloudPyApiCoreService $service,
								LoggerInterface $logger)
	{
		$this->appData = $appData;
		$this->service = $service;
		$this->logger = $logger;
	}

	public function runGrpcServer(string $hostname = '0.0.0.0', string $port = '50051', array $params = []) {
		self::$APP = $params;
		/** @var RpcServer */
		$server = new RpcServer();
		$server->addHttp2Port($hostname . ':' . $port);
		$server->handle($this->service);
		$server->run();
	}

	public function testFsList(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$userid = $input->getArgument('userid');
		$fileid = $input->getArgument('fileid');
		$client = new CloudPyApiCoreClient($hostname . ':' . $port, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);
		/** @var FsListRequest */
		$request = new FsListRequest([]);
		$fsId = new fsId();
		$fsId->setUserId($userid);
		if (isset($fileid)) {
			$fsId->setFileId($fileid);
		}
		$request->setDirId($fsId);
		/** @var FsListReply $response */
		list($response, $status) = $client->FsList($request)->wait();
		$output->writeln('Response status: ' . json_encode($status));
		if ($response !== null && count($response->getNodes()) > 0) {
			$output->writeln('Response items:');
			/** @var FsNodeInfo $responseNode */
			foreach ($response->getNodes() as $responseNode) {
				$output->writeln($responseNode->getFileId()->getFileId() . '. ' . $responseNode->getName() . ' (' . $responseNode->getSize() .' bytes)');
			}
		}
	}

	public function testGetFileInfo(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$userid = $input->getArgument('userid');
		$fileid = $input->getArgument('fileid');
		$client = new CloudPyApiCoreClient($hostname . ':' . $port, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);
		/** @var FsGetInfoRequest */
		$request = new FsGetInfoRequest([]);
		$fsId = new fsId();
		$fsId->setUserId($userid);
		$fsId->setFileId($fileid);
		$request->setFileId($fsId);
		/** @var FsListReply $response */
		list($response, $status) = $client->FsGetInfo($request)->wait();
		$output->writeln('Response status: ' . json_encode($status));
		if ($response !== null && count($response->getNodes()) > 0) {
			$output->writeln('Response items:');
			/** @var FsNodeInfo $responseNode */
			foreach ($response->getNodes() as $responseNode) {
				$output->writeln($responseNode->getFileId()->getFileId() . '. ' . $responseNode->getName() . ' (' . $responseNode->getSize() .' bytes)');
			}
		}
	}

	public function testTaskInit(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$client = new CloudPyApiCoreClient($hostname . ':' . $port, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);
		$request = new PBEmpty();
		/** @var TaskInitReply */
		list($response, $status) = $client->TaskInit($request)->wait();
		$output->writeln('Response status: ' . json_encode($status));
		$output->writeln('Response: ' . json_encode($response));
		$output->writeln('appname: ' . $response->getAppName());
		$output->writeln('modname: ' . $response->getModName());
		$output->writeln('modpath: ' . $response->getModPath());
		$output->writeln('funcname: ' . $response->getFuncName());
		if ($response->getArgs() !== null) {
			$output->write('args:');
			foreach ($response->getArgs() as $argument) {
				$output->write(' ' . $argument);
			}
			$output->writeln('');
		}
		$output->writeln('Config: ');
		/** @var cfgOptions */
		$cfg = $response->getConfig();
		$output->writeln('logLvl: ' . $cfg->getLogLvl());
		$output->writeln('datafolder: ' . $cfg->getDataFolder());
		$output->writeln('frameworkAppData: ' . $cfg->getFrameworkAppData());
		$output->writeln('useFileDirect: ' . json_encode($cfg->getUseFileDirect()));
		$output->writeln('useDBDirect: ' . json_encode($cfg->getUseDBDirect()));
	}

}