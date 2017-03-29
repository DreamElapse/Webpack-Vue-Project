$(function(){
	
	var $username = $('#username');
	var $usernameInput = $('.js-username');
	var $mobileInput = $('input[name="mobile"]');
	var $passwordInput = $('input[name="password"]');
	var $re_passwordInput = $('input[name="re_password"]');
	var $codeInput = $('input[name="code"]');
	var $getCodeBtn = $('.js-getCode');
	var codeInterval = null;
	var validateMsg = "";
	
	$usernameInput.on('input', function(){
		var result = /^1[34578]\d{9}$/.test($(this).val());
		if(!result){
			$(this).addClass('err');
			$getCodeBtn.addClass('disabled');
			validateMsg = '账号错误';
		}else{
			$(this).removeClass('err');
			if(codeInterval) return;
			$getCodeBtn.removeClass('disabled');
		}
	});
	
	$mobileInput.on('input', function(){
		var result = /^1[34578]\d{9}$/.test($(this).val());
		if(!result){
			$(this).addClass('err');
			$getCodeBtn.addClass('disabled');
			validateMsg = '手机号码错误';
		}else{
			$(this).removeClass('err');
			if(codeInterval) return;
			$getCodeBtn.removeClass('disabled');
		}
	});
	
	$('.js-account').on('input', function(){
		var result = /(^1[34578]\d{9}$)|(\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14})/.test($(this).val());
		if(!result){
			$(this).addClass('err');
			$getCodeBtn.addClass('disabled');
			validateMsg = '账号错误';
		}else{
			$(this).removeClass('err');
			if(codeInterval) return;
			$getCodeBtn.removeClass('disabled');
		}
	});
	
	$passwordInput.on('input', function(){
		var result = /^\w{6,}$/.test($(this).val());
		if(!result){
			$(this).addClass('err');
			validateMsg = '密码格式错误';
		}else{
			$(this).removeClass('err');
		}
	});
	
	$re_passwordInput.on('input', function(){
		var result = $passwordInput.val() == $re_passwordInput.val();
		if(!result){
			$(this).addClass('err');
			validateMsg = '2次输入的密码不一致！';
		}else{
			$(this).removeClass('err');
		}
	});
	
	$codeInput.on('input', function(){
		if($(this).val() == ""){
			validateMsg = '请输入验证码';
			return;
		}
		var result = /^\d{4,6}$/.test($(this).val());
		if(!result){
			$(this).addClass('err');
			validateMsg = '验证码错误';
		}else{
			$(this).removeClass('err');
		}
	});
	
	$('#getSMS').on('click', function(){
		var $btn = $(this);
		if($btn.hasClass('disabled')) return;
		validateUser($username.val(), function(exists){
			if(exists){
				popup('该用户已注册！');
				return;
			}
			getCodeEffect($btn);
			$.post('/User/sendSms.json', {
				mobile: $username.val()
			});
		});
	});
	
	function validateUser(username, callback){
		$.post('/User/find_password.json', {
			username: username,
			type: 1
		}, function(res){
			var exists = res.status == 1 ? true : false;
			callback(exists, res);
		});
	}
	
	function getCodeEffect(btn){
		var time = 60;
		codeInterval = setInterval(function(){
			if(time <= 0){
				clearInterval(codeInterval);
				btn.removeClass('disabled').text('获取验证码');
				return;
			}
			btn.text('(' + time-- + ')重发');
		}, 1000);
		btn.addClass('disabled');
	}
	
	function validateForm(input){
		var result = true;
		$.each(input, function(){
			if($(this).trigger('input').hasClass('err')){
				result = false;
				return false;
			}
		});
		return result;
	}
	
	function popup(text){
		var $html = $('<div class="popup-wrap"><span>' + text + '</span></div>').appendTo(document.body);
		$html.one('click', function(){
			$html.addClass('close');
		});
		$html.on('webkitTransitionEnd transitionend', function(){
			if($html.hasClass('close')){
				$html.remove();
			}
		});
		setTimeout(function(){
			$html.addClass('close');
		}, 1500);
	}
	
	$('#registSubmit').on('click', function(){
		var $form = $(this).closest('form');
		if(!validateForm($form.find('input'))){
			popup(validateMsg);
			return false;
		}
		$.post('/User/register.json', $form.serialize(), function(res){
			if(res.status == 1){
				popup('注册成功！');
			}else{
				popup(res.msg);
			}
		});
	});
	
	$('#loginSubmit').on('click', function(){
		var $form = $(this).closest('form');
		if($form.hasClass('loginValidate')){
			if(!validateForm($form.find('input'))){
				popup(validateMsg);
				return false;
			}
		}else{
			if(!validateForm([$usernameInput, $passwordInput])){
				popup(validateMsg);
				return false;
			}
		}
		$.post('/User/login.json', $form.serialize(), function(res){
			if(res.status == 1){
				popup('登录成功！');
			}else{
				popup(res.msg);
				if(res.data >= 3){
					$form.addClass('loginValidate');
					$('#loginCode').show();
				}
			}
		});
	});
	
	$('#getUserCode').on('click', function(){
		var $btn = $(this);
		if($btn.hasClass('disabled')) return;
		validateUser($username.val(), function(exists, res){
			if(!exists){
				popup(res.msg);
				$btn.removeClass('disabled');
				return;
			}
			getCodeEffect($btn);
			$btn.attr('check', res.data.check);
			switch ($btn.attr('check')){
				case 'mobile':
					$.post('/User/sendSms.json', {
						mobile: $username.val()
					}, function(res){
						if(res.status == 1){
							popup('短信验证码已发送!');
						}else{
							popup(res.msg);
						}
					});
					break;
				case 'email':
					$.post('/User/sendEmail.json', {
						email: $username.val()
					}, function(res){
						if(res.status == 1){
							popup('验证码已发送到邮箱！');
						}else{
							popup(res.msg);
						}
					});
					break;
				default:
					break;
			}
		});
	});
	
	$('#validateUser').on('click', function(){
		var $form = $(this).closest('form');
		if(!validateForm($form.find('input'))){
			popup(validateMsg);
			return false;
		}
		$.post('/User/find_password.json',{
			username: $username.val(),
			code: $codeInput.val(),
			check: $('#getUserCode').attr('check'),
			type: 2
		}, function(res){
			if(res.status == 1){
				popup('用户验证成功，请重置密码！');
				$('#validateUserBox').hide();
				$('#resetPasswordBox').show();
			}else{
				popup(res.msg);
			}
		});
	});
	
	$('#resetPassword').on('click', function(){
		var $form = $(this).closest('form');
		if(!validateForm($form.find('input'))){
			popup(validateMsg);
			return false;
		}
		$.post('/User/find_password.json',{
			password: $passwordInput.val(),
			re_password: $re_passwordInput.val(),
			type: 3
		}, function(res){
			if(res.status == 1){
				popup('密码修改成功!');
			}else{
				popup(res.msg);
			}
		});
	});
	
});