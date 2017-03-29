<script>
	export default {
		methods: {
			aa() {
				// console.log(Vue.route)
				// Vue.route.router.go({ name: 'act', params: {name: actName}});
				this.$dispatch('goAct');
				this.$dispatch('popup', '1212');
			}
		}
	}
</script>

<template>
	<div v-if="true" class="youhui">
	    <span>本期优惠</span><span>优惠大放送</span><a href="javascript:;" @click="aa">点击进入优惠专场></a>
	    <!-- <span>本期优惠</span><span>优惠大放送</span><a href="javascript:;" v-link="{ name: 'act', params: {name: actName} }">点击进入优惠专场></a> -->
	</div>
	<!--<div style="border-top: 1px solid #e1e1e1;">
		<img src="/public/images/common/action.jpg" alt="">
	</div>-->
	<div class="youhui2 clearfix">
		<p><b>减</b>在线支付，立减10元</p>
		<p><b>免</b>包邮福利，满200免运费</p>
	    <span><i class="font icon-round-check"></i>官网正品</span>
	    <span><i class="font icon-round-check"></i>1对1咨询</span>
	    <span><i class="font icon-round-check"></i>7天无理由</span>
	    <span><i class="font icon-round-check"></i>闪电发货</span>
	    <span><i class="font icon-round-check"></i>无效返券</span>
	</div>
	<div style="padding:0.6rem 0;">
		<a href="/act/index/id/20160818"><img src="/public/images/detail/action_01.jpg" alt=""></a>
	</div>
</template>

<style>
	.youhui{line-height: 2.2rem; padding: 0 0.8rem; background: #EA2143; color: #fff; margin: 0.8rem 0 0;}
	.youhui span{font-size: 0.9rem;}
	.youhui span:first-child{padding: 0 0.8rem 0 0.2rem; background: url(/public/images/detail/bg.png) no-repeat; background-size: 100% 100%; color: #EA2143; margin-right: 0.4rem;}
	.youhui a{color: #fff; float: right;}

	.youhui2{padding: 0.6rem 0.8rem 0; background: #fff; border: 1px solid #e1e1e1; border-width: 1px 0;}
	.youhui2 p{padding-bottom: 0.6rem; border-bottom: 1px solid #e1e1e1; font-size: 0.8rem;}
	.youhui2 p:nth-child(-n+2){width: 50%; float: left;}
	.youhui2 p:nth-child(3){border-bottom: 1px solid #e1e1e1; clear: both;}
	.youhui2 b{width: 1.2rem; height: 1.2rem; line-height: 1.2rem; border-radius: 4px; background-color: #ea2143; font-weight: normal; font-size: 0.85rem; color: #fff; text-align: center; display: inline-block; margin-right: 0.2rem;}
	.icon-warn{background-image: url(/public/images/detail/light.jpg); background-size: 100%; vertical-align: top;}
	.youhui2 span{width: 20%; line-height: 2rem; color: #5a5a5a; float: left; font-size: 0.7rem;}
	.youhui2 .font{color: #ea2647; margin-right: 0.2rem;}
</style>