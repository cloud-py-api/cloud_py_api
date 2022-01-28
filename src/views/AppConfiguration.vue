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
	<div class="cloud_py_api-configuration">
		<h2>{{ t('cloud_py_api', 'Cloud Py API App Configuration') }}</h2>
		<p>{{ t('cloud_py_api', 'Here will be configuration steps after installing app, that using this Framework') }}</p>
		<span v-if="loading" class="icon-loading" />
		<div v-else-if="app" class="app-info">
			<p>id: {{ app.id }}</p>
			<p>appId: {{ app.app_id }}</p>
			<p>token: {{ app.token }}</p>
			<div class="actions">
				<p>Actions for Python requirements (will be listed requirements.txt)</p>
				<button>{{ t('cloud_py_api', 'Install') }}</button>
				<button>{{ t('cloud_py_api', 'Update') }}</button>
				<button>{{ t('cloud_py_api', 'Delete') }}</button>
			</div>
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'AppConfiguration',
	data() {
		return {
			loading: false,
			app: null,
		}
	},
	beforeMount() {
		this.getApp()
	},
	mounted() {
		this.$emit('update:loading', false)
	},
	methods: {
		getApp() {
			this.loading = true
			axios.get(generateUrl(`/apps/cloud_py_api/api/v1/apps/${this.$route.params.appId}`)).then(res => {
				this.app = res.data
				this.loading = false
			}).catch(err => {
				console.debug(err)
				this.loading = false
			})
		},
	},
}
</script>

<style scoped>
.cloud_py_api-configuration {
	margin: 20px;
	text-align: center;
}

h2 {
	margin: 20px 0;
}
</style>
