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
	<Content app-name="app-cloud_py_api">
		<AppNavigation v-if="isAdmin">
			<template #list>
				<AppNavigationItem :to="{name: 'configuration'}"
					class="app-navigation__cloud_py_api"
					:title="t('cloud_py_api', 'Registered Apps')"
					icon="icon-toggle-filelist" />
				<AppNavigationItem class="app-navigation__cloud_py_api"
					:title="t('cloud_py_api', 'Admin settings')"
					icon="icon-user-admin"
					@click="goToAdminSettingsUrl()" />
			</template>
		</AppNavigation>
		<AppContent :class="{ 'icon-loading': loading }">
			<router-view v-if="isAdmin" v-show="!loading" :loading.sync="loading" />
			<div v-else style="margin: 20px; text-align: center; padding: 5px;">
				<h2>{{ t('cloud_py_api', 'Cloud Py Api Configuration') }}</h2>
				<p>{{ t('cloud_py_api', 'Configuration allowed only for administrator') }}</p>
			</div>
		</AppContent>
	</Content>
</template>

<script>
import Nextcludl10n from './mixins/Nextcludl10n'

import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import { getCurrentUser } from '@nextcloud/auth'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'CloudPyAPI',
	components: {
		Content,
		AppContent,
		AppNavigation,
		AppNavigationItem,
	},
	mixins: {
		Nextcludl10n,
	},
	data() {
		return {
			loading: true,
			isAdmin: getCurrentUser() === null ? false : getCurrentUser().isAdmin,
		}
	},
	beforeMount() {
		if (!this.isAdmin) {
			this.loading = false
		}
	},
	methods: {
		goToAdminSettingsUrl() {
			window.location.href = generateUrl('/settings/admin/cloud_py_api')
		},
	},
}
</script>

<style scoped>
h2 {
	margin: 20px 0;
}
</style>
