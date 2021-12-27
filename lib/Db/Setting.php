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
 * Class Setting
 *
 * @package OCA\Cloud_Py_API\Db
 *
 * @method string getName()
 * @method string getValue()
 * @method string getDisplayName()
 * @method string getTitle()
 * @method string getDescription()
 * @method string getHelpUrl()
 * @method void setName(string $name)
 * @method void setValue(string $value)
 * @method void setDisplayName(string $displayName)
 * @method void setTitle(string $title)
 * @method void setDescription(string $description)
 * @method void setHelpUrl(string $helpUrl)
 */
class Setting extends Entity implements JsonSerializable {

	protected $name;
	protected $value;
	protected $displayName;
	protected $title;
	protected $description;
	protected $helpUrl;

	/**
	 * @param array $params
	 */
	public function __construct(array $params = []) {
		if (isset($params['id'])) {
			$this->setId($params['id']);
		}
		if (isset($params['name'])) {
			$this->setName($params['name']);
		}
		if (isset($params['value'])) {
			$this->setValue($params['value']);
		}
		if (isset($params['displayName'])) {
			$this->setDisplayName($params['displayName']);
		}
		if (isset($params['title'])) {
			$this->setTitle($params['title']);
		}
		if (isset($params['description'])) {
			$this->setDescription($params['description']);
		}
		if (isset($params['helpUrl'])) {
			$this->setHelpUrl($params['helpUrl']);
		}
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'value' => $this->getValue(),
			'display_name' => $this->getDisplayName(),
			'title' => $this->getTitle(),
			'description' => $this->getDescription(),
			'help_url' => $this->getHelpUrl()
		];
	}
}
