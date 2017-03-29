export default {
	data() {
			return {
				// registered: false,
				// usernameMsg: '',
				mobile: '',
				sending: false,
				codeInterval: null
			}
		},
		computed: {
			btnSMSDisabled() {
				if (/^1[34578]\d{9}$/.test(this.mobile) && this.sending == false) {
					return false;
				} else {
					return true;
				}
			}
		},
		methods: {
			test_mobile() {
				if (this.mobile == '') {
					this.$dispatch('popup', '手机号不能为空');
					return false;
				}
				if (!/^1[34578]\d{9}$/.test(this.mobile)) {
					this.$dispatch('popup', '手机号格式不正确');
					return false;
				}
				return true;
			},
			test_old_password(){
				if (this.old_password == '') {
					this.$dispatch('popup', '原密码不能为空');
					return false;
				}
				if (!/^\S{6,}$/.test(this.old_password)) {
					this.$dispatch('popup', '原密码最少6位');
					return false;
				}
				return true;
			},
			test_password() {
				if (this.password == '') {
					this.$dispatch('popup', '密码不能为空');
					return false;
				}
				if (!/^\S{6,}$/.test(this.password)) {
					this.$dispatch('popup', '密码最少6位');
					return false;
				}

				// if (!/^\w{6,}$/.test(this.password)) {
				// 	this.$dispatch('popup', '密码不能包含特殊字符 如(!@#$%^&*)');
				// 	return false;
				// }
				return true;
			},
			test_re_password() {
				if (this.password != this.re_password) {
					this.$dispatch('popup', '2次输入的密码不一致');
					return false;
				}
				return true;
			},
			test_code() {
				if (this.code == '') {
					this.$dispatch('popup', '验证码不能为空');
					return false;
				}
				if (!/^\d{4,6}$/.test(this.code)) {
					this.$dispatch('popup', '验证码错误');
					return false;
				}
				return true;
			},
			test_username() {
				if (this.username == '') {
					this.$dispatch('popup', '用户账号不能为空');
					return false;
				}
				if (!/(^1[34578]\d{9}$)|(\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14})/.test(this.username)) {
					this.$dispatch('popup', '用户账号格式不正确');
					return false;
				}
				return true;
			},
			validate(arr) {
				for (let i of arr) {
					if (!this['test_' + i]()) {
						return false;
					}
				}
				return true;
			},
			getSMS(e) {
				if (this.btnSMSDisabled) {
					return;
				}
				this.sending = true;
				this.getCodeInterval(e.target);
				let data = {
					mobile: this.mobile
				}
				this.$http.post('/User/sendSms.json', data).then((res) => {
					res = res.json();
					if (res.status == 1) {
						this.$dispatch('popup', '验证码已发送到手机');
					} else {
						this.$dispatch('popup', res.msg);
					}
				});
			},
			getCodeInterval(btn) {
				let time = 60;
				this.codeInterval = setInterval(() => {
					if (time <= 0) {
						clearInterval(this.codeInterval);
						this.sending = false;
						btn.innerHTML = '获取验证码';
						return;
					}
					btn.innerHTML = '(' + time-- + ')重发';
				}, 1000);
			}
		}
}