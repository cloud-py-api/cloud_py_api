"use strict";(self.webpackChunkcloud_py_api=self.webpackChunkcloud_py_api||[]).push([["src_views_Configuration_vue"],{8912:(t,n,e)=>{e.d(n,{Z:()=>p});var a=e(3645),i=e.n(a)()(!0);i.push([t.id,"\n.cloud_py_api-configuration[data-v-56bae9c8] {\n\tmargin: 20px;\n\ttext-align: center;\n}\nh2[data-v-56bae9c8] {\n\tmargin: 20px 0;\n}\n.apps-list[data-v-56bae9c8] {\n\tmargin: 20px 0;\n}\n.registered-app[data-v-56bae9c8] {\n\tborder: 1px solid #eee;\n\tpadding: 10px 15px;\n\tmargin: 10px 0;\n\tborder-radius: 5px;\n}\n","",{version:3,sources:["webpack://src/views/Configuration.vue"],names:[],mappings:";AA8EA;CACA,YAAA;CACA,kBAAA;AACA;AAEA;CACA,cAAA;AACA;AAEA;CACA,cAAA;AACA;AAEA;CACA,sBAAA;CACA,kBAAA;CACA,cAAA;CACA,kBAAA;AACA",sourcesContent:["\x3c!--\n - @copyright Copyright (c) 2021 Andrey Borysenko <andrey18106x@gmail.com>\n -\n - @copyright Copyright (c) 2021 Alexander Piskun <bigcat88@icloud.com>\n -\n - @author Andrey Borysenko <andrey18106x@gmail.com>\n -\n - @license AGPL-3.0-or-later\n -\n - This program is free software: you can redistribute it and/or modify\n - it under the terms of the GNU Affero General Public License as\n - published by the Free Software Foundation, either version 3 of the\n - License, or (at your option) any later version.\n -\n - This program is distributed in the hope that it will be useful,\n - but WITHOUT ANY WARRANTY; without even the implied warranty of\n - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the\n - GNU Affero General Public License for more details.\n -\n - You should have received a copy of the GNU Affero General Public License\n - along with this program. If not, see <http://www.gnu.org/licenses/>.\n -\n --\x3e\n\n<template>\n\t<div class=\"cloud_py_api-configuration\">\n\t\t<h2>{{ t('cloud_py_api', 'Cloud Py API Configuration') }}</h2>\n\t\t<p>{{ t('cloud_py_api', 'Here will be list of registered apps, that using Python (via this Framework)') }}</p>\n\t\t<div v-if=\"apps && apps.length > 0\" class=\"apps-list\">\n\t\t\t<a v-for=\"app of apps\"\n\t\t\t\t:key=\"app.id\"\n\t\t\t\tclass=\"registered-app\"\n\t\t\t\t:href=\"getAppConfigurationUrl(app)\">\n\t\t\t\t{{ app.id }}. {{ app.app_id }} (token: {{ app.token }})\n\t\t\t</a>\n\t\t</div>\n\t\t<div v-else>\n\t\t\t<b>{{ t('cloud_py_api', 'No apps registered') }}</b>\n\t\t</div>\n\t</div>\n</template>\n\n<script>\nimport axios from '@nextcloud/axios'\nimport { generateUrl } from '@nextcloud/router'\n\nexport default {\n\tname: 'Configuration',\n\tdata() {\n\t\treturn {\n\t\t\tapps: [],\n\t\t}\n\t},\n\tbeforeMount() {\n\t\tthis.getApps()\n\t},\n\tmounted() {\n\t\tthis.$emit('update:loading', false)\n\t},\n\tmethods: {\n\t\tgetApps() {\n\t\t\tthis.$emit('update:loading', true)\n\t\t\taxios.get(generateUrl('/apps/cloud_py_api/api/v1/apps')).then(res => {\n\t\t\t\tthis.apps = res.data\n\t\t\t\tthis.$emit('update:loading', false)\n\t\t\t}).catch(err => {\n\t\t\t\tconsole.debug(err)\n\t\t\t\tthis.$emit('update:loading', false)\n\t\t\t})\n\t\t},\n\t\tgetAppConfigurationUrl(app) {\n\t\t\treturn generateUrl(`/apps/cloud_py_api/apps/${app.id}`)\n\t\t},\n\t},\n}\n<\/script>\n\n<style scoped>\n.cloud_py_api-configuration {\n\tmargin: 20px;\n\ttext-align: center;\n}\n\nh2 {\n\tmargin: 20px 0;\n}\n\n.apps-list {\n\tmargin: 20px 0;\n}\n\n.registered-app {\n\tborder: 1px solid #eee;\n\tpadding: 10px 15px;\n\tmargin: 10px 0;\n\tborder-radius: 5px;\n}\n</style>\n"],sourceRoot:""}]);const p=i},8810:(t,n,e)=>{e.r(n),e.d(n,{default:()=>u});var a=e(4820),i=e(9753),p=e(5108);const o={name:"Configuration",data:()=>({apps:[]}),beforeMount(){this.getApps()},mounted(){this.$emit("update:loading",!1)},methods:{getApps(){this.$emit("update:loading",!0),a.Z.get((0,i.nu)("/apps/cloud_py_api/api/v1/apps")).then((t=>{this.apps=t.data,this.$emit("update:loading",!1)})).catch((t=>{p.debug(t),this.$emit("update:loading",!1)}))},getAppConfigurationUrl:t=>(0,i.nu)("/apps/cloud_py_api/apps/".concat(t.id))}};var r=e(3379),s=e.n(r),d=e(8912),l={insert:"head",singleton:!1};s()(d.Z,l);d.Z.locals;const u=(0,e(1900).Z)(o,(function(){var t=this,n=t.$createElement,e=t._self._c||n;return e("div",{staticClass:"cloud_py_api-configuration"},[e("h2",[t._v(t._s(t.t("cloud_py_api","Cloud Py API Configuration")))]),t._v(" "),e("p",[t._v(t._s(t.t("cloud_py_api","Here will be list of registered apps, that using Python (via this Framework)")))]),t._v(" "),t.apps&&t.apps.length>0?e("div",{staticClass:"apps-list"},t._l(t.apps,(function(n){return e("a",{key:n.id,staticClass:"registered-app",attrs:{href:t.getAppConfigurationUrl(n)}},[t._v("\n\t\t\t"+t._s(n.id)+". "+t._s(n.app_id)+" (token: "+t._s(n.token)+")\n\t\t")])})),0):e("div",[e("b",[t._v(t._s(t.t("cloud_py_api","No apps registered")))])])])}),[],!1,null,"56bae9c8",null).exports}}]);
//# sourceMappingURL=cloud_py_api-src_views_Configuration_vue.js.map?v=aa545e45246f8b6b3634