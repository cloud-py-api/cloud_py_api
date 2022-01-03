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

namespace OCA\Cloud_Py_API\Service;

use OCP\Files\IAppData;

use OCA\Cloud_Py_API\Db\PackageMapper;
use OCA\Cloud_Py_API\Db\Package;


class PackagesService {

	/** @var PackageMapper */
	private $mapper;

	/** @var IAppData */
	private $appData;

	public function __construct(PackageMapper $packageMapper, IAppData $appData)
	{
		$this->mapper = $packageMapper;
		$this->appData = $appData;
	}

	public function getPackages() {
		return $this->mapper->findAll();
	}

	public function registerPackage(string $appId, array $packageData) {
		// TODO
	}

	public function getPackage(string $appId, string $packageName) {
		return $this->mapper->findAppPackageByName($appId, $packageName);
	}

	public function removePackage(string $appId, string $packageName) {
		/** @var Package */
		$package = $this->mapper->findAppPackageByName($appId, $packageName);
		return $this->mapper->delete($package);
	}

}