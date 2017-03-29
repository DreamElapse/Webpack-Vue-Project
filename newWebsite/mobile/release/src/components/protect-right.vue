
<script>
	import { updateAppHeader, checkLogin} from 'vuex_path/actions.js';
	import { isLogin } from 'vuex_path/getters.js';
	var footer = document.querySelector('.fixed-footer');
	var display = footer.style.display;
	
	export default{
		vuex: {
			getters: {
				isLogin
			},
			actions: {
				updateAppHeader,
                checkLogin
			}
		},
		route: {
			deactivate(transition) {
				footer.style.display = display;
				transition.next();
			},
			data() {
                this.checkLogin();
				this.updateAppHeader({
					type: 2,
					content: '用户维权'
				});				
				footer.style.display = 'none';
				
	            this.$watch('isLogin', (val) => {
					if(val == true){
						this.$route.router.replace({name: 'protect_right'});
					}else{
						this.$route.router.replace({name: 'login'});
					}
				}, {
					immediate: true
				});
			}
		},
	    ready() {
	        //评论文字控制
	        $.fn.limitTextarea=function(opts){
	            var defaults={
	                maxNumber:500,//允许输入的最大字数
	                okHandle:function(){},//数字未超出时调用
	                overHandle:function(){}//超出时限制
	
	            }
	            var options=$.extend(defaults, opts);
	            this.each(function(index, el) {
	                var _this=$(this);
	                var fn=function(){
	                    var cur_Num=$('.currentNum');
	                    var extraNum=options.maxNumber-_this.val().length;
	                    if(extraNum>=0){
	                        //未超出
	                        cur_Num.html(_this.val().length);
	                        options.okHandle();
	                    }else{
	                        //如果字数已经超出，则禁止输出
	                        cur_Num.html(options.maxNumber);                        
	                        options.overHandle();
	                    }
	                }
	                if(window.addEventListener){
	                    //JS中有oninput这样的事件，文本框的输入、退格、空格、粘贴等操作均能触发，利用这个事件就可以动态捕捉用户的输入情况
	                    // _this.get(0).addEventListener('input',fn,false);
	                    _this.get(0).addEventListener('input',fn,false)
	                }else{
	                    _this.get(0).addEventListener('onpropertychange',fn);//IE
	                }
	                
	                if (window.VBArray && window.addEventListener) { //IE9
	                    _this.get(0).attachEvent("onkeydown", function() {
	                        //8--back 46--delete
	                        (key == 8 || key == 46) && fn(); //处理回退与删除
	                    });
	                    _this.get(0).attachEvent("oncut", fn); //处理粘贴
	                }
	            });
	        };
	        $('.complaint').limitTextarea();
	    },
	    methods:{
	    	sub_content(){
	    		let lockit=false;
	    		let tel=$('.user_tel').val();
	    		let order_num=$('.order_num').val();
	    		
				if (order_num != '') {
					lockit=true;
				}
				
				if (tel!=''&&(!/^1[34578]\d{9}$/.test(tel))) {
					this.$dispatch('popup', '手机号格式不正确');
				}else{
					lockit=true;
				}
				
				let data = {
					order_sn:order_num,
					mobile:tel,
					remark:$('.complaint').val()
				}
				if(lockit){
		            this.$http.post('/Rights/index.json', data).then((res) => {
						res = res.json();
						if(res.status == 1){
							this.$dispatch('popup', '您的投诉提交成功');
							var self=this;
							setTimeout(function(){
								self.$route.router.go({name: "right_list"});
							},1000);
						}else{
							this.$dispatch('popup', res.msg);
						}
					});
				}
	    	}
	    },
	    data(){
	    	return {
	    		goodLists:''
	    	}	    	
	    }
    }
</script>

<template>
	<!--<link rel="stylesheet" type="text/css" href="/public/css/base-rem.css"/>-->
	
    <div class="width-full">
        <div class="sub_content">
        	<div class="use_info">
        		<p>
        			<label>订单号</label><input type="number" name="order_num" class="order_num" />
        		</p>
        		<p>
        			<label>手机号</label><input type="tel" name="user_tel" class="user_tel" />
        		</p>
        	</div>
            <div class="areabox">            
                <textarea class="complaint" name="content" placeholder="您的投诉内容" maxlength="500"></textarea>
                <p><span class="currentNum">0</span>/<span class="totalNum">500</span></p>
            </div>
        </div>

        <div class="submit">
            <span class="sub-btn" @click="sub_content">提 交</span>
        </div>
        <div class="to-myright"><a v-link="{ name: 'right_list' }">我的维权>></a></div>
    </div>
</template>

<style scoped>
	html{font-size: 20px;}
	@media only screen and (max-width: 320px){html {font-size: 20px;}}
	@media only screen and (device-width: 360px){html {font-size: 22.5px;}}
	@media screen and (device-width: 375px){html {font-size: 23.4375px;}}
	@media only screen and (min-width: 401px){html {font-size: 25px;}}
	@media screen and (device-width: 414px){html {font-size: 25.875px;}}
	@media only screen and (min-width: 428px){html { font-size: 26.75px; }}
	@media only screen and (min-width: 481px){html {font-size: 30px; }}
	@media only screen and (min-width: 569px){html { font-size: 35px; }}
	@media only screen and (min-width: 640px){html {font-size: 40px; }}
	body{font-family:'Microsoft YaHei',Verdana,Arial,Helvetica,sans-serif; color:#000;background-color:#fff;min-width:320px;max-width:640px;margin:0 auto;}
    .width-full{background-color: #fff;}
    .headerwrap{background-color: #fff;}
    .header{position:relative;height:2rem;line-height:2rem;margin: 0 0.4rem;border-bottom: 3px solid #000000;color: #000;font-size: 0.75rem;font-weight: bold;text-align: center;}
    .header .back{position: absolute;width:0.8rem;height:1.2rem;left:0;top:0.5rem;background-image:url(/public/images/service_evaluation/commom-icon.png);background-repeat: no-repeat;background-position: -2rem -1rem;background-size: 2.5rem;}
   
    .sub_content{margin: 2.4rem 1.2rem 0.6rem;}
    .sub_content .use_info p{text-align: left;margin-bottom: 1.5rem;color: #a9a9a9;font-size: 0.9rem;}
    .sub_content .use_info p input{border: 1px solid #a9a9a9;vertical-align: middle;height: 1.8rem;line-height: 1.8rem;margin-left: 1rem;box-sizing: border-box;padding-left: 0.2rem;width: 80%;}
    .areabox{position:relative;padding:0.5rem 0.4rem 0;background: #f5f5f5;color:#a9a9a9;font-size: 0.5rem;border-radius: 0.4rem;border: 1px solid #cecece;}
    .areabox textarea{width: 100%;height:6.4rem;border: 0;outline: 0;resize:none;background: #F5F5F5;color:#a9a9a9;font-size: 0.75rem;letter-spacing: 1px; }
    .areabox p{text-align: right;}
    .submit{padding-top:1.1rem;padding-bottom:1.1rem;text-align: center;background-color: #fff;margin-top: 0.75rem;}
    .submit .sub-btn {display:inline-block;width: 80%;height: 2.5rem;line-height:2.5rem;text-align: center;background-color: #C50007;color: #fff;font-size: 1.2rem;border-radius: 5px;}
    .to-myright{text-align: right;padding: 1rem 1rem 0 1rem;font-size: 0.9rem;}
</style>