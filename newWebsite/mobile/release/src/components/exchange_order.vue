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
		route:{
			data(){
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
						console.log(this.payType)
						console.log(this.supportwechatpay)
						// 默认地址
						this.$http.post('/UserAddress/Defaults.json').then((res) => {
							res = res.json();
							if(res.status == 1){
								let address = res.data;
								if(address){
									this.defAddress = address;
									this.address_id = address.address_id;
								}
								this.getOrderDetail();
							}else{
								this.getOrderDetail();
							}
						});
					}
				});
				// 商品列表
				let data = {
					exchange_id: this.$route.params.id
				}
				this.$http.post('/Integral/goodsInfo.json' , data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$nextTick(() => {
							this.$set('goodsList', res.data);
							this.exchange_id = res.data.exchange_id;
							this.price = res.data.price;
						});
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
				pending: false,
				supportwechatpay:true,
				payType: '',
				// addressID: -1,
				defAddress: '',
				goodsList: {},
				remark: '',
				amount: 0,
				show: false,
              	onCancel: false,
              	onOk: false,
				token: '',
				price:0,
				exchange_id:'',
				address_id:'',
				payment: ''

			}
		},
		watch: {
			payType(val) {
				this.getOrderDetail();
			}
		},
		methods: {
			op(type){
		        this.show = false
		        if(type == '1'){
		          if(this.onCancel) this.show = false;
		        }else{
		          this.addCodeCart()
		    	}
		        this.onCancel = false
		        this.onOk = false
		        
		        document.body.style.overflow = ''
		    },
			getOrderDetail(payType) {
				// if(this.pending){
				// 	return;
				// }
				// this.pending = true;
				// let data = {
				// 	bonus_type: this.couponID,
				// 	payment_id: this.$refs.pay.payType
				// }
				// if(payType){
				// 	data.payment_id = payType;
				// }
				// if(this.defAddress.address_id != undefined){
				// 	data.address_id = this.defAddress.address_id;
				// }
				// return this.$http.post('/OnlinePayment/Aggregate.json', data).then((res) => {
				// 	res = res.json();
				// 	if(res.status == 1){
				// 		let r = res.data;
				// 		this.goods_price = r.goods_price;
				// 		this.shipping_fee = r.shipping_fee;
				// 		this.will_get_integral = r.will_get_integral;
				// 		this.amount = r.amount;
				// 		this.token = r.token;
				// 	}else{
				// 		this.$dispatch('popup', res.msg);
				// 	}
				// 	this.pending = false;
				// }, () => {
				// 	this.pending = false;
				// });
			},
			submit() {
				this.show = true;
			},
			addCodeCart(){
				this.pending = true;
				let data = {
					exchange_id: this.exchange_id,
					address_id: this.address_id,
					payment_id: this.payType,
					remark: this.remark
				}

				this.$http.post('/Integral/createOrder.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						if( res.data.content == '' || res.data.content == null ){
							this.$route.router.go({name: 'paySuccess'});
						}else{
							this.$dispatch('popup', '正在跳转到支付页面~~请耐心等候');
							this.$refs.pay.$emit('payAction', res.data.payment_id, res.data.content);
						}

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
					<li>
						<a class="goods-img" v-link="{ name: 'pointsGood', params: {id: goodsList.exchange_id} }"><img v-for="item in goodsList.image" :src="item.img_url" alt="" /></a>
						<div class="goods-detail">
							<p><span class="fl">{{goodsList.goods_name}}</span><span>￥{{goodsList.shop_price}}</span></p>
							<p><del>￥{{goodsList.market_price}}</del></p>
							<p>x1</p>
						</div>
					</li>
				</ul>
			</dd>
		</dl>
		<div class="order-item order-list">
			<div class="item">商品总计<span><p class="goods-points">{{goodsList.point}}积分 + ￥{{goodsList.price}}元</p></span></div>
			<div class="item">运费<span>￥{{goodsList.shipping_fee}}元</span></div>
			<div class="item textarea">备注<textarea v-model="remark"></textarea></div>
		</div>
		<pay v-ref:pay :quick-pay="true" :pay-type.sync="payType" :supportwechatpay="supportwechatpay"></pay>
		<div class="sub-btn"><a class="btn btn-block" href="javascript:;" @click="submit">确认提交</a></div>

		<div class="modal-mask modal-transition" v-show="show">
	        <div class="modal-confirm">
	            <h2 class="confirm-header">
	                <i class="iconfont icon-questioncircle"></i> 你确定要兑换吗?
	            </h2>
	            <div class="confirm-content">
	                确定兑换后将立即扣掉你现有的 <i>{{goodsList.point}}积分</i> 哦~
	            </div>
	            <div class="confirm-btns">
	                <button class="btn" @click="op(1)">取 消</button>
	                <button class="btn btn-primary" @click="op(2)">确定兑换</button>
	            </div>
	        </div>

	    </div>
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
.sub-btn{background:#fff;margin:0.4rem 0;padding:1rem 0;text-align:center;}
.btn-block{width: 80%;line-height: 2.6rem;background: #c50007;color: #fff;text-align: center;display: inline-block;}


.confirm-header{font-size: 12px;color:#666}
.confirm-btns .btn{display:inline-block;margin-bottom:0;font-weight:500;text-align:center;cursor:pointer;border:1px solid #d9d9d9;white-space:nowrap;line-height:1.5;padding:2px 14px;font-size:14px;border-radius:6px;-webkit-user-select:none;user-select:none;color:#666;outline:0;background-color:#fff}
.confirm-btns .btn-primary{color:#fff;background-color:#2db7f5;border-color:#2db7f5}
.modal-mask{position:fixed;top:0;left:0;right:0;bottom:0;background-color:rgba(55,55,55,.6);z-index:10000;display:flex;align-items:center;justify-content:center}
.modal-box,.modal-confirm,.popover-box{box-sizing:border-box;background-color:#fff}
.confirm-content{padding-top:30px;padding-bottom:30px;font-size:12px;color:#666}
.modal-confirm .confirm-content i {color:#f60;font-size:12px;}
.modal-confirm{width:400px;padding:30px 40px;border-radius:6px;transition:transform .3s ease}
.modal-transition,.popover-transition{transition:all .3s ease}
.modal-confirm i{color:#fa0;font-size:24px;position:relative;top:2px}
.modal-confirm .confirm-btns{text-align:right}
.modal-box{width:520px;border-radius:6px}
@media only screen and (max-width:640px){.modal-box,.modal-confirm{width:100%;margin:0 20px}
.modal-confirm{padding:10px 20px}
}
.modal-header{padding:13px 18px 14px 16px;border-bottom:1px solid #e9e9e9;position:relative}
.modal-header i,.popover-wrap{position:absolute}
.modal-header i{right:20px;top:15px;font-size:14px}
.modal-header h3{font-size:14px}
.modal-body{padding:16px}
.modal-footer{padding:10px 18px 10px 10px;border-top:1px solid #e9e9e9;background:#fff;border-radius:0 0 6px 6px;text-align:right}
.modal-enter .modal-box,.modal-enter .modal-confirm,.modal-leave .modal-box,.modal-leave .modal-confirm{transform:scale(1.1)}
.popover-box{min-width:177px;background-clip:padding-box;border:1px solid #d9d9d9;border-radius:6px;box-shadow:0 1px 6px rgba(99,99,99,.2);position:relative}
.popover-title{padding:0 16px;line-height:32px;height:32px;border-bottom:1px solid #e9e9e9;color:#666}
.popover-content{padding:8px 16px;color:#666}
.popover-arrow{position:absolute;width:0;height:0;border:5px solid transparent;left:50%;margin-left:-5px}
.popover-arrow:after{content:" ";margin-left:-4px;border:4px solid transparent;position:absolute;width:0;height:0}
.popover-arrow-top{border-bottom-width:0;border-top-color:#d9d9d9;bottom:-5px}
.popover-arrow-top:after{border-top-color:#fff;bottom:-3px}
.popover-arrow-bottom{border-top-width:0;border-bottom-color:#d9d9d9;top:-5px}
.popover-arrow-bottom:after{border-bottom-color:#fff;top:-3px}
.table{width:100%;margin-bottom:24px;border-spacing:0;border-collapse:collapse;border:1px solid #ddd}
.table tbody th,.table td{border-top:1px solid #ddd}
.table thead th{border-bottom:2px solid #ddd}
.table td,.table th{text-align:left;padding:8px;border-left:1px solid #ddd}
caption{padding-top:8px;padding-bottom:8px;color:#777;text-align:left}
.table-hover tbody tr:hover,.table-striped tbody tr:nth-of-type(odd){background-color:#f9f9f9}
.table-sm td,.table-sm th{padding:5px}
.table-responsive{overflow:scroll}
.table-responsive thead tr{white-space:nowrap}
.table-responsive thead th{min-width:75px}
.table-responsive tbody td{white-space:nowrap;text-overflow:ellipsis;overflow:hidden}
.pagination{float:right}
.pagination:after{content:' ';clear:both}
.pagination li{float:left;border-radius:6px;-webkit-user-select:none;user-select:none;min-width:28px;height:28px;border:1px solid #d9d9d9;background-color:#fff;text-align:center;line-height:28px;margin:0 5px}
.pagination a{color:#666;padding:0 6px;display:inline-block}
.pagination .next:before{content:"\E600"}
.pagination .active{background-color:#2db7f5;color:#fff}
.pagination .active a{color:#fff;cursor:default}
.pagination .jump{border:none}
.pagination .jump:before{content:"\2022\2022\2022";letter-spacing:2px}
.modal-enter, .modal-leave {opacity: 0;}
.modal-transition{transition: all .3s ease;}
.modal-enter .modal-confirm,.modal-leave .modal-confirm {transform: scale(1.1);}
.modal-transition{transition: all .3s ease;}
</style>