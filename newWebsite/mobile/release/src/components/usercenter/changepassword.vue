<script>
	export default {
		ready() {
			
		},
		route: {
            data() {
                document.title = "设置新密码";
            }
        }
	}
</script>
<template>
<div class="container js-modal-login" style="min-height: 736px;">
	<div class="account-form">
		<form class="js-change-password-form" method="GET">
			<h2 class="form-title big">
			<span>设置新密码</span>
			</h2>
			<ul class="block form">
				<li class="block-item">
				<label class="c-gray-dark">手机号码</label>
				<input type="tel" name="phone" maxlength="11" class="js-txt-telphone c-gray-dark" autocomplete="off" placeholder="请输入您的手机号" value="13751735401" readonly="">
				</li>
				<li class="js-image-verify relative block-item hide">
				<label>图形验证码</label>
				<input type="tel" name="code" class="js-verify-code" placeholder="请输入图像验证码" maxlength="6">
				<img class="js-verify-image verify-image" src="">
				</li>
				<li class="relative block-item">
				<label>验证码</label>
				<input type="tel" name="code" class="js-txt-auth" placeholder="请输入短信验证码" maxlength="6">
				<button type="button" class="tag tag-green btn-auth-code font-size-12 js-send-auth" data-text="获取验证码">
                    获取验证码
				</button>
				</li>
				<li class="block-item">
				<label>新密码</label>
				<input type="password" name="password" autocomplete="off" maxlength="20" class="js-txt-password" placeholder="请输入8-20位数字和字母组合">
				</li>
			</ul>
			<p class="js-help-info help-info font-size-12">
			</p>
			<div class="action-container">
				<button type="submit" class="btn btn-green btn-block">确认提交</button>
				<button type="button" class="js-login-cancel btn btn-block btn-white">返回</button>
			</div>
			<p class="help-info c-gray-dark font-size-12">
				修改后，请使用新密码登录有赞其他应用，如有赞微商城，有赞买家版和有赞批发。
			</p>
		</form>
		<div class="success-tip hide js-success-tip">
			<span class="icon-right"></span>
			<h2 class="form-title big">
			<span>修改成功</span>
			</h2>
			<div class="action-container">
				<button type="button" class="btn btn-green btn-block hide js-goto-login">确定</button>
			</div>
		</div>
	</div>
</div>

</template>

<style scoped>
.container {
    background-color: #f8f8f8;
}
.account-form {
    overflow: hidden;
}
.account-form .form-title {
    margin: 25px 0 10px;
    padding: 0 12px;
    line-height: 24px;
    font-size: 14px;
    color: #7c7b83;
    text-transform: uppercase;
    text-shadow: 0 1px rgba(255,255,255,0.2);
}
.account-form .big {
    font-size: 20px;
    text-align: center;
    font-weight: normal;
}
.c-gray-dark {
    color: #999 !important;
}
.font-size-12 {
    font-size: 12px !important;
}
.account-form .help-info {
    color: #ed5050;
    line-height: 20px;
    padding-left: 10px;
}
.block{border-top:1px solid #e5e5e5;border-bottom:1px solid #e5e5e5;overflow:hidden;margin:10px 0;background-color:#fff;display:block;position:relative;font-size:14px}
.form{width:100%;margin:0;padding:0;padding-left:10px;padding-right:10px;list-style:none;font-size:14px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}
.block-item{position:relative;display:block;padding:10px;line-height:1.5;border:0;border-top:1px solid #e5e5e5;overflow:hidden}
.form .block-item{display:table;width:100%;padding:0}
.form>.block-item:first-child{border-top:0 none}
.account-form button,.account-form input,.account-form label,.account-form select,.account-form textarea{display:inline-block;vertical-align:top;-webkit-tap-highlight-color:transparent;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
.form .block-item label{display:table-cell;width:90px;line-height:28px;padding:10px 0}
.account-form input,.account-form select,.account-form textarea{margin:0;padding:0;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;font-size:16px;background:#fff;border:0}
.form .block-item a,.form .block-item input,.form .block-item select,.form .block-item span,.form .block-item textarea{display:table-cell;overflow:hidden;padding:10px 0;min-height:28px;line-height:28px;font-size:14px}
.form .block-item input,.form .block-item select,form .block-item textarea{width:99%;background-color:#fff;border:0 none;outline:0}
.block-item .verify-image{position:absolute;width:80px;height:30px;right:10px;top:7px}
.tag{display:inline-block;background-color:transparent;border:1px solid #e5e5e5;border-radius:3px;text-align:center;margin:0;color:#999;font-size:12px;line-height:12px;padding:4px}
.account-form .btn-auth-code{margin:0;padding:0;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;font-size:16px;background:#fff}
.block-item .btn-auth-code{position:absolute;top:6px;right:0;height:30px;line-height:30px;padding-left:7px;padding-right:7px;font-weight:700}
.tag.tag-green{color:#06bf04;border-color:#0c3}
.action-container{padding:0 10px;text-align:center;margin-top:20px;margin-bottom:20px;font-size:0}
.btn-green{color:#fff;background-color:#06bf04;border-color:#03b401}
.btn-block{text-align:center;width:100%;padding:11px 10px;font-size:16px;line-height:16px;border-radius:4px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}
.account-form button{border:1px solid #e5e5e5}
.action-container>.btn,.action-container>button{margin-bottom:10px}
.btn-white{color:#333;background-color:#fff;border-color:#e5e5e5}
.c-gray-dark{color:#999!important}
.account-form .help-info{color:#ed5050;line-height:20px;padding-left:10px}
.success-tip{margin-top:15%;text-align:center}
.success-tip .icon-right{width:50px;height:50px;display:inline-block;background:url(/public/images/user/ico_right.png) no-repeat;background-size:50px 50px}

.hide{display:none!important;visibility:hidden}















</style>