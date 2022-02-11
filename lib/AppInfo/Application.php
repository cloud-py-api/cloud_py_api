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

namespace OCA\Cloud_Py_API\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

use OCA\Cloud_Py_API\Event\RegisterAppEvent;
use OCA\Cloud_Py_API\Listener\RegisterAppListener;

require_once __DIR__ . '/../../vendor/autoload.php';

class Application extends App implements IBootstrap {

	public const APP_ID = 'cloud_py_api';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(RegisterAppEvent::class, RegisterAppListener::class);

		require_once __DIR__ . '/../Proto/GPBMetadata/Core.php';
		require_once __DIR__ . '/../Proto/GPBMetadata/Fs.php';
		require_once __DIR__ . '/../Proto/GPBMetadata/Db.php';
		require_once __DIR__ . '/../Proto/GPBMetadata/Service.php';
	}

	public function boot(IBootContext $context): void {
	}

}
