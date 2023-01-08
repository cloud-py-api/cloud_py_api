/**
 * @copyright Copyright (c) 2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Copyright (c) 2023 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author 2023 Andrey Borysenko <andrey18106x@gmail.com>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

import { createLocalVue, shallowMount } from '@vue/test-utils'
import AdminSettings from '../../../../../src/components/settings/AdminSettings.vue'

const setting1 = {
	"name": "python_command",
	"value": "/usr/bin/python3",
	"displayName": "Full path to python interpreter",
	"description": "Absolute path to the python runnable (e.g. \"/usr/bin/python3\"). Can be obtained by `which python3` command. Used when pre-compiled binaries option is not selected."
}
const setting2 = {
	"name": "use_php_path_from_settings",
	"value": false,
	"displayName": "Use path to PHP interpreter for Python from settings",
	"description": "Determine whether to use path from settings or detect it automatically (may not work with some unusual PHP install locations). Used in Python part."
}
const setting3 = {
	"name": "php_path",
	"value": "/usr/bin/php",
	"displayName": "Full path to PHP interpreter for Python",
	"description": "Absolute path to the PHP executable (e.g. \"/usr/bin/php7.4\"). Can be obtained by `which php` or `which php7.4` command"
}
const setting4 = {
	"name": "remote_filesize_limit",
	"value": 536870912,
	"displayName": "Remote/Encrypted file size limit to process",
	"description": "Maximum file size for requesting from php core. Used when file hosts on remote NC instance or have encrypted flag. Must be less then total available RAM size."
}
const setting5 = {
	"name": "cpa_loglevel",
	"value": "DEBUG",
	"displayName": "Framework loglevel",
	"description": "Used by apps, that using this Framework"
}

const settingsData = [
	setting1,
	setting2,
	setting3,
	setting4,
	setting5,
]

let url = ''
let axiosError = false
const urlsData = {
	get: {
		'/apps/cloud_py_api/api/v1/settings': settingsData,
	}
}
jest.mock('@nextcloud/axios', () => {
	const urlsData = {
		'/apps/cloud_py_api/api/v1/settings': [
			setting1,
			setting2,
			setting3,
			setting4,
			setting5,
		],
	}
	return {
		get: (_url) => {
			return new Promise((resolve) => {
				if (axiosError)
					throw Error()

				if (_url in urlsData)
					resolve({ data: urlsData[_url] })
				else
					resolve({ data: null })
			})
		},
	}
})

jest.mock('@nextcloud/dialogs', () => ({
	showSuccess: (msg) => console.log(`[Success] ${msg}`),
	showWarning: (msg) => console.log(`[Warning] ${msg}`),
	showError: (msg) => console.log(`[Error] ${msg}`),
	showInfo: (msg) => console.log(`[Info] ${msg}`),
}))

jest.mock('@nextcloud/l10n', () => ({
	translate: jest.fn((app, msg) => msg),
	translatePlural: jest.fn((app, msgS, msgN, len) => msgS),
	getLanguage: () => 'en',
	getLocale: () => 'en',
}))

const localVue = createLocalVue()

describe.skip('AdminSettings.vue test', () => {
	let wrapper

	beforeEach(() => {
		wrapper = shallowMount(AdminSettings, {
			localVue,
			mocks: {
				t: (app, msg) => msg,
			},
		})
	})

	it('AdminSettings component is a Vue instance', () => {
		expect(wrapper.isVueInstance).toBeTruthy()
	})
})
