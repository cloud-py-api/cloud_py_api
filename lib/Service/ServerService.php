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

use OCP\DB\QueryBuilder\IQueryBuilder;

use Grpc\RpcServer;
use Grpc\ClientStreamingCall;
use Grpc\ServerStreamingCall;

use OCA\Cloud_Py_API\Proto\TaskInitReply;
use OCA\Cloud_Py_API\Proto\TaskInitReply\cfgOptions;
use OCA\Cloud_Py_API\Proto\FsCreateReply;
use OCA\Cloud_Py_API\Proto\FsNodeInfo;
use OCA\Cloud_Py_API\Proto\FsReadReply;
use OCA\Cloud_Py_API\Proto\FsReply;
use OCA\Cloud_Py_API\Proto\DbSelectReply;
use OCA\Cloud_Py_API\Proto\DbCursorReply;
use OCA\Cloud_Py_API\Proto\DbCursorRequest\cCmd;
use OCA\Cloud_Py_API\Proto\DbCursorReply\columnData;
use OCA\Cloud_Py_API\Proto\DbExecRequest\rType;
use OCA\Cloud_Py_API\Proto\exprType;
use OCA\Cloud_Py_API\Proto\pType;
use OCA\Cloud_Py_API\Proto\pValueType;

use OCA\Cloud_Py_API\Framework\Core;
use OCA\Cloud_Py_API\Framework\Db;
use OCA\Cloud_Py_API\Framework\Fs;

use OCA\Cloud_Py_API\AppInfo\Application;


class ServerService {

	public static $APP = null;

	/** @var LoggerInterface */
	public static $staticLogger;

	/** @var Core */
	private $cpaCore;

	/** @var Fs */
	private $cpaFs;

	/** @var Db */
	private $cpaDb;

	public function __construct(IAppData $appData, LoggerInterface $logger,
								Core $cpaCore, Fs $cpaFs, Db $cpaDb)
	{
		$this->appData = $appData;
		$this->cpaCore = $cpaCore;
		$this->cpaFs = $cpaFs;
		$this->cpaDb = $cpaDb;
		$this->logger = $logger;
	}

	public function runGrpcServer(string $hostname = '0.0.0.0', string $port = '50051', array $appInfo = []) {
		self::$APP = $appInfo;
		self::$staticLogger = $this->logger;
		/** @var RpcServer */
		$server = $this->cpaCore->createServer([
			'hostname' => $hostname,
			'port' => $port
		]);
		// TODO Add running pyfrm
		$server->run();
	}
 
	public static function testHandler($params = []) {
		self::$staticLogger->info('[' . self::class . '] testHandler executed, result: ' . json_decode($params['result']));
	}

	public function testTaskInit(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$pid = $this->cpaCore->runBgGrpcServer([
			'hostname' => '0.0.0.0',
			'port' => $port,
			'userid' => 'admin',
			'appname' => 'mediadc',
			'handler' => '"\OCA\Cloud_Py_API\Service\ServerService::testHandler"',
			'modname' => 'install',
			'modpath' => '/var/www/html/nextcloud/apps/mediadc/lib/Service/python/install.py',
			'funcname' => 'check',
			'args' => null,
		]);
		if ($pid !== -1) {
			sleep(1);
			$output->writeln('Server started [pid:' . $pid . '] ... Creating client.');
			$client = $this->cpaCore->createClient([
				'hostname' => $hostname,
				'port' => $port,
			]);
			/** @var TaskInitReply $response */
			list($response, $status) = $this->cpaCore->TaskInit($client);
			if (isset($status)) {
				$output->writeln('Response status: ' . json_encode($status));
			}
			if (isset($response)) {
				$output->writeln('Response: ');
				$output->writeln('appname: ' . $response->getAppName());
				$output->writeln('handler: ' . $response->getHandler());
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
				$dbConf = $cfg->getDbConf();
				$output->writeln('dbhost: ' . json_encode($dbConf->getDbHost()));
				$output->writeln('dbtype: ' . json_encode($dbConf->getDbType()));
				$output->writeln('dbname: ' . json_encode($dbConf->getDbName()));
				$output->writeln('dbuser: ' . json_encode($dbConf->getDbUser()));
				$output->writeln('dbpass: ' . json_encode($dbConf->getDbPass()));
				$output->writeln('dbprefix: ' . json_encode($dbConf->getDbPrefix()));
			}
			$output->writeln('Closing server...');
			$this->cpaCore->TaskExit($client, ['result' => json_encode('"TaskInit successfull"')]);
		} else {
			$output->writeln('Server not started...');
		}
	}

	public function testTaskStatus(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$stCode = $input->getArgument('stCode');
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		$this->cpaCore->TaskLog($client, ['stCode' => $stCode]);
	}

	public function testTaskExit(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$result = $input->getArgument('result');
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		$this->cpaCore->TaskExit($client, ['result' => $result]);
		$output->writeln('TaskExit request sent. Server closed.');
	}

	public function testTaskLog(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$logLvl = $input->getArgument('logLvl');
		$module = $input->getArgument('module');
		$content = $input->getArgument('content');
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		$this->cpaCore->TaskLog($client, [
			'logLvl' => $logLvl,
			'module' => $module,
			'content' => json_decode($content)
		]);
	}

	public function testGetFileInfo(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$userid = $input->getArgument('userid');
		$fileid = $input->getArgument('fileid');
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		list($response, $status) = $this->cpaFs->FsInfo($client, [
			'userid' => $userid,
			'fileid' => $fileid,
		]);
		$output->writeln('Response status: ' . json_encode($status));
		if ($response !== null && count($response->getNodes()) > 0) {
			$output->writeln('Response items:');
			/** @var FsNodeInfo $responseNode */
			foreach ($response->getNodes() as $responseNode) {
				$output->writeln($responseNode->getFileId()->getFileId() . '. ' . $responseNode->getName() . ' (' . $responseNode->getSize() .' bytes)');
			}
		}
	}

	public function testFsList(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$userid = $input->getArgument('userid');
		$fileid = $input->getArgument('fileid');
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		list($response, $status) = $this->cpaFs->FsList($client, [
			'userid' => $userid,
			'fileid' => $fileid,
		]);
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
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		/** @var ServerStreamingCall */
		$call = $this->cpaFs->FsRead($client, [
			'userid' => $userid,
			'fileid' => $fileid,
			'offset' => $offset,
			'bytesToRead' => $length
		]);
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
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		/** @var ClientStreamingCall */
		$call = $this->cpaFs->FsWrite($client);
		foreach (str_split($content, Fs::CHUNK_SIZE) as $content) {
			$data = $this->cpaFs->createFsWriteRequest([
				'userid' => $userid,
				'fileid' => $fileid,
				'content' => $content,
			]);
			$output->writeln('Writing: ' . $data->getContent());
			$call->write($data);
		}
		/** @var FsReply */
		list($response, $status) = $call->wait();
		$output->writeln('Status: ' . json_encode($status));
		$output->writeln('Response: ' . json_encode($response->getResCode()));
	}

	public function testFsCreateFile(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$userid = $input->getArgument('userid');
		$parentDirId = $input->getArgument('parentdirid');
		$name = $input->getArgument('name');
		$isFile = $input->getArgument('isfile');
		$content = $input->getArgument('content');
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		/** @var FsCreateReply $response */
		list($response, $status) = $this->cpaFs->FsCreate($client, [
			'userid' => $userid,
			'fileid' => $parentDirId,
			'name' => $name,
			'isFile' => $isFile,
			'content' => $content,
		]);
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
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		/** @var FsReply $response */
		list($response, $status) = $this->cpaFs->FsDelete($client, [
			'userid' => $userid,
			'fileid' => $fileid,
		]);
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
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		/** @var FsReply $response */
		list($response, $status) = $this->cpaFs->FsMOve($client, [
			'userid' => $userid,
			'fileid' => $fileid,
			'targetPath' => $targetPath,
			'copy' => $copy
		]);
		$output->writeln('Response status: ' . json_encode($status));
		$output->writeln('Res code: ' . $response->getResCode());
	}

	public function testDbSelect(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		/** @var DbSelectReply $response */
		list($response, $status) = $this->cpaDb->DbSelect($client, [
			'columns' => [
				['name' => 's.name'],
			],
			'from' => [
				['name' => Application::APP_ID . '_settings', 'alias' => 's'],
			],
			'joins' => null,
			'whereas' => [
				[
					'type' => 'where',
					'expression' => [
						'type' => exprType::EQ,
						'column' => 'name',
						'param' => [
							'name' => 'name',
							'value' => 'appdata_path',
							'value_type' => pValueType::STR,
							'param_type' => pType::NAMED,
							'placeholder' => 'App data param placeholder',
						]
					]
				],
			],
			'groupBy' => null,
			'havings' => null,
			'orderBy' => ['name'],
			'maxResults' => 1, // limit
			'firstResult' => null, // offset
		]);
		$output->writeln('Response status: ' . json_encode($status));
		$output->writeln('Response: ' . json_encode($response));
		if (isset($response)) {
			$output->writeln('Found rows: ' . $response->getRowCount());
			if ($response->getRowCount() > 0 && $response->getHandle() !== '') {
				/** @var DbCursorReply */
				list($cResponse, $cStatus) = $this->cpaDb->DbCursor($client, [
					'cmd' => cCmd::FETCH_ALL,
					'handle' => $response->getHandle(),
				]);
				$output->writeln('Cursor response status: ' . json_encode($cStatus));
				if (isset($cResponse)) {
					$output->writeln('Response: ' . json_encode($cResponse));
					$output->write('Columns: ');
					foreach($cResponse->getColumnsName() as $columName) {
						$output->write(' ' . $columName);
					}
					$output->writeln('');
					$output->write('Columns data: ');
					/** @var columnData $columnData */
					foreach($cResponse->getColumnsData() as $columnData) {
						$output->write(' ' . $columnData->getData());
					}
					$output->writeln('');
					/** @var DbCursorReply */
					list($cCloseResponse, $cCloseStatus) = $this->cpaDb->DbCursor($client, [
						'cmd' => cCmd::CLOSE,
						'handle' => $response->getHandle(),
					]);
					$output->writeln('Close cursor status: ' . json_encode($cCloseStatus));
				}
			}
		}
	}

	public function testDbExec(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		list($response, $status) = $this->cpaDb->DbExec($client, [
			'type' => rType::INSERT,
			'tableName' => Application::APP_ID . '_settings',
			'columns' => [],
			'values' => [],
			'whereas' => [],
		]);
		$output->writeln('Response status: ' . json_encode($status));
	}

	public function testDbCursor(InputInterface $input, OutputInterface $output) {
		$hostname = $input->getArgument('hostname');
		$port = $input->getArgument('port');
		$cmd = $input->getArgument('cmd');
		$handle = $input->getArgument('handle');
		$client = $this->cpaCore->createClient(['hostname' => $hostname, 'port' => $port]);
		list($response, $status) = $this->cpaDb->DbCursor($client, [
			'cmd' => $cmd,
			'handle' => $handle,
		]);
		$output->writeln('Response status: ' . json_encode($status));
		if (isset($response)) {
			$output->writeln('Response: ' . json_encode($response));
			$output->write('Columns: ');
			foreach($response->getColumnsName() as $columName) {
				$output->write(' ' . $columName);
			}
			$output->writeln('');
			$output->write('Columns data: ');
			/** @var columnData $columnData */
			foreach($response->getColumnsData() as $columnData) {
				$output->write(' ' . $columnData->getData());
			}
			$output->writeln('');
		}
	}
}
