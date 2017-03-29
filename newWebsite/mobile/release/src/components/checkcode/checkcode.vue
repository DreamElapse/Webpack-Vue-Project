<script>
	export default {
		ready(){
			this.loading = true;
			this.$http.post('/WechatBindMobile/show.json').then((res) => {
				res = res.json();
				if(res.status == 1){
					this.loading = false;
					if(res.data.isCheckWechat == 1){
						this.showtWechat = true;
					}else{
						this.showtWechat = false;
						$('.error-tips').show();
					}
					this.bunded = ( res.data.data == null || res.data.data == '' ) ? true : false;
				}else{
                	alert(res.msg);
                }
			});
			// 60秒倒计时
		    // $(function(){
		        var wait=60;
		        var tips=document.getElementById("time_cha");
		        var code_tips=document.getElementById("code_tips");
		        function time() {
		            var mobile=$('#mobile').val();
		            if (!/^(13[0-9]|14[0-9]|15[0-9]|18[0-9])\d{8}$/i.test(mobile)) {
		                alert('请输入正确的手机号！！');
		                return;
		            }
		            if (wait == 0) {
		                $('.code_tips').removeClass('on')
		                $('#get_btn').removeClass('on')
		                $('#get_btn').removeAttr('disabled');
		                $('#get_btn').val("点击获取");
		                wait = 60;
		            } else {
		                if (wait == 60) {
		                    $.ajax({
		                       type: "POST",
		                       url: "/WechatBindMobile/sendSms.json",
		                       data: "mobile="+mobile,
		                       success: function(msg){
		                            if (msg.status == 1) {
		                            	$('.code_tips').addClass('on')
						                $('#get_btn').addClass('on')
						                $('#get_btn').attr("disabled",true);
						                $('#get_btn').val("重新获取");
						                
		                            }else{
		                            	alert(msg.msg);
		                                wait = 0;
		                            }
		                       }
		                    });
		                    
		                }
		                 setTimeout(function() {
		                    time()
			                },
			                1000)
		                 $('#time_cha').text(wait)
						  wait--;
		                
		            }
		            
		        }
		        $('#app').on('click', '.get_btn', () => {
		        	time()
		        });
		    // })
	        $('#app').on('click', '#verify', () =>{
	        	var mobile=$('#mobile').val();
		        var code=$("#code").val();
		        if (!/^(13[0-9]|14[0-9]|15[0-9]|18[0-9])\d{8}$/i.test(mobile)) {
		            alert('请输入正确的手机号！！');
		            return;
		        }
		        if (!code) {
		            alert('请输入验证码');
		            return;
		        }
		        $.ajax({
		           type: "POST",
		           url: "/WechatBindMobile/verify.json",
		           data: "mobile="+mobile+"&code="+code,
		           success: function(msg){
		                if (msg.status == 1) {
		                    alert('绑定成功！！');
		                    // wx.closeWindow();
		                    this.$route.router.go({name: 'index' });

		                } else {
		                    alert(msg.msg);
		                }
		           }
		        });
	        })

		},
		data() {
			return {
				showtWechat: false,
				bunded:false,
				loading: true
			}
		}
	}
</script>
<template>
<div v-show="loading" class="loading"></div>
<div v-if="showtWechat">
	<div v-if="!bunded">
		<div class="tips_icon">
	    <img src="/public/images/common/icon_01.jpg"><br /><span>手机已经绑定</span>
	    </div>
	    <div class="subm_btn02">
	        <a href="javascript:void(0)" id="unbind" v-link="{ name: 'index' }">去逛逛~~</a>
    	</div>
    </div>
    <div v-if="bunded">
		<div class="tips_tit">
		    <h3>手机认证</h3>
		    <p>为确保您的订单物流信息准确发送，请您使用购物时留下的手机号码进行认证！</p>
		</div>
		<div class="check_phone">
		    <dl>
		        <dt>手机号</dt>
		        <dd><input type="text" name="mobile" id="mobile"></dd>
		    </dl>
		    <dl class="get_code">
		        <dt>验证码</dt>
		        <dd><input type="text" name="code" id="code"><input type="button" class="get_btn" id="get_btn" value="点击获取" /></dd>
		    </dl>
		    <dl class="code_tips" id="code_tips">
		        <p>发送成功！若失效，<i id="time_cha">50</i>S后可重新获取</p>
		    </dl>
		</div>
		<div class="subm_btn">
		    <a href="javascript:void(0)" id="verify">确认提交</a>
		</div>
		<div class="sucess_com" style="display: none;">
		    <span>提交成功</span>
		</div>
	</div>
</div>
<div v-else>
	<p class="error-tips">请用微信打开！</p>
</div>
</template>
<style scoped>
.error-tips{text-align:center;font-size:1.2rem;padding-top:1rem;display:none;}
.tips_tit{padding:2em 1em;}
.tips_tit h3{font-size: 1.2rem;color:#282828;line-height: 1.8em;text-align: center;font-weight:bold;}
.tips_tit p{color:#636363;font-size: 1rem;}
.check_phone dl{width: 85%;margin:0 auto; margin-bottom: 0.5em;height:1.8em; padding-bottom:2em; border-bottom:1px solid #f4f4f4;}
.check_phone dl dt{float: left;width:22%;height:1.8em;text-align: left;padding-right:0.5em;line-height: 1.8em;}
.check_phone dl dd{float: left;width:78%;height:1.8em;line-height: 1.5em;}
.check_phone dl dd input{height:1.8em;border:0;}
.check_phone dl.get_code dd input{width:60%;}
.check_phone dl.get_code dd .get_btn{background: #f4bfcf; margin-top:-0.5em; font-family:"微软雅黑"; display: inline-block;width:38%;color:#5c5558;margin-left: 0.2em;}
.check_phone dl.get_code dd .get_btn.on{background:#ccc;}
.check_phone .code_tips{display: none; border-bottom:0;}
.check_phone .code_tips.on{display:block;}
.code_tips p{text-align: center;}
.code_tips p b{background:#f03254;color:#fff; border-radius: 0.5em;display:inline-block;width:0.8em;height:0.8em;line-height: 0.8em;text-align: center;}
.code_tips p i{color:#ef3354;font-style: normal;}
.subm_btn,.subm_btn02{text-align: center;}
.subm_btn a,.subm_btn02 a{display: inline-block;width:90%;background:#f03254;color:#fff;padding:0.8em;border-radius: 0.25em;margin-top: 1em;}
.subm_btn02 a{/* background:#f0f0f0; color:#575757; */}
.sucess_com{position: fixed;bottom:4em;width:100%;margin:0 auto;min-width: 320px;max-width: 640px;background: #666666;color:#fff;text-align: center;padding:1.5em 0;}
.sucess_com span{font-size: 1.5em;padding-left: 1.2em;position: relative;}
.sucess_com span:before{display:inline-block;position: absolute;top:50%;left:0;content: "";width:1em;height:1em;margin-top: -0.5em; background-image: url(/templates/common/images/common/right.png);background-repeat: no-repeat;background-size: 1em;}
.back_q{padding:0 1em;}
.back_q h4{text-align: center;font-size:1.4em;color: #282828;}
.back_q .aren{width:100%;overflow: hidden;padding: 1em 0;}
.back_q .aren textarea{width:100%;}
.tips_icon{padding:2em 1em; text-align:center; font-size:1em; color:#333; font-weight:bold; font-family:"微软雅黑";}
.tips_icon img{margin:0 auto; display:inline; width:15%; max-width:86px;}
.tips_icon span{padding-top:0.5em; display:inline-block; padding-top:0.5em;}
</style>