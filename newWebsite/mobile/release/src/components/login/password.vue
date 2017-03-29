<script>
	import { isLogin } from 'vuex_path/getters.js';
	import loginModel from 'loginModel.js';
	import { updateAppHeader } from '../../vuex/actions.js';

	export default {
		mixins: [loginModel],
		vuex: {
			getters: {
				isLogin
			},
			actions: {
				updateAppHeader
			}
		},
		ready() {
			
		},
		route:{
			data(){
				this.old_password = ''
				this.password = ''
				this.re_password = ''
				this.$watch('isLogin', (val) => {
					if(val == true){
						this.updateAppHeader({
							type: 2,
							content: '修改密码'
						});
						this.showResetPassword = true;
						this.showOldPassword = true;
					}else{
						this.updateAppHeader({
							type: 2,
							content: '找回密码'
						});
						this.showResetPassword = false;
					}
				}, {
					immediate: true
				});
			}
		},
		data () {
			return {
				validateUserMsg: '',
				username: '',
				code: '',
				showResetPassword: false,
				showOldPassword: false,
				old_password: '',
				password: '',
				re_password: '',
				isSuccess: false
			}
		},
		watch: {
			username() {
				this.validateUserMsg = '';
			}
		},
		computed: {
			accountType() {
				if(/^1[34578]\d{9}$/.test(this.username)){
					this.mobile = this.username;
					return 'mobile';
				}
				if(/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/.test(this.username)){
					return 'email';
				}
				return false;
			},
			btnCodeDisabled() {
				if(this.accountType){
					return false;
				}else{
					return true;
				}
			}
		},
		methods: {
				getEmailCode(e) {
					if (this.btnCodeDisabled) {
						return;
					}
					this.sending = true;
					this.getCodeInterval(e.currentTarget);
					let data = {
						email: this.username
					}
					this.$http.post('/User/sendEmail.json', data).then((res) => {
						res = res.json();
						if(res.status == 1){
							this.$dispatch('popup', '验证码已发送到邮箱');
						}else{
							this.$dispatch('popup', res.msg);
						}
					});
				},
				getCode(e) {
					this.validateUser(this.username).then((result) => {
						if(result == true){
							switch (this.accountType){
								case 'mobile':
									this.getSMS(e);
									break;
								case 'email':
									this.getEmailCode(e);
									break;
								default:
									break;
							}
						}else{
							this.validateUserMsg = result;
						}
					});
				},
				validateUser(username) {
					let data = {
						username: username,
						type: 1
					}
					return this.$http.post('/User/find_password.json', data).then((res) => {
						res = res.json();
						if (res.status == 1) {
							return true;
						} else {
							return res.msg;
						}
					});
				},
				findPassword() {
					if(!this.validate(['username', 'code'])){
						return;
					}
					let data = {
						username: this.username,
						code: this.code,
						check: this.accountType,
						type: 2
					}
					this.$http.post('/User/find_password.json', data).then((res) => {
						res = res.json();
						if(res.status == 1){
							this.showResetPassword = true;
						}else{
							this.$dispatch('popup', res.msg);
						}
					});
				},
				resetPassword() {
					if(!this.validate(['password', 're_password']) || this.isSuccess){
						return;
					}
					let data = {
						password: this.password,
						re_password: this.re_password,
						type: 3
					}
					this.$http.post('/User/find_password.json', data).then((res) => {
						res = res.json();
						if(res.status == 1){
							this.isSuccess = true;
							this.$dispatch('popup', '密码重置成功');
							this.$route.router.go({name: 'login'});
						}else{
							this.$dispatch('popup', res.msg);
						}
					});

				},
				loginresetPassword() {

					if(!this.validate(['old_password','password', 're_password'])){
						return;
					}

					let data = {
						old_password: this.old_password,
						password: this.password,
						re_password: this.re_password,
						type: 3
					}
					this.$http.post('/User/resetPassword.json', data).then((res) => {
						res = res.json();
						if(res.status == 1){
							// this.isSuccess = true;
							this.$dispatch('popup', '密码重置成功');
							this.$route.router.go({name: 'login'});
						}else{
							this.$dispatch('popup', res.msg);
						}
					});

				}
			}
		}

</script>

<template>
	<div class="container">
		<div v-if="showResetPassword == false">
			<form id="form" action="" method="post">
				<div class="input-form">
					<label class="input-item">
						<i class="font icon-user"></i><input type="text" placeholder="请输入手机/邮箱" v-model="username" /><span class="input-msg">{{validateUserMsg}}</span>
					</label>
					<label class="input-item">
						<i class="font icon-validate-code"></i><input type="text" placeholder="请输入验证码" v-model="code" /><a class="btn" :class="{disabled:btnCodeDisabled}" href="javascript:;" @click="getCode($event)">获取验证码</a>
					</label>
				</div>
				<a class="btn btn-block" href="javascript:;" @click="findPassword">下 一 步</a>
			</form>
			<dl class="reminder">
				<dt>温馨提示：</dt>
				<dd>
					<p>初始注册的会员账户是首次购物的手机号码；</p>
					<p>初始注册的登录密码是手机号码的后6位。</p>
				</dd>
			</dl>
		</div>

		<div v-else>
			<form id="form" action="" method="post">
				<div class="input-form">
					<label class="input-item" v-if="showOldPassword == true">
						<i class="font icon-password"></i><input type="password" placeholder="请输原密码" v-model="old_password" />
					</label>
					<label class="input-item">
						<i class="font icon-password"></i><input type="password" placeholder="请输入新密码" v-model="password" />
					</label>
					<label class="input-item">
						<i class="font icon-password"></i><input type="password" placeholder="请确认新密码" v-model="re_password" />
					</label>
				</div>
				<a class="btn btn-block" href="javascript:;"  v-if="showOldPassword == false" @click="resetPassword">确 认 修 改</a>
				<a class="btn btn-block" href="javascript:;"  v-if="showOldPassword == true" @click="loginresetPassword">确 认 修 改</a>
			</form>
			<dl class="reminder">
				<dt>温馨提示：</dt>
				<dd><p>重置密码后请重新登录，并完善您的个人信息，确保信息安全。</p></dd>
			</dl>
		</div>
		
	</div>
</template>

<style src="../../../../public/css/login.css" scoped></style>