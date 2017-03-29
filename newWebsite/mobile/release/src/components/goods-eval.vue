<style scoped>
    .width-full1{background-color: #F5F5F5;}
    .goods-lists .list{background-color: #fff;}
    .goods-lists .list:not(:first-child){border-top: 2px solid #F0F0F0;}
    .goods-lists .list .top{padding: 1rem 0 1rem 0.6rem;display: table}
    .goods-lists .list .top>*{display: table-cell;}
    .goods-lists .list .top .pro-thum{width: 4rem;height: 4rem;text-align: center;border: 2px solid #DCDCDC;vertical-align: middle;}
    .goods-lists .list .pro_name{font-size: 1rem;color:#252525;padding-left: 0.7rem;}
    .stars{padding:0.5rem 0.7rem 0.7rem;}
    .stars span{display:inline-block;width: 1.5rem;height: 1.2rem;background-image:url(/public/images/service_evaluation/commom-icon.png);background-repeat: no-repeat;background-position: -2.1rem 0;background-size: 3.5rem;margin-right: 0.5rem;vertical-align: middle; }
    .stars span.active{background-position: 0 0;}
    .areabox{position:relative;padding:0.5rem 0.4rem 0;background: #F5F5F5;color:#a9a9a9;font-size: 0.5rem;}
    .areabox textarea{width: 100%;height:4.6rem;border: 0;outline: 0;resize:none;background: #F5F5F5;color:#a9a9a9;font-size: 0.85rem;}
    .areabox p{text-align: right;}
    .submit{padding-top:1.1rem;padding-bottom:1.1rem;text-align: center;background-color: #fff;border-top:2px solid #F0F0F0;margin-top: 0.75rem;}
    .submit .sub-btn {display:inline-block;width: 85%;height: 2.2rem;line-height:2.2rem;text-align: center;background-color: #C50007;color: #fff;font-size: 1.35rem;border-radius: 5px;}
    .logist-wrap{padding-top:1.1rem;padding-bottom:1.1rem;background-color: #fff;border-top: 2px solid #F0F0F0;border-bottom: 2px solid #F0F0F0; }
    .logist-wrap .logist-top{padding-left: .4rem;padding-right: .4rem;border-bottom: 1px solid #F0F0F0;overflow: hidden; padding-bottom: 0.5rem;}
    .fontstyle1{font-size: 1rem;color: #252525;}
    .logist-wrap .logist-top li span{display:inline-block;width: 1.5rem;height: 1.2rem;background-image:url(/public/images/service_evaluation/commom-icon.png);background-repeat: no-repeat;background-position: -0 -1.4rem;background-size: 3.5rem;vertical-align: middle;}
    .logist-wrap .logist-top li.fl{width: 50%;}
    .logist-wrap .logist-top li.fr{width: 50%;text-align: right;font-size: 0.8rem;color: #989898;line-height: 1.1rem;}
    .logist-wrap .logist-marks{}
    .logist-wrap .logist-marks li{overflow: hidden;margin:0.9rem 0;padding: 0 0.4rem;}
    .logist-wrap .logist-marks li>p{font-size: 1rem;color:#252525;padding-left: 1rem;}
    .logist-wrap .logist-marks li.fr p{padding-left: 4rem;}
    .logist-wrap .stars{padding: 0 0 0 0.7rem;}
    .logist-wrap .stars span{margin-left: 0.5rem;margin-right: 0;}
    .modal-wrapper .msg{padding: 0.2rem 0.3rem!important; font-size: 1rem;}
</style>
<template>	
    <div class="width-full1">
        <ul class="goods-lists">
        	<li class="list" v-for="item in goodLists">
        		<dl class="top">
        			<dt class="pro-thum"><img :src="item.goods_thumb" alt=""/></dt>
        			<dd class="pro-marks">
        				<span class="pro_name">{{item.name}}</span>
        				<p class="stars">
        					<span v-for="i in 5" :class="{active : item.scope >=$index+1 }" @click="scopeHandle(item,$index+1)"></span>
        				</p>
        			</dd>
        		</dl>        		
        	</li>            
        </ul>
        <div class="areabox">            
            <textarea class="limit" name="content" placeholder="写下购买体会和使用感受来帮助其他小伙伴~" maxlength="500" v-model="msg"></textarea>
            <p><span class="currentNum">0</span>/<span class="totalNum">500</span></p>
        </div>
        <div class="logist-wrap">
            <ul class="logist-top">
                <li class="fl fontstyle1"><span></span>物流服务评价</li>
                <li class="fr">满意请给5星哦</li>
            </ul>
            <ul class="logist-marks">
                <li v-for="item in serviceLists">
                    <p class="fl">{{item.name}}</p>
                    <p class="fr stars">
                    	<span v-for="i in 5" :class="{active : item.scope >=$index+1 }" @click="scopeHandle(item,$index+1)"></span>
                    </p>
                </li>                
            </ul>
        </div>
        <div class="submit">
            <span class="sub-btn" @click="submitHandle">提 交 评 价</span>
        </div>
    </div>
    </template>

<script>
	import { updateAppHeader } from 'vuex_path/actions.js';
	import { isLogin } from 'vuex_path/getters.js';
	var footer = document.querySelector('.fixed-footer');
	var display = footer.style.display;
	
	export default{
		vuex: {
			getters:{
				isLogin
			},
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
					content: '商品评价'
				});				
				footer.style.display = 'none';	
				//未登录则跳转至登录界面
				if(this.isLogin==false){
					this.$route.router.replace({name:'user'});
				}

				let data={
					order_sn:this.$route.params.id
				};
				this.$http.post('/Order/Info.json',data).then((res) => {
					res=res.json();
					if(res.status===1){	
						var _data=res.data;
						var goods_list=res.data.goods_list;					
						var order_sn=data.order_sn;
						var _goodList=[];
						for(var i of goods_list){
							var obj={
								target:"01",
								name:i.goods_name,
								goods_thumb:i.goods_thumb,
								code:i.goods_sn,
								scope : 0,
								order_sn:order_sn
							};
							_goodList.push(obj);
						}
						var serviceLists=[
							{
			    				target:'02',
			    				order_sn:order_sn,						
								name:"购物体验",
								scope:0
			    			},
			    			{
			    				target:'02',
			    				order_sn:order_sn,						
								name:"商品包装",
								scope:0
			    			},
			    			{
			    				target:'02',
			    				order_sn:order_sn,						
								name:"物流速度",
								scope:0
			    			}
						];
						this.$set('goodLists',_goodList);
						this.$set('serviceLists',serviceLists);		
						this.$set('order_sn',order_sn);
					}
				});
			}
		},
	    ready() {	            
//	        //评论文字控制
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
	                    // 
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
	    data(){
	    	return {
	    		goodLists:'',
	    		serviceLists:'',
	    		order_sn:'',
	    		msg: ''
	    	}	    	
	    },
	    methods:{
	    	scopeHandle:function(item,num){
	    		item.scope=num;
	    	},
	    	getCommentRepeat() {
	    		let data = {
		    		order_sn: this.order_sn
		    	}
				return this.$http.post('/OrderComment/getComment.shtml', data).then((res)=>{
					res = res.json();
					if(res.error=="A00001"){
						return true;
					}else{
						this.$dispatch('popup',"已评论过");
						this.$route.router.go({
							name: "orderAll",
							params:{id:5},
							replace: true
						});
					}
	            });
	    	},
	    	postComment() {    			
	    		let _serviceData=this.$get('serviceLists')
	    		let _goodsData=this.$get('goodLists');
	    		
	    		let _data=_goodsData.concat(_serviceData); 
	    		let comments=[];
	    		for(var i of _data){
	    			i.mcontent = this.msg;
	    			comments.push(JSON.stringify(i));
	    		}		    		

				let _this=this;
	    		$.post('/OrderComment/comment.shtml', "comment=["+comments+"]",function(res){
	    			if(res.error=="A00000"){
	    				_this.$dispatch('popup',"提交成功");	
	    				_this.$route.router.go({
							name: "orderAll",
							params:{id:5},
							replace: true
						});
	    			}else{
	    				_this.$dispatch('popup',"提交失败");
	    			}
	    		});
    		},
	    	async getComment(){					
	            if(await this.getCommentRepeat()){
	            	await this.postComment();
	            }
			},    	
	    	submitHandle:function(){
	    		let _serviceData=this.$get('serviceLists')
	    		let _goodsData=this.$get('goodLists');
	    		let order_sn=this.order_sn;
	    		for(var i of _goodsData){
	    			if(i.scope==0){
	    				this.$dispatch('popup',"您还有未完成的评分");
	    				return;
	    			}
	    		}
	    		for(var i of _serviceData){
	    			if(i.scope==0){
	    				this.$dispatch('popup',"您还有未完成的评分");
	    				return;
	    			}
	    		}
	    		if(this.msg.length<=0){
	    			this.$dispatch('popup',"留言不能为空");
    				return;
	    		}
	    		this.getComment();
	    	}
	  }
    }
</script>