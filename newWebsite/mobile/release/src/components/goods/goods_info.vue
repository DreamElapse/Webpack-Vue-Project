<script>
	import { updateAppHeader, setHeaderTabIndex } from 'vuex_path/actions.js';

	import GoodsDesc from './goods_desc.vue';
	import GoodsComment from './goods_comment.vue';

	export default {
		components:{
			GoodsDesc,
			GoodsComment
		},
		vuex: {
			getters: {
				index: state => state.app.header.tabIndex
			},
			actions: {
				updateAppHeader,
				setHeaderTabIndex
			}
		},
		route: {
			activate() {
				return this.$root.getAdvisoryInfo();
			},
			data() {
				this.updateAppHeader({
					type: 2,
					content: ''
				});
				
				this.id = this.$route.params.id;

				if(this.$route.params.view == 'comment'){
					this.setHeaderTabIndex(1);
				}else{
					this.setHeaderTabIndex(0);
				}
			}
		},
		data() {
			return {
				id: this.$route.params.id,
				currentView: 'GoodsDesc'
			}
		},
		watch: {
			index(val) {
				if(val == 0){
					this.currentView = 'GoodsDesc';
				}else if(val == 1){
					this.currentView = 'GoodsComment';
				}
			}
		}
	}
</script>

<template>
	<goods-desc v-if="index == 0" :id="id"></goods-desc>
	<goods-comment v-else></goods-comment>
	<!-- <component :is="currentView" keep-alive>
		<goods-desc :gid="22222"></goods-desc>
		<goods-comment></goods-comment>
	</component> -->
</template>