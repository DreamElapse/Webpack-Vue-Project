<script>
	import Quantity from 'components/goods/quantity.vue';

	import { buyOption } from 'vuex_path/getters.js';
	import { addGoodsToCart } from 'vuex_path/actions.js';

	export default {
		components: {
			Quantity
		},
		vuex: {
			getters: {
				buyOption
			},
			actions: {
				addGoodsToCart
			}
		},
		ready() {
			this.$watch('buyOption.selected', (val) => {
				this.goodsData.id = val.goods_id;
				this.goodsData.num = val.quantity;
			});
		},
		watch:{

		},
		data() {
			return {
				activate: false,
				goodsData: {id: 0,num:1}
			}
		},
		methods: {
			close(e) {
				if(/js-close/.test(e.target.className)){
					this.activate = false;
					this.$root.$refs.footer.activate = true;
				}
			},
			change(goods,index) {

				// this.buyOption({
				// 	selected: this.buyOption.list[index],
				// 	list: this.buyOption.list 
				// });

				this.goodsData = {
					id: goods.goods_id,
					num: goods.quantity
				}
				this.$root.$refs.footer.goodsData = {
					id: goods.goods_id,
					num: goods.quantity,
					types:goods.goods_id
				}
				console.log(index)
				// this.setBuyOption({
				// 	selected: this.goods[index]
				// });
				
			},
			buy() {
				if(!this.goodsData.id){
					this.$dispatch('popup', '请勾选商品');
				}else{
					this.addGoodsToCart(this.goodsData);
					this.activate = false;
					this.$root.$refs.footer.activate = true;
				}
			}
		},
		events: {
			buyOption() {
				this.activate = true;
			},
			quantityChange(num, goods) {
				if(goods.goods_id == this.goodsData.id){
					this.goodsData.num = num;
				}
			}
		}
	}
</script>

<template>
	<style>
		.goods-qty{height: 1.6rem;}
		.goods-qty span{width: 1.6rem;}
		.goods-qty input{width: 2rem;}
	</style>
	<div class="width-full container js-close" :class="{'on': activate}" @click.stop="close($event)">
		<div class="option" :class="{'on': activate}">
			<div class="option-hd">
				<i class="font icon-close-round js-close" @click.stop="close($event)"></i>
			</div>
			<ul>
				<li v-for="item in buyOption.list" @change="change(item,$index)">
					<label>
						<input class="checkbox" type="radio" :value="item.goods_id" v-model="goodsData.id" ><img :src="item.goods_thumb" alt="" /><p>{{item.goods_name}}</p><span>￥{{item.shop_price}}</span>
					</label>
					<quantity :class-name="aa" :quantity.sync="item.quantity" :goods="item" v-on:quantityChange></quantity>
				</li>
			</ul>
			<a class="btn" href="javascript:;" @click="buy">加入购物车</a>
		</div>
	</div>
</template>

<style scoped>
	.container{height: 100%; background: rgba(0,0,0,0.3); position: fixed; top: 0; visibility: hidden; opacity: 0; transition: 0.3s ease-out;}
	.container.on{visibility: visible; opacity: 1;}
	.option{width: 100%; background: #fff; position: absolute; bottom: 0; transform: translate3d(0,100%,0); transition: 0.3s ease-out;}
	.option.on{transform: translate3d(0,0,0);}
	.option-hd{padding: 0.4rem 0.8rem 0; text-align: right;}
	.option-hd .font{font-size: 1.2rem; color: #c3c3c3;}
	.option ul{padding: 0 0.8rem;}
	.option li{padding: 0.6rem 0; border-top: 1px solid #f4f4f4; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.option li:first-child{border-top: 0;}
	.option li label{display: flex; display: -webkit-flex; flex: 1; -webkit-flex: 1; align-items: center; -webkit-align-items: center;}
	.option li .checkbox{margin-right: 0.6rem; display: block;}
	.option li img{width: 3rem; height: 3rem;}
	.option li p{padding-left: 0.6rem; font-size: 0.8rem; flex: 1; -webkit-flex: 1; display: block;}
	.option li span{width: 3rem; font-size: 0.9rem; color: #da3737; display: block;}
	.option .btn{line-height: 3rem; background: #da3737; display: block;}
</style>