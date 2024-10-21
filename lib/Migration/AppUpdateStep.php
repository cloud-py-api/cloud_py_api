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

namespace OCA\Cloud_Py_API\Migration;

use OCA\Cloud_Py_API\Migration\data\AppInitialData;
use OCA\Cloud_Py_API\Service\UtilsService;

use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;

class AppUpdateStep implements IRepairStep {
	public function __construct(
		private readonly UtilsService $utils,
	) {
	}

	public function getName(): string {
		return 'Updating Cloud_Py_API data';
	}

	public function run(IOutput $output) {
		$output->startProgress(1);
		$app_data = AppInitialData::$INITIAL_DATA;

		$output->advance(1, 'Checking for inital data changes and syncing with database');
		$this->utils->checkForSettingsUpdates($app_data);

		$output->finishProgress();
	}
}
