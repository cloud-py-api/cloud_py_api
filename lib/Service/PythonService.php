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

use OCP\IConfig;
use OCP\Files\IAppData;
use Psr\Log\LoggerInterface;

use OCA\Cloud_Py_API\AppInfo\Application;
use OCA\Cloud_Py_API\Db\SettingMapper;


class PythonService {

	/** @var IAppData */
	private $appData;

	/** @var string */
	private $cwd;

	/** @var string */
	private $pythonCommand;

	/** @var IConfig */
	private $config;

	/** @var LoggerInterface */
	private $logger;

	/** @var UtilsService */
	private $utils;

	public function __construct(SettingMapper $settingMapper, IConfig $config, 
								IAppData $appData, LoggerInterface $logger,
								UtilsService $utils)
	{
		$this->appData = $appData;
		$this->config = $config;
		$this->logger = $logger;
		/** @var Setting */
		$pythonCommand = $settingMapper->findByName('python_command');
		$this->pythonCommand = $pythonCommand->getValue();
		$this->utils = $utils;
		$this->cwd = $this->utils->getCustomAppsDirectory() . Application::APP_ID . '/lib/Service/python';
	}

	/**
	 * Runs Python script with given script relative path and script params
	 * 
	 * @param string $scriptName relative path to the Python script
	 * @param array $scriptParams params to script in array (`['-param1' => value1, '--param2' => value2]`)
	 * @param boolean $nonBlocking flag that determines how to run Python script.
	 * @param array $env env variables for python script
	 * 
	 * @return array|void
	 * 
	 * If `$nonBlocking = true` - function will not waiting for Python script output and return `void`.
	 * If `$nonBlocking = false` - function will return array with the `result_code` 
	 * and `output` of the script after Python script finish executing.
	 */
	public function run($scriptName, $scriptParams, $nonBlocking = false, $env = []) {
		if (count($scriptParams) > 0) {
			$params = array_map('\OCA\Cloud_Py_API\Service\PythonService::scriptParamsCallback', array_keys($scriptParams), array_values($scriptParams));
			$cmd = $this->pythonCommand . ' ' . $this->cwd . $scriptName . ' ' . join(' ', $params);
		} else {
			$cmd = $this->pythonCommand . ' ' .  $this->cwd . $scriptName;
		}
		if (count($env) > 0) {
			$envVariables = join(' ', array_map('\OCA\Cloud_Py_API\Service\PythonService::scriptEnvsCallback', array_keys($env), array_values($env)));
		} else {
			$envVariables = '';
		}
		if ($nonBlocking) {
			exec($envVariables . 'nohup ' . $cmd . ' > /dev/null 2>&1 &');
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

	/**
	 * Callback for concatinating Python script params
	 * 
	 * @param string $key
	 * @param string $value
	 * 
	 * @return string
	 */
	static function scriptParamsCallback($key, $value) {
		return $value !== '' ? "$key $value " : "$key";
	}

	/**
	 * Callback for concatinating Python environment variables
	 * 
	 * @param string $key
	 * @param string $value
	 * 
	 * @return string
	 */
	static function scriptEnvsCallback($key, $value) {
		return "$key=\"$value\" ";
	}

	/**
	 * @param array $pythonResult
	 *
	 * @return array
	 */
	private function parsePythonOutput($pythonResult) {
		// TODO
	}

}