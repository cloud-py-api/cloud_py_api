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
		<div v-if="settings.length > 0" class="settings">
			<NcSettingsSection :title="t('cloud_py_api', mappedSettings.python_command.display_name)"
				:description="t('cloud_py_api', mappedSettings.python_command.description)"
				:doc-url="mappedSettings.python_command.help_url">
				<input id="python_command"
					v-model="mappedSettings.python_command.value"
					type="text"
					name="python_command"
					@change="saveChanges">
			</NcSettingsSection>
			<NcSettingsSection :title="t('cloud_py_api', mappedSettings.remote_filesize_limit.display_name)"
				:description="t('cloud_py_api', mappedSettings.remote_filesize_limit.description)"
				:doc-url="mappedSettings.remote_filesize_limit.help_url">
				<input id="remote_filesize_limit"
					v-model="remote_filesize_limit"
					type="number"
					name="remote_filesize_limit"
					min="0"
					step="0.1"
					@input="updateRemoteFilesizeLimit"
					@change="saveChanges">
			</NcSettingsSection>
			<NcSettingsSection :title="t('cloud_py_api', mappedSettings.use_php_path_from_settings.display_name)"
				:description="t('cloud_py_api', mappedSettings.use_php_path_from_settings.description)"
				:doc-url="mappedSettings.use_php_path_from_settings.help_url">
				<NcCheckboxRadioSwitch :checked.sync="usePhpPathFromSettings" @update:checked="updateUsePhpPathFromSettings">
					{{ t('cloud_py_api', 'Use PHP path from settings') }}
				</NcCheckboxRadioSwitch>
			</NcSettingsSection>
			<NcSettingsSection :title="t('cloud_py_api', mappedSettings.php_path.display_name)"
				:description="t('cloud_py_api', mappedSettings.php_path.description)"
				:doc-url="mappedSettings.php_path.help_url">
				<input id="php_path"
					v-model="mappedSettings.php_path.value"
					type="text"
					name="php_path"
					@change="saveChanges">
			</NcSettingsSection>
			<NcSettingsSection :title="t('cloud_py_api', mappedSettings.python_binary.display_name)"
				:description="t('cloud_py_api', mappedSettings.python_binary.description)"
				:doc-url="mappedSettings.python_binary.help_url">
				<NcCheckboxRadioSwitch :checked.sync="python_binary" @update:checked="updatePythonBinary">
					{{ t('cloud_py_api', 'Use pre-compiled Python binaries') }}
				</NcCheckboxRadioSwitch>
			</NcSettingsSection>
		</div>
		<NcSettingsSection :title="t('cloud_py_api', 'Bug report')">
			<BugReport />
		</NcSettingsSection>
	</div>
</template>

<script>
import { mapActions } from 'vuex'
import { showError } from '@nextcloud/dialogs'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'

import BugReport from './BugReport.vue'

export default {
	name: 'AdminSettings',
	components: {
		NcSettingsSection,
		NcCheckboxRadioSwitch,
		BugReport,
	},
	data() {
		return {
			settings: [],
			mappedSettings: {},
			remote_filesize_limit: null,
			usePhpPathFromSettings: false,
			python_binary: true,
		}
	},
	beforeMount() {
		this.getSettings().then((res) => {
			this.settings = res.data
			this.settings.forEach(setting => {
				this.mappedSettings[setting.name] = setting
			})
			this.remote_filesize_limit = this.fromBytesToGBytes(Number(this.mappedSettings.remote_filesize_limit.value))
			this.usePhpPathFromSettings = JSON.parse(this.mappedSettings.use_php_path_from_settings.value)
			this.python_binary = JSON.parse(this.mappedSettings.python_binary.value)
		})
	},
	methods: {
		...mapActions(['getSettings', 'updateSettings']),
		saveChanges() {
			this.updateSettings(this.settings).catch(err => {
				console.debug(err)
				showError(this.t('cloud_py_api', 'Some error occurred while updating settings'))
			})
		},
		fromBytesToGBytes(bytes) {
			return bytes / Math.pow(1024, 3)
		},
		fromGBytesToBytes(GBytes) {
			return GBytes * Math.pow(1024, 3)
		},
		updateRemoteFilesizeLimit() {
			this.mappedSettings.remote_filesize_limit.value = this.fromGBytesToBytes(Number(this.remote_filesize_limit))
		},
		updateUsePhpPathFromSettings() {
			this.mappedSettings.use_php_path_from_settings.value = JSON.stringify(this.usePhpPathFromSettings)
			this.saveChanges()
		},
		updatePythonBinary() {
			this.mappedSettings.python_binary.value = JSON.stringify(this.python_binary)
			this.saveChanges()
		},
	},
}
</script>
