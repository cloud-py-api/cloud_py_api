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
use OC\Archive\TAR;
use OCA\Cloud_Py_API\AppInfo\Application;
use OCA\Cloud_Py_API\Db\Setting;
use OCA\Cloud_Py_API\Db\SettingMapper;
use OCA\ServerInfo\DatabaseStatistics;
use OCP\App\IAppManager;
use OCP\Files\AppData\IAppDataFactory;
use OCP\Files\NotFoundException;

use OCP\Files\NotPermittedException;
use OCP\Files\SimpleFS\ISimpleFile;

use OCP\Files\SimpleFS\ISimpleFolder;

use OCP\IConfig;
use OCP\ITempManager;
use Psr\Log\LoggerInterface;

class UtilsService {
	public function __construct(
		private readonly IConfig $config,
		private readonly SettingMapper $settingMapper,
		private readonly IAppManager $appManager,
		private readonly ?DatabaseStatistics $databaseStatistics,
		private readonly LoggerInterface $logger,
		private readonly IAppDataFactory $appDataFactory,
		private readonly ITempManager $tempManager,
	) {
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

	public function isMuslLinux(): bool {
		exec('ldd --version 2>&1', $output, $result_code);
		if (count($output) > 0 && strpos($output[0], 'musl') !== false) {
			return true;
		}
		return false;
	}

	/**
	 * @throws \OCA\Cloud_Py_API\Exception\UnknownMachineTypeException
	 */
	public function getOsArch(): string {
		$arm64_names = ['aarch64', 'armv8', 'arm64'];
		$machineType = php_uname('m');
		if (strpos($machineType, 'x86_64') !== false) {
			return 'amd64';
		}
		foreach ($arm64_names as $arm64_name) {
			if (strpos($machineType, $arm64_name) !== false) {
				return 'arm64';
			}
		}
		$this->logger->error('[' . self::class . '] Unknown machine type: ' . $machineType);
		throw new \OCA\Cloud_Py_API\Exception\UnknownMachineTypeException();
	}

	public function getCustomAppsDirectory(): string {
		$apps_directory = $this->config->getSystemValue('apps_paths');
		if ($apps_directory !== '' && is_array($apps_directory) && count($apps_directory) > 0) {
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
			'python-interpreter-setting' => json_decode($pythonCommand),
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
		bool $update = false,
	): array {
		if (isset($binariesFolder['success']) && $binariesFolder['success']) {
			$dir = $binariesFolder['path'] . '/';
		} else {
			return $binariesFolder; // Return getAppDataFolder result
		}
		$file_name = $filename . '.gz';
		$save_file_loc = $dir . $file_name;
		$shouldDownloadBinary = $this->compareBinaryHash($url, $dir . $filename);
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
	 * Compare binary hash from release. If hash not exists return `true` (download anyway)
	 *
	 * @param string $url
	 * @param string $binaryPath
	 *
	 * @return bool
	 */
	public function compareBinaryHash(string $url, string $binaryPath) {
		if (file_exists($binaryPath)) {
			$binaryData = file_get_contents($binaryPath);
			$currentBinaryHash = hash('sha256', $binaryData);
			$newBinaryHash = $this->downloadBinaryHash(str_replace('.gz', '.sha256', $url));
			$newHash = substr($newBinaryHash['binaryHash'], 0, 64);
			if ($newBinaryHash['success'] && strlen($newHash) == 64) {
				return $currentBinaryHash != $newHash;
			}
		}
		return true;
	}

	/**
	 * Perform cURL to download python binary (in directory format)
	 *
	 * @param string $url
	 * @param array $binariesFolder appdata binaries folder
	 * @param string $appId target Application::APP_ID
	 * @param string $filename archive and extracted folder name
	 * @param bool $update flag to determine whether to update already downloaded binary or not
	 *
	 * @return array
	 */
	public function downloadPythonBinaryDir(
		string $url,
		array $binariesFolder,
		string $appId,
		string $filename = 'main',
		bool $update = false,
	): array {
		$isObjectStore = $this->config->getSystemValue('objectstore', null) !== null;
		if (isset($binariesFolder['success']) && $binariesFolder['success']) {
			$dir = $binariesFolder['path'] . '/';
		} else {
			if ($isObjectStore) {
				$appDataFolder = $this->appDataFactory->get($appId)->getFolder($binariesFolder['folderName']);
				/** @var ISimpleFolder|ISimpleFile $nodes */
				$nodes = $appDataFolder->getDirectoryListing();
				$binariesTempFolder = $this->tempManager->getTemporaryFolder($appId . $binariesFolder['folderName']);
				$dir = $binariesTempFolder;
				$binariesFolder['path'] = $dir;
				$binaryArchiveName = $appId . '_' . $this->getBinaryName() . '.tar.gz';
				foreach ($nodes as $node) {
					if ($node instanceof ISimpleFile && $node->getName() === $binaryArchiveName) {
						// Copy archive to temp folder
						try {
							$handle = $node->read();
							if ($handle === false) {
								return ['success' => false, 'error' => 'Failed to read python binary file'];
							}

							$binariesArchiveFile = fopen($dir . '/' . $binaryArchiveName, 'wb');
							if ($binariesArchiveFile === false) {
								return ['success' => false, 'error' => 'Failed to write python binary file'];
							}
							while (!feof($handle)) {
								$chunk = fread($handle, 4 * 1024 * 1024);
								if ($chunk === false) {
									return ['success' => false, 'error' => 'Failed to read python binary file'];
								}
								fwrite($binariesArchiveFile, $chunk);
							}
							fclose($handle);
							fclose($binariesArchiveFile);
							$this->unTarGz($binariesFolder, $binaryArchiveName, true);
						} catch (NotPermittedException $e) {
							return ['success' => false, 'error' => $e->getMessage()];
						}
					}
				}
				$shouldDownloadBinary = !file_exists($dir . '/' . $binaryArchiveName);
			} else {
				return $binariesFolder; // Return getAppDataFolder result
			}
		}
		$file_name = $filename . '.tar.gz';
		$save_file_loc = $dir . $file_name;
		if (isset($shouldDownloadBinary) && !$shouldDownloadBinary) {
			$shouldDownloadBinary = $this->compareBinaryDirectoryHashes($url, $binariesFolder, $appId);
		} else {
			$shouldDownloadBinary = true;
		}

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
				$unpacked = $this->unTarGz($binariesFolder, $filename . '.tar.gz', $isObjectStore);
				if ($isObjectStore) {
					// Save binaries archive to AppData (object storage)
					$appDataFolder = $this->appDataFactory->get($appId)->getFolder($binariesFolder['folderName']);
					$handle = fopen($save_file_loc, 'rb');
					if ($handle === false) {
						return ['success' => false, 'error' => 'Failed to read python binary file'];
					}
					try {
						$appDataArchiveFile = $appDataFolder->newFile($binaryArchiveName);
						$appDataArchiveFile->putContent($handle);
					} catch (NotPermittedException|NotFoundException $e) {
						return ['success' => false, 'error' => $e->getMessage()];
					}
				}
				unlink($save_file_loc);
				return [
					'downloaded' => true,
					'unpacked' => $unpacked
				];
			}
		}

		return [
			'downloaded' => true,
			'unpacked' => true,
		];
	}

	public function prefetchAppDataFile(
		string $appId,
		string $folderName,
		string $fileName,
	) {
		$appDataFolder = $this->appDataFactory->get($appId)->getFolder($folderName);
		/** @var ISimpleFolder|ISimpleFile $nodes */
		$nodes = $appDataFolder->getDirectoryListing();
		//		$tempFolder = $this->tempManager->getTemporaryFolder($appId . $folderName);
		$tempFolder = $this->tempManager->getTempBaseDir() . '/' . $appId . '/' . $folderName;
		if (!file_exists($tempFolder)) {
			mkdir($tempFolder, 0700, true);
		}
		if (file_exists($tempFolder . '/' . $fileName)) {
			return [
				'success' => true,
				'path' => $this->tempManager->getTempBaseDir() . '/' . $appId . '/',
			];
		}
		foreach ($nodes as $node) {
			if ($node instanceof ISimpleFile && $node->getName() === $fileName) {
				// Copy archive to temp folder
				try {
					$handle = $node->read();
					if ($handle === false) {
						return ['success' => false, 'error' => 'Failed to read python binary file'];
					}

					$binariesArchiveFile = fopen($tempFolder . '/' . $fileName, 'wb');
					if ($binariesArchiveFile === false) {
						return ['success' => false, 'error' => 'Failed to write python binary file'];
					}
					while (!feof($handle)) {
						$chunk = fread($handle, 4 * 1024 * 1024);
						if ($chunk === false) {
							return ['success' => false, 'error' => 'Failed to read python binary file'];
						}
						fwrite($binariesArchiveFile, $chunk);
					}
					fclose($handle);
					fclose($binariesArchiveFile);
					$this->unTarGz([
						'success' => true,
						'path' => $tempFolder,
					], $fileName, true);
				} catch (NotPermittedException $e) {
					return ['success' => false, 'error' => $e->getMessage()];
				}
			}
		}
		return [
			'success' => true,
			'path' => $tempFolder,
		];
	}

	/**
	 * Extract tar.gz file
	 *
	 * @param array $binariesFolder appdata binaries folder
	 * @param string $src_filename source tar.gz file name
	 *
	 * @return array
	 */
	public function unTarGz(array $binariesFolder, string $src_filename, bool $objectStore = false): array {
		if (isset($binariesFolder['success']) && $binariesFolder['success'] || $objectStore) {
			$dir = $binariesFolder['path'] . '/';
			$src_file = $dir . $src_filename;
			$archive = new TAR($src_file);
			$extracted = $archive->extract($dir);
			return [
				'extracted' => $extracted,
			];
		}
		return [
			'extracted' => false,
		];
	}

	/**
	 * Perform cURL to get binary folder hashes sha256 sum
	 *
	 * @param string $url url to the binary hashsums file
	 *
	 * @return array
	 */
	public function downloadBinaryDirHashes(string $url): array {
		$cURL = curl_init($url);
		curl_setopt_array($cURL, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
		]);
		$binaryHashes = curl_exec($cURL);
		curl_close($cURL);
		return [
			'success' => $binaryHashes != false,
			'binaryHashes' => json_decode($binaryHashes, true),
		];
	}

	/**
	 * Compare binary folder hashes from release.
	 * If hash not exists return `true` (download anyway)
	 *
	 * @param string $url
	 * @param array $binariesFolder
	 *
	 * @return bool
	 */
	public function compareBinaryDirectoryHashes(
		string $url, array $binariesFolder, string $appId,
	): bool {
		$currentBinaryHashes = $this->getCurrentBinaryDirHashes($binariesFolder, $appId);
		$newBinaryHashes = $this->downloadBinaryDirHashes(str_replace('.tar.gz', '.json', $url));
		if ($newBinaryHashes['success'] && $currentBinaryHashes['success']) {
			// Skip hash check of archive file
			$archiveFilename = $appId . '_' . $this->getBinaryName() . '.tar.gz';
			if (isset($newBinaryHashes['binaryHashes'][$archiveFilename])) {
				unset($newBinaryHashes['binaryHashes'][$archiveFilename]);
			}
			foreach ($newBinaryHashes['binaryHashes'] as $filename => $hash) {
				$fileExists = !isset($currentBinaryHashes[$filename]);
				$currentHash = $currentBinaryHashes['binaryHashes'][$filename];
				$hashEqual = $currentHash == $hash;
				if (!$fileExists || !$hashEqual) {
					return true;
				}
			}
			return false;
		}
		return true;
	}

	/**
	 * Get current binary folder files hashes
	 *
	 * @param array $binariesFolder
	 *
	 * @return array
	 */
	public function getCurrentBinaryDirHashes(array $binariesFolder, string $appId): array {
		$currentBinaryHashes = [];
		$archiveFilename = $appId . '_' . $this->getBinaryName() . '.tar.gz';
		if (file_exists($binariesFolder['path'] . '/' . $archiveFilename)) {
			$currentBinaryHashes[$archiveFilename] = hash_file(
				'sha256',
				$binariesFolder['path'] . '/' . $archiveFilename
			);
		}
		$extractedBinaryFolder = $binariesFolder['path'] . '/' . $appId . '_' . $this->getBinaryName();
		$files = scandir($extractedBinaryFolder);
		if ($files !== false) {
			foreach ($files as $file) {
				if ($file != '.' && $file != '..') {
					// Get sha256 hash of each file
					// If file is directory, get sha256 hash of each file in directory
					if (is_dir($extractedBinaryFolder . '/' . $file)) {
						$dirFiles = scandir($extractedBinaryFolder . '/' . $file);
						$currentBinaryHashes = $this->getFolderHashes(
							$dirFiles,
							$file,
							$extractedBinaryFolder . '/' . $file,
							$currentBinaryHashes,
							$appId
						);
					} else {
						$binaryFolderFilePath = $appId . '_' . $this->getBinaryName() . '/' . $file;
						$currentBinaryHashes[$binaryFolderFilePath] = hash_file(
							'sha256',
							$extractedBinaryFolder . '/' . $file
						);
					}
				}
			}
		}
		return [
			'success' => count($currentBinaryHashes) > 0,
			'binaryHashes' => $currentBinaryHashes
		];
	}

	/**
	 * Get sha256 hashes of each file in binary folder
	 * Recursive function call if file is directory
	 *
	 * @param array $files
	 * @param string $folder
	 * @param string $extractedBinaryFolder
	 * @param array $currentBinaryHashes
	 * @param string $appId
	 *
	 * @return array
	 */
	private function getFolderHashes(
		array $files,
		string $folder,
		string $extractedBinaryFolder,
		array $currentBinaryHashes,
		string $appId,
	): array {
		foreach ($files as $file) {
			if ($file != '.' && $file != '..') {
				// Get sha256 hash of each file
				// If file is directory, get sha256 hash of each file in directory
				if (is_dir($extractedBinaryFolder . '/' . $file)) {
					$dirFiles = scandir($extractedBinaryFolder . '/' . $file);
					$currentBinaryHashes = $this->getFolderHashes(
						$dirFiles,
						$folder . '/' . $file, $extractedBinaryFolder . '/' . $file,
						$currentBinaryHashes, $appId
					);
				} else {
					$binaryFolderFilePath = $appId . '_'
						. $this->getBinaryName() . '/' . $folder . '/' . $file;
					$currentBinaryHashes[$binaryFolderFilePath] = hash_file(
						'sha256', $extractedBinaryFolder . '/' . $file
					);
				}
			}
		}
		return $currentBinaryHashes;
	}

	/**
	 * Perform cURL to get binary's sha256 sum
	 *
	 * @param string $url url to the binary hashsum file
	 *
	 * @return array
	 */
	public function downloadBinaryHash(string $url): array {
		$cURL = curl_init($url);
		curl_setopt_array($cURL, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RANGE => '0-64',
		]);
		$binaryHash = curl_exec($cURL);
		curl_close($cURL);
		return [
			'success' => $binaryHash != false,
			'binaryHash' => $binaryHash,
		];
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
		if (!$this->isMuslLinux()) {
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
