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

namespace OCA\Cloud_Py_API\Service\Process;

class Registry {

	public static $processes = array();

	protected static function cleanup(array $pids_unset = array()): void {
		foreach (self::$processes as $pid => $process) {
			if (($process instanceof Process) === false && !\in_array($pid, $pids_unset, true)) {
				$pids_unset[] = $pid;
			}
		}
		foreach ($pids_unset as $pid) {
			unset(self::$processes[$pid]);
		}
	}

	public static function getPids(): array {
		self::cleanup();

		return array_keys(self::$processes);
	}

	public static function add(Process $process): Process {
		self::$processes[$process->getPid()] = $process;

		return $process;
	}

	public static function registerShutdown(): void {
		register_shutdown_function([__CLASS__, 'shutdownAll']);
	}

	public static function shutdownAll(): bool {
		$pids_unset = array();
		foreach (self::$processes as $pid => $process) {
			if ($process instanceof Process) {
				try {
					if ($process->close(9)) {
						$pids_unset[] = $pid;
					}
				}
				catch (\Exception $e) {
				}
			}
		}
		self::cleanup($pids_unset);

		return (\count(self::$processes) === 0);
	}
}