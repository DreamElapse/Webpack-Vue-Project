<script>
	import bankModel from 'bankModel.js';
	import { getCartGoodsQty } from 'vuex_path/actions.js';

	export default {
		mixins: [bankModel],
		vuex: {
			actions: {
				getCartGoodsQty
			}
		},
		ready() {
			// this.$http.post('/OnlinePayment/checkWechatPay.json').then((res) => {
			// 	res = res.json();
			// 	if(res.status == 1){
			// 		this.supportwechatpay = res.data.result == 1 ? true : false;
			// 		this.payType = res.data.result == 1 ? 18 : 4;
			// 	}
			// }, () => {
			// 		this.pending = false;
			// });
		},
		data() {
			return {
				pending: false,
				bankList: null
			}
		},
		props: {
			quickPay: {
				type: Boolean,
				default: false
			},
			supportwechatpay:'',
			// payType: {
			// 	default: 4
			// },
			payType:'',
			amount: {
				type: Number
			},
			will_get_integral: {
				type: Number
			},
			remark: {
				type: String
			},
			token: {
				type: String
			}
		},
		watch: {
			payType() {
				this.$dispatch('payType', this.payType) 

			}
		},
		methods: {
			submit() {
				this.pending = true;
				let data = {
					remark: this.remark,
					token: this.token
				}
				data.source_url = localStorage.source_url;
				this.$http.post('/OnlinePayment/CreateOrder.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$dispatch('popup', '提交成功');
						// this.$dispatch('payAction', res.data.payment_id, res.data.content);
						// this.payAction(res.data.payment_id, res.data.content);
						this.$emit('payAction', res.data.payment_id, res.data.content);
					}else{
						this.$dispatch('popup', res.msg);
					}
					this.pending = false;
				}, () => {
					this.pending = false;
				}).catch(() => {
					this.pending = false;
				});
			}
		},
		events: {
			payAction(id, content) {
				let payment = Vue.extend({
					template: content
				});
				new payment().$mount().$appendTo('#payAction', () => {
					switch (id){
						case 1:
							this.$route.router.go({name: 'paySuccess'});
							this.getCartGoodsQty(true);
							break;
						case 4:
							$('#alipaysubmit').submit();
							break;
						case 8:
							$('#kuaiqian_submit').click();
							break;
						case 18:
							$('#wechatpay_button').click();
							break;
						case 7:
							$('#tenpay_button').click();
							break;
						default:
							$('#tenpay_button').click();
							break;
					}
				});
			}
		}
	}
</script>

<template>
	<div v-show="pending" class="loading"></div>
	<dl class="payment-way">
		<dt class="pay-row" :class="{on:quickPay == true}">
			<span>选择支付方式</span>
		</dt>
		<dd>
			<dl class="pay-item select-box">
				<dt class="pay-row">
					<span>平台支付</span>
				</dt>
				<dd>
					<label v-if="!supportwechatpay">
						<img src="/public/images/payment/pay_zfb.png" alt="" />
						<span>支付宝支付</span><input class="checkbox" type="radio" value="4" v-model="payType" />
					</label>
					<label v-if="supportwechatpay">
						<img src="/public/images/payment/pay_wx.png" alt="" />
						<span>微信支付</span><input class="checkbox" type="radio" value="18" v-model="payType" />
					</label>
					<label>
						<img src="/public/images/payment/pay_kq.png" alt="" />
						<span>快钱支付 <i>(网银支付)</i></span><input class="checkbox" type="radio" value="8" v-model="payType" />
					</label>
					<label>
						<img src="/public/images/payment/pay_cft.png" alt="" />
						<span>财付通支付</span><input class="checkbox" type="radio" value="7" v-model="payType" />
					</label>
				</dd>
			</dl>
			<!-- <dl class="pay-item select-box">
				<dt class="pay-row">
					<span>网银支付</span>
					<i class="font icon-arrow-bottom"></i>
				</dt>
				<dd>
					<label v-for="item in bankList">
						<img :src="'/public/images/payment/' + item.icon" alt="" />
						<span>{{item.name}}</span><input class="checkbox" type="radio" :value="item.value" v-model="payType" />
					</label>
				</dd>
			</dl> -->
			<label v-if="quickPay != true" class="pay-row"><span>货到付款</span><input class="checkbox" type="radio" value="1" v-model="payType" /></label>
		</dd>
	</dl>
	<div v-if="quickPay != true" class="pay-submit">
		<div>合计：<strong class="hl">￥{{amount}}</strong><span>(获得{{will_get_integral}}积分)</span></div>
		<a class="btn" href="javascript:;" @click="submit">结算</a>
	</div>
	<div id="payAction" style="display:none;"></div>
</template>

<style scoped>
	.payment-way{border: 1px solid #e1e1e1; border-width: 1px 0; background: #fff;}
	.payment-way > dt{border: 0; font-weight: bold;}

	.pay-item{margin: 0.4rem 0;}
	.pay-row.on .font{transform: rotate(-180deg);}
	.pay-row{height: 2.4rem; padding: 0 0.8rem; border-top: 1px solid #e1e1e1; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}

	.pay-row span{font-size: 0.9rem; flex: 1; -webkit-flex: 1; display: block;}
	.pay-row .font{margin-left: 0.8rem; transition: 0.6s ease-out; display: table-cell; vertical-align: middle;}
	.pay-item dd{border-top: 1px solid #e1e1e1; }

	.select-box{margin: 0; border-top: 0;}
	.select-box label{height: 2.4rem; padding: 0 0.8rem; margin-bottom: 0.2rem; background: #f8f8f8; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.select-box label img{width: 1.4rem; margin-right: 0.4rem;}
	.select-box label span{font-size: 0.9rem; flex: 1; -webkit-flex: 1; display: block;}
	.select-box label span i {color:#f00; }
	/*.select-box label .checkbox{display: block;}*/

	.pay-submit{width: 100%; height: 3rem; border-bottom: 1px solid #e1e1e1; background: #fff; display: table;}
	.pay-submit > *{display: table-cell; vertical-align: middle;}
	.pay-submit > div:first-child{padding-left: 0.8rem;}
	.pay-submit strong{font-size: 1rem; color: #c50007;}
	.pay-submit span{font-size: 0.9rem; color: #c50007; margin-left: 0.4rem;}
	.pay-submit .btn{width: 30%; background: #c50007;}
</style>