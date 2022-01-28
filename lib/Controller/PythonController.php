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
use OCA\Cloud_Py_API\Service\PythonService;


class PythonController extends Controller {

	/** @var PythonService */
	private $service;

	public function __construct(IRequest $request, PythonService $service) {
		parent::__construct(Application::APP_ID, $request);

		$this->service = $service;
	}

	/**
	 * @NoCSRFRequired
	 * 
	 * @return JSONResponse array of python output
	 */
	public function checkPyFrmInit() {
		return new JSONResponse($this->service->checkPyFrmInit(), Http::STATUS_OK);
	}

	/**
	 * @NoCSRFRequired
	 * 
	 * @param string $type basic or with extra packages
	 * 
	 * @return JSONResponse array of python output
	 */
	public function pyFrmInstall(string $type) {
		return new JSONResponse($this->service->pyFrmInstall($type), Http::STATUS_OK);
	}

}
