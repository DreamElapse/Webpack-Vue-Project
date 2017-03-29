<script>
	import IndexHeader from './index_header.vue';
	import SubHeader from './sub_header.vue';

	import { appHeader } from 'vuex_path/getters.js';

	export default {
		components: {
			IndexHeader,
			SubHeader
		},
		vuex: {
			getters: {
				appHeader
			}
		},
		computed: {
			isShow() {
				return this.appHeader.type == 1 ? true : false;
			}
		}
	}
</script>

<template>
	<index-header v-if="isShow"></index-header>
	<sub-header v-else></sub-header>
</template>