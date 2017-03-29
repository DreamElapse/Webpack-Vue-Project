<script>
	import wx from 'weixin-js-sdk';
	import { updateAppHeader } from 'vuex_path/actions.js';
	export default {
		vuex:{
			actions:{
				updateAppHeader
			}
		},
		ready() {
			
		},
		route: {
			 deactivate(transition){
		      this.loading = true;
		      this.scrollTop = window.scrollY;
		      transition.next();
		    },
            data(transition){
                document.title = "订单列表";
                this.updateAppHeader({
                	type: 1
                });
               
                let data = {
					status:this.$route.params.id
				}
				
				if(transition.from.name == 'orderDetail'){
					this.$nextTick(() => {
						scrollTo(0, this.scrollTop);
					});
					this.getOrderList();
				}else{
					this.$nextTick(() => {
						scrollTo(0, 0);
					});
					this.getOrderList(true);
				}
			
            }
        },
        data(){
        	return{
        		loading: true,
        		loaded: true,
        		loadTry: 0,
				loadingText: '加载中...',
        		showCartList:false,
        		actived:false,
        		sharePic:false,
        		page:1,
        		orderList:[]
        	}
        },
        methods:{
        	rePayOrder(e){
        		this.loaded = true;
        		let data = {
        			order_id:e
        		}
        		this.$http.post('/OfflinePayment/RePay.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$dispatch('popup', '正在跳转到支付页面~~请耐心等候');
						this.$emit('payAction', res.data.payment, res.data.content);
					}else{
						this.$dispatch('popup', res.msg);
					}
					this.loaded = false;
				}, () => {
					this.loaded = false;
				}).catch(() => {
					this.loaded = false;
				});

        	},
        	goToShare(){
        		this.sharePic = true;
        	},
        	closeShare(){
        		this.sharePic = false;
        	},
        	getOrderList(rest){
        		this.loading = true;
        		if(rest){
					 this.orderList = [];
                	this.page = 1;
				}
				let data = {
					status:this.$route.params.id,
					pageSize: this.limit ? this.limit : 8,
                    page: this.page
				}
                this.$http.post('/Order/Lists.json', data).then((res) => {
                    res = res.json();
                    if(res.status == 1){
                    	this.loaded = false;
                        
                        this.$nextTick(() => {
                        	this.showCartList = true;
                            if(res.data.list.length == 0){
                                this.loadingText = '( ⊙ o ⊙ )啊哦，没有更多订单啦~';
                                this.loading = true;
                            }else{
                            	for(let i of res.data.list){
		                            this.orderList.push(i);
		                        }
                                this.loading = false;
                            }
                        });
                    }else{
                        this.loading = true;
                    }
                }, () => {
                    this.loadTry++;
                    this.loading = false;
                    if(this.loadTry >= 3){
                        this.loading = true;
                    }
                });
        	},
        	loadMore() {
                this.page += 1;
                this.loading = true;
                this.getOrderList();
            }
        },
        events: {
			payAction(id, content) {
				let payment = Vue.extend({
					template: content
				});
				new payment().$mount().$appendTo('#payAction', () => {
					switch (parseInt(id)){
						case 1:
							this.$route.router.go({name: 'paySuccess'});
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
 <div class="container" >
 <div v-show="loaded" class="loading"></div>
 	<!-- 购物车不为空 -->
	<div class="content js-page-content">
		<div id="order-list-container"  v-if="showCartList">
			<div class="js-list b-list">
			<ul v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
				<li class="js-block-order block block-order animated" v-for="item in orderList">
				<div class="header">
					<div>
						<a href="javascript:;"><span class="font-size-14">店铺：자빛韩国瓷肌中国官方商城</span></a>
						<a class="order-state-str pull-right font-size-14" href="javascript:;">{{item.order_name}}</a>
					</div>
					<ul class="order_comment">
						<li class="order-no font-size-12 fl">
							订单编号：{{item.order_sn}}
						</li>
						<li class="fr" v-if="item.shipping_status==2 && item.order_status==1">					
							<a class="btn" v-link="{ name: 'goods_eval', params: { id: item.order_sn }}" v-if="item.handle==2">去评价</a>
							<a class="btn" href="javascript:;" v-if="item.handle==3">已评价</a>
						</li>
					</ul>					
				</div>
				<a class="name-card name-card-3col clearfix" href="javascript:;" v-link="{ name: 'orderDetail', params: {id: item.order_sn} }">
				<div class="thumb">
					<img :src="item.goods_list[0].goods_thumb">
				</div>
				<div class="detail">
					<h3 class="font-size-14 l2-ellipsis">{{item.goods_list[0].goods_name}}</h3>
					<p class="order-types">
						<button class="btn-order-type btn">{{item.pay_name}}</button>
						<button class="btn-order-type btn" v-if="item.winning == 1" >中奖订单</button>
					</p>
				</div>
				<div class="right-col">
					<div class="price c-black">
						￥<span>{{item.goods_list[0].goods_price}}</span>
					</div>
					<div class="num c-gray-darker">
            ×
						<span class="num-txt c-gray-darker">{{item.goods_list[0].goods_number}}</span>
					</div>
				</div>
				</a>
				<!-- 酒店商品合并成一个 -->
				<div class="bottom-price has-bottom-btns">
					<div class="pull-right">
        合计：
						<span class="c-orange">￥{{item.order_amount}}<em v-if="item.integral != 0 && item.integral !=null">{{item.integral}}积分</em></span>
					</div>
				</div>
				<div class="bottom" v-if="item.handle == 1">
					<div class="opt-btn pull-right">
						<!-- <a class="btn btn-default btn-in-order-list go-share" href="javascript:;" @click="goToShare()" ><i class="font icon-share"></i>分享</a> -->
						<a class="btn btn-default btn-in-order-list" href="javascript:;" @click="rePayOrder( item.order_id )" >去支付</a>
					</div>
				</div>
				</li>
			</ul>
			<div class="load-more">{{loadingText}}</div>
			</div>
			<!-- <div class="list-finished">( ⊙ o ⊙ )啊哦，没有更多订单啦~</div> -->
			<!-- 购物车不为空时 -->
		</div>
		<!-- 购物车为空时 -->
		<div class="empty-list list-finished" style="padding-top:60px;" :class="{'actived' : actived}">
		    <div>
		        <h4>居然还没有订单</h4>
		        <p class="font-size-12">好东西，手慢无</p>
		    </div>
		    <div><a href="javascript:;" class="tag tag-big tag-orange" style="padding:8px 30px;">去逛逛</a></div>
		</div>
		<!-- 购物车为空时 -->
	</div>
</div>
<div id="payAction" style="display:none;"></div>
<!-- <div class="sharePic" v-show="sharePic">
	<div class="picPr">
		<img src="/public/images/user/share.jpg" alt="">
		<a href="javascript:;" class="issure" @click="closeShare()"></a>
	</div>
</div> -->
</template>

<style scoped>
.font-size-12 { font-size: 12px !important;}
.font-size-14 {font-size: 14px !important;}
.pull-right {float: right;}
.block.block-order .order-state-str {color: #f60;}
.container {background-color: #f8f8f8;}
.content {margin: 0 auto;}
.ellipsis {white-space: nowrap;overflow: hidden;text-overflow: ellipsis;}
.container .content {zoom: 1;}
.block p {overflow: hidden;}
.c-gray-darker {color: #666 !important;}
.btn {display: inline-block;border-radius: 3px;padding: 5px 4px;text-align: center;margin: 0;font-size: 12px;cursor: pointer;line-height: 1.5;-webkit-appearance: none;background-color: #fff;border: 1px solid #f60;color: #f60;}
.c-orange {color: #f60 !important;}
#order-list-container {margin-top: 10px;}
.container .content:after {content: '';display: table;clear: both;}
.block {border-top: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5;overflow: hidden;margin: 10px 0;background-color: #fff;display: block;position: relative;font-size: 14px;}
.block:first-child {margin-top: 0;}
.block.block-order:last-of-type {margin-bottom: 0;}
.block.block-order .header {height: 37px;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;padding: 0 10px;padding-left: 10px;line-height: 37px;}
.block.block-order .header {height: auto;line-height: 26px;padding-top: 9px;}
.name-card {margin-left: 0px;width: auto;padding: 5px 0;overflow: hidden;position: relative;}
.name-card.name-card-3col {padding: 8px 0;padding-right: 85px;}
.block-order .name-card {display: block;padding-left: 10px;background: #fafafa;}
.name-card .thumb {width: 60px;height: 60px;float: left;position: relative;margin-left: auto;margin-right: auto;overflow: hidden;background-size: cover;}
.block-order .name-card .thumb {width: 90px;height: 90px;}
.name-card .thumb img {position: absolute;margin: auto;top: 0;left: 0;right: 0;bottom: 0;width: auto;height: auto;max-width: 100%;max-height: 100%;}
.name-card .detail{margin-left:68px;width:auto;position:relative}
.block-order .name-card .detail{margin-left:100px}
.name-card .detail h3{margin-top:1px;color:#333;font-size:12px;line-height:16px;width:100%}
.block-order .name-card h3{line-height:1.3}
.name-card .detail .l2-ellipsis{max-height:34px;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.name-card .detail p{position:relative;font-size:12px;line-height:16px;white-space:nowrap;margin:0 0 2px;color:#ccc}
.block-order .name-card .sku-detail{padding-right:20px;margin-top:5px}
.name-card .detail p{position:relative;font-size:12px;line-height:16px;white-space:nowrap;margin:0 0 2px;color:#ccc}
.block-order .name-card .order-types{margin-top:5px}
.block-order .name-card .btn-order-type{padding:0 3px;line-height:14px;border-radius:2px;color:#fff;background-color:#ff5050;border-color:#ff5050;font-size:10px}
.name-card.name-card-3col .right-col{position:absolute;right:0;top:8px;width:78px;padding-right:10px;font-size:12px}
.name-card.name-card-3col .right-col .price{font-size:14px;color:#515151;text-align:right;line-height:16px}
.block-order .name-card .right-col .price{line-height:1.3}
.name-card.name-card-3col .right-col .num{font-weight:200;text-align:right;margin-top:3px;padding:0;color:#555}
.block-order .name-card .right-col .num{margin-top:5px}
.name-card.name-card-3col .right-col .num .num-txt{padding:0;line-height:22px;color:#515151}
.block.block-order .bottom-price{height:30px;line-height:30px;padding:0 10px}

.block.block-order .has-bottom-btns {border-bottom: 1px solid #e5e5e5;}
.block.block-order .bottom{padding:10px 10px;height:30px;line-height:30px;box-sizing:initial}
.list-finished,.loading-more{width:100%;padding:20px 10px;vertical-align:middle;text-align:center;color:#999;font-size:12px;line-height:20px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}
.empty-list{width:100%;font-size:14px;display:block;text-align:center;padding:30px 10px;color:#999;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;display:none;}
.empty-list div{margin-bottom:20px}
.empty-list h4{font-size:16px;margin-bottom:10px;color:#666}
.tag{display:inline-block;background-color:transparent;border:1px solid #e5e5e5;border-radius:3px;text-align:center;margin:0;color:#999;font-size:12px;line-height:12px;padding:4px}
.tag-big{font-size:14px;line-height:18px}
.tag.tag-orange{color:#f60;border-color:#f60}
.empty-list.actived{display: block;}
.btn.go-share{margin-right:5px;}
.sharePic{position: fixed;top:0;left:0;width: 100%;height: 100%;background:#1a1a1a;text-align:center;z-index:9999;}
.sharePic img{width:100%;}
.picPr{position: relative;}
.issure{position: absolute;top: 77%;left: 28%;width: 44%;height: 24%;}
.order_comment{overflow: hidden;margin: 0.3rem 0;}
.order_comment .btn {border-radius: 3px;padding: 2px 3px;text-align: center;margin: 0;font-size: 12px;cursor: pointer;line-height: 1.5;-webkit-appearance: none;background-color: #fff;border: 1px solid #f60;color: #f60;}
</style>