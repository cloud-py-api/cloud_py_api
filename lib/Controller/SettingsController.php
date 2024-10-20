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

namespace OCA\Cloud_Py_API\Controller;

use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;

use OCA\Cloud_Py_API\AppInfo\Application;
use OCA\Cloud_Py_API\Service\SettingsService;
use OCA\Cloud_Py_API\Service\UtilsService;

class SettingsController extends Controller {
	public function __construct(
		IRequest $request,
		private readonly SettingsService $service,
		private readonly UtilsService $utils
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[NoAdminRequired]
	public function index(): JSONResponse {
		return new JSONResponse($this->service->getSettings(), Http::STATUS_OK);
	}

	#[PasswordConfirmationRequired]
	public function update(array $settings): JSONResponse {
		return new JSONResponse($this->service->updateSettings($settings), Http::STATUS_OK);
	}

	#[PasswordConfirmationRequired]
	public function updateSetting(array $setting): JSONResponse {
		return new JSONResponse($this->service->updateSetting($setting), Http::STATUS_OK);
	}

	public function getSettingById(int $id): JSONResponse {
		return new JSONResponse($this->service->getSettingById($id), Http::STATUS_OK);
	}

	public function getSettingByName($name): JSONResponse {
		return new JSONResponse($this->service->getSettingByName($name), Http::STATUS_OK);
	}

	public function systemInfo() {
		return new JSONResponse($this->utils->getSystemInfo(), Http::STATUS_OK);
	}
}
