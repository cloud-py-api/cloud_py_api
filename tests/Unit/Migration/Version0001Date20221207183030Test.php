<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Copyright (c) 2022-2023 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author 2021-2023 Andrey Borysenko <andrey18106x@gmail.com>
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

namespace OCA\Cloud_Py_API\Tests\Unit\Migration;

use OCA\Cloud_Py_API\Migration\Version0001Date20221207183030;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cloud_Py_API\Migration\Version0001Date20221207183030
 */
class Version0001Date20221207183030Test extends TestCase {
	/** @var Version0001Date20221207183030 */
	private $migration;

	public function setUp(): void {
		parent::setUp();

		$this->migration = new Version0001Date20221207183030();
	}

	public function testChangeSchema() {
		/** @var \OCP\Migration\IOutput|MockObject */
		$output = $this->createMock(\OCP\Migration\IOutput::class);
		/** @var \OCP\DB\ISchemaWrapper|MockObject */
		$schema = $this->createMock(\OCP\DB\ISchemaWrapper::class);
		$schema->expects($this->once())
			->method('hasTable')
			->with('cloud_py_api_settings')
			->willReturn(false);

		/** @var \Doctrine\DBAL\Schema\Table|MockObject */
		$table = $this->createMock(\Doctrine\DBAL\Schema\Table::class);
		$table->expects($this->any())
			->method('addColumn')
			->withConsecutive(
				['id', 'integer', [
					'autoincrement' => true,
					'notnull' => true
				]],
				['name', 'string', [
					'notnull' => true,
					'default' => ''
				]],
				['value', 'json', [
					'notnull' => true
				]],
				['display_name', 'string', [
					'notnull' => true,
					'default' => ''
				]],
				['title', 'string', [
					'notnull' => true,
					'default' => ''
				]],
				['description', 'string', [
					'notnull' => true,
					'default' => ''
				]],
				['help_url', 'string', [
					'notnull' => true,
					'default' => ''
				]]
			);
		$table->expects($this->once())
			->method('setPrimaryKey')
			->with(['id']);
		$table->expects($this->once())
			->method('addIndex')
			->with(['name'], 'cpa_setting__index');

		$schema->expects($this->once())
			->method('createTable')
			->with('cloud_py_api_settings')
			->willReturn($table);

		$this->migration->changeSchema($output, function () use ($schema) {
			return $schema;
		}, []);
	}
}
