<script>
	import AppHeader from './components/common/app_header.vue';
    import AppFooter from './components/common/app_footer.vue';
    import Modal from './components/common/modal.vue';

    import init from 'init.js';
    // import route from 'route.js';

    import store from './vuex/store.js';
    import { appHeader } from './vuex/getters.js';
    import { initStore, updateAppHeader, checkLogin, getCartGoodsQty , getWxOpenId } from './vuex/actions.js';

    Vue.component('app-header', AppHeader);
    Vue.component('app-footer', AppFooter);
    Vue.component('modal', Modal);

	export default {
		store,
		vuex: {
			getters: {
				appHeader
			},
			actions: {
				initStore,
				updateAppHeader,
				checkLogin,
				getCartGoodsQty,
				getWxOpenId
			}
		},

		mixins: [init],
		// mixins: [init, route],

		route: {
			activate() {
				console.log(1111)
			},
			data() {
				console.log(222)
			}
		},
		
		created() {
			this.initStore({
				vm: this
			});
			this.getWxOpenId({
				source:this.$route.path
			});
			// this.checkLogin();
			this.getCartGoodsQty();
		},
		ready() {
			let style = this.$el.style;
			style.paddingTop = this.paddingTop;
		},
		data() {
			return {
				paddingBottom: '',
				showModal: false,
				popupMsg: ''
			}
		},
		watch: {
			paddingTop(val) {
				this.$el.style.paddingTop = val;
			}
		},
		computed: {
			paddingTop() {
				return this.appHeader.type == 1 ? '4rem' : '3.2rem';
			}
		},
		events: {
			popup(text) {
				this.showModal = true;
				this.popupMsg = text;
			}
		}
	}
</script>

<template>
	<div :style="{paddingBottom: paddingBottom}">
		<app-header v-ref:header></app-header>
		<!-- <transition name="loading"> -->
			<router-view keep-alive></router-view>
		<!-- </transition> -->
		<app-footer v-ref:footer v-on:setBottom></app-footer>
		<modal :show.sync="showModal" :msg="popupMsg" v-on:popup></modal>
	</div>
</template>