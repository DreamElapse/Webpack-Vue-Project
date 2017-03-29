<script>
	export default {
		ready() {
			this.$http.post('/User/getBonus.json').then((res) => {
				res = res.json();
				if(res.status == 1){
					this.list = res.data;
				}
			});
		},
		data() {
			return {
				list: ''
			}
		}
	}
</script>

<template>
	<div class="member-coupon">
		<ul>
			<li v-for="item in list">
				<div><strong>{{item.type_name}}</strong><p>有效期:{{item.valid_period}}</p></div>
				<span class="hl">共{{item.number}}张</span>
			</li>
		</ul>
	</div>
</template>

<style scoped>
	.member-coupon{border-top: 1px solid #e1e1e1; background: #fff;}
	.member-coupon li{height: 4.4rem; padding: 0 0.8rem; border-bottom: 1px solid #e1e1e1; font-size: 0.9rem;
		display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;
	}
	.member-coupon li div{
		flex: 1; -webkit-flex: 1;
	}
	.member-coupon li strong{font-weight: normal;}
	.member-coupon li p{font-size: 0.8rem; color: #959595;}
	.member-coupon li .hl{color: #c6000c;}
</style>