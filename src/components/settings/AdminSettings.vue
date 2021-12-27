<!--
 - @copyright Copyright (c) 2021 Andrey Borysenko <andrey18106x@gmail.com>
 -
 - @copyright Copyright (c) 2021 Alexander Piskun <bigcat88@icloud.com>
 -
 - @author Andrey Borysenko <andrey18106x@gmail.com>
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
		<h2>{{ t('cloud_py_api', 'Cloud Python API (Framework)') }}</h2>
		<div v-if="settings.length > 0">
			<h3>Settings</h3>
			<p v-for="setting of settings" :key="setting.id" style="border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 10px 0;">
				Name: <b>{{ setting.name }}</b><br>
				Value: {{ setting.value }}<br>
				Display name: {{ setting.display_name }}<br>
				Title: {{ setting.title }}<br>
				Description: {{ setting.description }}<br>
				Help url: Read <a style="text-decoration: underline" :href="setting.help_url">the docs</a>
			</p>
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'AdminSettings',
	data() {
		return {
			settings: [],
		}
	},
	beforeMount() {
		this.getSettings()
	},
	methods: {
		getSettings() {
			axios.get(generateUrl('/apps/cloud_py_api/api/v1/settings')).then(res => {
				this.settings = res.data
			})
		},
	},
}
</script>

<style scoped>
.admin-settings {
	margin: 20px;
}
</style>
