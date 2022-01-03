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

namespace OCA\Cloud_Py_API\Migration;

use OCP\EventDispatcher\IEventDispatcher;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;

use OCA\Cloud_Py_API\Db\App;
use OCA\Cloud_Py_API\Event\SyncAppConfigEvent;
use OCA\Cloud_Py_API\Service\AppsService;


class AppUpdateStep implements IRepairStep {

	/** @var IEventDispatcher */
	private $eventDispatcher;

	/** @var AppsService */
	private $appsService;

	public function __construct(IEventDispatcher $eventDispatcher, AppsService $appsService) {
		$this->eventDispatcher = $eventDispatcher;
		$this->appsService = $appsService;
	}

	public function getName(): string {
		return "Cloud_Py_API apps configs synchronization";
	}

	public function run(IOutput $output) {
		$output->startProgress(1);
		$this->eventDispatcher->dispatchTyped(new SyncAppConfigEvent([
			'apps_id' => array_map(function (App $app) {
				return $app->getAppId();
			}, $this->appsService->getApps())
		]));
		$output->advance(1);
		$output->finishProgress();
	}

}
