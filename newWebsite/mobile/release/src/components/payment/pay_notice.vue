<script>
	import { updateAppHeader } from 'vuex_path/actions.js';
	import bankModel from 'bankModel.js';

	export default {
		mixins: [bankModel],
		vuex: {
			actions: {
				updateAppHeader
			}
		},
		ready() {
			this.updateAppHeader({
				type: 2,
				content: '支付帮助中心'
			});

			$('.tab-hd a').on('click', function(){
				var $self = $(this);
				$self.addClass('on').siblings().removeClass('on');
				$self.closest('.tab-box').find('.tab-cont').hide().eq($self.index()).show();
			});
		},
		data() {
			return {
				bankList: null
			}
		}
	}
</script>

<template>
	<div class="container">
		<section class="notice-box">
			<header>
				<div>
					<strong>瓷肌物流配送方式</strong>
					<p>도자기근육물류정보</p>
				</div>
			</header>
			<p class="p1">配送方式：瓷肌直递，全国各大城市支持货到付款，订单生成日起2-7天送达客户手中。</p>
			<table>
				<thead>
					<th width="28%">订单金额</th>
					<th>配送资费</th>
				</thead>
				<tr>
					<td>＜200元</td>
					<td>加收20元运输费（包括包装费）</td>
				</tr>
				<tr>
					<td>≥200元</td>
					<td>免收运输费</td>
				</tr>
			</table>
			<dl class="notice-text">
				<dt>配送资费收取标准：</dt>
				<dd>
					<p><span>1.  </span> 单笔订单金额未满200元，收20元运输费（包括包装费）</p>
					<p><span>2.  </span>单笔订单金额超过200元，免收运输费</p>
					<p><span>注: </span>选择网银在线支付方式的客户，请在支付成功后及时将您的姓名所订购的产品名称及数量、联系方式、汇款金额等详细资料告知在线客服人员。经确认后，我们将在24小时内发货，国内2-7天即可收到。</p>
				</dd>
			</dl>
		</section>
		<section class="notice-box">
			<header>
				<div>
					<strong>瓷肌在线支付支持的银行卡</strong>
					<p>도자기근육은행카드온라인결제</p>
				</div>
			</header>
			<p class="p1">目前瓷肌在线支付支持国内大部分银行，部分地方性银行暂时不支持，具体还请提交在线支付订单选择为准。</p>
			<strong>1．快捷支付支持的银行：</strong>
			<div class="tab-box">
				<div class="tab-hd">
					<a class="on" href="javascript:;">储蓄卡/信用卡</a>
				</div>
				<div>
					<ul class="tab-cont">
						<li v-for="item in bankList"><label><img :src="'/public/images/payment/' + item.icon" alt="" /></label><em>{{item.name}}</em></li>
					</ul>
				</div>
			</div>
			<strong>2．网银支付支持的银行</strong>
			<div class="tab-box">
				<div class="tab-hd">
					<a class="on" href="javascript:;">储蓄卡/信用卡</a>
				</div>
				<div>
					<ul class="tab-cont">
						<li v-for="item in bankList"><label><img :src="'/public/images/payment/' + item.icon" alt="" /></label><em>{{item.name}}</em></li>
					</ul>
				</div>
			</div>
		</section>
	</div>
</template>

<style scoped>
	.container{background: #f8f8f8;}

	.notice-box{padding: 0 0.8rem 1rem; margin-bottom: 0.6rem; background: #fff; border: 1px solid #e1e1e1; border-width: 1px 0; font-size: 0.9rem;}
	.notice-box header{height: 3.8rem; border-bottom: 1px solid #e1e1e1; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.notice-box header:before{content: ""; width: 0.2rem; height: 2rem; margin-right: 0.4rem; border-radius: 1rem; background: #cb2229; display: inline-block;}
	.notice-box header > div{display: block;}
	.notice-box header strong{font-size: 1rem;}
	.notice-box header p{font-size: 0.6rem; color: #bebebe;}
	.notice-box table{width: 100%; margin: 1.6rem 0; text-align: center;}
	.notice-box th, .notice-box td{border: 1px solid #ffcccd;}
	.notice-box th{height: 2.2rem; background: #fff3f3; vertical-align: middle;}
	.notice-box td{height: 2.6rem; vertical-align: middle;}
	.notice-box p{color: #6d6d6d;}
	.notice-box .p1{margin: 0.8rem 0 1.2rem;}

	.notice-text dt{color: #131313;}
	.notice-text dd p{margin: 0.4rem 0; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.notice-text dd p:last-child{font-size: 0.8rem; color: #a9a9a9;}
	.notice-text p span{white-space: pre; align-self: stretch; -webkit-align-self: stretch;}

	.tab-box{margin-bottom: 2rem;}
	.tab-hd{margin-top: 0.6rem; border-bottom: 0.4rem solid #e6e6e6; transition: 0.6s; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.tab-hd > a{line-height: 1.6; font-size: 1rem; text-align: center; flex: 0.5; -webkit-flex: 0.5; display: block;}
	.tab-hd > a.on{border-bottom: 1px solid #c22126; color: #c22126;}
	.tab-hd > a:first-child{min-width: 42%;}

	.tab-cont li{height: 2.8rem; padding: 0 0.5rem; border-bottom: 1px solid #e1e1e1; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.tab-cont li label{width: 2.4rem; height: 60%; margin-right: 0.2em; text-align: center; display: inline-block;}
	.tab-cont label img{height: 100%;}
	.tab-cont li em{display: block;}
</style>