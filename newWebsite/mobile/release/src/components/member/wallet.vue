<script>
	export default {
		ready() {
			this.getInfo();
		},
		data() {
			return {
				balance: '',
				list: ''
			}
		},
		methods: {
			getInfo() {
				this.$http.post('/Wallet/getInfo.json').then((res) => {
					res = res.json();
					if(res.status == 1){
						this.balance = res.data.balance;
						this.list = res.data.list[0];
					}
				});
			}
		}
	}
</script>

<template>
	<div class="container">
		<div class="wallet-status">
			<span>可用余额：<strong>{{balance}}</strong></span>
			<span>用户状态：<strong>可用</strong></span>
		</div>
		<div class="wallet-title">
			<h3>收支明细</h3>
			<div class="thead">
				<span>时间</span>
				<span>存入</span>
				<span>支出</span>
			</div>
		</div>
		<ul class="payment-detail">
			<li v-for="item in list">
				<div class="info">
					<span>{{item.dealDate}}</span>
					<span>{{item.money}}</span>
					<span>{{item.balance}}</span>
				</div>
				<p>备注：{{item.notes}}</p>
			</li>
		</ul>
	</div>
</template>

<style scoped>
	.container{background: #f8f8f8;}

	.wallet-status{height: 2.4rem; padding: 0 0.8rem; margin-bottom: 0.4rem; background: #fff; border: 1px solid #e1e1e1; border-width: 1px 0; font-size: 0.9rem; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.wallet-status span{flex: 1; -webkit-flex: 1; display: block;}
	.wallet-status strong{color: #f03439;}

	.wallet-title{padding: 0 0.8rem; border-top: 1px solid #e1e1e1; background: #fff; font-size: 0.9rem;}
	.wallet-title h3{height: 2.4rem; border-bottom: 2px solid #000; color: #000; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.thead{height: 2.4rem; border-bottom: 1px solid #e1e1e1; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.thead span{text-align: center; flex: 1; -webkit-flex: 1; display: block;}

	.payment-detail{background: #fff;}
	.payment-detail li{padding: 0 0.8rem 0.8rem; border-top: 1px solid #e1e1e1;}
	.payment-detail li:first-child{border-top: 0;}
	.payment-detail li .info{font-size: 0.8rem; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.payment-detail li span{padding: 0.8rem 0; text-align: center; flex: 1; -webkit-flex: 1; display: block;}
	.payment-detail li p{font-size: 0.8rem; color: #969696;}
</style>