<script>
	import { tel,showTel } from 'vuex_path/getters.js';
	import { updateAppHeader, setBuyOption } from 'vuex_path/actions.js';

	var Swipe = require('exports?Swipe!swipe.js');
	var imagesLoaded = require('imagesloaded');

	export default {
		components: {
			// Buy,
			// GoodsComment,
			// Quantity
		},
		vuex: {
			getters: {
                tel,
                showTel
            },
			actions: {
				updateAppHeader,
				setBuyOption
			}
		},
		route: {
			data(transition) {
				this.updateAppHeader({
                    type: 1
                });
				// 重置选择专题ID
				this.$root.$refs.footer.goodsData = {
					id: 0,
					num: 1
				}

				let id = transition.to.params.id;
				let is_package = transition.to.params.package;

				// 详情
				return Promise.all([this.getGoodsDetail({exchange_id: id})]);

			}
		},
		// created() {
		// 	this.updateAppHeader({
		// 		type: 1
		// 	});
		// },
		ready() {
			this.updateAppHeader({
                type: 1
            });
			let slider = this.$els.slider;
			new Swipe(slider, {
			  	speed: 800,
			  	auto: 4000,
			  	continuous: false,
			  	callback: function(index, elem) {
			  		$(slider).find('ol').children().removeClass("on").eq(index).addClass("on");
			  	}
			});
			let count = $(slider).find('.goods-other-li').length;
			let ol = '<ol>'
			if(count >= 2){
				for(let i = 0; i < count; i++){
					if(i == 0){
						ol += '<li class="on"></li>';
						continue;
					}
					ol += '<li></li>';
				}
				$self.append(ol + '</ol>');
			}
		},
		data() {
			return {
				exchange_id: this.$route.params.id,
				topicID: this.$route.params.name,
				selectedTopic: 0,
				saleText: '加载中...',
				showload: true,
				goods: {},
				comments: []
			}
		},
		methods: {
			getGoodsDetail(data) {
				return this.$http.post('/Integral/goodsInfo.json', data).then((res) => {
					res = res.json();
					if(res.status === 1){
						this.$set('goods', res.data);
						// 动画图片
						this.$root.$refs.footer.goodsImg = this.goods.original_img;
						this.$nextTick(() => {
							$('.pro-param dt').on('click', function(){
								$(this).closest('.pro-param').toggleClass('on');
							});
						});
					}
				});
			}
			
		},
		events: {
			quantityChange(num) {
				this.$root.$refs.footer.goodsData = {
					id: this.selectedTopic,
					num: num
				}
			}
		}
	}
</script>

<template>
	<div class="container">
        <div class="slide">
            <ul>
            	<li v-for="item in goods.image"><img :src="item.img_url" alt="" /></li>
            </ul>
            <div class="show-num"></div>
        </div>
        <!--产品名称 star-->
        <div v-if="!isTopic" class="basic-info">
            <div class="pro-name">
            	<span>{{goods.goods_name}}</span><!-- <a class="pro-data" v-link="{ name: 'goodsInfo', params:{id: gid} }">查看详情</a> -->
            </div>
            <div class="pro-price">
            	<span><i>￥</i><em id="shop-price"></em>{{goods.shop_price}}</span> <del>￥<i id="market-price">{{goods.market_price}}</i></del>
            </div>
        </div>
        <div class="points-price">
            兑换价：<span class="points">{{goods.point}}积分+￥{{goods.price}}.00</span>
        </div>
        <!--产品名称 end-->

        <!--产品参数-->
        <!-- <div v-if="goods.is_package == 1 && !isTopic" class="pro-param">
            <dl>
                <dt class="param-tit"><span>产品参数</span><i class="font icon-arrow-right"></i></dt>
                <dd>
                    <p v-for="item in goods.package_goods"><a href="javascript:;">{{item.goods_name}}</a><span>{{item.goods_number}}件</span></p>
                </dd>
            </dl>
        </div> -->
        <!--产品参数-->
		<!--规格-->
        <div v-if="!isTopic" class="pro-param pro-core">
            <dl>
                <dt class="param-tit"><span>规格</span><i class="font icon-arrow-right"></i></dt>
                <dd>
                    <p class="pro-effect">{{goods.weight}}</p>
                </dd>
            </dl>
        </div>
        <!--规格-->
        <!--适用肤质-->
        <div v-if="!isTopic" class="pro-param pro-core">
            <dl>
                <dt class="param-tit"><span>适用肤质</span><i class="font icon-arrow-right"></i></dt>
                <dd>
                    <p class="pro-effect">{{goods.guide}}</p>
                </dd>
            </dl>
        </div>
        <!--核心功效-->
        <div v-if="!isTopic" class="pro-param pro-core">
            <dl>
                <dt class="param-tit"><span>核心功效</span><i class="font icon-arrow-right"></i></dt>
                <dd>
                    <p class="pro-effect">{{goods.effect}}</p>
                </dd>
            </dl>
        </div>
        <!--核心功效-->
         <!--商品简述-->
        <div v-if="!isTopic" class="pro-param pro-core">
            <dl>
                <dt class="param-tit"><span>商品简述</span><i class="font icon-arrow-right"></i></dt>
                <dd>
                    <p class="pro-effect">{{goods.goods_brief}}</p>
                </dd>
            </dl>
        </div>
        <!--商品简述-->


       	<div class="copyright">
            <p>免费热线：{{ showTel }}</p>
            <p>公司名：江西瓷肌电子商务有限公司 </p>
            <p>赣ICP备12007816号-23</p>
        </div>
        <!-- 二维码 -->
        <div class="cj_wx">
            <div class="cj_com">
                <div class="cj_left">
                    <span><img src="/public/images/index/cj_wx.jpg" alt="" /></span>
                </div>
                <div class="cj_right">
                    <h4>扫一扫查物流</h4>
                    <div class="cj_check">
                        <p>关注“瓷肌Korea”</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- 二维码 -->
	</div>
</template>

<style>
	/* 晒单banner小圆点 */
    .show-num{position: absolute; bottom: 0.35rem; left: 40%;}
    .show-num span{width: 0.5rem; height: 0.5rem; border-radius: 1rem; background: #d5d5d5; display: inline-block; margin: 0 0.2rem;}
    .show-num span.on{background: #252525;}
    .show-banner{position: relative; overflow: hidden;}
    .show-banner ol{position: relative;}
    .show-banner ol li{width: 100%; float: left; position: relative;}
    .show-banner ol li img{width: 100%;}
</style>
<style scoped>
	.container{background: #f5f5f5;}
	/*首屏海报*/
	.slide{width: 100%; height: 320px; position: relative; overflow: hidden; padding-top: 0.7rem;}
	.slide ul{position: absolute; top: 0; left: 0; visibility: visible; z-index: 10; position: relative; overflow: hidden;text-align:center;background: #fff;}
	.slide ul > li{width: 300px; height: 300px; overflow:hidden;display: inline-block;}
	.slide ul > li img{display: block; width: 100%;}

	/*产品描述*/
	.basic-info{border-top: 1px solid #f3f4f4; padding: 0.6rem; margin: 0.4rem 0; background: #fff;}
	.pro-name{display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.pro-name span{flex: 1; -webkit-flex: 1; display: block;font-size:14px; }
	.pro-name a{border: 1px solid #bfbfbf; color: #686868; text-align: center; border-radius: 3px; padding: 0.2rem 1.2rem;}
	.basic-info .pro-price span{color: #da3737; font-size: 14px; display: inline-block;}
	.basic-info .pro-price span i{font-size: 0.75rem;}
	.pro-price del{font-size: 10px; color: #adadad;}

	/* 专题产品 */
	.topic-list{background: #fff;}
	.topic-list dt{padding: 0.8rem; border-bottom: 1px solid #e1e1e1; font-size: 0.9rem; color: #666;}
	.topic-list dd{padding: 0 0.8rem 0.8rem;}
	.topic-li{padding: 0.4rem 0; border-top: 1px dashed #e1e1e1;}
	.topic-list .topic-li:first-child{border-top: 0;}
	.topic-label{display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.topic-label label{flex: 1; -webkit-flex: 1; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.topic-name{padding-left: 0.4rem; flex: 1; -webkit-flex: 1;}
	.topic-price{padding-bottom: 0.2rem; color: #da3737; display: block;}
	.topic-price b{font-weight: normal; font-size: 0.8rem;}
	.topic-price em{font-size: 1.2rem;}
	.topic-info{padding-left: 1.4rem; font-size: 0.9rem; color: #bdbdbd; display: none; align-items: center; -webkit-align-items: center;}
	.topic-info-img{width: 6rem; height: 6rem; display: inline-block;}
	.topic-info p{margin-left: 0.8rem; flex: 1; -webkit-flex: 1; display: block;}
	.topic-li.selected .topic-info{display: flex; display: -webkit-flex;}

	/*产品参数*/
	.pro-param{background: #fff; overflow: hidden; margin: 0.4rem 0;}
	.pro-param dt.param-tit{padding: 0.65rem 0.65rem; font-size: 0.8rem; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.pro-param dt.param-tit span{color: #b2b2b2; flex: 1; -webkit-flex: 1; display: block;font-size:14px;}
	.pro-param dd{padding: 0.65rem .8rem 0.65rem 0.65rem; display: none;}
	.pro-param dd p{padding: 0.2rem 0; font-size: 0.8rem;  display: flex; display: -webkit-box; align-items: center; -webkit-align-items: center;}
	.pro-param dd p a{color: #da3737; text-decoration: underline; flex: 1; -webkit-flex: 1; display: block;}
	.pro-param .font{-webkit-transition: 0.2s ease-out;transition: 0.2s ease-out;color:#d0d0d0;}
	.pro-param.on .font{-webkit-transform: rotate(90deg);transform: rotate(90deg);}
	.pro-param.on dl dd{display:block;}
	.pro-param.on dl dt.param-tit{border-bottom:1px solid #efecec;}

	/*核心功效*/
	.pro-effect{color: #da3737;}

	/*本期优惠*/
	.pro-favo{background:#fff; margin:0.4rem 0; overflow: hidden; padding: 0.65rem 0.65rem;}
	.pro-favo dl{width:100%;display:table;}
	.pro-favo dl dt{display:table-cell;color:#b2b2b2;font-size:0.75rem;}
	.pro-favo dl dd{display:table-cell;}
	.pro-favo dl dd p{margin-top:0.2rem;color:#3a3a3a;font-size:0.75rem;}
	.pro-favo dl dd p .label-icon{color:#da3737;border:1px solid #da3737;padding:0 0.2rem;margin-right:0.2rem;border-radius:3px;}
	.pro-favo dl dd p a{float:right;color:#d0d0d0;}

	/*优惠信息*/
	.pay-favo{margin-top: 0.36rem;}
	.pay-favo p{background: #fff; font-size: 0.75rem; color: #747474; padding: 0.6rem 0.72rem;}
	.pay-favo p i{width: 0.8rem; height: 0.8rem; float: left; margin:0.1rem 0.2rem 0 0; background:url(/public/images/detail/favo.png) center center no-repeat; background-size: contain;}
	.pay-favo .favo-icon{padding: 0.6rem 0.72rem; overflow: hidden; background: #f8f8f8;}
	.pay-favo .favo-icon span{width: 20%; float: left; text-align: center; font-size: 0.64rem; display: inline-block;}
	.pay-favo .favo-icon span i{color: #ea2647; margin-right: 0.2em;}

	/*评价*/
	.good-comment .info{height: 2.4rem; padding: 0 0.8rem; font-size: 0.9rem; color: #b2b2b2; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.good-comment .info span{font-size: 0.84rem; flex: 1; -webkit-flex: 1; display: block;}
	.to-detail{padding: 0.8rem 0; background: #fff; text-align: center;}
	.to-detail .btn{width: 10rem; line-height: 2rem; border: 1px solid #000; border-radius: 10px; color: #282828;}

	/* 底部二维码 */
    .cj_wx{padding:0.5rem 0.5rem 1rem 3.5rem;}
    .cj_com{width: 100%; overflow: hidden; display: table;}
    .cj_com .cj_left{width:40%;padding:0.4rem;text-align:center;display: table-cell;background:#fff;}
    .cj_com .cj_right{width:59%;padding:0 0.5rem;display: table-cell;vertical-align: middle;}
    .cj_wx .cj_left span{display:inline-block;width:100%;}
    .cj_wx .cj_left span img{width:100%;max-width:200px;}
    .cj_com .cj_right .cj_check{width:100%;overflow: hidden;}
    .cj_com .cj_right h4{color: #000;font-size: 1rem;padding: .5rem 0 0.1rem;font-weight: bold;}
    .cj_com .cj_right p{font-size:0.85rem;color:#b8b8b8; padding-right: 0.1rem;line-height: 1.4rem;}
    .cj_com .cj_right span{color:#ec4270; width:9rem; display:inline-block; font-size:1.2rem; }

     /* 版权 */
    .copyright{padding-top: 1rem; overflow: hidden; text-align: center;}
    .copyright p{font-size: 0.8em; color: #b1b1b1; height: 1.2rem; line-height: 1.2rem;}
    .load-more{color: #f00;}
    .points-price {padding: 10px;color: #999;font-size: 16px;background: #fff;}
	.points-price .points {color: #f60;}
	.points-price .points::before {content: '';width: 10px;height: 10px;background: url(/public/images/user/icon_cout_r.png) no-repeat;background-size: 10px;display: inline-block;margin-right: 5px;}
</style>