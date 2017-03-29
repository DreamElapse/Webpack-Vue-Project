<script>
	export default {
		ready() {
			
		},
		route: {
            data() {
                document.title = "购买记录";
            }
        }
	}
</script>

<template>
 <div class="container" >
 	<!-- 购物车不为空 -->
	<div class="content js-page-content">
		<div class="tabber tabber-n4 tabber-double-11 clearfix">
			<a class="" href="https://h5.koudaitong.com/v2/trade/cart?source=weixin11&amp;kdt_id=319">购物车</a>
			<a class="active" href="https://h5.koudaitong.com/v2/trade/record/index?source=weixin11&amp;kdt_id=319">购物记录</a>
			<a class="" href="https://h5.koudaitong.com/v2/trade/record/luckymoney?source=weixin11&amp;kdt_id=319">我的红包</a>
			<a class="" href="https://h5.koudaitong.com/v2/trade/record/backs?source=weixin11&amp;kdt_id=319">我的返现</a>
		</div>
		<div id="order-list-container">
			<div class="js-list b-list">
				<li class="js-block-order block block-order animated">
				<div class="header">
					<div>
						<a href="https://h5.koudaitong.com/v2/showcase/homepage?kdt_id=319"><span class="font-size-14">店铺：WIS官方护肤品牌店</span></a>
						<a class="order-state-str pull-right font-size-14" href="javascript:;">等待买家付款</a>
					</div>
					<div class="order-no font-size-12">
						订单编号：E20170109150222068919400
					</div>
				</div>
				<a class="name-card name-card-3col clearfix" href="https://trade.koudaitong.com/trade/order/result?order_no=E20170109150222068919400&amp;kdt_id=319">
				<div class="thumb">
					<img src="https://img.yzcdn.cn/upload_files/2013/12/19/138744534266047315.jpg!200x0.jpg">
				</div>
				<div class="detail">
					<h3 class="font-size-14 l2-ellipsis">WIS全效祛痘去印套装 4件套</h3>
					<p class="sku-detail ellipsis js-toggle-more">
						<span class="c-gray-darker">
                        痘肌护理系列&nbsp;
                        护肤4件套&nbsp;
						</span>
					</p>
					<p class="order-types">
						<button class="btn-order-type btn">代付</button>
					</p>
				</div>
				<div class="right-col">
					<div class="price c-black">
						￥<span>199.00</span>
					</div>
					<div class="num c-gray-darker">
            ×
						<span class="num-txt c-gray-darker">1</span>
					</div>
				</div>
				</a>
				<!-- 酒店商品合并成一个 -->
				<div class="bottom-price has-bottom-btns">
					<div class="pull-right">
        合计：
						<span class="c-orange">￥199.00</span>
					</div>
				</div>
				<div class="bottom">
					<div class="opt-btn pull-right">
						<a class="btn btn-default btn-in-order-list" href="https://trade.koudaitong.com/confirmPeerpay?id=20170109150235747974&amp;sign=1db81dcc&amp;kdt_id=319">代付</a>
					</div>
				</div>
				</li>
			</div>
			<div class="list-finished">
				( ⊙ o ⊙ )啊哦，没有更多订单啦~
			</div>
			<!-- 购物车不为空时 -->
			<!-- 购物车为空时 -->

			<div class="empty-list list-finished" style="padding-top:60px;">
			    <div>
			        <h4>居然还没有订单</h4>
			        <p class="font-size-12">好东西，手慢无</p>
			    </div>
			    <div><a href="https://h5.koudaitong.com/v2/showcase/homepage?kdt_id=319" class="tag tag-big tag-orange" style="padding:8px 30px;">去逛逛</a></div>
			</div>

			<!-- 购物车为空时 -->
			
		</div>
	</div>
	
</div>
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
.btn {display: inline-block;border-radius: 3px;padding: 5px 4px;text-align: center;margin: 0;font-size: 12px;cursor: pointer;line-height: 1.5;-webkit-appearance: none;background-color: #fff;border: 1px solid #e5e5e5;color: #999;}
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
.empty-list{width:100%;font-size:14px;display:block;text-align:center;padding:30px 10px;color:#999;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}
.empty-list div{margin-bottom:20px}
.empty-list h4{font-size:16px;margin-bottom:10px;color:#666}
.tag{display:inline-block;background-color:transparent;border:1px solid #e5e5e5;border-radius:3px;text-align:center;margin:0;color:#999;font-size:12px;line-height:12px;padding:4px}
.tag-big{font-size:14px;line-height:18px}
.tag.tag-orange{color:#f60;border-color:#f60}


.tabber {width: 100%;color: #333;font-size: 14px;background-color: #fff;overflow: visible;border-top: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5;}
.tabber.tabber-double-11 {border: 0px none;overflow: hidden;margin-bottom: 1px;}
.tabber button, .tabber a {float: left;width: 50%;line-height: 40px;border: 0px none;outline: 0px none;background-color: #fff;text-align: center;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;}
.tabber.tabber-n4 button, .tabber.tabber-n4 a {width: 25%;}
.tabber.tabber-double-11 a {-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;border: 0px none;color: #999;display: block;/* -webkit-border-image: url(../images/order_list/border-line-2.png) 2 stretch;-moz-border-image: url(../images/order_list/border-line-2.png) 2 stretch;border-image: url(../images/order_list/border-line-2.png) 2 stretch; */border-bottom: 1px solid #e5e5e5;}
.tabber.tabber-double-11 a.active {color: #F15A0C;border-top: 0px none;border-bottom: 2px solid #F15A0C;-webkit-border-image: initial;-moz-border-image: initial;border-image: initial;}

</style>