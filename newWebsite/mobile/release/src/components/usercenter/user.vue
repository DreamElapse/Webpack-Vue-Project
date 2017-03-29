<script>

	import { isLogin } from 'vuex_path/getters.js';
	import { updateAppHeader, logout } from 'vuex_path/actions.js';

	export default {
		vuex: {
			getters: {
				isLogin
			},
			actions: {
				updateAppHeader,
				logout
			}
		},
		route: {
            data() {
                document.title = "会员中心";
				
					
    			this.updateAppHeader({
                    type: 1
                });

                this.$watch('isLogin', (val) => {
    				if(val == true){
    					this.getUserInfo();
    				}else{
    					this.$route.router.replace({name: 'login'});
    				}
    			}, {
    				immediate: true
    			});
            }
        },
        data(){
        	return{
        		loading: true,
        		orderList:[],
        		unPayNum:0,
        		unShipNum:0,
        		shipNum:0,
        		finishShipNum:0,
        		toUnPay:false,
        		toUnShip:false,
        		toShip:false,
        		toFinish:false,
        		user: {}
        	}
        },
        methods:{
        	getUserInfo() {
				this.loading = true;
				return this.$http.post('/Global/getUserInfo.json').then((res) => {
					res = res.json();
					if(res.status == 1){
						this.user = res.data;
						this.getUnPayNum();
						this.getUnShipNum();
						this.getShipNum();
						this.getFinishShipNum();
					}
					this.loading = false;
				}, () => {
					this.loading = false;
				});
			},
			getUnPayNum(){
				let data = {
					status:1
				}
				this.$http.post('/Order/Lists.json',data).then((res) => {
				res = res.json();
					if(res.status == 1){
						this.orderList = res.data.list;
						this.unPayNum = res.data.total;
						if(this.unPayNum > 0){
							this.toUnPay = true;
						}
					}
				})
			},
			getUnShipNum(){
				let data = {
					status:3
				}
				this.$http.post('/Order/Lists.json',data).then((res) => {
				res = res.json();
					if(res.status == 1){
						this.orderList = res.data.list;
						this.unShipNum = res.data.total;
						if(this.unShipNum > 0){
							this.toUnShip = true;
						}
					}
				})
			},
			getShipNum(){
				let data = {
					status:4
				}
				this.$http.post('/Order/Lists.json',data).then((res) => {
				res = res.json();
					if(res.status == 1){
						this.orderList = res.data.list;
						this.shipNum = res.data.total;
						if(this.shipNum > 0){
							this.toShip = true;
						}
					}
				})
			},
			getFinishShipNum(){
				let data = {
					status:5
				}
				this.$http.post('/Order/Lists.json',data).then((res) => {
				res = res.json();
					if(res.status == 1){
						this.orderList = res.data.list;
						this.finishShipNum = res.data.total;
						if(this.finishShipNum > 0){
							this.toFinish = true;
						}
					}
				})
			},
			getOrderList(){
				this.$http.post('/Order/Lists.json',data).then((res) => {
				res = res.json();
					if(res.status == 1){
						this.orderList = res.data.list;
					}
				})
			}
        }
	}
</script>

<template>
<div class="container">
<div v-show="loading" class="loading"></div>
	<div class="content">
		<div class="content-body">
		<!-- 用户等级 -->
			<div class="custom-level">
				<img v-if="user.level > 0" :src="'/public/images/user/card_0' + user.level +'.jpg'" alt="">
				<div class="custom-level-title-section js-custom-level-title-section">
					<h5 class="custom-level-title">尊贵的 <span class="js-custom-level">{{user.mobile}}</span>
						<br>您拥有本店积分：                
						<span class="js-custom-point">{{user.points_left}}</span>
					</h5>
				</div>
			</div>
		<!-- 用户等级 -->
		<!-- 订单信息 -->
			<div class="order-related">
				<ul class="uc-order list-horizon clearfix">
					<li>
			            <a class="link clearfix relative link-topay" v-link="{ name: 'orderAll',params: {id: 1} }">
			            	<span class="title-num" :class="{'active':toUnPay}">{{unPayNum}}</span>
			             	<p class="title-info c-black font-size-12">待付款</p>
			            </a>
			        </li>
			        <li>
			            <a class="link clearfix relative link-tosend" href="javascript:;" v-link="{ name: 'orderAll',params: {id: 3} }" >
			            	<span class="title-num" :class="{'active':toUnShip}">{{unShipNum}}</span>
			              	<p class="title-info c-black font-size-12">待发货</p>
			            </a>
			        </li>
			        <li>
			            <a class="link clearfix relative link-send" href="javascript:;" v-link="{ name: 'orderAll',params: {id: 4} }" >
			            	<span class="title-num" :class="{'active':toShip}">{{shipNum}}</span>
			                <p class="title-info c-black font-size-12">已发货</p>
			            </a>
			        </li>
			        <li>
			            <a class="link clearfix relative link-sign" href="javascript:;" v-link="{ name: 'orderAll',params: {id: 5} }" >
			            	<span class="title-num" :class="{'active':toFinish}">{{finishShipNum}}</span>
			                <p class="title-info c-black font-size-12">已完成</p>
			            </a>
			        </li>

				</ul>
				<div class="block block-list list-vertical">
			        <a class="block-item link clearfix ico-order" v-link="{ name: 'orderAll' ,params: {id: 0} }">
			            <p class="title-info c-black font-size-14">全部订单</p>
			        </a>
			    </div>
			    <div class="block block-list list-vertical">
					<!-- <a class="block-item link clearfix ico-record" v-link="{ name: 'trades' }"> -->
					<!-- <a class="block-item link clearfix ico-record" v-link="{ name: 'orderAll' ,params: {id: 0} }">
					<p class="title-info c-black font-size-14">我的购物记录</p>
					</a> -->
					<!-- <a class="block-item link clearfix ico-backs" href="javascript:;">
					<p class="title-info c-black font-size-14">我的返现</p>
					</a> -->
					<a class="block-item link clearfix ico-wish" v-link="{ name: 'myfocu' }" >
					<p class="title-info c-black font-size-14">我的收藏</p>
					</a>
				</div>
				<!-- <div class="block block-list list-vertical">
					<a class="block-item link clearfix ico-saler-center" href="javascript:;">
					<p class="title-info c-black font-size-14">销售员中心</p>
					</a>
				</div> -->
				<div class="block block-list list-vertical">
					<a class="block-item link clearfix ico-pointsstore" v-link="{ name: 'promocodes'}">
					<p class="title-info c-black font-size-14">我的积分</p>
					</a>
					<!-- <a class="block-item link clearfix ico-gift" href="javascript:;">
					<p class="title-info c-black font-size-14">我收到的礼物</p>
					</a> -->
					<a class="block-item link clearfix ico-coupon" v-link="{ name: 'coupons'}" >
					<p class="title-info c-black font-size-14">我的优惠券</p>
					</a>
					<a class="block-item link clearfix ico-coupon" v-link="{ name: 'coupons'}" >
					<p class="title-info c-black font-size-14">我的成长计划</p>
					</a>
					<!-- <a class="block-item link clearfix ico-promocode" href="javascript:;">
					<p class="title-info c-black font-size-14">我的优惠码</p>
					</a> -->
					<!-- <a class="block-item link clearfix ico-present" href="javascript:;">
					<p class="title-info c-black font-size-14">我的赠品</p>
					</a> -->
				</div>
				<div class="block block-list list-vertical">
					<a class="block-item link clearfix ico-address" v-link="{ name: 'address'}">
			            <p class="title-info c-black font-size-14">管理我的地址</p>
			        </a>
			         <a class="block-item link clearfix ico-manage" v-link="{ name: 'buyer'}">
			            <p class="title-info c-black font-size-14">管理账号</p>
			        </a>
			    </div>
			</div>
			<!-- 订单信息 -->
			<div class="custom-line-wrap"><hr class="custom-line"></div>
		</div>
	</div>
</div>
</template>

<style scoped>
.container {background-color: #f8f8f8;}
.clearfix {zoom: 1;}
.hide {display: none !important;visibility: hidden;}
.font-size-12 {font-size: 12px !important;}
.c-black {color: #333 !important;}
.relative {position: relative;}
.font-size-14 {font-size: 14px !important;}
.c-black {color: #333 !important;}
.custom-level {width: 100%;min-height: 220px;overflow: hidden;position: relative;background-image: #f8f8f8;background-size: 6px 6px;text-align: center;margin-top: 0.5rem;}
.custom-level img{width: 96%;}
.custom-level-title-section {position: absolute;bottom: 82px;left: 0;min-height: 26px;margin: 0 auto;width: 100%;z-index: 10;/* background-color: rgba(51,51,51,0.8); */}
.custom-level-title {color: #fff;font-size: 15px;padding: 5px 15px;line-height: 1.5;margin: 0;text-align: left;}
.order-related {margin-bottom: 12px;}
.order-related .uc-order {width: 100%;padding: 10px 0;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;background: #fff;}
.order-related .uc-order.list-horizon {padding: 10px 0;}
.order-related .uc-order.list-horizon>li {display: inline-block;width: 25%;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;float: left;}
.order-related .uc-order.list-horizon .link {display: block;padding: 42px 0 10px;background-size: 24px 24px;background-repeat: no-repeat;background-position: center 12px;}
.order-related .uc-order.list-horizon .link-topay{background-image: url(/public/images/user/icon_topay@2x.png);}
.order-related .uc-order.list-horizon .link-totuan{background-image: url(/public/images/user/icon_daijiedan@2x.png);}
.order-related .uc-order.list-horizon .link-tosend{background-image: url(/public/images/user/icon_tosend@2x.png);}
.order-related .uc-order.list-horizon .link-send{background-image: url(/public/images/user/icon_send@2x.png);}
.order-related .uc-order.list-horizon .link-sign{background-image: url(/public/images/user/icon_sign@2x.png);}
.order-related .uc-order.list-horizon .title-num{position: absolute;left: 50%;top: 2px;height: 20px;line-height: 15px;padding: 0 5px;margin-left: 1px;border-radius: 10px;border: 2px solid #fff;font-size: 10px;color: #fff;background-color: #f76161;display: none;}
.order-related .uc-order.list-horizon .title-num.active{display:block;}
.order-related .uc-order.list-horizon .title-info {text-align: center;line-height: 20px;}

.block{display: block;border-top: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5;background-color: #fff;}
.block.block-list {margin: 0;padding: 0 0 0 10px;list-style: none;font-size: 14px;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;}
.block, .block-item{display: block;}
.block, .block p, .block-item{overflow: hidden;}
.block-item{position: relative;line-height: 1.5;border: 0;border-top: 1px solid #e5e5e5;}
.block-list>.block-item {padding: 10px 10px 10px 0;}
.block-list>.block-item:first-child {border-top: 0 none;}
.order-related .block.block-list.list-vertical>a.link {padding-left: 38px;padding-right: 28px;}
.order-related .block.block-list.list-vertical>a.link::before {content: '';position: absolute;top: 8px;left: 5px;width: 25px;height: 25px;background-image: url(/public/images/user/uc_icon.png);background-repeat: no-repeat;background-size: 18px 218px;}
.order-related .block.block-list.list-vertical>a.link.ico-order::before {background-position: 3px 4px;}
.order-related .block.block-list.list-vertical>a.link p::after {content: '';position: absolute;width: 7px;height: 7px;border-top: 2px solid #cbc9cc;border-right: 2px solid #cbc9cc;-webkit-transform: rotate(45deg);-moz-transform: rotate(45deg);-ms-transform: rotate(45deg);transform: rotate(45deg);top: 16px;right: 12px;}

.block.block-list+.block.block-list {margin-top: 12px;}
.order-related .block.block-list.list-vertical>a.link.ico-record::before {background-position: 3px -18px;}
.order-related .block.block-list.list-vertical>a.link.ico-backs::before {background-position: 3px -41px;}
.order-related .block.block-list.list-vertical>a.link.ico-wish::before {background-position: 3px -63px;}
.order-related .block.block-list.list-vertical>a.link.ico-saler-center::before {background-image: url(/public/images/user/sale_new.png);background-size: 19px 18px;left: 6px;top: 11px;}
.order-related .block.block-list.list-vertical>a.link.ico-pointsstore::before {background-image: url(/public/images/user/icon_cout.png);background-size: 19px 18px;left: 6px;top: 12px;}
.order-related .block.block-list.list-vertical>a.link.ico-gift::before {background-position: 3px -108px;}
.order-related .block.block-list.list-vertical>a.link.ico-coupon::before {background-position: 3px -130px;}
.order-related .block.block-list.list-vertical>a.link.ico-promocode::before {background-position: 3px -153px;}
.order-related .block.block-list.list-vertical>a.link.ico-present::before {background-position: 3px -176px;}
.order-related .block.block-list.list-vertical>a.link.ico-manage::before {background-position: 3px -198px;}
.order-related .block.block-list.list-vertical>a.link.ico-address::before {background-image: url(/public/images/user/icon_address.png);background-size: 19px 18px;left: 6px;top: 12px;}

.custom-line-wrap {height: 30px;position: relative;background: #fff;}
.custom-line {border: 0 none;border-top: 1px dashed #bbb;margin: auto;padding: 0;height: 0;width: 100%;position: absolute;left: 0;top: 0;right: 0;bottom: 0;}


.custom-richtext{padding: 0 10px;padding-top: 10px;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;font-size: 16px;color: #333;line-height: 1.5;overflow: hidden;text-align: left;word-wrap: break-word;position: relative;background: #fff;}
.custom-richtext p {margin: 0 0 1em 0;}
.custom-richtext strong, .custom-richtext b {font-weight: bold;}

.sc-goods-list {font-size: 12px;padding: 5px;list-style: none;margin: 0;}
.sc-goods-list .goods-card{position: relative;}
.sc-goods-list.pic .goods-card.small-pic{width: 50%;float: left;}
.sc-goods-list.pic .goods-card.small-pic.card {margin: 4px 0;}
.sc-goods-list .link {display: block;background: #fff;min-height: 100px;}
.sc-goods-list.pic .goods-card.small-pic.card .link {border-top: 1px solid #e5e5e5;border-right: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5;border-left: 1px solid #e5e5e5;margin: 0 4px;}

.sc-goods-list .photo-block {text-align: center;overflow: hidden;position: relative;background-color: #f8f8f8;background-size: 6px 6px;}
.sc-goods-list .photo-block img{position: absolute;left: 0;right: 0;top: 0;bottom: 0;margin: auto;vertical-align: bottom;max-width: 100%;max-height: 100%;}
.sc-goods-list .info {position: relative;}
.sc-goods-list.pic .goods-card .info {padding-left: 4px;margin-top: 10px;}
.sc-goods-list.pic .goods-card.small-pic .info {font-size: 13px;}
.sc-goods-list.pic .goods-card.small-pic.card .info {min-height: 25px;}

.sc-goods-list .info p {margin: 0px;color: #333;line-height: 1.3;margin-bottom: 5px;overflow: hidden;word-break: break-all;}

.sc-goods-list .info p.goods-price {font-weight: bold;padding: 0px;}
.sc-goods-list.pic .goods-card .info .goods-price {float: left;margin: 0 10px 10px 0;}
.sc-goods-list.pic .goods-card.small-pic.card .info .goods-price {margin-top: 5px;}
.sc-goods-list .info p.goods-price>em {font-style: normal;color: #ff6600;}

.sc-goods-list .goods-buy {position: absolute;}
.sc-goods-list .goods-buy.btn1 {right: 10px;bottom: 8px;height: 25px;width: 30px;background-position: 0 0;}
.sc-goods-list .goods-buy.btn1, .sc-goods-list .goods-buy.btn2, .sc-goods-list .goods-buy.btn3, .sc-goods-list .goods-buy.btn4 {background-image: url(/public/images/user/showcase.png);background-repeat: no-repeat;background-size: 40px auto;}





</style>