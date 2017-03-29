<style scoped>
	.flexbox{padding:0.6rem 0.4rem;overflow: hidden;}
    .flexbox .pfontstyle{font-size: 1rem;color: #252525;padding-left: 0.3rem;}
    .flexbox .pfontstyle::before{content: " · ";font-size: 1.6rem;  display: inline-block;vertical-align: middle;height: 1.2rem; line-height: 0.9rem;  margin-right: 0.2rem;}
    .flexbox li{margin:0.5rem 0 0.8rem;}
    .flexbox li .stars{padding:0.1rem 1rem 0.2rem;}
    .flexbox li .stars span{display:inline-block;width: 1.5rem;height: 1.2rem;background-image:url(/public/images/service_evaluation/commom-icon.png);background-repeat: no-repeat;background-position: -2.1rem 0;background-size: 3.5rem;margin-right: 0.5rem;vertical-align: middle; cursor: pointer;}
    .flexbox li .stars span.active{background-position: 0 0;}
    .areabox{position:relative;margin: 0 0.4rem;padding:0.5rem 0.4rem 0;background: #F5F5F5;color:#a9a9a9;font-size: 0.5rem;margin: 0 0.4rem;border: 1px solid #E8E8E8;border-radius: 5px;}
    .areabox textarea{width: 100%;height:5.6rem;border: 0;outline: 0;resize:none;background: #F5F5F5;color:#a9a9a9;font-size: 0.85rem;letter-spacing: 1px; }
    .areabox p{text-align: right;}
    .submit{margin-top:1.1rem;margin-bottom:1.1rem;text-align: center; }
    .submit .sub-btn {display:inline-block;width: 85%;height: 2.2rem;line-height:2.2rem;text-align: center;background-color: #C50007;color: #fff;font-size: 1.35rem;border-radius: 5px;cursor: pointer;}
     .currentNum.over{color:#f00;}
     [v-cloak] {
	  display: none;
	}
</style>
<template>
	<div v-show="isWx">
		<ul class="flexbox">      
	    	<li v-for="item in orignList.comments" v-cloak>
	    		<div class="pfontstyle">{{item.content}}</div>
	    		<div class="stars">
	    			<span v-for="i in 5" :class="{active : item.scope >= $index+1}" @click="scopeHandle(item, $index+1)"></span>
	    		</div>
	    	</li>        	
	   </ul>
	    <div class="areabox">            
	        <textarea class="limit" id="limit" name="content" placeholder="其他建议" maxlength="500" v-model="msg"></textarea>
	        <p><span class="currentNum">0</span>/<span class="totalNum">500</span></p>
	    </div>
	    <div class="submit" v-if="isWx">
	        <span class="sub-btn" @click="submitHandle">提 交</span>
	    </div>
	</div>
</template>
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
					content: '品牌调查'
				});				
				footer.style.display = 'none';	
				var data={
		        	comments:[],           
		            msg:""//评价留言
		        }
				var that=this;
    			$.post('/UserSurvey/question.json',function(res){
					if(res.status===1){
						if(res.data.content){
	console.log(res.data.isCheckWechat);
							if(res.data.isCheckWechat==0){
								that.$dispatch('popup',"请在微信客户端打开");							
								return false;
							}							
							that.$set('isWx',true);	
							var content = res.data.content;
							for(var i of Object.keys(content)){
								var obj = {
									scope: 0
								}
								obj.content = content[i];
								data.comments.push(obj)
							}	
							that.$set('orignList',data);
						}else{
							window.location.href = '/';
						}						
					}else{
						alert(res.msg);
					}
		        })
			}
		},
	    ready() {	            
	        //评论文字控制
	        $.fn.limitTextarea=function(opts){
	            var defaults={
	                maxNumber:500,//允许输入的最大字数
	                okHandle:function(){},//数字未超出时调用
	                overHandle:function(){}//超出时限制	
	            };
	            var options=$.extend(defaults, opts);
	            this.each(function(index, el) {
	                var _this=$(this);
	                var fn=function(){
	                    var cur_Num=_this.parent().find('.currentNum');
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
	        $('#limit').limitTextarea();
	    },
	    data(){
	    	return {
	    		orignList:'',
	    		msg:'',
	    		isWx:false
	    	}	    	
	    },
	    methods:{
	    	scopeHandle:function(traget, num){
                traget.scope = num;
            },
	    	submitHandle:function(){
	        	var _data=this.$get('orignList');
	        	var comments=_data.comments;
	        	_data.msg=this.msg;
	        	for(var i=0,len=comments.length;i<len;i++){
	        		if(comments[i].scope==0){
	        			alert('您还有未完成的评分');	     
	        			return false;
	        		}	            		
	        	}
	        	let _this=this;
	            $.post('/UserSurvey/survey.json',_data,function(res){
					if(res.status===1){
						_this.$dispatch('popup',"提交成功");
					 	setTimeout(function(){					 		
					 		window.location.href = '/';
					 	},1000);
					}else{
						alert(res.msg);
					}
	    		}) 
	        }
	  }
    }
</script>