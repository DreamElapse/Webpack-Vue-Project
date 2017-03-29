<script>
	import loginModel from 'loginModel.js';
	import { isLogin } from 'vuex_path/getters.js';
	import { updateAppHeader, updateUser, setIsLogin, getCartGoodsQty } from 'vuex_path/actions.js';

	export default {
		mixins: [loginModel],
		vuex: {
			getters: {
				isLogin
			},
			actions: {
				updateAppHeader,
				updateUser,
				setIsLogin,
				getCartGoodsQty
			}
		},
		route:{
			data(transition){
				this.updateAppHeader({
					type: 2,
					content: '用户登录'
				});
				this.isShowLogin = false;
	            this.$watch('isLogin', (val) => {
					if(val == true){
						if(transition.from.name=='protect_right'){
							this.$route.router.replace({name: 'protect_right'});
						}else{
							this.$route.router.replace({name: 'user'});
						}
					}else{
						this.unLogin =true;
						this.isShowLogin = true;
					}
				}, {
					immediate: true
				});
			}
		},
		data() {
			return {
				mobile: '',
				password: '',
				code: '',
				showCode: sessionStorage.showLoginCode ? true : false,
				imgCodeRandom: '',
				isShowLogin:false,
				unLogin:false
			}
		},
		computed: {
			imgCode: {
				get() {
					return '/User/verify.json?random=' + this.imgCodeRandom;
				},
				set(random) {
					this.imgCodeRandom = random;
				}
			}
		},
		methods: {
			test_username() {
				if (this.mobile == '') {
					this.$dispatch('popup', '账号不能为空');
					return false;
				}
				if (!/^1[34578]\d{9}$/.test(this.mobile)) {
					this.$dispatch('popup', '账号格式不正确');
					return false;
				}
				return true;
			},
			getImgCode() {
				this.imgCode = Math.random();
			},
			login() {
				if (!this.validate(['username', 'password'])) {
					return;
				}
				let data = {
					username: this.mobile,
					password: this.password
				}
				if(this.showCode){
					data.code = this.code;
				}
				this.$http.post('/User/login.json', data).then((res) => {
					res = res.json();
					if (res.status == 1) {
						let user = res.data;
						this.updateUser({
							name: user.user_name,
							portrait: user.photo_url,
							level: user.level,
							curPoint: user.min_points,
							maxPoint: user.max_points
						});

						this.setIsLogin(true);
						this.getCartGoodsQty(true);

						this.$dispatch('popup', '登录成功！');
						this.$route.router.go({
							name: "user"
						});
					} else {
						this.imgCode = Math.random();
						this.$dispatch('popup', res.msg);
						if (res.data >= 3) {
							sessionStorage.showLoginCode = true;
							this.showCode = true;
						}
					}
				});
			}
		}
	}
</script>

<template>
	<div class="container">
		<div class="isShowLogin" :class="{on: unLogin}" v-if="isShowLogin">
			<form id="form" action="" method="post">
				<div class="input-form">
					<label class="input-item">
						<i class="font icon-user"></i><input type="text" placeholder="请输入手机号码" v-model="mobile" />
					</label>
					<label class="input-item">
						<i class="font icon-password"></i><input type="password" placeholder="请输入6个字符，数字或字母" v-model="password" />
					</label>
					<label class="input-item" v-show="showCode">
						<i class="font icon-validate-code"></i><input type="text" placeholder="请输入验证码" v-model="code" />
						<img class="img-code" :src="imgCode" alt="" @click="getImgCode" />
						<!-- <a class="btn" :class="{'disabled':btnSMSDisabled}" href="javascript:;" @click="getSMS($event)">获取验证码</a> -->
					</label>
				</div>
				<a class="btn btn-block" href="javascript:;" @click="login">立 即 登 录</a>
			</form>
			<div class="login-action">
				<a class="hl" v-link="{ name: 'regist' }">立即注册</a><i></i><a v-link="{ name: 'findPassword' }">忘记密码？</a>
			</div>
		</div>
		<div class="loginLoading" v-else>
			<div class="isLoading">
				<div class="loadingTimer">
					<div class="timerWrap">
						<svg version="1.1" viewBox="131.623 175.5 120 160" preserveAspectRatio="xMinYMin meet" class="timer">
						<path fill="#FFFFFF" d="M212.922,255.45l36.855-64.492c1.742-3.069,1.742-6.836-0.037-9.896c-1.783-3.06-5.037-4.938-8.581-4.938
						h-99.158c-3.524,0-6.797,1.878-8.569,4.938c-1.773,3.06-1.792,6.827-0.03,9.896l36.846,64.491l-36.845,64.492
						c-1.762,3.068-1.743,6.836,0.03,9.896c1.772,3.061,5.044,4.938,8.569,4.938h99.158c3.544,0,6.798-1.878,8.581-4.938
						c1.779-3.06,1.779-6.827,0.037-9.896L212.922,255.45z M142.001,324.86l39.664-69.41l-39.664-69.41h99.158l-39.663,69.41
						l39.663,69.41H142.001z"/>
						</svg>
					</div>
				</div>
				<span>正常努力加载中~~~</span>
			</div>
		</div>
	</div>
</template>

<style src="../../../../public/css/login.css" scoped></style>