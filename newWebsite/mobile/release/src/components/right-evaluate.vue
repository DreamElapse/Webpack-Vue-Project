
<script>
	import { updateAppHeader } from 'vuex_path/actions.js';
	var footer = document.querySelector('.fixed-footer');
	var display = footer.style.display;
	
	export default{
		vuex: {
			actions: {
				updateAppHeader
			}
		},
		route: {
			deactivate(transition) {
				footer.style.display = display;
				transition.next();
			},
			data() {				
				this.updateAppHeader({
					type: 2,
					content: '维权评价'
				});
				footer.style.display = 'none';
				this.rid=this.$route.params.rid;
				this.current=0;
				this.level='';
				this.content='';
			}
			
		},
	    data(){
	    	return {
	        	rid:'',
	        	level:'',
				current:0,
				content:''
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
	        $('.limit').limitTextarea();
	    },
	    methods:{
			writeNum(num){
				this.current=num;
				this.level=num;
			},
	    	tosubmit(){
	        	if(this.level==""){
	       			alert("您还有未完成的评分");
	       			return false;
	        	}
	        	if(this.content==""){
	        		alert("您还有未完成的评价");
	        		return false;
	        	}
	          
	    		let data={
	    			rid:this.rid,
	    			level:this.level,
	    			content:this.content
	    		}
	            this.$http.post('/Rights/confirm.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$dispatch('popup', '提交成功');
						this.$route.router.go({name: "right_list"});
					}else{
						this.$dispatch('popup', res.msg);
					}
				});
	          
	    	}
	    }
    }
</script>

<template>
    <div class="width-full">
        <ul class="flexbox">
            <li class="headimg"><img src="/public/images/service_evaluation/service-img_03.jpg"></li>
            <li>
                <div class="pfontstyle">亲爱的顾客，请您为本次服务作出评价~么么哒~</div>
                <div class="stars">
	                <span @click='writeNum(1)' :class="{active:this.current >=1}"></span>
					<span @click='writeNum(2)' :class="{active:this.current >=2}"></span>
					<span @click='writeNum(3)' :class="{active:this.current >=3}"></span>
					<span @click='writeNum(4)' :class="{active:this.current >=4}"></span>
					<span @click='writeNum(5)' :class="{active:this.current >=5}"></span>
                </div>
            </li>
        </ul>
        <div class="areabox">            
            <textarea class="limit" id="limit" name="content" maxlength="500" placeholder="输入您想说的话" autocomplete="off" v-model='content'></textarea>
            <p><span class="currentNum">0</span>/<span class="totalNum">500</span></p>
        </div>
        <div class="submit">
            <span class="sub-btn" @click="tosubmit">提 交 评 价</span>
        </div>
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
    .flexbox{padding:0.6rem 0.4rem;overflow: hidden;}
    .flexbox .pfontstyle{font-size: 0.5rem;color: #646464;padding-left: 0.3rem;}
    .flexbox li{float: left;}
    .flexbox .headimg{width: 2.775rem;}
    .flexbox li .stars{padding: 0.5rem;}
    .flexbox li .stars span{display:inline-block;width: 0.75rem;height: 0.75rem;background-image:url(/public/images/service_evaluation/commom-icon.png);background-repeat: no-repeat;background-position: -1.23rem 0;background-size: 2rem;margin-right: 0.5rem;vertical-align: middle; }
    .flexbox li .stars span.active{background-position: 0 0;}
    .areabox{position:relative;padding:0.5rem 0.4rem 0;background: #F5F5F5;color:#a9a9a9;font-size: 0.5rem;}
    .areabox textarea{width: 100%;height:4.6rem;border: 0;outline: 0;resize:none;background: #F5F5F5;color:#a9a9a9;font-size: 0.55rem;letter-spacing: 1px; }
    .areabox p{text-align: right;}
    .submit{margin-top:1.1rem;margin-bottom:1.1rem;text-align: center; }
    .submit .sub-btn {display:inline-block;width: 85%;height: 1.9rem;line-height:1.9rem;text-align: center;background-color: #C50007;color: #fff;font-size: 1.15rem;border-radius: 5px;cursor: pointer;}
     .currentNum.over{color:#f00;}
     .pop-model{position: fixed;height: 100%;width: 100%;top:0;left:0;text-align: center;background: rgba(0,0,0,0.5);display: none;}
     .pop-model span{position:relative;display: inline-block;padding: 0.2rem 0.3rem;background: rgba(0,0,0,0.7);color: #fff;font-size: 0.8rem;top:35%;border-radius: 0.3rem;}
</style>