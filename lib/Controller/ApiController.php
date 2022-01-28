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

namespace OCA\Cloud_Py_API\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;

use OCA\Cloud_Py_API\AppInfo\Application;
use OCA\Cloud_Py_API\Service\AppsService;
use OCA\Cloud_Py_API\Service\PackagesService;
use OCA\Cloud_Py_API\Service\UtilsService;


class ApiController extends Controller {

	/** @var AppsService */
	private $appsService;

	/** @var PackagesService */
	private $packagesService;

	/** @var UtilsService */
	private $utils;

	public function __construct(IRequest $request, AppsService $appsService, 
								PackagesService $packagesService, UtilsService $utils) {
		parent::__construct(Application::APP_ID, $request);

		$this->appsService = $appsService;
		$this->packagesService = $packagesService;
		$this->utils = $utils;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * 
	 * @return JSONResponse array of all settings
	 */
	public function apps() {
		return new JSONResponse($this->appsService->getApps(), Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * 
	 * @param int $appId
	 * 
	 * @return JSONResponse array of all settings
	 */
	public function appInfo(int $appId) {
		return new JSONResponse($this->appsService->getApp($appId), Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * 
	 * @return JSONResponse array of all settings
	 */
	public function packages() {
		return new JSONResponse($this->packagesService->getPackages(), Http::STATUS_OK);
	}

	/**
	 * @NoCSRFRequired
	 * 
	 * @return JSONResponse array of system configuration
	 */
	public function systemInfo() {
		return new JSONResponse($this->utils->getSystemInfo(), Http::STATUS_OK);
	}


}
