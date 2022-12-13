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

namespace OCA\Cloud_Py_API\Migration\data;

class AppInitialData {
	/** @var \OCP\IL10N */
	private $l10n;

	public static $INITIAL_DATA = [
		'settings' => [
			[
				"name" => "python_command",
				"value" => "/usr/bin/python3",
				"displayName" => "Full path to python interpreter",
				"description" => "Absolute path to the python runnable (e.g. \"/usr/bin/python3\"). Can be obtained by `which python3` command.",
				"helpUrl" => "https://cloud-py-api.readthedocs.io/{todo-url:/settings/setting_name}"
			],
			[
				"name" => "remote_filesize_limit",
				"value" => 536870912,
				"displayName" => "Remote/Encrypted file size limit to process",
				"description" => "Maximum file size for requesting from php core. Used when file hosts on remote NC instance or have encrypted flag. Must be less then total available RAM size.",
				"helpUrl" => "https://cloud-py-api.readthedocs.io/{todo-url:/settings/setting_name}"
			],
			[
				"name" => "use_php_path_from_settings",
				"value" => false,
				"displayName" => "Use path to PHP interpreter for Python from settings",
				"description" => "Determine whether to use path from settings or detect it automatically (may not work with some unusual PHP install locations). Used in Python part.",
				"helpUrl" => "https://cloud-py-api.readthedocs.io/{todo-url:/settings/setting_name}"
			],
			[
				"name" => "php_path",
				"value" => "/usr/bin/php",
				"displayName" => "Full path to PHP interpreter for Python",
				"description" => "Absolute path to the PHP executable (e.g. \"/usr/bin/php7.4\"). Can be obtained by `which php` or `which php7.4` command",
				"helpUrl" => "https://cloud-py-api.readthedocs.io/{todo-url:/settings/setting_name}"
			],
			[
				"name" => "python_binary",
				"value" => true,
				"displayName" => "Use pre-compiled Python binaries",
				"description" => "Use Python part in binary format (located in the appdata folder)",
				"helpUrl" => "https://cloud-py-api.readthedocs.io/{todo-url:/settings/setting_name}"
			],
			[
				"name" => "cpa_loglevel",
				"value" => "WARNING",
				"displayName" => "Framework loglevel",
				"description" => "Used by apps, that using this Framework",
				"helpUrl" => "https://cloud-py-api.readthedocs.io/{todo-url:/settings/setting_name}"
			],
		]
	];

	public function __construct(\OCP\IL10N $l10n) {
		$this->l10n = $l10n;
	}

	private function _stringsForL10N(): void {
		// TRANSLATORS Admin settings strings for localization (Eng versions stored in the database and translated on front-end)
		$this->l10n->t("Full path to python interpreter");
		$this->l10n->t("Absolute path to the python runnable (e.g. \"/usr/bin/python3\"). Can be obtained by `which python3` command.");
		$this->l10n->t("Remote/Encrypted file size limit to process");
		$this->l10n->t("Maximum file size for requesting from php core. Used when file hosts on remote NC instance or have encrypted flag. Must be less then total available RAM size.");
		$this->l10n->t("Use path to PHP interpreter for Python from settings");
		$this->l10n->t("Determine whether to use path from settings or detect it automatically (may not work with some unusual PHP install locations). Used in Python part.");
		$this->l10n->t("Full path to PHP interpreter for Python");
		$this->l10n->t("Absolute path to the PHP executable (e.g. \"/usr/bin/php7.4\"). Can be obtained by `which php` or `which php7.4` command");
		$this->l10n->t("Use pre-compiled Python binaries");
		$this->l10n->t("Use Python part in binary format (located in the appdata folder)");
		$this->l10n->t("Framework loglevel");
		$this->l10n->t("Used by apps, that using this Framework");
	}
}
