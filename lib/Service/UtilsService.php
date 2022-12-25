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

use bantu\IniGetWrapper\IniGetWrapper;
use OCP\IConfig;
use OCP\App\IAppManager;

use OCA\ServerInfo\DatabaseStatistics;

use OCA\Cloud_Py_API\AppInfo\Application;
use OCA\Cloud_Py_API\Db\Setting;
use OCA\Cloud_Py_API\Db\SettingMapper;

class UtilsService {
	/** @var IConfig */
	private $config;

	/** @var SettingMapper */
	private $settingMapper;

	/** @var IAppManager */
	private $appManager;

	/** @var DatabaseStatistics */
	private $databaseStatistics;

	public function __construct(
		IConfig $config,
		SettingMapper $settingMapper,
		IAppManager $appManager,
		?DatabaseStatistics $databaseStatistics
	) {
		$this->config = $config;
		$this->settingMapper = $settingMapper;
		$this->appManager = $appManager;
		$this->databaseStatistics = $databaseStatistics;
	}

	public function getNCLogLevel(): string {
		$loglevel = $this->config->getSystemValue('loglevel', 2);
		$loglevels = [
			0 => 'DEBUG',
			1 => 'INFO',
			2 => 'WARNING',
			3 => 'ERROR',
			4 => 'FATAL',
		];
		return $loglevels[$loglevel];
	}

	public function getCpaLogLevel(): string {
		$cpaLobLevelSetting = $this->settingMapper->findByName('cpa_loglevel');
		return json_decode($cpaLobLevelSetting->getValue());
	}

	/**
	 * Return a suitable PHP interpreter that is likely to be the same version as the
	 * currently running interpreter.  This is similar to using the PHP_BINARY constant, but
	 * it will also work from within mod_php or PHP-FPM, in which case PHP_BINARY will return
	 * unusable interpreters.
	 *
	 * @return string
	 */
	public function getPhpInterpreter() {
		$usePhpPathFromSettings = json_decode($this->settingMapper->findByName('use_php_path_from_settings')->getValue());

		if (isset($usePhpPathFromSettings) && $usePhpPathFromSettings) {
			$phpPath = json_decode($this->settingMapper->findByName('php_path')->getValue());
			return $phpPath;
		}

		static $cachedExecutable = null;

		if ($cachedExecutable !== null) {
			return $cachedExecutable;
		}

		$basename = basename(PHP_BINARY);

		// If the binary is 'php', 'php7', 'php7.3' etc, then assume it's a usable interpreter
		if ($basename === 'php' || preg_match('/^php\d+(?:\.\d+)*$/', $basename)) {
			return PHP_BINARY;
		}

		// Otherwise, we might be running as mod_php, php-fpm, etc, where PHP_BINARY is not a
		// usable PHP interpreter.  Try to find one with the same version as the current one.

		$candidates = [
			'php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION,
			'php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
			'php' . PHP_MAJOR_VERSION,
		];

		$envPath = $_SERVER['PATH'] ?? '';
		$paths = $envPath !== '' ? explode(':', $envPath) : [];

		if (!in_array(PHP_BINDIR, $paths, true)) {
			$paths[] = PHP_BINDIR;
		}

		foreach ($candidates as $candidate) {
			foreach ($paths as $path) {
				$executable = $path . DIRECTORY_SEPARATOR . $candidate;
				if (is_executable($executable)) {
					$cachedExecutable = $executable;
					return $executable;
				}
			}
		}

		// Fallback, if nothing else can be found
		$cachedExecutable = 'php';
		return $cachedExecutable;
	}

	/**
	 * Check if a php function available
	 *
	 * @param string $function_name
	 *
	 * @return bool
	 */
	public function isFunctionEnabled($function_name) {
		if (!function_exists($function_name)) {
			return false;
		}
		/**
		 * @psalm-suppress UndefinedClass
		 * @psalm-suppress UndefinedDocblockClass
		 * @var IniGetWrapper $ini
		 */
		$ini = \OC::$server->get(IniGetWrapper::class);
		$disabled = explode(',', $ini->get('disable_functions') ?: '');
		$disabled = array_map('trim', $disabled);
		if (in_array($function_name, $disabled)) {
			return false;
		}
		/** @psalm-suppress UndefinedDocblockClass */
		$disabled = explode(',', $ini->get('suhosin.executor.func.blacklist') ?: '');
		$disabled = array_map('trim', $disabled);
		if (in_array($function_name, $disabled)) {
			return false;
		}
		return true;
	}

	public function isSnapEnv(): bool {
		return getenv('SNAP') !== false;
	}

	public function isVideosSupported(): bool {
		$result = false;
		exec('ffmpeg -version', $output, $result_code);
		if ($result_code === 0 && count($output) > 0 && preg_match('/version/s', $output[0], $matches)) {
			$result = count($matches) > 0 && $matches[0] === 'version';
		}
		exec('ffprobe -version', $output, $result_code);
		if ($result_code === 0 && count($output) > 0 && preg_match('/version/s', $output[0], $matches)) {
			$result = $result && count($matches) > 0 && $matches[0] === 'version';
		}
		return $result;
	}

	public function isMusliLinux(): bool {
		exec('ldd --version', $output, $result_code);
		if ($result_code == 0 && count($output) > 0 && str_contains($output[0], 'musl')) {
			return true;
		}
		return false;
	}

	public function getOsArch(): string {
		$machineType = php_uname('m');
		if (str_contains($machineType, 'x86_64')) {
			return 'amd64';
		} elseif (str_contains($machineType, 'arm64')) {
			return 'arm64';
		}
		return $machineType;
	}

	public function getCustomAppsDirectory() {
		$apps_directory = $this->config->getSystemValue('apps_paths');
		if ($apps_directory !== "" && is_array($apps_directory) && count($apps_directory) > 0) {
			foreach ($apps_directory as $custom_apps_dir) {
				$appDir = $custom_apps_dir['path'] . '/' . Application::APP_ID;
				if (
					file_exists($custom_apps_dir['path']) && is_dir($custom_apps_dir['path'])
					&& $custom_apps_dir['writable'] && file_exists($appDir) && is_dir($appDir)
				) {
					return $custom_apps_dir['path'] . '/';
				}
			}
		}
		return getcwd() . '/apps/';
	}

	public function getSystemInfo($appId = null): array {
		$pythonCommand = $this->settingMapper->findByName('python_command')->getValue();
		$appVersions = [
			Application::APP_ID . '-version' => $this->appManager->getAppVersion(Application::APP_ID),
		];
		if (isset($appId)) {
			$appVersions[$appId . '-version'] = $this->appManager->getAppVersion($appId);
		}
		$result = [
			'nextcloud-version' => $this->config->getSystemValue('version'),
			'app-versions' => $appVersions,
			'is-videos-supported' => $this->isVideosSupported(),
			'is-snap' => $this->isSnapEnv(),
			'arch' => $this->getOsArch(),
			'webserver' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : null,
			'database' => $this->databaseStatistics !== null ? $this->databaseStatistics->getDatabaseStatistics() : null,
			'php-version' => phpversion(),
			'php-interpreter' => $this->getPhpInterpreter(),
			'python-interpretter-setting' => json_decode($pythonCommand),
			'os' => php_uname('s'),
			'os-release' => php_uname('r'),
			'machine-type' => php_uname('m'),
		];
		return $result;
	}

	/**
	 * Perform cURL download binary file request
	 *
	 * @param string $url download url
	 * @param array $binariesFolder appdata binaries folder
	 * @param string $filename result binary name
	 * @param bool $update flag to determine whether to update already downloaded binary or not
	 *
	 * @return array
	 */
	public function downloadPythonBinary(
		string $url,
		array $binariesFolder,
		string $filename = 'main',
		bool $update = false
	): array {
		if (isset($binariesFolder['success']) && $binariesFolder['success']) {
			$dir = $binariesFolder['path'] . '/';
		} else {
			return $binariesFolder; // Return getAppDataFolder result
		}
		$file_name = $filename . '.gz';
		$save_file_loc = $dir . $file_name;
		$shouldDownloadBinary = $this->compareBinaryHash(
			$url, $dir . $filename, $binariesFolder, $filename
		);
		if (!file_exists($dir . $filename) || ($update && $shouldDownloadBinary)) {
			$cURL = curl_init($url);
			$fp = fopen($save_file_loc, 'wb');
			if ($fp) {
				curl_setopt_array($cURL, [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_FILE => $fp,
					CURLOPT_FOLLOWLOCATION => true,
				]);
				curl_exec($cURL);
				curl_close($cURL);
				fclose($fp);
				$ungzipped = $this->unGz($binariesFolder, $file_name);
				$chmodx = $this->addChmodX($binariesFolder, $file_name);
				unlink($save_file_loc);
				return [
					'downloaded' => file_exists($save_file_loc),
					'ungzipped' => $ungzipped,
					'chmodx' => $chmodx
				];
			}
		}
		if (!file_exists($dir . $filename)) {
			return ['success' => false, 'file' => $save_file_loc];
		} else {
			return [
				'success' => true,
				'downloaded' => true,
				'ungzipped' => true,
				'chmodx' => true,
			];
		}
	}

	/**
	 * @param string $binaryPath
	 * @param array $binariesFolder,
	 * @param string $filanem
	 *
	 * @return bool
	 */
	public function compareBinaryHash(
		string $url,
		string $binaryPath,
		array $binariesFolder,
		string $filename
	) {
		if (file_exists($binaryPath)) {
			// get current binary hash (from .sha256 file or directly from existing binary)
			if (file_exists($binaryPath . '.sha256')) {
				$currentBinaryHash = file_get_contents(
					$binaryPath . '.sha256', false, null, 0, 64
				);
			} else {
				$binaryData = file_get_contents($binaryPath);
				$currentBinaryHash = hash('sha256', $binaryData);
			}
			// download new binary sha256 hash from attached file to release
			copy($binaryPath . '.sha256', $binaryPath . '.sha256.old');
			$newBinaryHash = $this->downloadBinaryHash(
				str_replace('.gz', '.sha256', $url), $binariesFolder, $filename
			);
			// should update binary if hashes not equial
			if ($newBinaryHash['success']) {
				return $currentBinaryHash != $newBinaryHash['binaryHash'];
			} else {
				// revert back old hash file
				copy($binaryPath . '.sha256.old', $binaryPath . '.sha256');
				unlink($binaryPath . '.sha256.old');
			}
		}
		return false;
	}

	/**
	 * Perform cURL download binary's sha256 sum file
	 *
	 * @param string $url url to the binary hashsum file
	 * @param array $binariesFolder appdata binaries folder
	 * @param string $filename downloaded checksum filename
	 *
	 * @return array
	 */
	public function downloadBinaryHash(
		string $url,
		array $binariesFolder,
		string $filename
	): array {
		if (isset($binariesFolder['success']) && $binariesFolder['success']) {
			$dir = $binariesFolder['path'] . '/';
		} else {
			return $binariesFolder; // Return getAppDataFolder result
		}
		$file_name = $filename . '.sha256';
		$save_file_loc = $dir . $file_name;
		$cURL = curl_init($url);
		$fp = fopen($save_file_loc, 'w');
		if ($fp) {
			curl_setopt_array($cURL, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FILE => $fp,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RANGE => 64,
			]);
			$binaryHash = curl_exec($cURL);
			curl_close($cURL);
			fclose($fp);
			return [
				'success' => true,
				'binaryHash' => $binaryHash,
				'binaryHashFilePath' => $save_file_loc,
			];
		}
		return ['success' => false];
	}

	/**
	 * Ungzip target file
	 *
	 * @param array $binariesFolder binaries folder
	 * @param string $file_name target `.gz` file
	 *
	 * @return bool
	 */
	public function unGz(array $binariesFolder, string $file_name): bool {
		$out_file_name = $binariesFolder['path'] . '/main';
		$buffer_size = 4096;
		$file_name = $binariesFolder['path'] . '/' . $file_name;
		$gz_file = gzopen($file_name, 'rb');
		$out_file = fopen($out_file_name, 'wb');
		while (!gzeof($gz_file)) {
			fwrite($out_file, gzread($gz_file, $buffer_size));
		}
		fclose($out_file);
		gzclose($gz_file);
		return file_exists($out_file_name);
	}

	/**
	 * Add executable flag to the binary
	 *
	 * @param array $binariesFolder binaries folder
	 * @param string $file_name target binary filename
	 *
	 * @return bool
	 */
	public function addChmodX(array $binariesFolder, string $file_name): bool {
		$file_name = $binariesFolder['path'] . '/' .
			str_replace('.gz', '', $file_name);
		if (file_exists($file_name)) {
			exec('chmod +x ' . $file_name, $output, $result_code);
			return $result_code === 0;
		}
		return false;
	}

	/**
	 * Get correct binary name part
	 *
	 * @return string part of binary name
	 */
	public function getBinaryName(): string {
		if (!$this->isMusliLinux()) {
			$binaryName = 'manylinux_' . $this->getOsArch();
		} else {
			$binaryName = 'musllinux_' . $this->getOsArch();
		}
		return $binaryName;
	}

	public function checkForSettingsUpdates($app_data) {
		$settings = $this->settingMapper->findAll();
		if (count($settings) > 0) {
			$this->updateSettingsTexts($app_data, $settings);
			$this->checkForNewSettings($app_data, $settings);
			$this->checkForDeletedSettings($app_data, $settings);
		}
	}

	private function checkForNewSettings(array $app_data, array $settings): void {
		$currentSettingsKeys = array_map(function ($setting) {
			return $setting->getName();
		}, $settings);
		$newSettingsKeys = array_map(function ($setting) {
			return $setting['name'];
		}, $app_data['settings']);
		$newSettings = [];
		foreach ($newSettingsKeys as $setting) {
			if (!in_array($setting, $currentSettingsKeys)) {
				array_push($newSettings, $setting);
			}
		}
		foreach ($app_data['settings'] as $setting) {
			if (in_array($setting['name'], $newSettings)) {
				$this->settingMapper->insert(new Setting([
					'name' => $setting['name'],
					'value' => is_array($setting['value']) ?
						json_encode($setting['value'])
						: str_replace('\\', '', json_encode($setting['value'])),
					'displayName' => $setting['displayName'],
					'description' => $setting['description'],
					'helpUrl' => $setting['helpUrl']
				]));
			}
		}
	}

	private function checkForDeletedSettings(array $app_data, array $settings): void {
		$currentSettingsKeys = array_map(function ($setting) {
			return $setting->getName();
		}, $settings);
		$newSettingsKeys = array_map(function ($setting) {
			return $setting['name'];
		}, $app_data['settings']);
		$settingsToRemove = [];
		foreach ($currentSettingsKeys as $setting) {
			if (!in_array($setting, $newSettingsKeys)) {
				array_push($settingsToRemove, $setting);
			}
		}
		foreach ($settingsToRemove as $settingName) {
			$setting = $this->settingMapper->findByName($settingName);
			if (isset($setting)) {
				$this->settingMapper->delete($setting);
			}
		}
	}

	private function updateSettingsTexts(array $app_data, array $settings) {
		$newSettingsKeys = array_map(function ($setting) {
			return $setting['name'];
		}, $app_data['settings']);
		foreach ($settings as $setting) {
			if (in_array($setting->getName(), $newSettingsKeys)) {
				$newSetting = null;
				foreach ($app_data['settings'] as $s) {
					if ($s['name'] == $setting->getName()) {
						$newSetting = $s;
					}
				}
				if (isset($newSetting)) {
					if ($setting->getDescription() !== $newSetting['description']) {
						$setting->setDescription($newSetting['description']);
					}
					if ($setting->getDisplayName() !== $newSetting['displayName']) {
						$setting->setDisplayName($newSetting['displayName']);
					}
					if ($setting->getHelpUrl() !== $newSetting['helpUrl']) {
						$setting->setHelpUrl($newSetting['helpUrl']);
					}
					$this->settingMapper->update($setting);
				}
			}
		}
	}
}
