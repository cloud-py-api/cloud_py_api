<?php

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

namespace OCA\Cloud_Py_API\Tests\Unit\Service;

use ChristophWurst\Nextcloud\Testing\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

use OCP\IConfig;
use OCP\Files\IAppData;

use OCA\Cloud_Py_API\Db\App;
use OCA\Cloud_Py_API\Db\AppMapper;
use OCA\Cloud_Py_API\Service\AppsService;
use OCA\Cloud_Py_API\Service\UtilsService;
use OCP\AppFramework\Db\DoesNotExistException;


class AppsServiceTest extends TestCase {

	/** @var AppMapper|MockObject */
	private $appMapper;

	/** @var IAppData|MockObject */
	private $appData;

	/** @var IConfig|MockObject */
	private $config;

	/** @var UtilsService|MockObject */
	private $utils;

	/** @var AppsService|MockObject */
	private $appsService;

	protected function setUp(): void {
		parent::setUp();

		$this->appMapper = $this->createMock(AppMapper::class);
		$this->appData = $this->createMock(IAppData::class);
		$this->config = $this->createMock(IConfig::class);

		$this->utils = new UtilsService($this->config);
		$this->appsService = new AppsService(
			$this->appMapper,
			$this->appData,
			$this->config,
			$this->utils
		);
	}

	public function testRegisterApp() {
		$this->appMapper->expects($this->once())
			->method('findByAppId')
			->with('mediadc')
			->will($this->throwException(new DoesNotExistException('mediadc not found')));
		$this->appMapper->expects($this->once())
			->method('insert')
			->with(new App([
				'appId' => 'mediadc',
				'token' => sha1('mediadc')
			]))
			->will($this->returnValue(new App([
				'id' => 1,
				'appId' => 'mediadc',
				'token' => sha1('mediadc')
			])));

		/** @var App|MockObject */
		$actual = $this->appsService->registerApp('mediadc');

		$this->assertEquals('mediadc', $actual->getAppId(), 'Seems like app not registered');
	}

}