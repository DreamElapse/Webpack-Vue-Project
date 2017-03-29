<script>
	import { tel , showTel } from 'vuex_path/getters.js';
	import { updateAppHeader, setBuyOption } from 'vuex_path/actions.js';
	import Buy from './buy.vue';
	import GoodsComment from './goods_comment.vue';
	import Quantity from './quantity.vue';

	var Swipe = require('exports?Swipe!swipe.js');
	var imagesLoaded = require('imagesloaded');

	export default {
		components: {
			Buy,
			GoodsComment,
			Quantity
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
		
		ready() {


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
				this.selectedTopic = 0;
				this.topicList = '';
				let id = transition.to.params.id;
				let is_package = transition.to.params.package;
				this.isCollect();
				if(this.$route.name == 'topic'){
					// 专题
					this.isTopic = true;
					this.topicID = this.$route.params.name;
					this.q1=this.arrObj[this.topicID][0];
					this.q2=this.arrObj[this.topicID][1];
					this.loading = true;
					let data = {
						page_name: this.$route.params.name,
						limit: 3
					}
					return Promise.all([this.getTopic(data)]).then(() => {
						this.loading = false;
					}, () => {
						this.loading = false;
					}).catch(() => {
						this.loading = false;
					});
				}else{
					// 详情
					this.topicList = '';
					this.isTopic = false;
					this.gid = this.$route.params.id;
					this.loading = true;
					return Promise.all([this.getGoodsDetail({gid: id, is_package: is_package})]).then(() => {
						this.loading = false;
					}, () => {
						this.loading = false;
					}).catch(() => {
						this.loading = false;
					});
					

				}
			}
		},
		data() {
			return {
				loading: false,
				gid: '',
				topicID: '',
				isTopic: false,
				topicName: '',
				topicList: '',
				selectedTopic: 0,
				saleText: '加载中...',
				showload: true,
				goods: {},
				collect:{},
				isstatus:'',
				goodsType:'',
				comments: [],
				arrObj:{
					mm:['面膜怎么敷效果最好？','皮肤科医生在线解答，教你护肤'],  //mm
					mb:['你更适合哪种美白方法？','皮肤科医生在线测试，教你变白'],  //mb
					qb:['除斑点，哪种方法最有效？','皮肤医生定制方案，教你祛斑'],   //qb
					qdwoman:['为什么痘痘反复长?','皮肤科医生免费诊断，教你祛痘'],  //qdwoman
					bs:['脸干掉皮怎么办？','皮肤科医生教你1招，脸蛋水又嫩'],  //bs
					qht:['安全战胜黑头，秘诀是什么？','皮肤科医生教你1招，鼻头超干净'],  //qht
					qj:['超火洗脸秘诀，1招就嫩','皮肤科医生在线解答，手把手教你'],  //qj
					qdman:['脸油多的男生，想变帅？','皮肤科医生在线诊断，教你护肤变帅']   //qdman
				},
				q1:'',
				q2:''
			}
		},
		methods: {
			getGoodsDetail(data) {
				return this.$http.post('/Goods/detail.json', data).then((res) => {
					res = res.json();
					if(res.status === 1){
						this.$set('goods', res.data);
						this.goodsType = res.data.is_package; 
						// 动画图片
						this.$root.$refs.footer.goodsImg = this.goods.original_img;
						this.$nextTick(() => {
							$('.pro-param dt').on('click', function(){
								$(this).closest('.pro-param').toggleClass('on');
							});
						});
					}
				});
			},
			isCollect(){

				let data ={
					goods_id:this.$route.params.id
				} 
				this.$http.post('/Goods/isCollectGoods.json',data).then((res) => {
					res = res.json();
					this.collect = res;
					this.isstatus = res.status;
				});	
			},
			getTopic(data = {}) {
				return this.$http.post('/Goods/specialPage.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						for(let i of res.data.goods_list){
							i.quantity = 1;
						}
						this.topicName = res.data.name;
						if(res.data.goods_list.length > 0){
							this.topicList = res.data.goods_list;
							this.showload = false;
						}else{						
							this.saleText = '商品已下架，详情请咨询客服';
							this.showload = true;
						}
						this.comments = res.data.comment_list;
						this.selectedTopic = res.data.goods_list[0].goods_id;
						this.setBuyOption({
							selected: res.data.goods_list[0],
							list: this.topicList 
						});
						this.$root.$refs.footer.goodsData = {
							id: res.data.goods_list[0].goods_id,
							num: res.data.goods_list[0].quantity
						}
					}
				});
			},
			selectTopic(goods,index) {  //勾选专题商品时传值，可以正常传值
				this.setBuyOption({
					selected: this.topicList[index],
					list: this.topicList 
				});
				this.$root.$refs.footer.goodsData = {
					id: goods.goods_id,
					num: goods.quantity
				}

		
			}
		},
		events: {
			quantityChange(num) {   //正常返回数值
				this.$root.$refs.footer.goodsData = {
					id: this.selectedTopic,
					num: num
				}
			},
			// selectedTopic(){
			// 	this.selectedTopic = this.$root.$refs.footer.goodsData.id;
			// },
			isStatus(msg) {
			
				this.isstatus = msg;
				
			 }
		}
	}
</script>

<template>
	<div class="container">
		<div v-show="loading" class="loading"></div>

        <div v-if="isTopic">
        	<img :src="'/public/images/topic/banner/' + topicID + '.jpg'" alt="" />
        </div>
        <div v-else class="slide">
            <ul>
            	<li><img :src="goods.original_img" alt="" /></li>
            </ul>
            <div class="show-num"></div>
        </div>

		<!-- 专题 -->
		<div v-if="isTopic" class="basic-info">
			<div class="pro-name">
				<span>韩国瓷肌{{topicName}}方案</span><a class="pro-data" v-link="{ name: 'goodsInfo', params:{id: $route.params.name} }">查看详情</a>
			</div>
        </div>
        <!-- 头部二维码 -->
        <div class="webchat_area {{topicID}}" v-if="isTopic">
	    	<div class="wc_l">
	    		<p class="p1">{{q1}}</p>
	    		<p>微信号： beautifulchnskin</p>
	    		<p>{{q2}}   </p>
	    	</div>
	    	<div class="wc_r">
	    		<img src="/public/images/topic/wx_code.jpg" alt="">
	    	</div>
	    </div>
        <dl v-if="isTopic" class="topic-list">
        	<dt>专题产品</dt>
        	<dd>
        		<div class="topic-li" :class="{selected: selectedTopic == item.goods_id}" v-for="item in topicList">
        			<div class="topic-label">
	        			<label @click="selectTopic(item,$index)">
	        				<input class="checkbox" type="radio" :value="item.goods_id" v-model="selectedTopic">
	        				<div class="topic-name">
	        					<h3>{{item.goods_name}}</h3>
	        					<span class="topic-price"><b>￥</b><em>{{item.shop_price}}</em></span>
	        				</div>
	        				<quantity :quantity.sync="item.quantity" v-on:quantityChange></quantity>
	        			</label>
					</div>
					<div class="topic-info">
						<a class="topic-info-img" v-link="{ name: 'topic' }"><img :src="item.goods_img" alt="" /></a>
						<p>{{item.attr_value}}</p>
					</div>
        		</div>
        		<div class="load-more" v-if="showload">{{saleText}}</div>
        	</dd>
        </dl>

        <!--产品名称 star-->
        <div v-if="!isTopic" class="basic-info">
            <div class="pro-name">
            	<span>{{goods.goods_name}}</span><a class="pro-data" v-link="{ name: 'goodsInfo', params:{id: gid} }">查看详情</a>
            </div>
            <div class="pro-price">
            	<span><i>￥</i><em id="shop-price"></em>{{goods.shop_price}}</span> <del>￥<i id="market-price">{{goods.market_price}}</i></del>
            </div>
        </div>
        <!--产品名称 end-->

        <!--产品参数-->
        <div v-if="goods.is_package == 1 && !isTopic" class="pro-param">
            <dl>
                <dt class="param-tit"><span>产品参数</span><i class="font icon-arrow-right"></i></dt>
                <dd>
                    <p v-for="item in goods.package_info.package_goods"><a href="javascript:;">{{item.goods_name}}</a><span>{{item.goods_number}}件</span></p>
                </dd>
            </dl>
        </div>
        <!--产品参数-->

        <!--核心功效-->
        <div v-if="!isTopic" class="pro-param pro-core">
            <dl>
                <dt class="param-tit"><span>核心功效</span><i class="font icon-arrow-right"></i></dt>
                <dd>
                    <p class="pro-effect">{{goods.attr_value}}</p>
                </dd>
            </dl>
        </div>
        <!--核心功效-->

        <!--本期优惠-->
        <div class="pro-favo">
            <dl>
                <dt class="param-tit"><span>本期优惠</span></dt>
                <dd>
                    <!-- <p><i class="label-icon">减</i>在线支付，立减10元<a href="javascript:void(0)"><i class="font icon-arrow-right"></i></a></p> -->
                    <p><i class="label-icon">免</i>包邮福利，满200免运费<a href="javascript:void(0)"><i class="font icon-arrow-right"></i></a></p>
                </dd>
            </dl>
        </div>
        <!--本期优惠-->

        <!--购买数量-->
        <buy v-if="!isTopic" :id="goods.goods_id" :ispackage="goodsType" :isstatus="parseInt(isstatus)" :quantity="1"></buy>
        <!--购买数量-->

        <div class="pay-favo">
            <p><i></i>包装升级：新旧包装均为正品，请放心购买</p>
            <div class="favo-icon">
                <span><i class="font icon-round-check"></i>官网正品</span>
                <span><i class="font icon-round-check"></i>7天退换货</span>
                <span><i class="font icon-round-check"></i>1对1咨询</span>
                <span><i class="font icon-round-check"></i>闪电发货</span>
                <span><i class="font icon-round-check"></i>无效返券</span>
            </div>
        </div>

        <!--评价-->
        <div class="good-comment">
            <div class="info" v-link="{ name: 'goodsInfo', params: {id: isTopic ? $route.params.name : gid, view: 'comment'} }">
            	<span>评价</span><i class="font icon-arrow-right"></i>
            </div>
            <goods-comment :limit="3" :comment-list="comments"></goods-comment>
       	</div>
       	<div class="to-detail">
       		<a v-if="isTopic" class="btn" v-link="{ name: 'goodsInfo', params:{id: $route.params.name} }">查看图文详情</a>
       		<a v-else class="btn" v-link="{ name: 'goodsInfo', params:{id: gid} }">查看图文详情</a>
       	</div>
       	<div class="copyright">
       		<p>免费热线：{{ showTel }}</p>
            <p>公司名：江西瓷肌电子商务有限公司 </p>
            <p>赣ICP备12007816号-23</p>
        </div>
        <!-- 二维码 -->
        <!--<div class="cj_wx">
            <div class="cj_com">
                <div class="cj_left">
                    <span><img src="/public/images/index/cj_wx.jpg" alt="" /></span>
                </div>
                <div class="cj_right">
                    <h4>扫一扫查物流</h4>
                    <div class="cj_check">
                        <p><span>beautifulchnskin</span><br />关注微信号查物流</p>
                        <p>关注“瓷肌Korea”</p>
                    </div>
                </div>
            </div>
        </div>-->
        <div class="top_wx">
			<p class="left">
				长按复制加微信<br />免费咨询皮肤科医生<br /><span>beautifulchnskin</span>
			</p>
			<img src="/public/images/detail/wx.jpg" alt="">
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
	
	/* 头部二维码  */
	.webchat_area {background: #f5f5f5;padding: 0.6em 1.2em;box-sizing: border-box;overflow: hidden;border-top: 1px solid #e5e5e5;}
	.webchat_area .wc_l{float: left;width: 60%;text-align: center;margin: 0 3%;}
	.webchat_area .wc_r{float: left;width: 32%;}
	.webchat_area .wc_l p:nth-of-type(1){font-size: 1em;margin: 1em 0 0;}
	.webchat_area .wc_l p:nth-of-type(2){background: #171717;font-size: 0.85em;color: #fff;border-radius: 2em;font-weight: bold;margin: 0.2em 0;height: 3em;line-height: 3em;}
	.webchat_area .wc_l p:nth-of-type(2) span{color: #74ea00;}
	.webchat_area .wc_l p:nth-of-type(3){color: #5a5a5a;font-size: 0.75em;}
	.webchat_area.mb .p1{color: #eb1338;}
	.webchat_area.qb .p1{color: #c29806;}
	.webchat_area.qdwoman .p1{color: #02bbbc;}
	.webchat_area.qht .p1{color: #02bbbc;}
	.webchat_area.qj .p1{color: #02bbbc;}
	.webchat_area.mm .p1{color: #4969a6;}
	.webchat_area.qdman .p1{color: #3372a8;}
	/*产品描述*/
	.basic-info{border-top: 1px solid #f3f4f4; padding: 0.6rem; margin: 0.4rem 0; background: #fff;}
	.pro-name{display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.pro-name span{flex: 1; -webkit-flex: 1; display: block;}
	.pro-name a{border: 1px solid #bfbfbf; color: #686868; text-align: center; border-radius: 3px; padding: 0.2rem 1.2rem;}
	.basic-info .pro-price span{color: #da3737; font-size: 1.32rem; display: inline-block;}
	.basic-info .pro-price span i{font-size: 0.75rem;}
	.pro-price del{font-size: 0.8rem; color: #adadad;}

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
	.pro-param dt.param-tit span{color: #b2b2b2; flex: 1; -webkit-flex: 1; display: block;}
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
    /*.cj_wx{padding:0.5rem 0.5rem 1rem 3.5rem;}
    .cj_com{width: 100%; overflow: hidden; display: table;}
    .cj_com .cj_left{width:40%;padding:0.4rem;text-align:center;display: table-cell;background:#fff;}
    .cj_com .cj_right{width:59%;padding:0 0.5rem;display: table-cell;vertical-align: middle;}
    .cj_wx .cj_left span{display:inline-block;width:100%;}
    .cj_wx .cj_left span img{width:100%;max-width:200px;}
    .cj_com .cj_right .cj_check{width:100%;overflow: hidden;}
    .cj_com .cj_right h4{color: #000;font-size: 1rem;padding: .5rem 0 0.1rem;font-weight: bold;}
    .cj_com .cj_right p{font-size:0.85rem;color:#b8b8b8; padding-right: 0.1rem;line-height: 1.4rem;}
    .cj_com .cj_right span{color:#ec4270; width:9rem; display:inline-block; font-size:1.2rem; }*/
	
	.top_wx{ background:url(/public/images/detail/wx_bg.jpg) repeat; padding:0.8em;margin: 1em 0;}
	.top_wx p{ width:60%; display:inline-block; text-align: center; vertical-align: middle; font-size:1em; color:#858585; line-height: 1.5em; padding-bottom:0.4em;}
	.top_wx strong{color: #f10b31;font-size: 0.85rem;line-height: 2.1em;}
	.top_wx span{ width:9.8em; font-size:1.2em; border-radius: 5em; height:1.8em; line-height: 1.8em; margin-top:0.3em; display: inline-block; background:#fff; font-weight:bold; text-align: center; color:#f10b31;}
	.top_wx i{ border-left:0.5em solid #f10b31; border-top:0.4em solid transparent;  border-bottom:0.4em solid transparent; display: inline-block; margin-left: 0.6em;}
	.top_wx img{display:inline-block; width:34%; vertical-align: middle;  padding-left:0.5em;}

     /* 版权 */
    .copyright{padding-top: 1rem; overflow: hidden; text-align: center;}
    .copyright p{font-size: 0.8em; color: #b1b1b1; height: 1.2rem; line-height: 1.2rem;}
    .load-more{color: #f00;}
</style>