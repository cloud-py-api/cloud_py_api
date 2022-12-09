<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Copyright (c) 2022-2023 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @license GNU AGPL version 3 or any later version
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

return [
	'routes' => [
		// SETTINGS API
		['name' => 'settings#index', 'url' => '/api/v1/settings', 'verb' => 'GET'],
		['name' => 'settings#update', 'url' => '/api/v1/settings', 'verb' => 'PUT'],
		['name' => 'settings#getSettingById', 'url' => '/api/v1/settings/{id}', 'verb' => 'GET'],
		['name' => 'settings#getSettingByName', 'url' => '/api/v1/settings/name/{name}', 'verb' => 'GET'],
		['name' => 'settings#updateSetting', 'url' => '/api/v1/settings/name/{name}', 'verb' => 'PUT'],
		['name' => 'settings#systemInfo', 'url' => '/api/v1/system-info', 'verb' => 'GET']
	]
];
