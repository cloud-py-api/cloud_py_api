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
import BugReport from '../../../../../src/components/settings/BugReport.vue'

jest.mock('@nextcloud/l10n', () => ({
	translate: jest.fn((app, msg) => msg),
	translatePlural: jest.fn((app, msgS, msgN, len) => msgS),
	getLanguage: () => 'en',
	getLocale: () => 'en',
}))

const localVue = createLocalVue()

describe('BugReport.vue test', () => {
	let wrapper

	beforeEach(() => {
		wrapper = shallowMount(BugReport, {
			localVue,
			mocks: {
				t: (app, msg) => msg,
				OC: jest.fn()
			},
		})
	})

	it('BugReport component is a Vue instance', () => {
		expect(wrapper.isVueInstance).toBeTruthy()
	})
})
