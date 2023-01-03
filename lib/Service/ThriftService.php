<?php

declare(strict_types=1);

/**
 * @copyright Сopyright (c) 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Сopyright (c) 2022-2023 Alexander Piskun <bigcat88@icloud.com>
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

namespace OCA\Cloud_Py_API\Service;

use OCA\Cloud_Py_API\THandler\TestServiceHandler;
use OCA\Cloud_Py_API\TProto\TestServiceProcessor;
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Exception\TException;
use Thrift\Factory\TBinaryProtocolFactory;
use Thrift\Factory\TTransportFactory;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Server\TServerSocket;
use Thrift\Server\TSimpleServer;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TSocket;

/**
 * @codeCoverageIgnore
 */
class ThriftService {
	public function __construct() {
		$loader = new ThriftClassLoader();
		$loader->registerNamespace('test', '../TProto');
		$loader->register();
	}

	public function runThriftServer(array $params = []): array {
		try {
			$handler = new TestServiceHandler();
			$processor = new TestServiceProcessor($handler);
			$transport = new TServerSocket('0.0.0.0', 7080);
			$tfactory = new TTransportFactory($transport);
			$pfactory = new TBinaryProtocolFactory(true, true);
			$server = new TSimpleServer($processor, $transport, $tfactory, $tfactory, $pfactory, $pfactory);
			print('Running Thrift server...' . PHP_EOL);
			$server->serve();
		} catch (TException $e) {
			return [
				'success' => false,
				'error_message' => $e->getMessage(),
			];
		}
	}

	public function runThriftClient(array $params = []): array {
		try {
			$socket = new TSocket('0.0.0.0', 7080);
			$transport = new TBufferedTransport($socket, 1024, 1024);
			$protocol = new TBinaryProtocol($transport);
			$client = new \OCA\Cloud_Py_API\TProto\TestServiceClient($protocol);

			$transport->open();
			$result = $client->ping(\OCA\Cloud_Py_API\TProto\logLvl::DEBUG);
			$client->exit(0);
			$transport->close();

			return [
				'success' => $result === 0,
				'recieved_ping_response' => $result
			];
		} catch (TException $e) {
			return [
				'success' => false,
				'error_message' => $e->getMessage(),
			];
		}
	}
}
