<script>
	import { Q_Chinaskin } from 'vuex_path/getters.js';
	import { updateAppHeader, getCartGoodsQty, addGoodsToCart } from 'vuex_path/actions.js';
//	import AppSearch from '../common/search.vue';
	export default {
//		components: {
//			AppSearch
//		},
		vuex: {
			actions: {
				updateAppHeader,
				getCartGoodsQty,
				addGoodsToCart,
				updateAppHeader
			},
			getters: {
				Q_Chinaskin: state => state.Q_Chinaskin
			}
		},
		route: {
			deactivate(transition) {
				this.scrollTop = window.scrollY;
				transition.next();
			},
			data(transition) {
				this.updateAppHeader({
					type: 1
				});
				this.placeholder="美白"
				this.goodsFilter.cid = this.$route.params.cid;
				this.goodsFilter.package = this.$route.params.package;

				if(transition.from.name == 'goodsDetail'){
					this.$nextTick(() => {
						scrollTo(0, this.scrollTop);
					});
				}else{
					if(this.$route.query.keyword!=""){
						this.goodsFilter.keyword=this.$route.query.keyword;
					}
					this.findGoodsList(true);
				}
				
			}
		},
		ready() {
			this.$http.post('/Goods/getCates.json').then((res) => {
				res = res.json();
				if(res.status == 1){
					this.catList = res.data;
				}else{
                    this.$dispatch('popup', res.msg);
                }
			});
			$('.hd-item span').on('click', function(){
		        let $self = $('.combo-box').children().eq($(this).index());
		        $self.toggleClass('on').siblings().removeClass('on');
				$(document.body).one("click", function(){
					$self.removeClass('on');
				});
				return false;
		    });

		    $('#app').on('click', '.combo-box a', (e) => {
		    	$(e.currentTarget).closest('div').find('a').removeClass('on');
		    	let $self = $(e.currentTarget).addClass('on');
		    	$self.closest('div').removeClass('on');
		    	let selfText = $self.text();
		    	let type = $self.closest('div').removeClass('on').attr('filter-type');
		    	this.goodsFilter[type] = $self.attr('data');	    	
		    	$(e.currentTarget).parents('div').parents('.combo-box').siblings('.hd-item').children('span').eq($(e.currentTarget).closest('div').index()).children('em').html(selfText);
		    	this.findGoodsList(true);
		    });

			this.goodsFilter.cid = this.$route.params.cid;
			this.goodsFilter.package = this.$route.params.package;	
		},
		data() {
			return {
				scrollTop: 0,
				loading: false,
				loadingText: '加载中...',
				loadTry: 0,
				goodsFilter: {
					page: 1,
					package: 0,
					price: 'asc',
					cid: 0,
					keyword: ''
				},
				catList: '',
				goodsList: [],
				paddingBottom: this.$root.paddingBottom,
				hotSelects:{					
					"3g":[
						{name:"美白",router_name:"goodsList",cid:1,is_package:0},
						{name:"抑黑焕白",router_name:"goodsDetail",cid:269,is_package:0},
						{name:"祛痘",router_name:"goodsList",cid:8,is_package:0},
						{name:"美容仪",router_name:"goodsList",cid:126,is_package:0},
						{name:"淡斑",router_name:"goodsList",cid:2,is_package:0},
						{name:"去黑头",router_name:"goodsList",cid:4,is_package:0},
						{name:"补水",router_name:"goodsList",cid:5,is_package:0},
						{name:"清洁",router_name:"goodsList",cid:93,is_package:0}
					],				
					"q":[
						{name:"美白",router_name:"goodsList",cid:37,is_package:0},
						{name:"抑黑焕白",router_name:"goodsDetail",cid:459,is_package:0},
						{name:"祛痘",router_name:"goodsList",cid:126,is_package:0},
						{name:"美容仪",router_name:"goodsList",cid:135,is_package:0},
						{name:"淡斑",router_name:"goodsList",cid:8,is_package:0},
						{name:"去黑头",router_name:"goodsList",cid:6,is_package:0},
						{name:"补水",router_name:"goodsList",cid:36,is_package:0},
						{name:"清洁",router_name:"goodsList",cid:35,is_package:0}
					]
				},
				curHotList:[],
				searchLists:[],
				keyword:'',
				is_showClose:false,
				is_showHot:false,
				placeholder:'美白'
			}
		},
		methods: {
			findGoodsList(reset) {
				this.loading = true;
				if(reset){
					this.goodsFilter.page = 1;
					this.goodsList = [];
				}			
				if(this.keyword.length>0){
					this.goodsFilter.keyword=this.keyword;
					this.goodsFilter.cid=0;
				}else{
					this.goodsFilter.keyword="";
				}
				this.$http.post('/Goods/lists.json', this.goodsFilter).then((res) => {
					res = res.json();
					if(res.status == 1){
						for(let i of res.data){
                            this.goodsList.push(i);
                        }
						var temp=this.keyword;
						this.placeholder=temp;
						console.log(temp);
//						this.keyword="";
						this.searchLists=[];						
						this.$nextTick(() => {
						    if(res.data.length == 0){
						        this.loadingText = '没有更多了';
						        this.loading = true;
						    }else{
						        this.loading = false;
						    }
						});
						
					}else{
						this.loading = false;
					}
				}, () => {
					this.loadTry++;
					this.loading = false;
					if(this.loadTry >= 3){
						this.loading = true;
					}
				});

			},
			loadMore() {
                this.goodsFilter.page += 1;
                this.findGoodsList();
            },
			textLimit(txt) {
				if(!txt){
					return;
				}
				return txt.length > 24 ? txt.substr(0, 24) + '...' : txt;
			},
			search:function(){
				if(this.keyword=="")return;				
				let	keyword=this.keyword;
				this.goodsFilter.keyword=keyword;
				this.findGoodsList(true);				
			},
			deleteKeyword:function(){
				this.keyword="";
				this.searchLists=[];
			},
			controlHot:function(){	
				if(this.keyword.length>0){
					this.is_showHot=false;
				}else{
					this.is_showHot=true;
				}
			},
			hiddenHot:function(){
				if(this.keyword.length>0)return;
				this.is_showHot=false;
			},
			getLists:function(evt){
				if (evt.keyCode == 38 || evt.keyCode == 40 || evt.keyCode==13 )return;
				if(this.keyword.length==0){
					this.searchLists=[];
					return;
				}
				this.is_showHot=false;
				let data={
					page: 1,
					package: 0,
					price: 'asc',
					cid: 0,
					keyword:this.keyword
				}
	            this.$http.post("/Goods/lists.json",data).then((res)=>{
	            	res = res.json();
					if(res.status == 1){
						this.searchLists=res.data;
					}
				});
			}
		},
		computed:{
			curHotList() {
				if(this.Q_Chinaskin){
					return this.hotSelects["q"];
				}else{
					return this.hotSelects["3g"];
				}
			},
			is_showClose(){
				if(this.keyword.length>0){
					return true;
				}else{
					return false;
				}
			}
		}	
	}
</script>

<template>
    <div class="width-full search-item">
        <!-- <h2><span>美白</span>Etioline专利美白成分</h2> -->
        <!--<app-search></app-search>-->
        <div class="searchBar">
			<div class="input">
				<input id="search-input" type="text" :placeholder="placeholder" v-model="keyword" maxlength="16" @keyup="getLists($event)" @focus="controlHot" @blur="hiddenHot"/>			
				<span class="searBtn" @click="search"></span>
			</div>		
			<i class="font icon-close-ico closeBtn" v-if="is_showClose" @click="deleteKeyword"></i>
			<div class="hot-search" v-show="is_showHot" transition="fade" v-cloak>
		    	<p>热门搜索</p>
		    	<ul>
		    		<li v-for="hot in curHotList" :class="{hot:$index==0}">
	    				<a v-if="hot.router_name=='goodsList'" v-link="{ name: hot.router_name, params: {cid: hot.cid, package: hot.is_package} }">{{hot.name}}</a>
	    				<a v-else v-link="{ name: hot.router_name, params: {id: hot.cid, package: hot.is_package} }">{{hot.name}}</a>
		    		</li>	    		
		    	</ul>
		    </div>
		    <div class="search-select" v-show="this.searchLists.length>0" transition="fade" v-cloak>
		        <ul>
		            <li v-for="item in searchLists" class="search-select-option search-select-list"   ><a v-link="{ name: 'goodsDetail', params: {id: item.goods_id,package: 0} }">{{item.goods_name}}</a>	                
		            </li>
		        </ul>
		    </div>
		</div> 
        <div class="hd-item">
            <span :class="{'on': goodsFilter.cid != undefined}"><em>全部</em><i class="font icon-arrow-bottom"></i></span>
            <span :class="{'on': goodsFilter.package != undefined }"><em>热销</em><i class="font icon-arrow-bottom"></i></span>
            <span :class="{'on': goodsFilter.price != undefined }"><em>价格</em><i class="font icon-arrow-bottom"></i></span>
        </div>
        <div class="combo-box">
            <div class="cj-all" filter-type="cid">
                <a v-for="item in catList" data="{{item.cat_id}}">{{item.cat_name}}</a>
            </div>
            <div class="cj-hot" filter-type="package">
            	<a href="javascript:void(0)" data="0" class="on">默认</a>
            	<a href="javascript:void(0)" data="1">单品</a>
            	<a href="javascript:void(0)" data="2">套装</a>
            </div>
            <div class="cj-price" filter-type="price">
            	<a href="javascript:void(0)" class="on" data="asc">默认</a>
            	<a href="javascript:void(0)" data="asc">低至高</a>
            	<a href="javascript:void(0)" data="desc">高至低</a>
            </div>
        </div>
    </div>

	<div class="combo-list" :style="{paddingBottom:paddingBottom}">
		<ul v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
			<li v-for="item in goodsList">
				<a class="product-img" v-link="{ name: 'goodsDetail', params: {id: item.goods_id,package: 0} }"><img :src="item.original_img" alt="" /></a>
				<div class="product-infor">
					<h3>{{item.goods_name}}</h3>
					<p>{{textLimit(item.attr_value)}}</p>
					<div class="cj-cart">
						<span class="price">¥{{item.shop_price}}</span>
						<a href="javascript:;" @click="addGoodsToCart({id: item.goods_id})"><i class="font icon-cart"></i>加入购物车</a>
					</div>
				</div>
			</li>
		</ul>
		<div class="load-more">{{loadingText}}</div>
	</div>
</template>

<style scoped>
	/*筛选列表样式*/
	.search-item{padding:0.5em .5rem 0; background: #fff; position: fixed;z-index: 999;}
	.search-item h2{text-align: left; color: #919191; font-size: 0.75rem; padding: 0.5rem 0 0.5rem 0.4rem;}
	.search-item h2 span{display: inline-block; position: relative; color: #000; margin-right: 0.6rem;}
	.search-item h2 span:after{content: '';top: 50%;right:-0.3rem;width: 1px;height: 0.6rem;background: #919191;position: absolute;margin-top: -0.25rem;}

	.search-item .hd-item{height:2.2rem; border: 1px solid #565656; background:#fff; position: relative; z-index: 40; display: flex;}
	.search-item .hd-item span{border-left: 1px solid #565656; color:#555; flex: 1; -webkit-flex: 1; display: flex; -webkit-display: flex; justify-content: center; -webkit-justify-content: center; align-items: center; -webkit-align-items: center;}
	.search-item .hd-item span:first-child{border-left:0;}
	.search-item .hd-item span .font{transition: 0.3s; display: inline-block; vertical-align: middle; margin-left: 1rem;}
	.search-item .hd-item span.on{color: #c22224;}
	.search-item .hd-item span.on .font{transform: rotate(180deg);}

	/*下拉列表*/
	.combo-box{position: absolute; top: 0.8rem; left: 0.5rem; right: 0.5rem;}
	.combo-box > div{width: 100%; padding: 0 0.4rem 1.4rem; box-shadow: 0 0 10px #EAEAEA; position: absolute; background: #fff; left: 0; z-index: 20; transition: 0.6s; transform: translateY(-30%); display: none;}
	.combo-box > div.on{z-index: 30; top: 4.5rem; transform: translateY(0); display: block;}
	.combo-box a{width: 20%; margin-top: 1.4rem; text-align: center; font-size: 0.7rem; color:#353535; float: left; position: relative;}
	.combo-box a.on {color:#c22224;}
	.combo-box a:after{content: ""; width: 1px; height: 80%; background: #868686; position: absolute; top: 10%; left: 0;}
	.combo-box a:first-child:after, .combo-box a:nth-child(6):after{content: none;}

	/* 产品列表 */
	.combo-list{width: 100%; position: relative; top: 5.7rem;}
	.combo-list .product-img{width: 38.5%; display: table-cell; text-align: center;}
	.combo-list li .product-infor{padding: 0 0.8rem;display: table-cell;vertical-align: middle;}
	.combo-list li{padding: 0.8rem 0.5rem; border-top:1px solid #d9d9d9; display: table; width: 100%; overflow: hidden;}
	.combo-list li:nth-child(1){border-top: 0;}
	.combo-list li .product-infor h3{font-size: 1rem;}
	.combo-list li .product-infor p{font-size: 0.8rem; color: #9f9f9f;}
	.combo-list li .product-infor .cj-cart{margin-top: 1rem; padding-right: 0.8rem;}
	.product-infor .cj-cart a{display: inline-block; padding: 0.5rem; background: #000; color: #fff; font-size: 0.82rem; float: right;}
	.product-infor .cj-cart a .icon-cart{margin-right:0.25rem;}
	.cj-cart span.price{color: #c52429; font-size: 1.2rem; line-height: 2rem;}
	/*搜索*/
	.searchBar{position:relative;margin: .6rem auto;padding: 0.3rem 0.7rem;background: #F1F1F1; border-radius: 0.3rem;}
	.searchBar .input{overflow: hidden;}
	.searchBar .input input{float: left;width: 90%;outline: 0;border: 0; height: 1.5rem;font-size: 0.85rem;
    background-color: transparent;}
	.searchBar .input .searBtn{float: right;display:inline-block;width: 1.2rem;height: 1.5rem;background: url(/public/images/common/search-ico.png) no-repeat 50%;background-size: contain;cursor: pointer;}
	.searchBar .closeBtn{position: absolute;font-size:0.65rem; right: 3rem;top: .75rem;}
	.search-select { position: absolute;width:100%;bottom:-8rem; left:0; box-sizing: border-box;z-index: 9999;background: rgba(255,255,255, 0.95);height: 8rem;overflow-y: auto;}
	.search-select li:not(:last-child) {border-bottom: 1px solid #d4d4d4;}
	.search-select-option {box-sizing: border-box; padding: 0.3rem 0.8rem;	}
	.selectback {background-color: #eee !important;cursor: pointer;}
	input::-ms-clear {display: none;	}
	.search-select ul{margin:0;text-align: left; }
	.hot-search{position:absolute;bottom:-6.5rem;left:0;font-size: 0.85rem;padding: 0.6rem;z-index: 9999; background: rgba(255,255,255,0.95);}
	.hot-search p{color:#B6B6B6;}
	.hot-search ul li{display: inline-block;padding: 0.2rem 0.8rem;border:1px solid #CECECE;border-radius: 1.5rem;color: #646464;margin-right: 1rem;margin-top: 0.5rem;}
	.hot-search ul li.hot{border-color:#F12D2E ;color: #F12D2E;}
	[v-cloak]{display: none;}
	
	.fade-enter {animation: fade-in .2s;}
	.fade-leave { animation: fade-out .2s;}
	@keyframes fade-in {
		0% { opacity: 0; }
	    50% {opacity: 0.5;}
	    100% {opacity: 1;}
	}
	@keyframes fade-out {
	  0% {opacity: 1;}
	  50% { opacity: 0.5;}
	  100% { opacity: 0;}
	}
</style>