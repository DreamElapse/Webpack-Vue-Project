<script>
	import { addGoodsToCart } from 'vuex_path/actions.js';
	import { tel } from 'vuex_path/getters.js';
	export default {
		vuex: {
			actions: {
				addGoodsToCart
			},
			getters:{
				tel
			}
		},
		route: {
			data() {
				this.$root.updateAppHeader({
					type: 1
				});

				let act = this;
				let name = this.$route.params.name;

				this.$http.post('/release/src/view/act/' + name + '.html').then((res) => {
					let topic = Vue.extend({
						template: res.data,
						ready() {
							this.$http.post('/Activity/getActInfo.json?act=' + name).then((res) => {
								res = res.json();
								if(res.status == 1){
									let data = res.data;
									let giftArr = [];
									let packageArr = [];
									for(let i of Object.keys(data)){
										let act_id = data[i].act_id;
										let gift = data[i].gift;
										let gift_package = data[i].gift_package;
										let arr = [];
										let j = 0;
										for(let g of Object.keys(gift)){
											gift[g].act_id = act_id;
											arr.push(gift[g]);
											j++;
										}
										giftArr.push(arr);
										if(typeof gift_package == 'object'){
											let k = 0;
											for(let g of Object.keys(gift_package)){
												gift_package[g].act_id = act_id;
												arr.push(gift_package[g]);
												k++;
											}
											packageArr.push(arr);
										}
									}
									this.giftList = giftArr;
									this.packageList = packageArr;
								}
							});
						},
						data() {
							return {
								giftList: '',
								packageList: '',
								buyList: []
							}
						},
						methods: {
							buy(goods, goodType) {
								act.addGoodsToCart({
									id: goods.id,
									act_id: goods.act_id,
									package: goodType
								});
								
							},
							tel(){
								window.location.href = 'tel://'+act.$store.state.tel;
							}
						}
					});
					$('#actContainer').html('');
					new topic().$mount().$appendTo('#actContainer');
	            });
			}
		}
	}
</script>

<template>
	<div id="actContainer"></div>
</template>