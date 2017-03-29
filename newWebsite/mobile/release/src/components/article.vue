<script>
	import { updateAppHeader } from '../vuex/actions.js';
	import buy from './goods/buy.vue';

	export default {
		components: {
			buy
		},
		vuex: {
			actions: {
				updateAppHeader
			}
		},
		route: {
			data() {
				let data = {
					id: this.$route.params.id
				}
                this.isCollect();
				this.$http.post('/Praise/praise_detail.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$set('article', res.data);
					}
				});

				this.$http.post('/Praise/DetailGoods.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$set('goods', res.data);
                        this.goodsType = res.data.is_package;
					}
				});

				this.$http.post('/Praise/DetailRand.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$set('list', res.data);
					}
				});
			}
		},
		ready() {
			this.updateAppHeader({
				type: 2,
				content: '达人分享'
			});
		},
		data() {
			return {
				article: '',
				goods: '',
				list: '',
                collect:{},
                isstatus:'',
                goodsType:''
			}
		},
        methods:{
            isCollect(){

                let data ={
                    goods_id:this.$route.params.id
                }
                this.$http.post('/Goods/isCollectGoods.json',data).then((res) => {
                    res = res.json();
                    this.collect = res;
                    this.isstatus = res.status;
                });
            }
		},
        events: {

            isStatus(msg) {

                this.isstatus = msg;

            }
        }
	}
</script>

<template>
	<div class="container">
		<article class="article">
			<header class="article-hd">
				<h3>{{article.title}}</h3>
				<p><span>昵称：{{article.author}}</span><!-- 职业：网红 --><span>肤质：{{article.keywords}}</span><!-- 使用时间：2个月 --></p>
			</header>
			<div class="article-bd">
				{{{article.content}}}
			</div>
		</article>
		<dl v-if="goods.goods_name" class="recommend-box">
			<dt class="hd">
				<b>粉丝<br />推荐</b>
				<h3>
					<strong>粉丝推荐</strong>
					<p>RECOMMENDED FANS</p>
				</h3>
			</dt>
			<dd>
				<a class="goods-img" v-link="{ name: 'goodsDetail', params: {id: goods.goods_id} }"><img :src="goods.original_img" alt="" /></a>
				<div class="goods-info">
					<div class="goods-name"><strong>{{goods.goods_name}}</strong><span>好评指数:<span class="hl">★★★★★</span></span></div>
					<div class="goods-price"><strong><i>￥</i>{{goods.shop_price}}</strong><del><i>￥</i>{{goods.market_price}}</del></div>
				</div>
				<dl class="goods-effect">
					<dt>核心功效</dt>
					<dd>{{goods.effect}}</dd>
				</dl>
				<buy :id="goods.goods_id" :ispackage="goodsType" :isstatus="parseInt(isstatus)"></buy>
			</dd>
		</dl>
		<dl class="recommend-box">
			<dt class="hd">
				<b>口碑<br />最佳</b>
				<h3>
					<strong>口碑分享</strong>
					<p>SHARE REPUTATION</p>
				</h3>
			</dt>
			<dd>
				<ul class="share-list">
					<li v-for="item in list">
						<a v-link="{ name: 'article', params: {id: item.article_id} }">
						<span>{{item.title}}</span><i v-if="$index == 0">NEW</i></a>
					</li>
				</ul>
			</dd>
		</dl>
	</div>
</template>

<style scoped>
	.container{background: #efefef;}
	.recommend-box{background: #fff; margin-bottom: 0.6rem;}

	.article{padding: 0.8rem; margin-bottom: 0.6rem; background: #fff;}
	.article-hd{border-bottom: 1px solid #d1d1d1; text-align: center;}
	.article-hd h3{font-size: 1.3rem;}
	.article-hd p{padding: 0.4rem 0 0.6rem; font-size: 0.8rem; color: #a6a6a6;}
	.article-hd p span{margin-right: 0.8rem;}

	.hd{padding: 0.8rem; border-bottom: 1px solid #e1e1e1; display: flex;}
	.hd b{width: 2rem; padding: 0.2rem 0 0; margin-right: 0.4rem; border-radius: 0 0 4px 4px; background: #da3737; font-weight: normal; font-size: 0.6rem; color: #fff; text-align: center; display: inline-block;}
	.hd b:after{content: ""; border: 1rem solid transparent; border-top-width: 0.3rem; border-bottom: 0.3rem solid #fff; display: block;}
	.hd h3{flex: 1;}
	.hd strong{line-height: 1; margin-bottom: 0.2rem; font-size: 1.1rem; display: block;}
	.hd p{font-size: 0.8rem; color: #afafaf;}

	/* 粉丝推荐 */
	.goods-img{height: 12rem; display: flex; align-items: center; justify-content: center;}
	.goods-img img{max-height: 10rem;}
	.goods-info{padding: 0.6rem 0.8rem; border-top: 1px solid #e1e1e1;}
	.goods-name{padding-bottom: 0.2rem; color: #313131; display: flex; align-items: center;}
	.goods-name strong{font-size: 1rem; flex: 1;}
	.goods-name span{font-size: 0.85rem;}
	.goods-name .hl{color: #da3737;}
	.goods-price i{font-size: 0.8rem;}
	.goods-price strong{font-size: 1.2rem; color: #da3737; margin-right: 0.8rem;}
	.goods-price del{font-size: 0.9rem; color: #adadad;}

	.goods-effect{border: 0.4rem solid #efefef; border-width: 0.4rem 0; font-size: 0.9rem;}
	.goods-effect dt{line-height: 2.4rem; padding: 0 0.8rem; border-bottom: 1px solid #e1e1e1; color: #b2b2b2;}
	.goods-effect dd{padding: 0.4rem 0.8rem 0.8rem; color: #eb4a4c;font-size: 0.8rem;}

	.share-list{font-size: 0.9rem; color: #a0a0a0;}
	.share-list li a{height: 2.6rem; padding: 0 0.8rem; border-bottom: 1px dashed #e1e1e1; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.share-list li i{margin-left: 0.2rem; border: 1px solid #da3737; border-radius: 4px; font-size: 0.6rem; color: #da3737; display: block;}
</style>