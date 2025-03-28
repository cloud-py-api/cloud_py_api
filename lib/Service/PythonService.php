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

namespace OCA\Cloud_Py_API\Service;

use OCA\Cloud_Py_API\Db\SettingMapper;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IConfig;
use OCP\ITempManager;

class PythonService {
	private string $pythonCommand;
	private string $ncInstanceId;
	private string $ncDataFolder;
	private bool $isObjectStorage;

	public function __construct(
		private readonly IConfig $config,
		SettingMapper $settingMapper,
		private readonly UtilsService $utils,
		private readonly ITempManager $tempManager,
	) {
		try {
			$pythonCommand = $settingMapper->findByName('python_command');
			$this->pythonCommand = $pythonCommand->getValue();
		} catch (DoesNotExistException $e) {
			$this->pythonCommand = '/usr/bin/python3';
		}
		$this->ncInstanceId = $this->config->getSystemValue('instanceid');
		$this->ncDataFolder = $this->config->getSystemValue('datadirectory');
		$this->isObjectStorage = $this->config->getSystemValue('objectstore', null) !== null;
	}

	/**
	 * Runs Python script with given script relative path, script params and env variables
	 *
	 * @param string $appId target Application::APP_ID
	 * @param string $scriptName relative to cwd path to the Python script or binary
	 * @param array $scriptParams params to script in array (`['-param1' => value1, '--param2' => value2]`)
	 * @param bool $nonBlocking flag that determines how to run Python script
	 * @param array $env environment variables for python script or binary
	 * @param bool $binary flag to determine is python binary given or a python script
	 *
	 * @return array|void output, result_code, errors
	 *
	 * If `$nonBlocking = true` - function will not wait for Python script output, return `void`.
	 * If `$nonBlocking = false` - function will return array with the `result_code`,
	 * `output` and `errors` of the script after Python finish executing.
	 */
	public function run(
		string $appId,
		string $scriptName,
		array $scriptParams = [],
		bool $nonBlocking = false,
		array $env = [],
		bool $binary = false,
	) {
		if ($binary) {
			if ($this->isObjectStorage) {
				$cwd = ''; // scriptName should already include absolute path (/tmp/...)
			} else {
				$cwd = $this->ncDataFolder . '/appdata_' . $this->ncInstanceId . '/' . $appId . '/';
			}
		} else {
			$cwd = $this->utils->getCustomAppsDirectory() . $appId . '/';
		}
		if (count($scriptParams) > 0) {
			$params = array_map(function ($key, $value) {
				return $value !== '' ? "$key $value " : "$key";
			}, array_keys($scriptParams), array_values($scriptParams));
			$cmd = $cwd . $scriptName . ' ' . join(' ', $params);
		} else {
			$cmd = $cwd . $scriptName;
		}
		if (count($env) > 0) {
			$envVariables = join(' ', array_map(function ($key, $value) {
				return "$key=\"$value\" ";
			}, array_keys($env), array_values($env)));
		} else {
			$envVariables = '';
		}
		if (!$binary) {
			$cmd = $this->pythonCommand . ' ' . $cmd;
		}
		if ($nonBlocking) {
			if ($binary) {
				if (!$this->isObjectStorage) {
					$logFile = $cwd . 'logs/' . date('d-m-Y_H-i-s', time()) . '.log';
				} else {
					$tempLogsDir = $this->tempManager->getTempBaseDir() . '/' . $appId . '/logs/';
					if (!file_exists($tempLogsDir)) {
						mkdir($tempLogsDir, 0700, true);
					}
					$logFile = $tempLogsDir . $appId . '_' . date('d-m-Y_H-i-s', time()) . '.log';
				}
			} else {
				$appDataDir = $this->ncDataFolder . '/appdata_' . $this->ncInstanceId . '/' . $appId . '/';
				$pyBitecodeEnvVar = 'PYTHONBYTECODEBASE="' . $appDataDir . '" ';
				$envVariables = $pyBitecodeEnvVar . $envVariables;
				$logFile = $appDataDir . 'logs/' . date('d-m-Y_H-i-s', time()) . '.log';
			}
			$cmd = $envVariables . 'nohup ' . $cmd . ' > ' . $logFile . ' 2>' . $logFile . ' &';
			exec($cmd);
		} else {
			$errors = [];
			exec($envVariables . $cmd, $output, $result_code);
			if ($result_code !== 0) {
				if (count($output) > 0) {
					if (isset(json_decode($output[0], true)['errors'])) {
						$errors = json_decode($output[0], true)['errors'];
					} else {
						exec($envVariables . $cmd . ' 2>&1 1>/dev/null', $o_errors, $result_code);
						$errors = array_merge($output, ['', ''], $o_errors);
					}
				} else {
					exec($envVariables . $cmd . ' 2>&1 1>/dev/null', $o_errors, $result_code);
					$errors = $o_errors;
				}
			}
			return [
				'output' => $output,
				'result_code' => $result_code,
				'errors' => $errors,
			];
		}
	}
}
