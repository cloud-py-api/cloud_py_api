<?php
/**
 * @copyright 2021 Andrey Borysenko <andrey18106x@gmail.com>
 * @copyright 2021 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author Andrey Borysenko <andrey18106x@gmail.com>
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
		// PAGES
		['name' => 'page#configuration', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#configuration', 'url' => '/configuration', 'verb' => 'GET', 'postfix' => 'configuration'],

		// SETTINGS API
		['name' => 'settings#index', 'url' => '/api/v1/settings', 'verb' => 'GET'],

		// APPS API
		['name' => 'api#apps', 'url' => '/api/v1/apps', 'verb' => 'GET'],

		// PACKAGES API
		['name' => 'api#packages', 'url' => '/api/v1/packages', 'verb' => 'GET'],
	]
];
