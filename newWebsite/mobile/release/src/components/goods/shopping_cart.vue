<script>
	import { updateAppHeader, getCartGoodsQty, caclCartGoodsQty, addGoodsToCart } from 'vuex_path/actions.js';

	import GoodsQuantity from './goods_quantity.vue';
	import Quantity from './quantity.vue';

	var Swipe = require('exports?Swipe!swipe.js');

	export default {
		components: {
			GoodsQuantity,
			Quantity
		},
		vuex: {
			actions: {
				updateAppHeader,
				getCartGoodsQty,
				caclCartGoodsQty,
				addGoodsToCart
			}
		},
		route: {
			data() {
				this.updateAppHeader({
					type: 2,
					content: '购物车'
				});

				// 还原数据
				this.isEdit = false;
				this.page = 1;
				this.goodsList = [];//购物车列表
				
				this.getPromotionMsg();
				this.showCart();
				this.getActList();
			}
		},
		ready() {
			$('.goods-tab-hd a').on('click', function(){
				var $self = $(this);
				$self.addClass('on').siblings().removeClass('on');
				$self.closest('.goods-tab').find('.goods-tab-cont').removeClass('on').hide().eq($self.index()).addClass('on').show();
			});
		},
		data() {
			return {
				promotionMsg: '',
				isEdit: false,
				computing: false,
				page: 1,//初始页码
				loadCartBtn: '点击加载更多...',
				goodsList: [],
				page_size: 5,//首次显示5条数据
				total_page: 0,//总页数
				giftList: [],////赠品列表
				exchangeList: [],//换购列表
				total_amount: 0//总价				
			}
		},
		watch: {
			goodsList(val) {
			    if(val.length == 0){
			        return
				}
				this.page = Math.ceil(val.length / this.page_size);
			}
		},
		computed: {
			isSelectAll() {
				if(this.goodsList.length == 0){
					return false;
				}
				for(let i of this.goodsList){
					if(i.select == false){
						return false;
					}
				}
				return true;
			}
		},
		methods: {
			getPromotionMsg() {
				let data = {
					is_promotion: 1
				}
				this.$http.post('/User/getInformations.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						if(res.data[0]) this.promotionMsg = res.data[0].title;
					}
				});
			},
			edit(e) {
				this.isEdit = !this.isEdit;
				let target = e.target;
				this.isEdit ? target.innerHTML = '完成' : target.innerHTML = '编辑';
			},
			showCart(option = {}) {
				let data = {
					page_size: this.page_size,
					page: this.page
				}
				this.loadCartBtn = '加载中...';
				return this.$http.post('/cart/showcart.json', data).then((res) => {
                   
					res = res.json();
					let list = res.cart_goods_page_data;

					if(list){
						if(option.page){
							let index = this.page == 1 ? 0 : this.page_size * option.page;
							index = index % this.page_size == 0 ? index - this.page_size : index;
							this.goodsList.splice(index, this.page_size);
						}
						for(let i of list){
							this.goodsList.push(i);
						}
					}
					this.$set('total_page', res.total_page);
					this.$set('total_amount', res.total_amount);
					if(this.page < this.total_page){
						this.loadCartBtn = '点击加载更多...';
					}else{
						this.loadCartBtn = '没有更多了';
					}
				});
			},
			// 购物车下一页
			showCartNextPage() {
				if(this.page < this.total_page){
					this.page += 1;
					this.computing = true;
					this.showCart().then(() => {
						this.computing = false;
					});
				}
			},
			updateGoods() {
				let data = {
					page_size: this.page_size * this.page,
					page: 1
				}
				return this.$http.post('/cart/showcart.json', data).then((res) => {
					res = res.json();
					
					let list = res.cart_goods_page_data;
					if(list) {
						this.goodsList = list;
					}else{
						this.goodsList = [];
					}
					this.$set('total_amount', res.total_amount);
                    this.$set('total_page', res.total_page);
                    if(this.page < this.total_page){
                        this.loadCartBtn = '点击加载更多...';
                    }else{
                        this.loadCartBtn = '没有更多了';
                    }
				});
			},
			txtLimit(txt) {
				return txt.length > 10 ? txt.substr(0, 10) + '...' : txt;
			},
			slider(obj) {
				let $self = $(obj);
				new Swipe(obj, {
				  	speed: 800,
				  	auto: 0,
				  	continuous: false,
				  	callback: function(index, elem) {
				  		$self.find('ol').children().removeClass("on").eq(index).addClass("on");
				  	}
				});
				let count = $self.find('.goods-other-li').length;
				let li = '';
				if(count >= 2){
					for(let i = 0; i < count; i++){
						if(i == 0){
							li += '<li class="on"></li>';
							continue;
						}
						li += '<li></li>';
					}
					$self.find('ol').append(li);
				}
			},


			// 勾选商品
			select(goods) {
				if(goods.select){
					this.unselectGoods(goods);
				}else{
					this.selectGoods(goods);
				}
			},
			// 勾选商品
			selectGoods(goods) {
				if(this.computing){
					return;
				}
				this.computing = true;
				let data = {
					num: goods.goods_number,
					act_id: goods.is_gift,
					option: 'select',
					msg: false
				}
				if(!parseInt(goods.pg_id)){
					data.id= goods.goods_id;
					if(goods.extension_code=="package_buy"){
						data.package=1;
					}
				}else{					
					data.id=goods.pg_id;
					if(goods.extension_code=="package_buy"){
						data.package=0;
					}
				}				
				// 如果是活动商品
				if(goods.act_id){
					data.id = goods.id;
					data.act_id = goods.act_id;
					data.package=goods.is_package
				}
				this.addGoodsToCart(data, () => {
					goods.select = true;
					let taskArr = [this.showCart({page: this.page}), this.getActList(), this.getCartGoodsQty(true)];
					Promise.all(taskArr).then(() => {
						this.computing = false;
					}, () => {
						this.computing = false;
					}).catch(() => {
						this.computing = false;
					});
				}, () => {
					this.computing = false;
				});
			},
			// 去掉选择商品
			unselectGoods(goods, del = 0) {
				if(this.computing){
					return;
				}
				this.computing = true;
				let data = {
					rec_id: goods.rec_id,
					real_del: del
				}
				this.$http.post('/cart/delGoods.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						if(del){
							this.goodsList.$remove(goods);
						}else{
							goods.select = false;
						}
						let taskArr = [this.updateGoods(), this.getActList(), this.getCartGoodsQty(true)];
						Promise.all(taskArr).then(() => {
							this.computing = false;
						}, () => {
							this.computing = false;
						}).catch(() => {
							this.computing = false;
						});
					}else{
						this.$dispatch('popup', res.msg);
						this.computing = false;
					}
				}, () => {
					this.computing = false;
				});
			},
			// 删除商品
			delGoods(goods) {
				let result = window.confirm('确认删除该商品吗');
				if(result){
					this.unselectGoods(goods, 1);
				}else{
					this.computing = false;
				}
			},
			// 全选
			selectAll(e) {
				e.preventDefault();
				if(this.computing){
					return;
				}
				this.computing = true;
				if(this.isSelectAll){
					this.$http.post('/cart/delAllGoods.json').then((res) => {
						res = res.json();
						if(res.status == 1){
							for(let i of this.goodsList){
								i.select = false;
							}
							Promise.all([this.updateGoods(), this.getActList()]).then(() => {
								this.computing = false;
							}).catch(() => {
								this.computing = false;
							});
						}else{
							this.computing = false;
						}
					});
				}else{
					this.$http.post('/cart/selectAllGoods.json').then((res) => {
						res = res.json();
						if(res.status == 1){
							for(let i of this.goodsList){
								i.select = true;
							}
							Promise.all([this.getActList(), this.getAmount()]).then(() => {
								this.computing = false;
							}).catch(() => {
								this.computing = false;
							});
						}else{
							this.computing = false;
						}
					});
				}
			},
			// 获取合计价钱
			getAmount() {
				this.$http.post('/cart/showcart.json').then((res) => {
                    
					res = res.json();
					this.total_amount = res.total_amount;
				});
			},
			// 活动商品列表
			getActList() {
				this.$http.get('/cart/activityList.json?gift=1').then((res) => {
					res = res.json();
					let giftArr = [];
					let exchangeArr = [];
					for(let i of Object.keys(res)) {
						let g = res[i];
						//满赠
						if(g.is_free_gift == 1){
							//单品
							for(let i of Object.keys(g.gift)){
								g.gift[i].act_id = g.act_id;
								g.gift[i].is_package = 0;
								g.gift[i].goods_number = 1;
								giftArr.push(g.gift[i]);
							}
							//套装
							for(let i of Object.keys(g.gift_package)){
								g.gift_package[i].act_id = g.act_id;
								g.gift_package[i].is_package = 1;
								g.gift_package[i].goods_number = 1;
								giftArr.push(g.gift_package[i]);
							}
						}
						//换购
						if(g.is_exchange_buy == 1){
							//单品
							for(let i of Object.keys(g.gift)){
								g.gift[i].act_id = g.act_id;
								g.gift[i].is_package = 0;
								g.gift[i].goods_number = 1;
								g.gift[i].select = false;
								exchangeArr.push(g.gift[i]);
							}
							//套装
							for(let i of Object.keys(g.gift_package)){
								//新增
								g.gift_package[i].act_id = g.act_id;
								g.gift_package[i].is_package = 1;
								g.gift_package[i].goods_number = 1;
								g.gift_package[i].select = false;
								exchangeArr.push(g.gift_package[i]);
							}
						}
					}
					this.$set('giftList', giftArr);
					this.$set('exchangeList', exchangeArr);
					//数据变化之后更新列表
					this.$nextTick(() => {
						$('.goods-tab-cont ol').html('');
						let objs = document.querySelectorAll('.goods-tab-cont');
						$('.goods-tab-hd a').removeClass('on').eq(0).addClass('on');
						for(let i of objs){
							i.style.display = 'block';
							this.slider(i);
						}
					});
				});
			},
			toOrder() {
				if(this.goodsList.length == 0){
					this.$dispatch('popup', '购物车还没有加入商品');
					return;
				}
				let result = false;
				for(let i of this.goodsList){
					if(i.select){
						result = true;
						break;
					}
				}
				if(result){
					this.$route.router.go({ name: 'order' });
				}else{
					this.$dispatch('popup', '你还没有勾选商品');
				}
			}
		},
		events: {
			quantityMinus(goods) {
				if(this.computing){
					return;
				}
				this.computing = true;
				let data = {					
					act_id: goods.is_gift
				}
				if(!parseInt(goods.pg_id)){
					data.goods_id= goods.goods_id;
					if(goods.extension_code=="package_buy"){
						data.is_package=1;
					}
				}else{
					data.goods_id=goods.pg_id;
					if(goods.extension_code=="package_buy"){
						data.is_package=0;
					}
				}
				this.$http.post('/cart/mineOneGoods.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						goods.goods_number -= 1;
						this.caclCartGoodsQty(-1);
						Promise.all([this.getActList(), this.showCart({page: this.page})]).then(() => {
							this.computing = false;
						});
					}else{
						this.$dispatch('popup', res.msg);
						this.computing = false;
					}
				});
			},
			//extension_code==package_buy(套装)时要传is_package=1
			quantityAdd(goods) {
				if(this.computing){
					return;
				}
				this.computing = true;
				let data = {
					num: 1,
					act_id: goods.is_gift,
					msg: false
				}
				if(!parseInt(goods.pg_id)){
					data.id= goods.goods_id;
					if(goods.extension_code=="package_buy"){
						data.package=1;
					}					
				}else{
					data.id=goods.pg_id;
					if(goods.extension_code=="package_buy"){
						data.package=0;
					}
				}				
				this.addGoodsToCart(data, () => {
					goods.select = true;
					goods.goods_number += 1;
					Promise.all([this.getActList(), this.showCart({page: this.page})]).then(() => {
						this.computing = false;
					});
				}, () => {
					this.computing = false;
				});
			}
		}
	}
</script>

<template>
	<div class="container">
		<div v-show="computing" class="loading"></div>

		<div class="width-full tips">
			<div><p v-if="promotionMsg">本期优惠大放送：{{promotionMsg}}</p></div><span v-if="goodsList.length > 0" @click="edit($event)">编辑</span>
		</div>

		<ul class="goods-list" :class="{edit: isEdit}">
			<li v-for="item in goodsList">
				<div class="goods-item">
					<b v-if="parseInt(item.is_gift)" class="sale">活动SALE</b>
					<b class="checkbox" :class="{selected: item.select}" @click.prevent="select(item)"></b>
					<a v-if="parseInt(item.is_gift)" class="goods-item-img"><img :src="item.thumb" alt="" /></a>
					<template v-else>
						<a v-if="item.extension_code=='package_buy'" class="goods-item-img" v-link="{ name: 'goodsDetail', params: {id: item.pg_id, package: 0} }"><img :src="item.thumb" alt="" /></a>
						<a v-else class="goods-item-img" v-link="{ name: 'goodsDetail', params: {id: item.goods_id, package: 0} }"><img :src="item.thumb" alt="" /></a>
					</template>					
					<div class="goods-item-info">
						<strong v-if="parseInt(item.is_gift)">{{item.goods_name}}</strong>
						<template v-else>
							<strong v-if="item.extension_code=='package_buy'" v-link="{ name: 'goodsDetail', params: {id: item.pg_id, package: 0} }">{{item.goods_name}}</strong>
							<strong v-else v-link="{ name: 'goodsDetail', params: {id: item.goods_id, package: 0} }">{{item.goods_name}}</strong>
						</template>
						<div class="goods-action">
							<div class="price">￥{{item.goods_price}}<del>￥{{item.market_price}}</del></div>
							<goods-quantity :goods="item" v-on:quantityMinus v-on:quantityAdd></goods-quantity>
						</div>
					</div>
					<div class="btn-delete" @click="delGoods(item)">删除</div>
				</div>
			</li>
		</ul>

		<a class="load-more" href="javascript:;" @click="showCartNextPage">{{loadCartBtn}}</a>

		<div class="goods-tab">
			<div class="goods-tab-hd">
				<a class="on" href="javascript:;"><i class="font icon-gift"></i><em>赠品</em></a>
				<a href="javascript:;"><i class="font icon-add-to-ShoppingCart"></i><em>换购</em></a>
			</div>
			<div class="goods-tab-bd">
				<div class="tab-cont-wrapper">
					<div class="goods-tab-cont">
						<div class="goods-other-list clf">
							<div class="goods-other-li" v-for="item in giftList">
								<div class="goods-item">
									<input class="checkbox" type="checkbox" @click.prevent="select(item)" />
									<a class="goods-item-img" href="javascript:;"><img :src="item.thumb" alt="" /></a>
									<strong>{{txtLimit(item.name)}}</strong>
									<div class="price">￥{{item.price}}</div>
									<div class="goods-action">
										x{{item.goods_number}}
									</div>
								</div>
							</div>
						</div>
						<ol></ol>
					</div>
					<div class="goods-tab-cont">
						<div class="goods-other-list clf">
							<div class="goods-other-li" v-for="item in exchangeList">
								<div class="goods-item">								
									<input class="checkbox" type="checkbox" @click.prevent="select(item)" />
									<a class="goods-item-img" href="javascript:;"><img :src="item.thumb" alt="" /></a>
									<strong>{{txtLimit(item.name)}}</strong>
									<div class="price">￥{{item.price}}</div>
									<div class="goods-action">
										x{{item.goods_number}}
									</div>
									<!-- <quantity :quantity.sync="item.goods_number"></quantity> -->
								</div>
							</div>
						</div>
						<ol></ol>
					</div>
				</div>
			</div>
		</div>

		<p class="p2">包邮提示：购买满200元包邮！(港澳台除外)</p>

		<div class="width-full goods-ft">
			<div class="check-all"><label @click.prevent="selectAll($event)"><input class="checkbox" type="checkbox" v-model="isSelectAll" /><em>全选</em></label></div>
			<div class="total">合计：<span>￥{{total_amount}}</span></div>
			<a class="btn" @click="toOrder">结算</a>
		</div>
	</div>
</template>

<style>
	.goods-tab-cont ol{min-height: 1.4rem; line-height: 0; padding: 0.4rem; text-align: center;}
	.goods-tab-cont ol li{width: 0.6rem; height: 0.6rem; margin: 0 0.15rem; border-radius: 100%; background: #b1b1b1; display: inline-block;}
	.goods-tab-cont ol li.on{background: #000;}
</style>
<style scoped>
	.tips{padding: 0 0.8rem; height: 2.4rem; background: #fff; border: 1px solid #e1e1e1; border-width: 1px 0; font-size: 0.9rem; color: #222; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center; position: fixed; z-index: 10;}
	.tips div{white-space: nowrap; text-overflow: ellipsis; overflow: hidden; flex: 1; -webkit-flex: 1; display: block;}
	.tips span{margin-left: 0.8rem;}

	.goods-list{padding: 2.4rem 0.6rem 0;}
	.goods-item{padding: 0.8rem 0; border-bottom: 1px solid #e1e1e1; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center; position: relative;}
	.goods-item .sale{width: 2rem; padding: 0.2rem 0 0; border-radius: 0 0 4px 4px; background: #da3737; font-weight: normal; font-size: 0.6rem; color: #fff; text-align: center; display: inline-block; position: absolute; left: 0; top: 0;}
	.goods-item .sale:after{content: ""; border: 1rem solid transparent; border-top-width: 0.3rem; border-bottom: 0.3rem solid #fff; display: block;}
	.goods-item .checkbox{display: block; margin-right: 0.8rem;}
	.goods-item-img{width: 5.6rem; height: 5.6rem; text-align: center; display: inline-block;}
	.goods-item-img img{max-height: 100%;}
	.goods-item-info{padding-left: 0.4rem; flex: 1; -webkit-flex: 1;}
	.goods-item-info strong{font-weight: normal; font-size: 1.2rem; margin-bottom: 0.6rem; cursor: pointer; display: block;}
	.goods-item .price{color: #c50007;}
	.goods-action{display: flex; display: -webkit-flex; align-items: flex-end; -webkit-align-items: flex-end;}
	.goods-action .price{flex: 1; -webkit-flex: 1; display: block;}
	.goods-action del{font-size: 0.8rem; color: #adadad; margin-left: 0.2rem;}
	.goods-item .btn-delete{width: 3rem; background: #c50007; font-size: 0.9rem; color: #fff; display: none;}

	.goods-list.edit{padding-top: 2.4rem;}
	.goods-list.edit .goods-item-info{overflow: hidden;}
	.goods-list.edit .goods-item-info strong{margin: 0; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;}
	.goods-list.edit .goods-action{display: block;}
	.goods-list.edit .goods-action .price{margin: 0.2rem 0;}
	.goods-list.edit .btn-delete{align-self: stretch; -webkit-align-self: stretch; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center; justify-content: center; -webkit-justify-content: center;}
	/*.goods-list.edit .btn-delete{align-self: stretch; -webkit-align-self: stretch; display: block; height: 100%;}*/

	.goods-tab{margin-top: 0.8rem;}
	.goods-tab-hd{margin: 0 0.8rem; border-bottom: 2px solid #000; display: flex; display: -webkit-flex; justify-content: flex-end; -webkit-justify-content: flex-end;}
	.goods-tab-hd a{width: 5.4rem; height: 1.8rem; margin-left: 0.4rem; font-size: 0.9rem; border: 1px solid #c4c4c4; border-bottom: 0; display: flex; display: -webkit-flex; justify-content: center; -webkit-justify-content: center; align-items: center; -webkit-align-items: center;}
	.goods-tab-hd a.on{background: #000; color: #fff;}
	.goods-tab-hd a .font{margin-right: 0.2rem; font-size: 1.1rem; display: block;}

	.goods-tab-bd{position: relative; overflow: hidden;}
	.tab-cont-wrapper{width: 200%; position: relative;}
	
	.goods-tab-cont{width: 50%; float: left; position: relative;}
	.goods-tab-cont .goods-other-li{position: relative; float: left;}

	.goods-tab-bd .goods-item{padding: 0.8rem; margin: 0 0 2px; background: #f4f4f4;}
	.goods-tab-bd .goods-item-img{width: 3rem; height: 3rem; margin-left: 0.4rem; background: #fff;}
	.goods-tab-bd .goods-item strong{padding: 0 0.6rem; font-weight: normal; font-size: 0.9rem; flex: 1; -webkit-flex: 1; display: block;}
	.goods-tab-bd .goods-item .price{width: 4rem;}
	.goods-tab-bd .goods-qty span{background: #fcfcfc;}

	.p2{line-height: 2.4rem; padding: 0 0.8rem; border: 1px solid #e1e1e1; border-width: 1px 0; font-size: 0.9rem; color: #222;}

	.goods-ft{width: 100%; height: 3rem; background: #fff; border-top: 1px solid #e1e1e1; display: table; position: fixed; bottom: 0;}
	.goods-ft > *{display: table-cell; vertical-align: middle;}
	.goods-ft .check-all{width: 25%; padding-left: 0.8rem;}
	.goods-ft .check-all input{vertical-align: middle; margin-right: 0.2rem;}
	.goods-ft .check-all em{line-height: 1; vertical-align: middle; font-size: 0.9rem;}
	.goods-ft .total{padding-right: 0.8rem; text-align: right;}
	.goods-ft .total span{color: #c50007;}
	.goods-ft .btn{width: 25%; background: #c50007;}
</style>