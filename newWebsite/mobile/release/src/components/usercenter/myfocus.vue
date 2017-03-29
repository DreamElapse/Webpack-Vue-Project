<script>
	import { addGoodsToCart,updateAppHeader } from 'vuex_path/actions.js';
	export default {
		vuex: {
			actions: {
                updateAppHeader,
				addGoodsToCart
			}
		},
		ready() {
			
		},
		route: {
            data() {
                document.title = "我的收藏";
                this.updateAppHeader({
                    type: 1
                });
                this.upCollectList()
            }
        },
        data(){
        	return{
        		collectGoods:[],
        		page:1,
        		computing: true
        	}
        },
        methods: {
        	upCollectList() {
        		let data = {
					page: this.page
				}
	        	this.$http.post('/User/getCollectGoods.json', data).then((res) => {
	                res = res.json();
	                this.computing = false;
	                if(res.status == 1){
	                    this.collectGoods = res.data;
	                }
	            });
        	},
        	removeCollect(a){
                this.computing = true;
        		let data ={
					goods_id: a.goods_id
				}
				this.$http.post('/Goods/collectGoods.json',data).then((res) => {
					res = res.json();
					let taskArr = [this.upCollectList()];
					Promise.all(taskArr).then(() =>{
						this.computing = false;
						}, () => {
							this.computing = false;
						}).catch(() => {
							this.computing = false;
						});
					this.$dispatch('popup', '成功移除我的收藏！');
				});
        	}
        }
	}
</script>

<template>
<div class="container">
	<div v-show="computing" class="loading"></div>
	<ul class="myattention-productlist">
	    <li v-for="item in collectGoods">
	    	<a href="javascript:;" class="myattention-productlist-a J_ping">
	    		<div class="pic-div" v-link="{ name: 'goodsDetail', params: {id: item.goods_id} }">
					<img :src="item.img">
				</div>
				<div class="prolist-content">
					<div class="prolist-name grayF">
						<span> {{item.name}} </span>
					</div>
					<div class="pro-price grayF">
					<em>¥<span class="big-price">{{item.shop_price}}</span> <del>￥<i id="market-price">{{item.market_price}}</i></del></em>
					</div>
					<div class="discount-info"></div>
					<div class="buttons cf">
						<!-- <span class="sim-btn J_ping" >看相似</span> -->
						<span class="cancle-btn J_ping" @click="removeCollect(item)">取消收藏</span>
						<i class="car-btn font icon-add-to-ShoppingCart" @click="addGoodsToCart({id: item.goods_id})"></i>
					</div>
				</div>
	    	</a>
	    </li>
	</ul>
</div>
</template>

<style scoped>
.myattention-productlist{overflow-x:hidden;padding-top:0.5rem;}
.myattention-productlist li{display:block;}
.myattention-productlist li .myattention-productlist-a{display:block;padding:9px 0 0 10px;display:box;display:-webkit-box;display:-moz-box;display:-ms-box;display:-o-box;}
.myattention-productlist li .myattention-productlist-a .pic-div{height:5.625rem;width:5.625rem;border-radius:2px;overflow:hidden;position:relative;margin-top:2px;}
.myattention-productlist li .myattention-productlist-a .pic-div img{height:100%;width:100%;}
.myattention-productlist li .myattention-productlist-a .prolist-content{padding-bottom:10px;position:relative;box-flex:1;-webkit-box-flex:1;-moz-box-flex:1;-ms-box-flex:1;-o-box-flex:1;margin-left:10px;padding-right:10px;border-bottom:1px solid #ccc;}
.myattention-productlist li .myattention-productlist-a .prolist-content .prolist-name{color:#232326;font-size:.75rem;line-height:.9375rem;height:1.9rem;overflow:hidden;text-overflow:ellipsis;display:box;display:-webkit-box;display:-moz-box;display:-ms-box;display:-o-box;line-clamp:2;-webkit-line-clamp:2;-moz-line-clamp:2;-ms-line-clamp:2;-o-line-clamp:2;box-orient:vertical;-webkit-box-orient:vertical;-moz-box-orient:vertical;-ms-box-orient:vertical;-o-box-orient:vertical;word-break:break-all;margin-bottom:16px;}
.myattention-productlist li .myattention-productlist-a .prolist-content .pro-price{overflow:hidden;margin-bottom:8px;color:#f02323;height:.9375rem;line-height:.9375rem;overflow:hidden;font-size:0;}
.myattention-productlist li .myattention-productlist-a .prolist-content .pro-price em{font-size:.6875rem;margin-right:8px;display:inline-block;height:1.125rem;overflow:hidden;float:left;vertical-align:top;position:relative;top:0;}
.myattention-productlist li .myattention-productlist-a .prolist-content .pro-price em .big-price{font-size:.9375rem;}
.myattention-productlist li .myattention-productlist-a .prolist-content .discount-info{position:relative;}
.myattention-productlist li .myattention-productlist-a .prolist-content .buttons{margin-top:2px;}
.myattention-productlist li .myattention-productlist-a .prolist-content .buttons span{display:box;display:-webkit-box;display:-moz-box;display:-ms-box;display:-o-box;box-align:center;-webkit-box-align:center;-moz-box-align:center;-ms-box-align:center;-o-box-align:center;box-pack:center;-webkit-box-pack:center;-moz-box-pack:center;-ms-box-pack:center;-o-box-pack:center;color:#686868;font-size:.625rem;height:1.375rem;line-height:1.25rem;position:relative;float:left;margin-right:10px;}
.myattention-productlist li .myattention-productlist-a .prolist-content .buttons .sim-btn{width:2.75rem;}
.myattention-productlist li .myattention-productlist-a .prolist-content .buttons .cancle-btn{width:3.375rem;border:1px solid #686868;border-radius: 4px;-webkit-border-radius: 4px;}
.myattention-productlist li .myattention-productlist-a .prolist-content .buttons .car-btn{position:absolute;width:39px;height:37px;bottom:3px;right:5px;display:block;z-index:1;font-size: 1.2rem;}
.pro-price del{font-size: .7rem;color: #adadad;}
/* .myattention-productlist li .myattention-productlist-a .prolist-content:after{content:'';height:1px;width:200%;position:absolute;left:0;top:auto;right:auto;bottom:0;background-color:#e3e5e9;border:0 solid transparent;border-radius:0;-webkit-border-radius:0;transform:scale(.5);-webkit-transform:scale(.5);-moz-transform:scale(.5);-ms-transform:scale(.5);-o-transform:scale(.5);transform-origin:top left;-webkit-transform-origin:top left;-moz-transform-origin:top left;-ms-transform-origin:top left;-o-transform-origin:top left;}
.myattention-productlist li .myattention-productlist-a .prolist-content .buttons span:after {content: '';height: 2.25rem;width: 196%;position: absolute;left: 0;top: 0;border: 1px solid #686868;border-radius: 4px;-webkit-border-radius: 4px;transform: scale(0.5);-webkit-transform: scale(0.5);-moz-transform: scale(0.5);-ms-transform: scale(0.5);-o-transform: scale(0.5);transform-origin: top left;-webkit-transform-origin: top left;-moz-transform-origin: top left;-ms-transform-origin: top left;-o-transform-origin: top left;} */





</style>