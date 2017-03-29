<style scoped>
.contain{/*position: absolute;top:4.3rem;left:0;border:2px solid #bcbdc0;width: 100%;height: 100%;overflow: hidden;*/}
.s_tit,.team{padding-top:1.5rem;text-align:center;}
.s_tit span{display:inline-block;width:25.3%;}
.team span{display:inline-block;width:76.1%;}
.s_tit span img{width:100%;max-width:162px;}
.team span img{width:100%;max-width:487px;}
.apply{width:100%;text-align:center;margin-top:1.2rem;}
.apply p{width:76.1%;display: inline-block;text-align: left;color:#58595b;}
.info{width:76.1%;margin:1rem auto 0;}
.info dl{display:table;margin-top:1rem;width: 100%;overflow: hidden;}
.info dl dt{display:table-cell;vertical-align:middle;width:42%;font-size:0.85rem;color:#58595b;}
.info dl dt b{color:#b01116;vertical-align: middle;}
.info dl dd{display:table-cell;vertical-align:middle;width:58%;padding-left:0.5rem;}
.info dl dd input{border:1px solid #f26649;height: 1.2rem;line-height: 1.2em;display: inline-block;font-size: 1rem;width: 85%;}
.info dl dd a{display:inline-block;width:58%;}
.info dl.file dd a{display:inline-block;width:58%;max-width:161px;background:url(/public/images/uploadphoto/sub-btn.png) no-repeat center center;background-size: cover;}
.info dl.file dd input{opacity:0; filter:alpha(opacity=0);}
.server{width:76.1%;margin:4rem auto 0;}
.sun-btn{width:100%;text-align:center;margin-top:1.5rem;}
.sun-btn a{display:inline-block;width:50%;background:#d20000;color:#fff;padding:0.4rem 0;border-radius:2.5px;}
.upload-preview{padding-top: 1rem;}
.upload-preview li{width: 49%; height: 9rem; margin: 0.2rem 0; border: 1px solid #e1e1e1; display: inline-block; vertical-align: middle; position: relative; float: left;}
.upload-preview li:nth-child(2n){float: right;}
.upload-preview li .font{font-size: 1rem; position: absolute; right: 0.4rem; top: 0.4rem;background: #5D5D5D;color: #f7ebeb;font-style: normal;width: 1.2rem;height: 1.2rem;line-height: 1rem;text-align: center;border-radius: 50%;}
.upload-info{padding-top: 0.8rem;}
.upload-info span{width: 50%; white-space: nowrap; float: left;font-size: 0.8rem;}
[v-cloak] {display: none;}
.upload-preview img{width: 100%;}
.upload-preview img.height{height: 100%;width: auto;}
.loadprogress{height: 2.5rem;line-height:2.5rem;text-align: center;font-size: 0.85rem;}
.loadprogress::after{content:"";display:inline-block;width:2rem;height:2rem;vertical-align:middle;text-align:center;background: url(/public/images/common/load_bg.gif) no-repeat center center;}

</style>
<template>
	<div class="contain">
		<div class="s_tit">
			<span><img src="/public/images/uploadphoto/cj.png" alt=""></span>
		</div>
		<div class="team">
			<span><img src="/public/images/uploadphoto/team.png" alt=""></span>
		</div>
		<div class="apply"><p>韩国瓷肌具有专业的皮肤科医生团队为您提供关于问题肌肤的诊断及免费的专业建议。</p></div>		
		<div class="info">
            <form >
				<dl>
					<dt style="letter-spacing: 0.15em;"><b>*</b>请输入手机号</dt><dd><input type="tel" maxlength="11" name="phoneNum" v-model="phoneNum" onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')"></dd>
				</dl>				
				<dl class="file">
					<dt><b>*</b>请上传面部照片</dt><dd><a href="javascript:;"><input type="file" name="file" v-on:change="uploadHandle( $event)"></a></dd>				
				</dl>
				<div class="loadprogress" v-show="isLoad"><span>{{loadtips}}</span></div>
				<ul class="upload-preview clf" v-show="uploadList.length>0" v-cloak>
					<li v-for="item in uploadList">
						<img :src="item.base64" :class="{height:item.isHeight}"/><i class="font" @click="removeList($index)">x</i>				
					</li>
				</ul>				
				<div class="upload-info clf">
					<span>上传张数：<em id="count">{{count}}</em>/10</span><span>上传大小：<em id="size">{{size}}</em>KB/2048KB</span>
				</div>
				<div class="sun-btn" @click="formSubmit"><a href="javascript:;">提交</a></div>
            </form>
		</div>
		<p class="server">我们将在三天内对您的皮肤问题做出诊断及建议，谢谢！</p>
	</div>
</template>
<script>
	import lrz from 'lrz.all.bundle.js';
	let footer = document.querySelector('.fixed-footer');
	let header=document.querySelector('.header');
	let display_footer = footer.style.display;	
	let display_header = header.style.display;	
	export default {
		route:{	
			deactivate(transition) {
				footer.style.display = display_footer;
				header.style.display = display_header;
				transition.next();
			},
			data(){
				document.title = "자빛韩国瓷肌中国官方商城肌肤问题图片上传";
				document.querySelector('#app').style.paddingTop="0px";
				footer.style.display = 'none';	
				header.style.display = 'none';	
			}			
		},
		ready(){
		},
		data(){
			return {
				phoneNum:'',
				uploadList:[],
				count:0,
				size:0,
				isLoad:false,
				loadtips:''
			}
		},
		methods:{			
			formSubmit:function(){
				if(this.phoneNum == '' || ((/^1[3-9]{1}[0-9]{9}$/.test(this.phoneNum) == false) && (/^0\d{2,3}(\-)?\d{7,8}$/.test(this.phoneNum)==false))){
		    		this.$dispatch('popup',"请输入正确的手机号!");
	                return false;
				}
	            if(this.uploadList.length <= 0){
	                this.$dispatch('popup',"请上传合适尺寸的图片!");
	                return false;
	            }
				if(this.size >= 2048){
					this.$dispatch('popup',"上传的图片总大小不能超过2M!");
	                return false;
				}
				var data={
					phoneNum:this.phoneNum					
				};
				this.isLoad=true;
				this.loadtips="上传中，请稍后";
				data.upload_preview=[];
				for(let i of this.uploadList){
					data.upload_preview.push(i.base64);
				}						
				data.upload_preview=JSON.stringify(data.upload_preview);				
				this.$http.post('/UpImage/index.json',data).then((res) => {
	                res = res.json();
	                if(res.status == 1){
	                	this.isLoad=false;
	                    this.$dispatch('popup',"上传成功");
	                    this.phoneNum="";
	                    this.uploadList=[];
	                }else{
	                	 this.$dispatch('popup',res.msg);
	                }					
	            });
			},
			uploadHandle:function(event){
				if(this.uploadList.length>=10){
					this.$dispatch('popup',"最多只能上传10张图片");
					return;
				}				
				var _that=this,_isHeight=false;
				if (event.target.files.length === 0) return;
				var _file=event.target.files[0];
				this.isLoad=true;
				this.loadtips="图片加载中，请稍后";
				lrz(_file).then(function(result){								
					var img=new Image();								
					img.onload=function(){	
						if(img.width<img.height){
							_isHeight=true;
						}else{
							_isHeight=false;
						}									
						result.isHeight=_isHeight;
						_that.isLoad=false;
						_that.uploadList.push(result);
					}
					img.src=result.base64;													
				})

			},			
			removeList:function(index){
				this.uploadList.splice(index,1);
			}
		},
		computed:{
			count:function(){
				return this.uploadList.length;
			},
			size:function(){
				let _size = 0;				
				for(let i of this.uploadList){
	    			_size += Math.round(i.fileLen / 1024);
	    		};
	    		return _size;
			}
		}
	}
</script>
