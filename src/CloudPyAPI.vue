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
		<AppNavigation>
			<template #list>
				<AppNavigationItem :to="{name: 'configuration'}"
					class="app-navigation__cloud_py_api"
					:title="t('cloud_py_api', 'Configuration')"
					icon="icon-user-admin" />
			</template>
		</AppNavigation>
		<AppContent :class="{ 'icon-loading': loading }">
			<router-view v-show="!loading" :loading.sync="loading" />
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
			showConfiguration: getCurrentUser() === null ? false : getCurrentUser().isAdmin,
		}
	},
}
</script>

<style scoped>
h2 {
	margin: 20px 0;
}
</style>
