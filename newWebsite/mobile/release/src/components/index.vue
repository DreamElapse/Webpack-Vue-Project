<script>
//	import AppSearch from './common/search.vue';
    import { tel , showTel , Q_Chinaskin , actName } from 'vuex_path/getters.js';
    import { updateAppHeader, addGoodsToCart, checkLogin} from 'vuex_path/actions.js';
    var Swipe = require('exports?Swipe!swipe.js');
    var imagesLoaded = require('imagesloaded');

    export default {
//  	components: {
//			AppSearch
//		},
        vuex: {
            getters: {
                tel,
                showTel,
                actName,
                Q_Chinaskin
            },
            actions: {
                updateAppHeader,
                addGoodsToCart,
                checkLogin
            }
        },
        route: {
            data() {
                document.title = "자빛韩国瓷肌中国官方商城";
  
                this.updateAppHeader({
                    type: 1
                });
                this.keyword="";
                this.searchLists=[];
            }
        },
        ready() {
            new Swipe(document.getElementById("banner"), {
                speed: 400,
                auto: 2800,
                callback: function(index, elem) {
                    $(".banner-num ol li").removeClass("cur").eq(index).addClass("cur");
                }
            });

            $(".banner").each(function(){
                for(var i = 0; i < $(this).find("ul li").length; i++){
                    if(i == 0){
                        $(this).parent().siblings('.banner-num').find("ol").append('<li class="cur"></li>');
                    }else{
                        $(this).parent().siblings('.banner-num').find("ol").append('<li></li>');
                    }
                }
            });

            this.$http.post('/Goods/hotGoods.json').then((res) => {
                res = res.json();
                if(res.status == 1){
                    this.hotList = res.data;
                }
            });
            this.$http.post('/FreeTrial/getTrial.json').then((res) => {
                res = res.json();
                if(res.status == 1){
                    this.getTrial = res.data;
                }
            });

            this.$http.post('/Praise/praiseIndex.json').then((res) => {
                res = res.json();
                if(res.status == 1){
                    this.praiseList = res.data;
                    this.$nextTick(() => {
                        imagesLoaded('#show-banner', () => {
                            new Swipe(document.getElementById("show-banner"), {
                                speed: 400,
                                auto: 2800,
                                callback: function(index, elem) {
                                    $(".show-pic ol li").removeClass("cur").eq(index).addClass("cur");
                                    $(".show-num span").removeClass("on").eq(index).addClass("on");
                                }
                            });
                            let banner = $('#show-banner');
                            for(var i = 0; i < banner.find("ol li").length; i++){
                                if(i == 0){
                                    banner.children('.show-num').append('<span class="on"></span>');
                                }else{
                                    banner.children('.show-num').append('<span></span>');
                                }
                            }
                        });
                    });
                }
            });

            this.$http.post('/Brand/brand_list.json', {pageSize: 3 }).then((res) => {
                res = res.json();
                if(res.status == 1){
                    this.brandList = res.data;
                }
            });

            // 优惠券
            this.$http.post('/Bouns/bouns.json', {act_id: '20170203'}).then((res) => {
                res = res.json();
                if(res.status == 1){
                    this.couponList = res.data;
                }
            });
        },
        data() {
            return {
                couponList: '',
                hotList: [],
                praiseList: [],
                brandList: [],
                getTrial:{},
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
				is_showHot:false
            }
        },
        methods: {
            acquireCoupon(id) {
                let data = {
                    act_id: '20170203',
                    type_id: id
                }
                this.$http.post('/Bouns/getBouns.json', data).then((res) => {
                    res = res.json();
                    if(res.status == 1){
                        this.$dispatch('popup', '获取成功！');
                    }else{
                        this.$dispatch('popup', res.msg);
                    }
                });
            },
            search:function(){
				if(this.keyword=="")return;				
				let	keyword=this.keyword;
				this.$router.go({ name: 'goodsList', params: { cid: 0,package:0 },query:{keyword:keyword}});				
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
    <div class="container">
		<!--<app-search @searchevt="search"></app-search>-->
		<div class="searchBar">
			<div class="input">
				<input id="search-input" type="text" placeholder="美白" v-model="keyword" maxlength="16" @keyup="getLists($event)" @focus="controlHot" @blur="hiddenHot"/>			
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
        <div class="cj-index-bd">
            <div id="banner" class="banner">
                <ul> 
                    <!-- <li><a v-link="{ name: 'act', params: {name: actName} }"><img src="/public/images/index/focus/banner_0914.jpg" alt=""></a></li> -->
                    <li><a href="javascript:;"><img src="/public/images/index/focus/banner_1223.jpg" alt=""></a></li>
                    <li><a v-link="{ name: 'freetryDetail',params: {id: getTrial.activity_id}}"><img src="/public/images/index/focus/free.jpg" alt=""></a></li>
                    <li><a href="http://fanxing.kugou.com/subject/zhaomu/index.html?channleId=10"><img src="/public/images/index/focus/banner_1230.jpg" alt=""></a></li>
                </ul>
            </div>
        </div>
        <div class="banner-num"><ol></ol></div>
        
        <div class="cj-quick-menu">
            <ul>
                <li><a v-link="{ name: 'topic', params: { name: 'mb', }}"><i class="icon-01"></i>美白</a></li>
                <li><a v-link="{ name: 'topic', params: { name: 'qb' }}"><i class="icon-02"></i>淡斑</a></li>
                <li><a v-link="{ name: 'topic', params: { name: 'qdwoman' }}"><i class="icon-03"></i>女士祛痘</a></li>
                <li v-if="Q_Chinaskin"><a v-link="{ name: 'goodsList', params: {cid: 135, package: 0} }"><i class="icon-09"></i>美容仪器</a></li>
                <li v-else><a v-link="{ name: 'goodsList', params: {cid: 126, package: 0} }"><i class="icon-09"></i>美容仪器</a></li>
                <!--<li><a v-link="{ name: 'topic', params: { name: 'bs' }}"><i class="icon-04"></i>补水</a></li>-->
                <li><a v-link="{ name: 'topic', params: { name: 'qht' }}"><i class="icon-05"></i>去黑头</a></li>
                <!--<li><a v-link="{ name: 'topic', params: { name: 'qj' }}"><i class="icon-06"></i>清洁</a></li>-->
                <li><a v-link="{ name: 'topic', params: { name: 'mm' }}"><i class="icon-07"></i>面膜</a></li>
                <li><a v-link="{ name: 'topic', params: { name: 'qdman' }}"><i class="icon-08"></i>男士专区</a></li>
                <li><a v-link="{ name: 'goodsList', params: {cid: 141, package: 0} }"><i class="icon-10"></i>内调</a></li>
            </ul>
        </div>
        <div class="bouns" v-if="couponList.length != 0 ">
            <h3>☆优惠券领取</h3>
            <ul>
                <li v-for="item in couponList" @click="acquireCoupon(item.type_id)">{{item.type_name}}</li>
            </ul>
        </div>
        <div class="index-box">
            <div class="index-hd">
                <h2>畅销<span>프로모션</span></h2><!-- <a href="">more></a>-->
            </div>
            <div>
                <ul class="best-seller">
                    <li v-for="item in hotList">
                        <a class="pic-item" v-link="{ name: 'goodsDetail', params: {id: item.goods_id,package: 0} }"><img :src="item.original_img" alt="" /></a>
                        <h3 v-link="{ name: 'goodsDetail', params: {id: item.goods_id} }"><span>{{item.goods_name}}</span></h3>
                        <div><b>¥{{item.shop_price}}</b><del>¥{{item.market_price}}</del></div>
                        <a class="btn" href="javascript:;" @click="addGoodsToCart({id: item.goods_id})">加入购物车</a>
                    </li>
                </ul>
            </div>

            <div class="index-box reveal">
                <div class="index-hd">
                    <!-- <h2>晒单</h2><span>싱글 일</span> <a v-link="{ name: 'praise' }">more<i class="font icon-arrow-right"></i></a> -->
                    <h2>晒单</h2><span>싱글일</span> <a v-link="{ name: 'praise' }"><img src="/public/images/index/more.png" alt=""></a>
                </div>
                <div class="show-box">
                    <div id="show-banner" class="show-banner">
                        <ol>
                            <li v-for="(index,item) in praiseList"><a class="pic-item" v-link="{ name: 'article', params: {id: item.article_id} }"><img :src="'/public/images/index/show_pic_0' + ($index + 1) + '.jpg'" alt="" /></a></li>
                        </ol>
                        <div class="show-num"></div>
                    </div>
                </div>
            </div>

            <div class="index-box cj-star">
                <div class="index-hd">
                    <h2>星说</h2><span>스타는말한다</span><!-- <a href="">more></a> -->
                </div>
                <a v-link="{ name:'brandArticles' }"><img v-lazy="'/public/images/index/pic_04.jpg'" alt="" /></a>
            </div>

            <div class="index-box">
                <div class="index-hd">
                    <h2>品牌动态</h2><span>브랜드활동</span><a v-link="{ name: 'brand' }"><img src="/public/images/index/more.png" alt=""></a>
                </div>
                <div class="index-bd">
                    <ul class="brand_li">
                        <li v-for="item in brandList">
                            <a v-link="{ name: 'brandDetail', params: {id: item.id} }"><b v-show="$index == 0">NEW</b><p>{{item.short_title}}</p><span>{{item.create_date}}</span></a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

    </div>


    <footer class="footer">
        <div class="foot-item"><img v-lazy="'/public/images/index/footer_t.jpg'" alt="" /></div>
        <section>
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
            <div class="index-box branch">
                <div class="index-hd">
                    <h2>4ever热门分院</h2><span>4ever인기지점</span> <a v-link="{ name: 'store' }"><img src="/public/images/index/more.png" alt=""></a>
                </div>
                <div class="index-bd branch_list">
                    <h3>中国地区</h3>
                    <ul>
                        <li><span>广州店</span><span>成都店</span><span style="text-align:left;">青岛店</span></li>
                    </ul>
                    <h3>韩国地区</h3>
                    <ul>
                        <li><span>首尔站店</span><span>麻浦店</span><span>安养店</span><span>九老店</span></li>
                        <li><span>江南店</span><span>弘济店</span><span>仁川富平店</span><span>丽水店</span></li>
                        <li><span>盆塘店</span><span>蚕室店</span><span>狎欧亭店</span><span>天安店</span></li>
                        <li><span>昌原店</span><span>水原店</span><span>首尔芦原店</span><span>清州店</span></li>
                        <li><span>江西店</span><span>釜山海云台店</span><span>一山店</span><span>大田店</span></li>
                        <li><span>釜山西面店</span><span>济州店</span><span>九里店</span><span>蔚山芦原店</span></li>
                    </ul>
                </div>
                <div class="foot_nav">
                    <ul>
                        <li><a v-link="{ name: 'anti-fake' }"><i class="ic_n icon_01"></i><h4>防伪打假</h4><em>위조 방지 및 단속</em></a></li>
                        <li><a v-link="{ name: 'quickPay' }"><i class="ic_n icon_02"></i><h4>快速下单</h4><em>신속주문</em></a></li>
                        <li><a v-link="{name:'searTest'}"><i class="ic_n icon_03"></i><h4>肌肤测试</h4><em>피부테스트</em></a></li>
                        <li><a href="http://www.4-ever.co.kr/mobile/"><i class="ic_n icon_04"></i><h4>韩国4ever官网</h4><em>한국4ever홈페이지</em></a></li>
                        <li><a href="http://4everm.chinaskin.com.cn/"><i class="ic_n icon_05"></i><h4>瓷肌医学美肤</h4><em>첸스킨 의학피부미용</em></a></li>
                        <li><a href="http://m.chinaskin.cc/"><i class="ic_n icon_06"></i><h4>面部肌肤修复中心</h4><em>스킨메디컬센터</em></a></li>
                        <li><a href="http://www.chnskin-dzenus.com/"><i class="ic_n icon_07"></i><h4>韩国瓷肌定妆</h4><em>첸스킨코리아 반영구메이크업</em></a></li>
                        <li><a href="http://4ever.chinaskin.cn/"><i class="ic_n icon_08"></i><h4>瓷肌韩国整形</h4><em>첸스킨 한국성형</em></a></li>
                    </ul>
                </div>
            </div>
        </section>
        <div class="copyright">
            <p>联系电话：{{{ showTel }}}</p>
            <p>公司名：江西瓷肌电子商务有限公司 </p>
            <p>赣ICP备12007816号-23</p>
        </div>
    </footer>
</template>

<style>
    .banner-num ol{text-align: center;display: inline-block;font-size:0;}
    .banner-num ol li{width: 7px; height:7px; border-radius: 8px; background: #d5d5d5; display: inline-block; margin: 0 0.3em;font-size: 0.8rem;}
    .banner-num ol li.cur{background: #252525;}

    /* 晒单banner小圆点 */
    .show-num{position: absolute; bottom: 0.35rem; left: 35%;}
    .show-num span{width: 7px; height: 7px; border-radius:8px; background: #d5d5d5; display: inline-block; margin: 0 0.2rem;}
    .show-num span.on{background: #252525;}
    .show-banner{position: relative; overflow: hidden;}
    .show-banner ol{position:relative;}
    .show-banner ol li{width: 100%;min-height:15rem;float: left; position: relative;}
    .show-banner ol li img{width: 100%;}
</style>
<style scoped>
    .container{padding: 0 0.8rem;}

    .cj-mobile-page{position: relative; overflow: hidden;padding: 0 0.9rem; margin-top: 4rem;}
    .cj-index-bd{width: 100%;min-height:10rem; margin: 1rem 0 0.65rem; overflow: hidden;}
    .banner{position: relative; overflow: hidden; /*box-shadow:0px 0px 10px #D2D2D2;*/}
    .banner ul{position: relative;}
    .banner ul li{float: left; position: relative;}
    .banner ul li img{width: 100%;}
    .banner ol{width: 100%; text-align: center; position: absolute; bottom: 0.2em;}
    .banner ol li{width: 1em; height: 1em; border-radius: 1em; background: #d5d5d5; display: inline-block; margin: 0 0.2em;}
    .banner ol li.cur{background: #252525;}
    .banner-num{width: 100%; overflow: hidden; text-align: center; margin: 0.7rem 0;font-size: 0;}
    .banner-num ul{overflow: hidden;}
    .banner-num ul li{width: 100%; float: left; position: relative;}
    
    /*快速进入图标*/
    .cj-quick-menu{overflow: hidden;margin:0 auto;background:#f9f9f9;padding:1.25rem 0;}
    .cj-quick-menu i{text-align: center; width: 4rem; height: 4rem; display: block; margin: 0 auto; background-size: contain; background-position: 50% 50%;background-repeat: no-repeat;background-image: url(/public/images/index/icon_01.png);}
    .cj-quick-menu i.icon-02{background-image: url(/public/images/index/icon_02.png);}
    .cj-quick-menu i.icon-03{background-image: url(/public/images/index/icon_03.png);}
    .cj-quick-menu i.icon-04{background-image: url(/public/images/index/icon_04.png);}
    .cj-quick-menu i.icon-05{background-image: url(/public/images/index/icon_05.png);}
    .cj-quick-menu i.icon-06{background-image: url(/public/images/index/icon_06.png);}
    .cj-quick-menu i.icon-07{background-image: url(/public/images/index/icon_07.png);}
    .cj-quick-menu i.icon-08{background-image: url(/public/images/index/icon_08.png);}
    .cj-quick-menu i.icon-09{background-image: url(/public/images/index/icon_09.png);}
    .cj-quick-menu i.icon-10{background-image: url(/public/images/index/icon_10.png);}
    .cj-quick-menu ul li{width: 25%; float: left; overflow: hidden; text-align: center;}
    .cj-quick-menu ul li:nth-child(5),.cj-quick-menu ul li:nth-child(6),.cj-quick-menu ul li:nth-child(7),.cj-quick-menu ul li:nth-child(8){margin-top:1.1rem;}
    .cj-quick-menu ul li a{display: block; font-size: 0.8rem;color:#555;}
    .bouns{margin-top:1rem;}
    .bouns ul{padding-top:0.5rem;width:100%;overflow:hidden;}
    .bouns ul li{width:50%;float:left;height:3rem;line-height:3rem;background:#f53636;color:#fff;margin-bottom:0.2rem;border-radius: 5px; text-align: center;}
    .bouns ul li:nth-child(even){border-left:1px solid #fff;}
    /* 公共title */
    .index-hd {margin: 1.5rem 0 0.5rem; border-bottom: 0.18rem solid #000; padding-bottom: 0.1rem;}
    .index-hd:nth-child(4){border-bottom: 0;}
    .index-hd h2 {font-size: 1.35rem; color: #2c2c2c; display: inline-block; font-weight: bolder;}
    .index-hd h2:before {content: "";width: 1rem;height: 1rem;background: url(/public/images/index/icon.png) center center no-repeat;float: left;/* margin: 0.2em 0.4em 0 0; */background-size: 0.8rem;margin:0.6rem 0.1rem 0 0;}
    .index-box .index-hd span {color: #949090;margin-left: 0.1rem;font-size: 0.65rem;font-weight: bold;}
    .index-hd a {margin-top: 1.2rem;background-size: 100%;font-size:0;color: #cecece;float: right;width: 2.36rem;}
    .index-hd a i{font-size:0.4rem;vertical-align:middle;}

    /* 产品列表 */
    .best-seller{padding:.6rem 0 0.5rem .6rem; background: #f9f9f9; overflow: hidden;}
    .best-seller li{display: inline-block;width: 50%; padding: 0 0.6rem 1rem 0;overflow: hidden;}
    .best-seller li a{display: block;}
    .best-seller li a img{max-width:220px;width:100%;}
    .best-seller li h3{margin: 0.5rem 0 0rem; font-size: 0.9rem;}
    .best-seller h3 span{white-space: nowrap; text-overflow: ellipsis; overflow: hidden; display: block;color:#2c2c2c;font-size:0.85rem;}
    .best-seller li h3:after{content: ""; width:4.8rem; height:0.12rem; background: #000; display: block; margin-top: 0.1rem;}
    .best-seller li b{font-weight: normal; font-size: 1.1rem; color: #f30000;}
    .best-seller li del{font-size: 0.65rem; color: #bababa; margin-left: 0.1rem;}
    .best-seller .btn, .show-box .btn{width: 7rem; height: 1.86rem; border: 1px solid #000; font-size: 0.72rem; color: #1d1d1d; display: -webkit-inline-flex; display: inline-flex; -webkit-align-items: center; align-items: center; -webkit-justify-content: center; justify-content: center;}
    .best-seller .btn:before, .show-box .btn:before{content: ""; border-top: 0.2rem solid transparent; border-bottom: 0.2rem solid transparent; border-left: 0.2rem solid #000; display: inline-block; margin-right: 0.6rem;}

    /* 晒单 */
    .reveal .index-hd{border-bottom: 0;padding: 0;margin-bottom: 0.2rem;}
    .show-box{display: flex;}
    .show-banner{flex: 1.25; position: relative;}
    .show-pic{margin-left: 0.8rem; border-top: 0.15rem solid #000; flex: 1;}
    .show-pic ol li{display:none;}
    .show-pic ol li.cur{display:block;}
    .show-pic h3{font-size: 0.9rem;}
    .show-pic p{margin: 0.2rem 0; font-size: 0.8rem; color: #9e9e9e;}

    /* 星说 */
    .cj-star .index-hd{border-bottom: 0;padding: 0;margin-bottom: 0.2rem;}

    /* 品牌动态 */
    .brand_li li a{height: 2.6rem; border-bottom: 1px dashed #eaeaea; color: #222; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
    .brand_li li b{line-height: 1.8; padding: 0 0.2rem; margin-right: 0.2rem; border-radius: 2px; background: #f00028; font-weight: normal; font-size: 0.5rem; color: #fff; display: block;}
    .brand_li li p{padding-right: 1rem; color: #5b5b5b; font-size: 0.9rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; flex: 1; -webkit-flex: 1; display: block;}
    .brand_li li span{font-size: 0.8rem; color: #919191;}


    /* footer底部 */
    .footer{margin-top: 2rem; padding-bottom: 0.5rem;}
    .footer .foot-item{border-bottom: 0.15rem solid #000000;}

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
    /* 门店信息 */
    .branch .branch_list{color: #b3b3b3; padding: 0 0.9rem;}
    .branch .index-hd{border: 1px dotted #cdcdcd; border-left: 0; border-right: 0; padding: 0.5rem 0.9rem;}
    .branch .index-hd h2{}
    .branch .branch_list h3{font-size: 1rem; color: #545454; margin: 0.8rem 0 0.4rem;}
    .branch .branch_list li span{width: 25%; display: inline-block; font-size: 0.8rem;}
    .branch .branch_list li span:nth-child(3){padding-left: 1rem;}
    .branch .branch_list li span:last-child{text-align: right; padding-right: 1rem;}
    .branch .index-hd a{margin-top:1rem;}
    /* 底部nav */
    .foot_nav{overflow: hidden;margin-top: 2.5rem;}
    .foot_nav ul li{float:left;text-align:center;width:25%;border-top:1px solid #e6e6e6;padding:0.5rem 0;}
    .foot_nav ul li:nth-child(5),.foot_nav ul li:nth-child(6),.foot_nav ul li:nth-child(7),.foot_nav ul li:nth-child(8){border-bottom:1px solid #e6e6e6;}
    .foot_nav ul li i.ic_n{display:block;width:2.5rem;height:2.2rem;background-image:url(/public/images/index/foot_icon.png);background-size: 10em;margin:0 auto;}
    .foot_nav ul li i.icon_01{background-position:0.08rem -0.1rem;}
    .foot_nav ul li i.icon_02{background-position:-2.5rem -0rem;}
    .foot_nav ul li i.icon_03{background-position:-5rem -0rem;}
    .foot_nav ul li i.icon_04{background-position:-7.6rem -0rem;}
    .foot_nav ul li i.icon_05{background-position:0.08rem -2.5rem;}
    .foot_nav ul li i.icon_06{background-position:-2.5rem -2.5rem;}
    .foot_nav ul li i.icon_07{background-position:-5rem -2.45rem;}
    .foot_nav ul li i.icon_08{background-position:-7.6rem -2.5rem;}
    .foot_nav ul li a{display: block;border-right: 1px dashed #ebebeb; height:6rem;}
    .foot_nav ul li:nth-child(4) a,.foot_nav ul li:nth-child(8) a{border-right:none;}
    .foot_nav ul li a h4{color:#8c8c8c;font-size: 0.8rem;font-weight:normal;}
    .foot_nav ul li a em{color:#a8a8a8;font-size: 0.6rem;letter-spacing:-0.1rem;}
    /* 版权 */
    .copyright{padding-top:1em;overflow: hidden;text-align: center;}
    .copyright p{font-size: 0.8em; color: #b1b1b1; height: 1.4em; line-height: 1.4em;}
    /*搜索*/
   .searchBar{position:relative;margin: 1rem auto 0.8rem;padding: 0.3rem 0.7rem;background: #F1F1F1; border-radius: 0.3rem;}
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