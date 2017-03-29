<script>
import { updateAppHeader } from 'vuex_path/actions.js';
	export default {
		vuex: {
			actions: {
				updateAppHeader
			}
		},
		route: {
			data() {
				this.updateAppHeader({
					type: 2,
					content: '订单详情'
				});
				let data = {
					order_sn: this.$route.params.id
				}
				this.$http.post('/Order/Info.json', data).then((res) => {
					res = res.json();
					if(res.status === 1){
						this.$set('detail', res.data);
					}else{
						alert(res.msg);
					}
				});
			}
		},
		ready() {			
			$('.order-hd').on('click', function(){
				$(this).toggleClass('on');
			});
			
			// let data = {
			// 	order_sn: this.$route.params.id
			// }
			// this.$http.post('/Order/Info.json', data).then((res) => {
			// 	res = res.json();
			// 	if(res.status === 1){
			// 		this.$set('detail', res.data);
			// 	}else{
			// 		alert(res.msg);
			// 	}
			// });
		},
		data() {
			return {
				detail: ''
			}
		}
	}
</script>

<template>
	<link rel="stylesheet" href="/public/css/order.css">

	<div class="container">
		<div class="order-list">
			<div class="item">订单号<span>{{detail.order_sn}}</span></div>
			<div class="item">下单日期<span>{{detail.add_date}}</span></div>
			<div class="item">订单状态<span>{{detail.order_name}}</span></div>
		</div>
		<div class="order-list pay-money">
			<div class="item">商品总金额<span class="all-amount">{{detail.goods_amount}}</span></div>
			<div class="item">+运费<span>{{detail.shipping_fee}}</span></div>
			<div class="item">-折扣金额<span>{{detail.discount}}</span></div>
			<div class="item">-优惠券<span>{{detail.bonus}}</span></div>
		</div>
		<div class="order-list pay-moneys">
			<div class="item"><span>支付金额: <b>{{detail.order_amount}}</b></span></div>
		</div>
		<dl class="order-item">
			<dt class="order-hd">
				<span>查看商品详情</span>
				<i class="font icon-arrow-bottom"></i>
			</dt>
			<dd>
				<ul class="goods-list">
					<li v-for="item in detail.goods_list">
						<a class="goods-img" v-link="{ name: 'goodsDetail', params: {id: item.goods_id}}"><img :src="item.goods_thumb" alt="" /></a>
						<div class="goods-detail">
							<p><span class="fl">{{item.goods_name}}</span><span>￥{{item.goods_price}}</span></p>
							<p><del>￥{{item.market_price}}</del></p>
							<p>x{{item.goods_number}}</p>
						</div>
					</li>
				</ul>				
			</dd>
		</dl>
	</div>
</template>
<style scoped>
.pay-money{margin: 0.4rem 0;border: 1px solid #e1e1e1;border-width: 1px 0;background: #fff;}
.pay-money span{color: #f15353;}
.pay-money .all-amount{font-size:1.2rem;}
.pay-moneys span{font-size:0.8rem;}
.pay-moneys span b{color: #f15353;font-size: 1.2rem;font-weight:normal;}
</style>