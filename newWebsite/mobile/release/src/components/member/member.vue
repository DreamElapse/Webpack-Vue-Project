<script>
	import memberOrder from './member_order.vue';
	import address from './address.vue';
	import memberCoupon from './member_coupon.vue';
	import Wallet from './wallet.vue';
	import memberMessage from './member_message.vue';

	import { isLogin } from 'vuex_path/getters.js';
	import { updateAppHeader, logout } from 'vuex_path/actions.js';

	// import route from 'route.js';

	var lrz = require('lrz.all.bundle.js');

	export default {
		// mixins: [route],
		components: {
			memberOrder,
			address,
			memberCoupon,
			Wallet,
			memberMessage
		},
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
				let view = this.$route.params.view;
				if(view){
					this.currentView = view;
				}
			}
		},
		ready() {
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
		},
		data() {
			return {
				loading: false,
				user: {},
				defaultPhoto: '/public/images/member/portrait.png',
				currentView: 'memberOrder'
			}
		},
		computed: {
			username() {
				return this.user.user_name || this.user.mobile;
			},
			userPhoto() {
				return this.user.photo_url ? this.user.photo_url : this.defaultPhoto;
			}
		},
		methods: {
			changeView(view) {
				this.currentView = view;
			},
			getUserInfo() {
				this.loading = true;
				return this.$http.post('/Global/getUserInfo.json').then((res) => {
					res = res.json();
					if(res.status == 1){
						this.user = res.data;
					}
					this.loading = false;
				}, () => {
					this.loading = false;
				});
			},
			changePhoto(e) {
				let file = e.currentTarget.files[0];
				let supportTypes = ['image/jpg', 'image/jpeg', 'image/png'];

				if(supportTypes.indexOf(file.type) < 0){
					this.$dispatch('popup', '只能上传jpg、jpeg、png格式的图片');
					return;
				}

				// if(file.size > 0.5 * 1024 * 1024){
				// 	this.$dispatch('popup', '上传的图片大小不能大于500k');
				// 	return;
				// }

				// let formData = new FormData();
				// formData.append('photo', file);

				lrz(file, {width: 164}).then((result) => {
					this.loading = true;
					result.formData.append('photo', result.base64);
					this.$http.post('/User/changePhoto.json', result.formData).then((res) => {
						res = res.json();
						if(res.status == 1){
							// let reader = new FileReader();
							// reader.readAsDataURL(file);
							// reader.onload = (e) => {
							// 	this.user.photo_url = e.target.result;
							// }
							this.user.photo_url = result.base64;
							this.$dispatch('popup', '修改头像成功');
						}else{
							this.$dispatch('popup', res.msg);
						}
						this.loading = false;
					}, () => {
						this.loading = false;
					});
				});
			}
		}
	}
</script>

<template>
	<div class="container" style="background:#f8f8f8;">
		<div v-show="loading" class="loading"></div>

		<div class="member-top">
			<a class="btn-logout" href="javascript:;" @click="logout"><i class="font icon-logout"></i><em>注销</em></a>
			<div class="portrait">
				<img :src="userPhoto" alt="" />
				<input type="file" name="file" @change="changePhoto($event)" />
			</div>
			<div class="portrait-txt"><span>Hi，{{username}}</span><span class="level"><i class="font icon-level"></i><em>Lv {{user.level}}</em></span></div>
			<div class="member-exp">
				<div class="member-exp-bar"><span :style="{width: user.percent + '%'}"></span></div>
				<p class="exp">exp<span class="fr">{{user.total_points}} / {{user.max_points}}</span></p>
			</div>
		</div>
		<nav class="member-nav">
			<a :class="{on:currentView=='memberOrder'}" @click="changeView('memberOrder')"><i class="font icon-member-order"></i>我的订单</a><b></b>
			<a :class="{on:currentView=='address'}" @click="changeView('address')"><i class="font icon-member-address"></i>地址管理</a><b></b>
			<a :class="{on:currentView=='memberCoupon'}" @click="changeView('memberCoupon')"><i class="font icon-member-coupon"></i>优惠券</a><b></b>
			<!-- <a :class="{on:currentView=='wallet'}" @click="changeView('wallet')"><i class="font icon-member-wallet"></i>钱包</a><b></b> -->
			<a :class="{on:currentView=='memberMessage'}" @click="changeView('memberMessage')"><i class="font icon-member-message"></i>站内信</a>
		</nav>
		<div class="member-container">
			<component :is="currentView" keep-alive>
				<!-- 我的订单 -->
				<member-order></member-order>
				
				<!-- 地址管理 -->
				<address></address>
				
				<!-- 优惠券 -->
				<member-coupon></member-coupon>

				<!-- 钱包 -->
				<wallet></wallet>
				
				<!-- 站内信 -->
				<member-message></member-message>
			</component>
		</div>
	</div>
</template>

<style scoped>
	.member-top{min-height: 12rem; border-top: 1px solid #e1e1e1; background: #fff url(/public/images/member/member_bg.jpg) no-repeat 50% 0; background-size: 100%; text-align: center; position: relative;}
	.btn-logout{display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center; position: absolute; left: 0.4rem; top: 0.4rem;}
	.btn-logout em{font-size: 0.9rem;}
	.portrait{width: 5rem; height: 5rem; padding-top: 0.3rem; margin-top: 1.3rem; background: url(/public/images/member/portrait_border.png) no-repeat; background-size: 100%; display: inline-block; position: relative;}
	.portrait:before{content: ""; width: 2.4rem; height: 2.4rem; margin: -1.1rem 0 0 -0.6rem; background: url(/public/images/member/crown.png) no-repeat; background-size: 100%; position: absolute;}
	.portrait img{width: 4.5rem; height: 4.5rem; border-radius: 5rem;}
	.portrait input{width: 100%; height: 100%; opacity: 0; position: absolute; left: 0; top: 0;}
	.portrait-txt{height: 2rem; margin: 0.2rem 0 0.2rem;
		display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center; justify-content: center; -webkit-justify-content: center;
	}
	.level{height: 1.2rem; line-height: 1; margin-left: 1rem; border-radius: 2rem; background: #ffea00; font-size: 0.9rem; color: #ff4800; display: inline-table;}
	.level .font{width: 1.2rem; border-radius:2rem ; background: #ff4800; color: #fff; font-size: 0.9rem; display: table-cell; vertical-align: middle;}
	.level em{padding: 0 0.2rem; display: table-cell; vertical-align: middle;}
	.member-exp{width: 60%; margin: 0 auto; text-align: left;}
	.member-exp p{padding: 0 0.4rem; margin-top: 0.2rem; font-size: 0.8rem;}
	.member-exp-bar{height: 1.2rem; padding: 0 0.3rem; background: url(/public/images/member/exp_progress_bar.png) no-repeat; background-size: 100% 100%;
		display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;
	}
	.member-exp-bar span{height: 60%; background: url(/public/images/member/exp_progress.jpg) repeat-x; background-size: auto 100%; transition: width 0.6s ease-out;}

	.member-nav{height: 4.6rem; margin-bottom: 0.4rem; border: 1px solid #e1e1e1; border-width: 1px 0; background: #fff; font-size: 0.9rem;
		display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;
	}
	.member-nav a{color: #898989; text-align: center; transition: 0.6s; flex: 1; -webkit-flex: 1; display: block;}
	.member-nav a.on{color: #f03439;}
	.member-nav b{width: 1px; height: 50%; background: #efefef; display: block;}
	.member-nav a .font{font-size: 2rem; display: block;}
	.member-container{background: #fff;}
</style>