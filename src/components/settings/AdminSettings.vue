<!--
 - @copyright Copyright (c) 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 -
 - @copyright Copyright (c) 2022-2023 Alexander Piskun <bigcat88@icloud.com>
 -
 - @author 2022-2023 Andrey Borysenko <andrey18106x@gmail.com>
 -
 - @license AGPL-3.0-or-later
 -
 - This program is free software: you can redistribute it and/or modify
 - it under the terms of the GNU Affero General Public License as
 - published by the Free Software Foundation, either version 3 of the
 - License, or (at your option) any later version.
 -
 - This program is distributed in the hope that it will be useful,
 - but WITHOUT ANY WARRANTY; without even the implied warranty of
 - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 - GNU Affero General Public License for more details.
 -
 - You should have received a copy of the GNU Affero General Public License
 - along with this program. If not, see <http://www.gnu.org/licenses/>.
 -
 -->

<template>
	<div class="admin-settings">
		<div class="settings-heading">
			<h2 style="padding: 30px 30px 0 30px; font-size: 24px;">
				{{ t('cloud_py_api', 'Cloud Python API (Framework)') }}
			</h2>
		</div>
		<div v-if="settings.length > 0">
			<NcSettingsSection v-for="setting of settings"
				:key="setting.id"
				:title="t('cloud_py_api', setting.name)"
				:description="t('cloud_py_api', setting.description)">
				<p>
					{{ t('cloud_py_api', 'Display name: ') }}
					{{ t('cloud_py_api', setting.display_name) }}
				</p>
				<p>
					{{ t('cloud_py_api', 'Value: ') }}
					{{ t('cloud_py_api', setting.value) }}
				</p>
				<p>
					{{ t('cloud_py_api', 'Help url: ') }}
					{{ setting.help_url }}
				</p>
			</NcSettingsSection>
		</div>
		<NcSettingsSection :title="t('cloud_py_api', 'Bug report')">
			<BugReport />
		</NcSettingsSection>
	</div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'

import BugReport from './BugReport.vue'

export default {
	name: 'AdminSettings',
	components: {
		NcSettingsSection,
		BugReport,
	},
	computed: {
		...mapGetters([
			'settings',
		]),
	},
	beforeMount() {
		this.getSettings()
	},
	methods: {
		...mapActions(['getSettings']),
	},
}
</script>
