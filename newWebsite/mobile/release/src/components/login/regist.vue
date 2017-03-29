<script>
	import loginModel from 'loginModel.js';
	import { updateAppHeader } from '../../vuex/actions.js';

	export default {
		mixins: [loginModel],
		vuex: {
			actions: {
				updateAppHeader
			}
		},
		route:{
			data(){
				this.updateAppHeader({
					type: 2,
					content: '免费注册'
				});
			}
		},
		data() {
			return {
				canRegist: false,
				loading: false,
				checkMobileMsg: '',
				mobile: '',
				password: '',
				re_password: '',
				code: ''
			}
		},
		methods: {
			// checkMobile() {
			// 	let data = {
			// 		mobile: this.mobile
			// 	}
			// 	this.$http.post('/User/checkUserExists.json', data).then((res) => {
			// 		res = res.json();
			// 		if(res.status == 1){
			// 			if(res.data.is_register == 1){
			// 				this.checkMobileMsg = '该手机号已被注册';
			// 				this.canRegist = false;
			// 			}else{
			// 				this.checkMobileMsg = '';   //当没有注册的时候
			// 				this.canRegist = true;
			// 				this.getRegistSMS();
			// 			}
			// 		}
			// 	});
			// },
			checkMobile(){
				this.checkMobileMsg = '';
				console.log(2111)
			},
			getRegistSMS(e) {
				this.loading = false;
				let data = {
					mobile: this.mobile
				}
				this.$http.post('/User/checkUserExists.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						if(res.data.is_register == 1){
							this.loading = false;
							this.checkMobileMsg = '该手机号已被注册';
							this.canRegist = false;
						}else{
							this.loading = false;
							this.checkMobileMsg = '';   //当没有注册的时候
							this.canRegist = true;
							if(this.canRegist == true){
								this.getSMS(e);
							}
						}
					}else{
						this.$dispatch('popup', res.msg);
					}
				})
				
			},
			regist() {
				if(!this.validate(['mobile', 'password', 're_password', 'code'])){
					return;
				}
				let data = {
					mobile: this.mobile,
					password: this.password,
					re_password: this.re_password,
					code: this.code
				}
				this.$http.post('/User/register.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$dispatch('popup', '注册成功！');
						// this.login();
						this.$route.router.go({
							name: "member"
						});
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
	<div v-show="loading" class="loading"></div>
		<form id="form" action="" method="post">
			<div class="input-form">
				<label class="input-item">
					<i class="font icon-user"></i><input type="text" placeholder="请输入手机号码" v-model="mobile" @focus="checkMobile" /><span class="input-msg">{{checkMobileMsg}}</span>
				</label>
				<label class="input-item">
					<i class="font icon-validate-code"></i><input type="text" placeholder="请输入验证码" v-model="code" /><a class="btn" :class="{'disabled':btnSMSDisabled}" href="javascript:;" @click="getRegistSMS($event)">获取验证码</a>
				</label>
				<label class="input-item">
					<i class="font icon-password"></i><input type="password" placeholder="请设置密码(不少于6个数字或字母)" v-model="password" />
				</label>
				<label class="input-item">
					<i class="font icon-password"></i><input type="password" placeholder="请确认密码" v-model="re_password" />
				</label>
			</div>
			<a class="btn btn-block" href="javascript:;" @click="regist">确 认 注 册</a>
		</form>
	</div>
</template>

<style src="../../../../public/css/login.css" scoped></style>