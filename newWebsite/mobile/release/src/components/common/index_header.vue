<script>
	import { actName } from 'vuex_path/getters.js';
	import { shoppingCart } from 'vuex_path/getters.js';

	export default {
		vuex: {
			getters: {
				actName,
				shoppingCart
			}
		},
		ready() {
			this.$http.post('/Goods/getCates.json').then((res) => {
					res = res.json();
					if(res.status == 1){
						this.list = res.data;
					}
				});

			$('#menubtn').on('click', function(){
                $('.nav-side-warpper').toggleClass('on');
            });

            $('.nav-side-warpper').on('click', function(e){
                if(e.target == this){
                    $('.nav-side-warpper').removeClass('on');
                }
            });

            $('.t-nav li').not('.goods-choice').on('click', function(e){
            	$('.nav-side-warpper').removeClass('on');
            });
            //  滚轮事件
            $(document).scroll(function(){
                var st = document.documentElement.scrollTop || window.pageYOffset || document.body.scrollTop;
                if(st>10){
                    $('.header').addClass('on');
                    $('.cj-head').css({
                        'border-bottom':'0.3rem solid #fff'
                    });
                }else{
                    $('.header').removeClass('on');
                    $('.cj-head').css({
                        'border-bottom':'0.3rem solid #202020'
                    })
                }
            });
		},
		data() {
			return {
				list: '',
				showMenuEffect: false
			}
		},
		computed: {
			hasBack() {
				return /topic|goodsDetail|buyer|orderAll|myfocu|promocodes|pointsstore|pointsGoods|codeExchange|codesHistory|coupons|address/.test(this.$route.name);
			}
		},
		methods: {
			show() {
				this.showMenuEffect = !this.showMenuEffect;
			}
		}
	}
</script>

<template>
	<div class="width-full header">
        <div class="head-line clearfix">
        	<div class="cj-head clearfix">
        		<a v-if="hasBack" class="header-btn" href="javascript:history.back();"><i class="font icon-arrow-left"></i></a>
        		<a id="menubtn" class="header-btn" href="javascript:;"><i class="font icon-menu"></i></a>
	            <div class="logo">
	            	<img v-link="{ name: 'index' }" src="/public/images/index/logo.png" alt="" />
	            </div>
	            <a v-if="hasBack" class="header-btn"></a>
	            <a class="header-btn" v-link="{ name: 'shoppingCart' }"><i class="font icon-cart"></i><b>{{shoppingCart.quantity}}</b></a>
        	</div>
           
        </div>
	</div>
	<div class="width-full nav-side-warpper">
	        <div class="sidebar">
	            <ul class="t-nav">
	            	<li>
	                    <a v-link="{ name: 'index' }"><span>首页 홈</span><i class="font icon-arrow-right"></i></a>
	                </li>
	                <li>
	                    <a v-link="{ name: 'goodsList', params: {cid: 0, package: 0} }"><span>全部分类 분류</span><i class="font icon-arrow-right"></i></a>
	                </li>
	                <li>
	                    <a v-link="{name: 'searTest'}"><span>肌肤测试 테스트</span><i class="font icon-arrow-right"></i></a>
	                </li>
	                <li>
	                    <a v-link="{ name: 'act', params: {name: actName} }"><span>促销活动 프로모</span><i class="font icon-arrow-right"></i></a> 
	                </li>
	                <li>
	                    <a v-link="{ name: 'goodsList', params: {cid: 0, package: 0} }"><span>热销推荐 뜨거운</span><i class="font icon-arrow-right"></i></a>
	                </li>
	                <li>
	                    <a v-link="{ name: 'goodsList', params: {cid: 0, package: 2} }"><span>人气套装 인기</span><i class="font icon-arrow-right"></i></a>
	                </li>
	                <li>
	                    <a v-link="{ name: 'goodsList', params: {cid: 0, package: 1} }"><span>畅销单品 잘판매</span><i class="font icon-arrow-right"></i></a>
	                </li>
	                <li class="goods-choice" @click="show">
	                    <a href="javascript:void(0)"><span>功效选择 선택효과</span><i class="font icon-arrow-right"></i></a>
	                </li>
	                <li class="cli-nav" :class="{on: showMenuEffect}">
	                	<a v-for="item in list" v-link="{ name: 'goodsList', params: {cid: item.cat_id, package: 0} }">{{item.cat_name}}</a>
	                </li>
	            </ul>
	        </div>
	    </div>
</template>

<style scoped>
	.header{/* height:4rem; */ position: fixed; top: 0; z-index: 9999; padding-bottom: 0;}
	.head-line{padding:0 0.8rem; background: #fff;}
	.header.on{background:url(/public/images/common/header_shadow.png) 0 4.3rem repeat-x;padding-bottom:0.5rem;}
	.cj-head{height: 4.3rem;/* margin: 0 0.8rem; */ border-bottom: 0.3rem solid #202020; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center; background: #fff;}
	.header .logo{height: 100%; flex: 1; -webkit-flex: 1; display: flex; display: -webkit-flex; justify-content: center; -webkit-justify-content: center; align-items: center; -webkit-align-items: center;}
	.header .logo img{height: 68%; cursor: pointer;}
	.header-btn{width: 2rem; height: 100%; text-align: center; position: relative; display: flex; display: -webkit-flex; justify-content: center; -webkit-justify-content: center; align-items: center; -webkit-align-items: center;}
	.header-btn .font{font-size: 1.8rem;position: relative;}
	.header-btn b{width: 1.2rem; height: 1.2rem; background: #f12d2e; border-radius: 1.2rem; font-weight: normal; font-family: Arial, Helvetica, sans-serif; font-size: 0.6rem; color: #fff; display: flex; display: -webkit-flex; justify-content: center; -webkit-justify-content: center; align-items: center; -webkit-align-items: center; position: absolute; top: 0.4rem; right: -0.34rem;font-weight: bold;}

	/*侧边栏样式*/
	.nav-side-warpper.on{visibility: visible;}
	.nav-side-warpper.on .sidebar{transform: translate3d(0,0,0);}

	.nav-side-warpper{padding-top: 4rem; position: fixed; top: 0; bottom: 0; z-index: 9999; visibility: hidden;}
	.nav-side-warpper .sidebar{height: 100%; margin-right: 7.5rem; background: #fff; box-shadow: 3px 3px 10px #EAEAEA; transform: translate3d(-100%,0,0); transition: 0.4s; overflow-y: auto; -webkit-overflow-scrolling: touch;}

	.t-nav li{line-height: 2.8rem; font-size: 1rem; color: #b1b1b1; padding: 0 0.8rem;}
	.t-nav li a{border-bottom: 1px solid #f2f2f2; color: #484848; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.t-nav li span{flex: 1; -webkit-flex: 1; display: block;}
	.t-nav li .font{font-size: 0.9rem; color: #c1c1c1;}
	.t-nav li.on{background: #f3f3f3;}
	.t-nav li.on a{border-bottom: 1px solid #e0e0e0;}
	.t-nav li.on span{transform: rotate(90deg); transform-origin: center center;}
	.cli-nav{background: #f3f3f3; opacity: 0; transition: 1s;}
	.cli-nav.on{opacity: 1;}
	.cli-nav a{border-bottom: 1px solid #e0e0e0;}
</style>