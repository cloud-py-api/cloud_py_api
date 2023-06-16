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
				{{ t('cloud_py_api', 'Cloud Python API') }}
			</h2>
		</div>
		<div v-if="settings.length > 0" class="settings">
			<NcSettingsSection :title="t('cloud_py_api', mappedSettings.python_command.display_name)"
				:description="t('cloud_py_api', mappedSettings.python_command.description)">
				<input id="python_command"
					v-model="mappedSettings.python_command.value"
					type="text"
					name="python_command"
					style="width: fit-content;"
					@change="saveChanges">
			</NcSettingsSection>
			<NcSettingsSection :title="t('cloud_py_api', mappedSettings.remote_filesize_limit.display_name)"
				:description="t('cloud_py_api', mappedSettings.remote_filesize_limit.description)">
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
				:description="t('cloud_py_api', mappedSettings.use_php_path_from_settings.description)">
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
					:disabled="!usePhpPathFromSettings"
					style="width: fit-content;"
					@change="saveChanges">
			</NcSettingsSection>
			<NcSettingsSection :title="t('cloud_py_api', mappedSettings.cpa_loglevel.display_name)"
				:description="t('cloud_py_api', mappedSettings.cpa_loglevel.description)">
				<select id="cpa_loglevel"
					v-model="cpaLoglevel"
					name="cpa_loglevel"
					@change="updateCpaLoglevel">
					<option v-for="loglevel in cpaLoglevels" :key="loglevel" :value="loglevel">
						{{ loglevel }}
					</option>
				</select>
			</NcSettingsSection>
		</div>
		<div v-else>
			<NcSettingsSection :title="t('cloud_py_api', 'Error')">
				<NcEmptyContent style="margin-top: 0;"
					:title="t('cloud_py_api', 'Settings list is empty')"
					:description="t('cloud_py_api', 'Seems like database not initialized properly. Try to re-enable the app')">
					<template #icon>
						<AlertCircleOutline />
					</template>
				</NcEmptyContent>
			</NcSettingsSection>
		</div>
		<NcSettingsSection :title="t('cloud_py_api', 'Bug report')">
			<BugReport />
		</NcSettingsSection>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import AlertCircleOutline from 'vue-material-design-icons/AlertCircleOutline.vue'

import BugReport from './BugReport.vue'

export default {
	name: 'AdminSettings',
	components: {
		NcSettingsSection,
		NcCheckboxRadioSwitch,
		NcEmptyContent,
		BugReport,
		AlertCircleOutline,
	},
	data() {
		return {
			settings: [],
			mappedSettings: {},
			remote_filesize_limit: null,
			usePhpPathFromSettings: false,
			cpaLoglevel: 'INFO',
			cpaLoglevels: ['DEBUG', 'INFO', 'WARNING', 'ERROR'],
		}
	},
	beforeMount() {
		this._getSettings()
	},
	methods: {
		_updateSettings(settings) {
			return axios.put(generateUrl('/apps/cloud_py_api/api/v1/settings'), { settings }).then(res => {
				if (res.data.success) {
					this.settings = res.data.updated_settings
					this.settings.forEach(setting => {
						this.mappedSettings[setting.name] = setting
					})
				}
				return res
			})
		},
		_getSettings() {
			axios.get(generateUrl('/apps/cloud_py_api/api/v1/settings')).then(res => {
				this.settings = res.data
				this.settings.forEach(setting => {
					this.mappedSettings[setting.name] = setting
				})
				this.remote_filesize_limit = this.fromBytesToGBytes(Number(this.mappedSettings.remote_filesize_limit.value))
				this.usePhpPathFromSettings = JSON.parse(this.mappedSettings.use_php_path_from_settings.value)
				this.cpaLoglevel = JSON.parse(this.mappedSettings.cpa_loglevel.value)
			})
		},
		saveChanges() {
			this._updateSettings(this.settings).then(res => {
				if (res.data.success) {
					showSuccess(this.t('cloud_py_api', 'Settings successfully updated'))
				}
			})
				.catch(err => {
					console.debug(err)
					showError(this.t('cloud_py_api', 'Some error occurred while updating settings'))
				})
		},
		fromBytesToGBytes(bytes) {
			return (bytes / Math.pow(1024, 3)).toFixed(1)
		},
		fromGBytesToBytes(GBytes) {
			return (GBytes * Math.pow(1024, 3)).toFixed(0)
		},
		updateRemoteFilesizeLimit() {
			this.mappedSettings.remote_filesize_limit.value = this.fromGBytesToBytes(Number(this.remote_filesize_limit))
		},
		updateUsePhpPathFromSettings() {
			this.mappedSettings.use_php_path_from_settings.value = JSON.stringify(this.usePhpPathFromSettings)
			this.saveChanges()
		},
		updateCpaLoglevel() {
			this.mappedSettings.cpa_loglevel.value = JSON.stringify(this.cpaLoglevel)
			this.saveChanges()
		},
	},
}
</script>
