<script>
	import { actName } from 'vuex_path/getters.js';
	import { addGoodsToCart, setBuyOption } from 'vuex_path/actions.js';
	
	import Favourable from 'components/common/favourable.vue';
	import Ntell from 'components/common/ntell.vue';

	// Vue.component('ntell', Ntell);
	var Swipe = require('exports?Swipe!swipe.js');

	export default {
		// components: {
		// 	Ntell
		// },
		vuex: {
			getters: {
				index: state => state.app.header.tabIndex,
				Q_Chinaskin: state => state.Q_Chinaskin,
				actName
			},
			actions: {
				addGoodsToCart,
				setBuyOption
			}
		},
		ready() {
			this.$watch('index', (val) => {
				this.change(this.id);
			}, { immediate: true });
		},
		data() {
			return {
				html: ''
			}
		},
		props: {
			id: {
				required: true
			}
		},
		watch: {
			id(val) {
				this.change(val);
			}
		},
		computed: {
			detailURL() {
				if(this.Q_Chinaskin){
					return '/release/src/view/QChinaskin/';
				}
				return '/release/src/view/goods/detail_';
			}
		},
		methods: {
			change(id) {
				$('#goodsDesc').html('');
				if(parseInt(id)){
					// 商品详情
					if(this.index == 0){
						this.loadDetail(id);
					}
				}else{
					// 专题详情
					if(this.index == 0){
						this.loadTopic(id);
					}
				}
			},
			loadDetail(id) {
				let infoComp = this;
				this.$el.style.padding = '0.5rem 0 0';
				this.$el.style.background = '#fff';
				this.$http.post(this.detailURL + id + '.html').then((res) => {
					let detail = Vue.extend({
						template: res.data
					});
					new detail().$mount().$appendTo('#goodsDesc');
	            });
			},
			loadTopic(id) {
				let infoComp = this;
				this.$http.post('/release/src/view/topic/' + id + '.html').then((res) => {
					let detail = Vue.extend({
						template: res.data,
						components: {
							Favourable
						},
						ready() {
							let script = $('#topicReady').html();
							if(script){
								eval(script);
							}
							let data = {
								page_name: id
							}
							return this.$http.post('/Goods/specialPage.json', data).then((res) => {
								res = res.json();
								if(res.status == 1){
									for(let i of res.data.goods_list){
										i.quantity = 1;
									}
									this.goodsList = res.data.goods_list;
									infoComp.setBuyOption({
										selected: this.goodsList[0],
										list: this.goodsList
									});
								}
							});
						},
						data() {
							return {
								goodsList: ''
							}
						},
						methods: {
							buy(goods) {
								if(goods != undefined){
									if(goods.goods_id == "" || goods.goods_id ==undefined){
										alert('商品缺货下架，抓紧咨询客服抢购吧！');
									}else{
										infoComp.addGoodsToCart({
											id: goods.goods_id
										});
									}
								}else{
									alert('商品缺货下架，抓紧咨询客服抢购吧！');
								}
								
							}
						},
						events: {
							goAct() {
								infoComp.$route.router.go({ name: 'act', params: {name: infoComp.actName} });
							}
						}
					});
					new detail().$mount().$appendTo('#goodsDesc');
	            });
			}
		}
	}
</script>

<template>
	<div>
		<link v-if="Q_Chinaskin" rel="stylesheet" href="/public/css/q_detail.css" />
		<div id="goodsDesc"></div>
	</div>
</template>