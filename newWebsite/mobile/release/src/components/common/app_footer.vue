<script>
	import BuyOption from './buy_option.vue';

	import { tel, QQ, SWT, actName, shoppingCart, buyOption } from 'vuex_path/getters.js';
	import { addGoodsToCart } from 'vuex_path/actions.js';

	export default {
		components: {
			BuyOption
		},
		vuex: {
			getters: {
				tel,
				QQ,
				SWT,
				actName,
				shoppingCart,
				buyOption
			},
			actions: {
				addGoodsToCart
			}
		},
		ready() {
			this.$root.paddingBottom = '3.4rem';

			let channel = this.$route.query.campaign;
			if (channel) {
				channel = channel.match(/\d+/g)[0];
			}

			switch (parseInt(channel)){
				case 22:
					this.QQ_IMG = 'floatqq05.png';
					break;
				case 57:
					this.QQ_IMG = 'floatqq02.jpg';
					break;
				case 81:
					this.QQ_IMG = 'floatqq06.png';
					break;
				case 27:
					this.QQ_IMG = 'floatqq05.png';
					break;
				case 65:
					this.QQ_IMG = 'floatqq07.png';
					break;
				case 70:
					this.QQ_IMG = 'floatqq08.png';
					break;
				case 82:
					this.QQ_IMG = 'floatqq02.jpg';
					break;
				case 69:
					this.QQ_IMG = 'floatqq08.png';
					break;
				// case 62:
				// 	this.QQ_IMG = 'floatqq08.png';
				// 	break;
				default:
					this.QQ_IMG = 'floatqq01.png';
					break;
			}

			// this.$http.jsonp('http://wpa.b.qq.com/cgi/wpa.php').then((res) => {
			// 	console.log(res);
			// }, () => {
			// 	if (this.QQ) {
			// 		BizQQWPA.addCustom([{
			// 			aty: '0',
			// 			a: '0',
			// 			nameAccount: this.QQ,
			// 			selector: 'QQ'
			// 		}]);
			// 		$(document).on('click', '.js-QQ', () => {
			// 			$('#QQ').trigger('click');
			// 		});
			// 	}
			// });

			function mqqChat(qq, type, callback) {
				var ua=window.navigator.userAgent.toLowerCase(),url;
				if(ua.indexOf('mobile')>-1){//移动端
					 if(ua.indexOf('micromessenger')<0){//除了微信
						type=type===2?'crm':'wpa';
						var app;
						if(ua.indexOf('mac os x')> -1)app='mqq';
						else app='mqqwpa';
						url=app+'://im/chat?chat_type='+type+'&uin='+qq+'&version=1&src_type=web&web_src=http:://'+window.location.hostname;
						if(ua.indexOf('qq/')>-1){//手机QQ
							window.location.href=url;
							if(typeof(callback)=='function'){
								callback(true);
							}
						}
						else if(ua.indexOf('android')>-1){//安卓手机
							var win=window.open(url);
							var status=true;
							setTimeout(function(){
								if(!win.closed){
									win.close();
									status=false;
								}
								if(typeof(callback)=='function'){
									callback(status);
								}
							},500);
						}
						else if(ua.indexOf('mac os x')> -1){//苹果手机
							window.location.href=url;
							var _onblur=window.onblur;
							window.onblur=function(){
								if(typeof(callback)=='function'){
									callback(true);
								}
								window.onblur=_onblur;
							};
						}
						else{//其他机型
							window.location.href=url;
						}
					}
					else{//微信
						if(type===2)window.location.href='http://wpd.b.qq.com/page/info.php?nameAccount='+qq;
						else window.location.href='http://wpa.qq.com/msgrd?v=1&uin=' +qq + '&site=qq&menu=yes';
					}
				}
				else{//台式电脑
					if(type===2)url='http://wpa.b.qq.com/cgi/wpa.php?ln=2&uin='+qq;
					else url='http://wpa.qq.com/msgrd?v=1&uin=' +qq + '&site=qq&menu=yes';
					if(!mqqChat.qqwpa_iframe){
						var ifr=document.createElement('iframe');
						ifr.style.position='absolute';
						ifr.style.width='0px';
						ifr.style.height='0px';
						ifr.style.opacity='0';
						ifr.style.overflow='hidden';
						document.body.appendChild(ifr);
						mqqChat.qqwpa_iframe=ifr;
					}
					mqqChat.qqwpa_iframe.src='about:blank';
					mqqChat.qqwpa_iframe.src=url;
				}
			}

			this.$watch('QQ', (val) => {
				$(document).on('click', '.js-QQ', () => {
					mqqChat(val, 2);
				});
			});

			$(document).on('click', '.js-SWT', () => {
				$('#SWT').trigger('click');
			});
		},
		data() {
			return {
				activate: true,
				QQ_IMG: '',
				goodsData: '',
				goodsImg: ''
			}
		},
		computed: {
			isIndx() {
				return this.$route.name == 'index' ? true : false;
			},
			isCodeExchange() {
				return this.$route.name == 'pointsGood' ? true : false;
			},
			detailFooter() {
				return /topic|goodsDetail|goodsInfo|pointsGood/.test(this.$route.name);
			},
			hideFooter() {
				return /shoppingCart/.test(this.$route.name);
			},
			hasOption() {
				let name = this.$route.name;
				let id = this.$route.params.id;
				return /topic/.test(name) || /goodsInfo/.test(name) && !parseInt(id);
			},
            shareOption(){
                let name = this.$route.name;
                let id = this.$route.params.id;
                return /topic|goodsDetail|goodsInfo|brand|article/.test(this.$route.name);
            }
		},
		methods: {
			addToCart() {
				if(this.hasOption){
					if(this.goodsData.id != 0){
						this.activate = false;
						this.$root.$broadcast('buyOption');
					}else{
						this.$dispatch('popup', '请勾选商品');
					}
					
					// this.$nextTick(() => {
					// 	this.$root.$broadcast('buyOption');
					// })
					
					// if(!this.goodsData.id){
					// 	this.$dispatch('popup', '请勾选商品');
					// }else{
					// 	this.addGoodsToCart(this.goodsData);
					// }
				}else{
					let data = {
						id: this.$route.params.id
					}
					if(this.goodsImg) data.delay = 1200;
					this.addGoodsToCart(data, () => {
						$('.goods-imgs').addClass('on');
			        	setTimeout(function(){$('.goods-imgs').removeClass('on')}, 1200);
					});
				}
			},
			toBuy() {
				let exchange_id = this.$route.params.id;
				this.$route.router.go({name: 'exchangeOorder' , params:{ id:exchange_id }});
			},
            share(){
                var os = function() {
                    var ua = navigator.userAgent,
                            isQQ = /(?:QQ)/.test(ua),
                            isQQBrowser = /(?:MQQBrowser)/.test(ua),
                            isAndroid = /(?:Android)/.test(ua),
                            isWX = /(?:MicroMessenger)/.test(ua);

                    return {
                        isQQ: isQQ,
                        isQQBrowser: isQQBrowser,
                        isWX: isWX,
                        isAndroid:isAndroid,
                        ua
                    };
                }();
                var sharetips = function(){
                    $('.share_btn').after('<div id="share_tips"><img src="/public/images/common/share_arrow.png" alt="分享提示" /></div>');
                    if($('#share_tips').length > 0){
                        $('#share_tips').click(function(){
                            $(this).hide();
                        })
                    }
                }
                if(os.isWX & os.isQQBrowser){
                    sharetips();
                    return  false;
                }else if(os.isQQ & os.isQQBrowser & os.isAndroid){
                    sharetips();
                    return  false;
                }else if(os.isQQ & os.isQQBrowser){
                    alert('建议使用浏览器自带的分享功能');
                    return  false;
                }else if(os.isQQ || os.isWX){
                    sharetips();
                    return  false;
                }else{
                    alert('建议使用浏览器自带的分享功能');
                    return  false;
                }
            }
		}
	}
</script>

<template>
    <div class="share_btn" @click="share()" v-if="shareOption">分享</div>
    <!--<div id="shareBox" class="width-full share-box">-->
    <!--<ul class="clf">-->
    <!--<li><a class="nativeShare" data-app="QQ" ><img src="/public/images/common/share_qq.png" alt="" /></a><span>QQ好友</span></li>-->
    <!--<li><a class="nativeShare" data-app="QZone"><img src="/public/images/common/share_QQzone.png" alt="" /></a><span>QQ空间</span></li>-->
    <!--<li><a class="nativeShare" data-app="weixin"><img src="/public/images/common/share_weixin.png" alt="" /></a><span>微信</span></li>-->
    <!--<li><a class="nativeShare" data-app="weixinFriend"><img src="/public/images/common/share_pyq.png" alt="" /></a><span>朋友圈</span></li>-->
    <!--<li><a class="nativeShare" data-app="sinaWeibo"><img src="/public/images/common/share_weibo.png" alt="" /></a><span>新浪微博</span></li>-->
    <!--</ul>-->
    <!--<i class="font icon-bottom-close close"><img src="/public/images/common/share_close.png" alt="" /></i>-->
    <!--</div>-->
	<buy-option v-if="hasOption" v-on:buyOption></buy-option>
	<div v-if="detailFooter" class="width-full fixed-footer fixed-detail" :class="{'close': !activate}">
		<a v-if="QQ" class="js-QQ" href="javascript:;"><i class="font icon-consult"></i><em>QQ咨询</em></a>
		<a v-if="SWT" href="javascript:;" onclick="openZoosUrl('chatwin')"><i class="font icon-consult"></i><em>咨询</em></a>
		<a :href="'tel:' + tel"><i class="font icon-tel"></i><em>热线</em></a>
		<!-- <a v-if="isCodeExchange" href="javascript:;" class="js-points-btn" @click="toBuy({exchange_id: id, num: quantity})">积分兑换</a> -->
		<a v-if="isCodeExchange" href="javascript:;" class="js-points-btn" @click="toBuy()">积分兑换</a>
		<a v-else class="btn-cart" v-link="{ name: 'shoppingCart' }"><div class="goods-imgs"><span><img :src="goodsImg" alt="" /></span></div><i class="font icon-cart"></i><em>购物车</em><b>{{shoppingCart.quantity}}</b></a>
		<a v-if="!isCodeExchange" class="btn-add-cart" href="javascript:;" @click="addToCart"><em>加入购物车</em></a>
	</div>
	<div v-if="detailFooter == false && hideFooter == false" class="width-full fixed-footer" :class="{'close': !activate}">
		<a v-if="isIndx" v-link="{ name: 'user' }"><i class="font icon-user"></i><em>会员</em></a>
		<a v-else v-link="{ name: 'index' }"><i class="font icon-home"></i><em>首页</em></a>
		<a v-link="{ name: 'act', params: {name: actName} }"><i class="font icon-hot-sales"></i><em>促销</em></a> 
		<a :href="'tel:' + tel" ><i class="font icon-tel"></i><em>热线</em></a>
		<a v-if="QQ" class="js-QQ" href="javascript:;"><i class="font icon-consult"></i><em>QQ咨询</em></a>
		<a v-if="SWT" href="javascript:;" onclick="openZoosUrl('chatwin')"><i class="font icon-consult"></i><em>咨询</em></a>
	</div>
	<div v-if="QQ" class="js-QQ fixed-QQ">
		<img :src="'/public/images/qq/' + QQ_IMG" alt="" />
	</div>
	<!-- <div id="QQ" style="display:none;"></div> -->
	<div id="SWT" style="display:none;" onclick="openZoosUrl('chatwin')"></div>
</template>

<style>
    #share_tips{ background-color:rgba(0,0,0,0.8); position: fixed; bottom:0;width: 100%; max-width: 640px; height: 100%; z-index: 10000; text-align: right;}
    #share_tips img{ width: 56%; max-width: 354px; box-sizing: content-box; padding: 2rem 3rem 0 0;}
</style>
<style scoped>
.share_btn{background-color:rgba(0,0,0,0.6); position: fixed; right: 0; bottom: 20%; color: #eee; width: 1.5rem; text-align: center; line-height: 1rem; font-size: 0.8rem; padding: 0.4rem 0.3rem; border-radius: 0.3rem 0 0 0.3rem;box-sizing: content-box;}
.share_btn:before{ content: ""; display: inline-block; width: 1rem; height: 1rem; background: url(/public/images/common/share_icon.png) no-repeat center; background-size: 100% 100%;}
.share-box.on{visibility: visible; opacity: 1; -webkit-transition: 0.6s; transition: 0.6s;}
.share-box{background: rgba(0,0,0,0.75); text-align: center; position: fixed; bottom: 0; z-index: 10000; visibility: hidden; opacity: 0;}
.share-box li{width: 33.3%; color: #fff; float: left; margin-top: 1em; text-align: center;}
.share-box li a{width: 4rem; display: inline-block;}
.share-box li img{ width: 100%;}
.share-box li span{display: block; margin-top: 0.4em;}
.share-box .close{ color: #fff; display: inline-block; margin: 2em auto; width: 2rem;}

.fixed-footer{height: 3.4rem; background: rgba(0,0,0,0.8); display: table; position: fixed; bottom: 0; z-index: 9998;}
.fixed-footer.close{visibility: hidden;}
.fixed-footer > a{width: 25%; font-size: 0.8rem; color: #fff; text-align: center; display: table-cell; vertical-align: middle;}
.fixed-footer .font{font-size: 1.4rem; display: block;}
.fixed-detail > a{width: 20%;}
.fixed-footer .js-points-btn{width:50%;background-color: #F44;color: #fff;}
.fixed-detail .btn-cart{background: #f7f7f7; color: #313131; position: relative;}
.fixed-detail .btn-cart .goods-imgs{position: absolute;    top: -1rem;right: -3rem;width: 4rem;height: 4rem;text-align: left;font-size: 0;-webkit-transform: all 1s;-ms-transform: all 1s;-o-transform: all 1s;transform: all 1s;display:none;}
.fixed-detail .btn-cart .goods-imgs.on{-webkit-animation: rotateInDownRight 1.2s ease-in 0s ;-o-animation: rotateInDownRight 1.2s ease-in 0s ;animation: rotateInDownRight 1.2s ease-in 0s ;display:block;}
.fixed-detail .btn-cart .goods-imgs span{display:inline-block;width:1.5rem;height:1.5rem;border-radius:50%;overflow:hidden;-webkit-animation: scaleImg 1.2s ease-in 0s ;-o-animation: scaleImg 1.2s ease-in 0s ;animation: scaleImg 1.2s ease-in 0s ;}
.fixed-detail .btn-cart b{min-width: 1.2rem; height: 0.9rem; background: #f12d2e; border-radius: 1.2rem; font-weight: normal; font-family: Arial, Helvetica, sans-serif; font-size: 0.6rem; color: #fff; display: flex; display: -webkit-flex; justify-content: center; -webkit-justify-content: center; align-items: center; -webkit-align-items: center; position: absolute; top: 0; right: 0.4rem;}
.fixed-detail .btn-add-cart{width: 40%; background: #da3737; font-size: 0.9rem; color: #fff;}
.fixed-QQ{width: 24%; max-width: 100px; position:fixed; z-index:9999; right:0; top:20%; cursor:pointer;}
@-webkit-keyframes rotateInDownRight {
  0% {
    -webkit-transform-origin: right bottom;
            transform-origin: right bottom;
    -webkit-transform: rotate3d(0, 0, 1, 90deg);
            transform: rotate3d(0, 0, 1, 90deg);
    opacity: 1;
  }
  50%{opacity: 1;}
  85%{opacity: 1;}
  100% {
    -webkit-transform-origin: right bottom;
            transform-origin: right bottom;
    -webkit-transform: none;
            transform: none;
    opacity: 0;
  }
}
@keyframes rotateInDownRight {
  0% {
    -webkit-transform-origin: right bottom;
            transform-origin: right bottom;
    -webkit-transform: rotate3d(0, 0, 1, 90deg);
            transform: rotate3d(0, 0, 1, 90deg);
    opacity: 1;
  }
  50%{opacity: 1;}
  85%{opacity: 1;}
  100% {
    -webkit-transform-origin: right bottom;
            transform-origin: right bottom;
    -webkit-transform: none;
            transform: none;
    opacity: 0;
  }
}
@-webkit-keyframes scaleImg {
  0% {-webkit-transform:scale(1,1)}
  65%{-webkit-transform:scale(1.8,1.8)}
  85%{-webkit-transform:scale(1.2,1.2)}
  100%{-webkit-transform:scale(1,1)}
}

@keyframes scaleImg {
  0% {-webkit-transform:scale(1,1)}
  65%{-webkit-transform:scale(1.8,1.8)}
  85%{-webkit-transform:scale(1.2,1.2)}
  100%{-webkit-transform:scale(1,1)}
}
</style>