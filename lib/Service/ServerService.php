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
use Grpc\ClientStreamingCall;
use Grpc\ServerStreamingCall;

use OCA\Cloud_Py_API\Proto\CloudPyApiCoreClient;
use OCA\Cloud_Py_API\Proto\FsCreateReply;
use OCA\Cloud_Py_API\Proto\FsCreateRequest;
use OCA\Cloud_Py_API\Proto\FsDeleteRequest;
use OCA\Cloud_Py_API\Proto\FsGetInfoRequest;
use OCA\Cloud_Py_API\Proto\FsNodeInfo;
use OCA\Cloud_Py_API\Proto\fsId;
use OCA\Cloud_Py_API\Proto\FsListReply;
use OCA\Cloud_Py_API\Proto\FsListRequest;
use OCA\Cloud_Py_API\Proto\FsMoveRequest;
use OCA\Cloud_Py_API\Proto\FsReadReply;
use OCA\Cloud_Py_API\Proto\FsReadRequest;
use OCA\Cloud_Py_API\Proto\FsReply;
use OCA\Cloud_Py_API\Proto\FsWriteRequest;
use OCA\Cloud_Py_API\Proto\PBEmpty;
use OCA\Cloud_Py_API\Proto\TaskExitRequest;
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
		// TODO Add running pyfrm
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

	public function testFsReadFile(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$userid = $input->getArgument('userid');
		$fileid = $input->getArgument('fileid');
		$offset = $input->getArgument('offset');
		$length = $input->getArgument('length');
		$client = new CloudPyApiCoreClient($hostname . ':' . $port, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);
		/** @var FsReadRequest */
		$request = new FsReadRequest();
		$fsId = new fsId();
		$fsId->setUserId($userid);
		$fsId->setFileId(intval($fileid));
		$request->setFileId($fsId);
		if (isset($offset)) {
			$request->setOffset($offset);
		}
		if (isset($length)) {
			$request->setBytesToRead($length);
		}
		/** @var ServerStreamingCall */
		$call = $client->FsRead($request);
		$output->writeln('Responses: ');
		/** @var FsReadReply $response */
		foreach ($call->responses() as $response) {
			$output->writeln('Res code: ' . $response->getResCode());
			$output->writeln('Last: ' . json_encode(boolval($response->getLast())));
			$output->writeln('Content: ' . $response->getContent());
		}
		$client->close();
	}

	public function testFsWriteFile(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$userid = $input->getArgument('userid');
		$fileid = $input->getArgument('fileid');
		$content = $input->getArgument('content');
		$client = new CloudPyApiCoreClient($hostname . ':' . $port, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);

		$request1 = new FsWriteRequest();
		$fsId = new fsId();
		$fsId->setFileId($fileid);
		$fsId->setUserId($userid);
		$request1->setFileId($fsId);
		$request1->setLast(false);
		$request1->setContent($content);

		$request2 = new FsWriteRequest();
		$fsId = new fsId();
		$fsId->setFileId($fileid);
		$fsId->setUserId($userid);
		$request2->setFileId($fsId);
		$request2->setLast(true);
		$request2->setContent($content);

		/** @var ClientStreamingCall */
		$call = $client->FsWrite();
		$call->write($request1);
		$call->write($request2);
		/** @var FsReply */
		list($response, $status) = $call->wait();
		$output->writeln('Status: ' . json_encode($status));
		$output->writeln('Response: ' . json_encode($response->getResCode()));
	}

	public function testFsCreateFile(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$userId = $input->getArgument('userid');
		$parentDirId = $input->getArgument('parentdirid');
		$name = $input->getArgument('name');
		$isFile = $input->getArgument('isfile');
		$content = $input->getArgument('content');
		$client = new CloudPyApiCoreClient($hostname . ':' . $port, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);
		$request = new FsCreateRequest();
		$fsId = new fsId();
		$fsId->setFileId($parentDirId);
		$fsId->setUserId($userId);
		$request->setParentDirId($fsId);
		$request->setIsFile(boolval($isFile));
		$request->setName($name);
		$request->setContent($content);
		/** @var FsCreateReply $response */
		list($response, $status) = $client->FsCreate($request)->wait();
		$output->writeln('Response status: ' . json_encode($status));
		if (isset($response)) {
			$output->writeln('Response: ');
			$output->writeln('Res code: ' . $response->getResCode());
			$output->writeln('FileId: ' . $response->getFileId()->getFileId());
		}
	}

	public function testFsDeleteFile(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$userid = $input->getArgument('userid');
		$fileid = $input->getArgument('fileid');
		$client = new CloudPyApiCoreClient($hostname . ':' . $port, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);
		$request = new FsDeleteRequest();
		$fsId = new fsId();
		$fsId->setUserId($userid);
		$fsId->setFileId($fileid);
		$request->setFileId($fsId);
		/** @var FsReply $response */
		list($response, $status) = $client->FsDelete($request)->wait();
		$output->writeln('Response status: ' . json_encode($status));
		$output->writeln('Res code: ' . $response->getResCode());
	}

	public function testFsMoveFile(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$userid = $input->getArgument('userid');
		$fileid = $input->getArgument('fileid');
		$targetPath = $input->getArgument('targetpath');
		$copy = $input->getArgument('copy');
		$client = new CloudPyApiCoreClient($hostname . ':' . $port, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);
		$request = new FsMoveRequest();
		$fsId = new fsId();
		$fsId->setUserId($userid);
		$fsId->setFileId($fileid);
		$request->setFileId($fsId);
		$request->setTargetPath($targetPath);
		$request->setCopy(boolval($copy));
		/** @var FsReply $response */
		list($response, $status) = $client->FsMove($request)->wait();
		$output->writeln('Response status: ' . json_encode($status));
		$output->writeln('Res code: ' . $response->getResCode());
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
		$output->writeln('userId: ' . $cfg->getUserId());
		$output->writeln('logLvl: ' . $cfg->getLogLvl());
		$output->writeln('datafolder: ' . $cfg->getDataFolder());
		$output->writeln('frameworkAppData: ' . $cfg->getFrameworkAppData());
		$output->writeln('useFileDirect: ' . json_encode($cfg->getUseFileDirect()));
		$output->writeln('useDBDirect: ' . json_encode($cfg->getUseDBDirect()));
	}

	public function testTaskExit(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$client = new CloudPyApiCoreClient($hostname . ':' . $port, [
			'credentials' => \Grpc\ChannelCredentials::createInsecure()
		]);
		$client->TaskExit(new TaskExitRequest());
	}

}