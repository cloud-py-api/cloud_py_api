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

namespace OCA\Cloud_Py_API\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;


/**
 * Class Package
 *
 * @package OCA\Cloud_Py_API\Db
 *
 * @method string getAppId()
 * @method string getName()
 * @method string getSource()
 * @method string getLocation()
 * @method string getVersion()
 * @method int getInstalledTime()
 * @method string getStatus()
 * @method void setAppId(string $appId)
 * @method void setName(string $name)
 * @method void setSource(string $source)
 * @method void setLocation(string $location)
 * @method void setVersion(string $version)
 * @method void setInstalledTime(int $installedTime)
 * @method void setStatus(string $status)
 */
class Package extends Entity implements JsonSerializable {

	protected $appId;
	protected $name;
	protected $source;
	protected $location;
	protected $version;
	protected $installedTime;
	protected $status;

	/**
	 * @param array $params
	 */
	public function __construct(array $params = []) {
		if (isset($params['id'])) {
			$this->setId($params['id']);
		}
		if (isset($params['app_id'])) {
			$this->setAppId($params['app_id']);
		}
		if (isset($params['name'])) {
			$this->setName($params['name']);
		}
		if (isset($params['source'])) {
			$this->setSource($params['source']);
		}
		if (isset($params['location'])) {
			$this->setLocation($params['location']);
		}
		if (isset($params['version'])) {
			$this->setVersion($params['version']);
		}
		if (isset($params['installed_time'])) {
			$this->setInstalledTime($params['installed_time']);
		}
		if (isset($params['status'])) {
			$this->setStatus($params['status']);
		}
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'app_id' => $this->getAppId(),
			'name' => $this->getName(),
			'source' => $this->getSource(),
			'location' => $this->getLocation(),
			'version' => $this->getVersion(),
			'installed_time' => $this->getInstalledTime(),
			'status' => $this->getStatus(),
		];
	}
}
