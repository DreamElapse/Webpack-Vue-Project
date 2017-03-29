<script>
	import { updateAppHeader } from 'vuex_path/actions.js';
	import Pay from './pay.vue';

	export default {
		components: {
			Pay
		},
		vuex: {
			actions: {
				updateAppHeader
			}
		},
		ready() {
			this.updateAppHeader({
				type: 1
			});
			
		},
		route:{
			data(){
				this.$http.post('/OnlinePayment/checkWechatPay.json').then((res) => {
					res = res.json();
					if(res.status == 1){
						this.supportwechatpay = res.data.result == 1 ? true : false;
						// this.payment = res.data.result == 1 ? 18 : 4;
						this.payType = res.data.result == 1 ? 18 : 4;
						this.pay_param = this.$route.query.pay_param;
						if(this.pay_param) {
							let data = {
								param_code: this.pay_param
							}
							this.$http.post('/OfflinePayment/getOrderInfo.json', data).then((res) => {
								res = res.json();
								if(res.status == 1){
									this.order_sn = res.data.orderSn;
									this.payamount = res.data.payAmount;
									this.payerName = res.data.payerName;
								}else{
									this.isFailure = true;
									this.failureMsg = res.msg;
								}
							});
						}
					}
				});
			}
		},
		data() {
			return {
				isFailure: false,
				failureMsg: '',
				loading: false,
				supportwechatpay:true,
				pay_param: '',
				order_sn: '',
				payamount: '',
				payerName: '',
				payerTelephone: '',
				orderList: [],
				selectedOrder: '',
				payment: 4,
				payType: 4
			}
		},
		computed: {
			readonly() {
				return (this.pay_param || this.orderList.length) ? true : false;
			}
		},
		watch: {
			selectedOrder(v) {
				if(!v) {
					this.order_sn = '';
					this.payamount = '';
					this.payerName = '';
				}
			}
		},
		methods: {
			test_order_sn() {
				if (this.order_sn == '') {
					this.$dispatch('popup', '订单编号不能为空');
					return false;
				}
				return true;
			},
			test_payerName() {
				if (this.payamount == '') {
					this.$dispatch('popup', '支付金额不能为空');
					return false;
				}
				return true;
			},
			test_payamount() {
				if (this.payerName == '') {
					this.$dispatch('popup', '收货人不能为空');
					return false;
				}
				return true;
			},
			test_payerTelephone() {
				if (this.payerTelephone == '') {
					this.$dispatch('popup', '手机号码不能为空');
					return false;
				}
				if (!/^1[34578]\d{9}$/.test(this.payerTelephone)) {
					this.$dispatch('popup', '手机号码格式不正确');
					return false;
				}
				return true;
			},
			validate() {
				for (let i of ['order_sn', 'payerName', 'payamount', 'payerTelephone']) {
					if (!this['test_' + i]()) {
						return false;
					}
				}
				return true;
			},
			getOrderList() {
				if(this.pay_param){
					return;
				}
				if(this.test_payerTelephone() == false){
					return;
				}
				this.selectedOrder = 0;
				this.loading = true;
				let data = {
					mobile: this.payerTelephone
				}
				this.$http.post('/OfflinePayment/getOrderSnList.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.orderList = res.data;
					}
					this.loading = false;
				}, () => {
					this.loading = false;
				});
			},
			changeOrderInfo() {
				this.order_sn = this.selectedOrder.orderSn || this.selectedOrder.order_sn;
				this.payerName = this.selectedOrder.consignee;
				this.payamount = this.selectedOrder.order_amount;
			},
			submit() {
				if(this.validate() == false){
					return;
				}
				let data = {
					order_sn: this.order_sn,
					payamount: this.payamount,
					payerName: this.payerName,
					payerTelephone: this.payerTelephone,
					payment: this.payType
				}
				this.$http.post('/OfflinePayment/Create.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$refs.pay.$emit('payAction', res.data.payment, res.data.content);
					}
				});
			}
		}
	}
</script>

<template>
	<div class="container">
		<div v-show="loading" class="loading"></div>

		<div v-if="!isFailure">
			<div style="padding-bottom:0.4rem; background:#f8f8f8;">
				<h2>快捷下单</h2>
				<div class="pay-txt">
					<i class="font icon-msg-notice"></i><p><strong>等待买家付款!</strong> 目前库存紧张，要抓紧时间抢货！</p>
				</div>
			</div>
			<div class="order-form">
				<div v-if="pay_param" class="input-item">
					<label>订单编号:</label><input type="text" v-model="order_sn" :value="order_sn.split('_')[0]" readonly="{{readonly}}" />
				</div>
				<div v-else class="input-item">
					<label>订单编号:</label>
					<select v-model="selectedOrder" @change="changeOrderInfo()">
						<option :value="0">选择订单号</option>
						<option v-for="item in orderList" :value="item">{{item.order_sn.split('_')[0]}}</option>
					</select>
				</div>
				<div class="input-item">
					<label>支付金额:</label><input type="text" v-model="payamount" readonly="{{readonly}}" />
				</div>
				<div class="input-item">
					<label>收 货 人:</label><input type="text" v-model="payerName" readonly="{{readonly}}" />
				</div>
				<div class="input-item">
					<label>手机号码:</label><input type="text" v-model="payerTelephone" @change="getOrderList" />
				</div>
			</div>
			<h2>在线支付选择</h2>
			<pay v-ref:pay :quick-pay="true" :pay-type.sync="payType" :supportwechatpay="supportwechatpay"></pay>
			<div class="shop-guide">
				<a v-link="{ name: 'payGuide'}" id="shopping-guide"><i class="font icon-shop-help"></i><span>购物指南</span></a>
			</div>
			<a class="btn btn-block" href="javascript:;" @click="submit">确认提交</a>
		</div>

		<div v-else>
			<div class="tips">
				<img src="/public/images/payment/tips.jpg" alt="">
				<p>{{failureMsg}}</p>
			</div>
		</div>

		<div v-if="isFailure" class="shop-guide">
			<a v-link="{ name: 'payGuide'}" id="shopping-guide"><i class="font icon-shop-help"></i><span>购物指南</span></a>
		</div>
		<footer class="footer">
			<section>
				 <!-- 二维码 -->
	            <div class="cj_wx">
	                <div class="cj_com">
	                    <div class="cj_left">
	                        <span><img src="/public/images/index/cj_wx.jpg" alt="" /></span>
	                    </div>
	                    <div class="cj_right">
	                        <h4>扫一扫 查物流</h4>
	                        <div class="cj_check">
	                            <p><span>beautifulchnskin</span><br />关注微信号查物流</p>
	                            <!--<div class="box_img"><img src="/public/images/common/cj_box.png" alt=""></div> -->
	                        </div>
	                    </div>
	                </div>
	            </div>
            	<!-- 二维码 -->
            	 <div class="copyright">
		            <p>公司名：广州瓷肌化妆品有限公司</p>
		            <p>热线电话：400-1020-398</p>
		        </div>
			</section>
		</footer>
	</div>
</template>

<style scoped>
	h2{line-height: 2.6rem; border-top: 1px solid #e1e1e1; background: #f8f8f8; text-align: center;}
	.pay-txt{height: 2.6rem; padding: 0 0.8rem; border: 1px solid #e1e1e1; border-width: 1px 0; background: #fff; font-size: 0.9rem; color: #ca1f28; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.pay-txt .font{font-size: 1rem; margin-right: 0.4rem;}
	.pay-txt strong{font-size: 0.9rem;}
	.order-form{padding: 1rem 0.8rem 0; border-top: 1px solid #e1e1e1; background: #fff; font-size: 0.9rem;}
	.input-item{padding-bottom: 1rem; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.input-item label{width: 5rem; padding-right: 0.8rem; color: #787878; text-align: right; display: inline-block;}
	.input-item input{height: 2.2rem; border: 1px solid #cdcdcd; font-size: 1em; text-indent: 0.4rem; flex: 1; -webkit-flex: 1; display: block;}
	.input-item input[readonly]{background: #f8f8f8;}
	.input-item select{height: 2.2rem; font-size: 1em; flex: 1; -webkit-flex: 1; display: block;}

	.btn-block{width: 80%; line-height: 2.6rem; margin: 1rem auto; background: #c50007; color: #fff; text-align: center; display: block;}
	
	.shop-guide{background: #f6f6f6; padding: 0.5rem 0 0.5rem;}
	#shopping-guide{height: 2.4rem; padding: 0 0.8rem; background: #fff; font-size: 1rem; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	#shopping-guide i{margin-right: 0.2rem;}

	.tips{padding-top: 3rem; background: #f3f2f2;}
	.tips p{padding: 2rem 0 3rem; text-align: center;}
	
	 /* 底部二维码 */
    .cj_wx{width: 95%; margin :2em auto 0; padding: 0.5em;}
    .cj_com{width: 100%; overflow: hidden; display: table;}
    .cj_com .cj_left{width: 45%; padding: 0.5em; text-align: center; display: table-cell;}
    .cj_com .cj_right{width: 59%; padding: 0 0.5em; display: table-cell; vertical-align: middle;}
    .cj_wx .cj_left span{display:inline-block;width:100%;}
    .cj_wx .cj_left span img{width:100%;max-width:200px;}
    .cj_com .cj_right .cj_check{width:100%;overflow: hidden;}
    .cj_com .cj_right h4{color: #000;font-size: 1.5em;padding:0.5em 0; text-align:center;}
    .cj_com .cj_right p{font-size:1em;color:#b8b8b8; text-align:center; padding-right: 0.1em;line-height: 1.4em;}
    .cj_com .cj_right span{color:#ec4270; width:9em; display:inline-block; font-size:1.2em; }
    /* 版权 */
    .copyright{overflow: hidden;padding:0.4em 0;text-align: center;background:#f1f1f1;}
    .copyright p{font-size: 0.8em;color:#a3a3a3;height:1.2em;line-height: 1.2em;}
</style>