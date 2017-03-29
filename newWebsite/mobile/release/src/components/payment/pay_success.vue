<script>
	import { tel, QQ, SWT, isLogin } from 'vuex_path/getters.js';

	export default {
		vuex: {
			getters: {
				tel,
				QQ,
				SWT,
                isLogin
			}
		}
	}
</script>

<template>
	<div class="container">
	    <div class="pay-success">
	        <div class="suc-img">
	            <img src="/public/images/payment/sucess.jpg" alt=""/>
	            <p>恭喜您 , 已下单成功</p>
	        </div>
	        <div class="suc-order-item">
	            <p>客服专员会在30分钟内致电，向您确认订单信息！请保持电话畅通！</p>
	            <div class="back-btn"><a v-link="{name: 'user'}" class="view-order">查看订单</a><a href="/" class="go-home">返回首页</a></div>
	        </div>
			<div class="gentle-tips" v-else>
				<h3>温馨提示：</h3>
				<p>1、以防第三方恶意假冒，建议您：添加官方唯一外显热线：{{tel}}至通讯录，如有其它疑问欢迎给我们来电！</p>
				<p v-if="!isLogin">2、您初始购物，系统会自动为您注册成为瓷肌会员，可参与积分换购大礼包活动！</p>
			</div>
	    </div>
	</div>
</template>

<style scoped>

	.pay-success .suc-img{text-align:center;margin-top:1.6rem;padding-bottom:1.68rem;border-bottom:1px solid #d1d1d1;}
    .pay-success .suc-img img{width:20.95%;max-width:134px;}
    .pay-success .suc-img p{font-size:1.3rem;margin-top:0.72rem;}
    .suc-order-item{padding:1rem;}
    .suc-order-item p{font-size:0.8rem;margin-bottom:0.3rem;}
    .suc-order-item p .on-online{color:#c50007;}
    .suc-order-item .back-btn{text-align:center;margin-top:1.6rem;}
    .suc-order-item .back-btn a{display:inline-block;width:47%;height:1.9rem;font-size:1rem;line-height:1.8rem;border:1px solid #959595;border-radius:3px;}
    .suc-order-item .back-btn a.go-home{margin-left:6%;color:#ffffff;background:#c50007;border:1px solid #c50007;}
    .gentle-tips{background:#f6f7f9;border-top:1px solid #d1d1d1;margin-top:0.5rem;padding:0.3rem 1rem 1.4rem;font-size:0.72rem;}
    .gentle-tips h3{margin:0.4rem 0 0.2rem 0;line-height:1.4rem;font-size:1rem;}
    .gentle-tips p{line-height:1.4rem;text-align: justify;}

</style>