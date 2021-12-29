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
		<h2>{{ t('cloud_py_api', 'Cloud Py API Configuration') }}</h2>
		<p>{{ t('cloud_py_api', 'Here will be configuration steps after installing app, that using this Framework') }}</p>
	</div>
</template>

<script>
export default {
	name: 'Configuration',
	mounted() {
		this.$emit('update:loading', false)
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
