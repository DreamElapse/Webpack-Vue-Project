<script>
	import { user, isLogin } from 'vuex_path/getters.js';
	import { updateAppHeader } from 'vuex_path/actions.js';
	import Pay from './payment/pay.vue';

	export default {
		components: {
			Pay
		},
		vuex: {
			getters: {
				user,
				isLogin
			},
			actions: {
				updateAppHeader
			}
		},
		route: {
			data() {
				this.updateAppHeader({
					type: 2,
					content: '订单确认'
				});

				this.$http.post('/OnlinePayment/checkWechatPay.json').then((res) => {
					res = res.json();
					if(res.status == 1){
						this.supportwechatpay = res.data.result == 1 ? true : false;
						this.payType = res.data.result == 1 ? 18 : 4;
						this.payment = res.data.result == 1 ? 18 : 4;
						// 默认地址
						this.$http.post('/UserAddress/Defaults.json').then((res) => {
							res = res.json();
							if(res.status == 1){
								let address = res.data;
									if(address){
										this.defAddress = address;
									}
									this.getOrderDetail();						
							}else{
								this.getOrderDetail();
							}
						});
					}
				});
				

				// 商品列表
				this.$http.post('/OnlinePayment/getGoodsList.json').then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$set('goodsList', res.data);
					}
				});

				// 优惠券列表
				this.$http.post('/OnlinePayment/getBonusList.json').then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$set('coupons', res.data.list);
					}
				});
			}
		},
		ready() {
			$('.order-hd').on('click', function(){
				$(this).toggleClass('on');
			});
		},
		data() {
			return {
				pending: true,
				supportwechatpay:true,
				payType:4,
				// addressID: -1,
				defAddress: '',
				goodsList: [],
				coupons: [],
				couponCode: '',
				couponID: 0,
				remark: '',
				goods_price: 0,
				shipping_fee: 0,
				will_get_integral: 0,
				amount: 0,
				token: ''
			}
		},
		watch: {

			payType(val) {
				if(this.pending){
					return;
				}
				this.getOrderDetail();
			}
		},
		computed: {
			couponsQuantity() {
				let num = 0;
				for(let i of this.coupons){
					num += i.count;
				}
				return num;
			}
		},
		methods: {
			couponChange(e, val) {
				e.preventDefault();
				this.couponID = val;
				this.getOrderDetail();
			},
			applyCoupon() {
				this.$http.post('/OnlinePayment/checkBonus.json', {bonus_sn: this.couponCode}).then((res) => {
					res = res.json();
					if(res.status == 1){
						for(let i of this.coupons){
							if(i.type_id == res.data.type_id){
								this.couponID = res.data.type_id;
								break;
							}else{
								this.coupons.push(res.data);
								this.couponID = res.data.type_id;
								break;
							}
						}
						this.getOrderDetail();
						this.$dispatch('popup', '此优惠券可以使用');
					}else{
						this.couponCode = '';
						this.$dispatch('popup', res.msg);
					}
				});
			},
			getOrderDetail(payType) {
				// if(this.pending){
				// 	return;
				// }
				// this.pending = true;
				let data = {
					bonus_type: this.couponID,
					payment_id: this.$refs.pay.payType
				}
				if(payType){
					data.payment_id = payType;
				}
				if(this.defAddress.address_id != undefined){
					data.address_id = this.defAddress.address_id;
				}
				return this.$http.post('/OnlinePayment/Aggregate.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.pending = false;
						let r = res.data;
						this.goods_price = r.goods_price;
						this.shipping_fee = r.shipping_fee;
						this.will_get_integral = r.will_get_integral;
						this.amount = r.amount;
						this.token = r.token;
					}else{
						this.$dispatch('popup', res.msg);
					}
					this.pending = false;
				}, () => {
					this.pending = false;
				});
			}
		},
		events: {
			payType(msg) {
			
				this.payType = msg;
				
			 }
		}
	}
</script>

<template>
	<div class="container">
		<div v-show="pending" class="loading"></div>

		<dl class="order-item" style="margin-top:0;">
			<dt class="order-hd">
				<span>填写地址</span>
				<i class="font icon-arrow-bottom"></i>
			</dt>
			<dd>
				<a v-if="(typeof defAddress != 'object') || isLogin == false && (typeof defAddress != 'object')" class="address address-none" v-link="{ name: 'addressEdit', params: {id: 0} }">
					<label><i class="font icon-address"></i></label>
					<div>+ 添加地址</div>
				</a>
				<div v-else class="address">
					<label class="hl"><i class="font icon-address"></i>默认</label>
					<div>
						<p>收货人：<span>{{defAddress.consignee}}</span><span class="fr">{{defAddress.mobile}}</span></p>
						<p><span>收货地址：</span><span v-if="defAddress.attribute">[{{defAddress.attribute}}]</span><span>{{defAddress.province_name}}{{defAddress.city_name}}{{defAddress.district_name}}{{defAddress.town_name}}</span></p>
						<div class="action">
							<a v-link="{ name: 'addressEdit', params: {id: defAddress.address_id} }">修改</a>
							<a v-if="isLogin" v-link="{ name: 'address', params: {full: true} }">管理地址</a>
						</div>
					</div>
				</div>
			</dd>
		</dl>
		<dl class="order-item">
			<dt class="order-hd">
				<span>查看商品详情</span>
				<i class="font icon-arrow-bottom"></i>
			</dt>
			<dd>
				<ul class="goods-list">
					<li v-for="item in goodsList">
						<a class="goods-img" v-link="{ name: 'goodsDetail', params: {id: item.goods_id} }"><img v-lazy="item.goods_thumb" alt="" /></a>
						<div class="goods-detail">
							<p><span class="fl">{{item.goods_name}}</span><span>￥{{item.goods_price}}</span></p>
							<p><del>￥{{item.market_price}}</del></p>
							<p>x{{item.goods_number}}</p>
						</div>
					</li>
				</ul>
			</dd>
		</dl>
		<dl class="order-item">
			<dt class="order-hd">
				<span>优惠券</span><em>共{{couponsQuantity}}张可用</em>
				<i class="font icon-arrow-bottom"></i>
			</dt>
			<dd>
				<div class="order-list">
					<label class="item" v-for="item in coupons" @click="couponChange($event, item.type_id)">
						<b class="checkbox" :class="{selected: item.type_id == couponID}"></b><em>{{item.type_name}}</em><span>共{{item.count}}张</span>
					</label>
					<label class="item" @click="couponChange($event, 0)">
						<b class="checkbox" :class="{selected: couponID == 0}"></b><em>不使用优惠券</em>
					</label>
				</div>
				<div class="input-coupon">
					<div><label><em>输入优惠券编码</em><input type="text" placeholder="输入优惠券编码" v-model="couponCode" /></label></div>
					<a class="btn" href="javascript:;" @click="applyCoupon">确认</a>
				</div>
			</dd>
		</dl>
		<div class="order-item order-list">
			<div class="item">商品总计<span>￥{{goods_price}}</span></div>
			<div class="item">运费<span>￥{{shipping_fee}}</span></div>
			<div class="item textarea">备注<textarea v-model="remark"></textarea></div>
			<!-- <div class="item">总计<span class="hl"><strong>￥{{amount}}</strong>(获得{{will_get_integral}}积分)</span></div> -->
		</div>
		<pay v-ref:pay :pay-type.sync="payType" :amount="amount" :supportwechatpay="supportwechatpay" :will_get_integral="will_get_integral" :remark="remark" :token="token"></pay>
	</div>
</template>

<style scoped>
	.container{background: #f4f4f4;}
	.hl{color: #c50007;}

	.address{padding: 0.8rem 1rem 1rem; background: url(/public/images/payment/address_border.jpg) repeat-x 0 100%; background-size: auto 0.2rem; font-size: 0.8rem; display: flex; display: -webkit-flex; align-items: flex-start; -webkit-align-items: flex-start;}
	.address label{width: 4rem; text-align: left;}
	.address label .font{font-size: 1.6rem; display: block;}
	.address > div{flex: 1; -webkit-flex: 1;}
	.address .action{font-size: 0.9rem; text-align: right;}
	.address .action a{color: #929292; margin-left: 1rem;}
	.address-none{height: 5rem; font-size: 1rem; color: #929292; text-align: center; align-items: center;}

	.order-item{margin: 0.4rem 0; border: 1px solid #e1e1e1; border-width: 1px 0; background: #fff;}
	.order-hd.on .font{transform: rotate(-180deg);}
	.order-hd.on + dd{display: block;}
	.order-hd{height: 2.4rem; padding: 0 0.8rem; font-size: 0.9rem; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.order-hd span{padding-right: 0.8rem; flex: 1; -webkit-flex: 1; display: block;}

	.order-hd .font{margin-left: 0.8rem; transition: 0.6s ease-out;}
	.order-item dd{border-top: 1px solid #e1e1e1; display: none;}
	.order-item dd > :last-child{border-bottom: 0;}
	.order-item .order-list{border: 0;}

	.order-list{padding: 0 0.8rem; background: #fff; color: #696969;}
	.order-list .item{height: 2.4rem; border-bottom: 1px solid #e1e1e1; font-size: 0.9rem; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.order-list .item.textarea{height: 4.8rem;}
	.order-list .item.textarea textarea{height: 100%; padding: 0.4rem; margin-left: 3rem; border: 0; font-size: 1em; display: flex; display: -webkit-flex; flex: 1; -webkit-flex: 1; resize: none;}
	.order-list .item:last-child{border-bottom: 0;}
	.order-list label .checkbox{margin-right: 0.2rem;}
	.order-list .item span{text-align: right; flex: 1; -webkit-flex: 1; display: block;}

	.goods-list{padding: 0;}
	.goods-list li{padding: 0.8rem; background: #f8f8f8; border-bottom: 1px solid #e1e1e1; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.goods-list li:last-child{border-bottom: 0;}
	.goods-img{width: 4rem; height: 4rem; border: 1px solid #e1e1e1; background: #fff; text-align: center; display: block;}
	.goods-img img{max-height: 100%;}
	.goods-detail{padding-left: 1rem; font-size: 0.9rem; text-align: right; flex: 1; -webkit-flex: 1;}
	.goods-detail p:nth-child(n+2){color: #adadad;}

	.select-box{margin: 0; border-top: 0;}
	.select-box label{height: 2.4rem; padding: 0 0.8rem; margin-bottom: 0.2rem; background: #f8f8f8; display: flex; align-items: center;}
	.select-box label img{width: 1.4rem; margin-right: 0.4rem;}
	.select-box label span{font-size: 0.9rem; flex: 1;}
	.order-row{height: 2.4rem; padding: 0 0.8rem; border-bottom: 1px solid #e1e1e1; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.order-row span{font-size: 0.9rem; flex: 1; -webkit-flex: 1;}

	.order-total{width: 100%; height: 3rem; border: 1px solid #e1e1e1; border-width: 1px 0; background: #fff; display: table;}
	.order-total > *{display: table-cell; vertical-align: middle;}
	.order-total > div:first-child{padding-left: 0.8rem;}
	.order-total strong{font-size: 1rem;}
	.order-total .btn{width: 25%; background: #c50007;}

	.input-coupon{width: 100%; height: 2.4rem; border-top: 1px solid #e1e1e1; font-size: 0.9rem; display: table;}
	.input-coupon > *{display: table-cell; vertical-align: middle;}
	.input-coupon label{width: 100%; padding: 0 0.4rem 0 0.8rem; display: table;}
	.input-coupon label em{display: table-cell; vertical-align: middle; white-space: nowrap;}
	.input-coupon label input{width: 100%; border: 0; font-size: 1em; color: #c50007; text-align: right; vertical-align: middle;}
	.input-coupon .btn{width: 25%; background: #c50007; color: #fff; display: table-cell; vertical-align: middle;}
</style>