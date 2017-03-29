<script>
	export default {
		ready() {

		},
		route: {
            data() {
                document.title = "积分商城";
                this.$http.post('/Global/getUserInfo.json').then((res) => {
                    // this.loading = false;
                    res = res.json();
                    if(res.status == 1){
                        this.userCout = res.data.list;
                        this.$set('userCout', res.data);
                        this.findStoreList();
                    }
                }); 
            }
        },
        data() {
        	return {
        		loading: true,
        		loaded:true,
        		loadTry: 0,
				loadingText: '加载中...',
        		empty:false,
        		fullGoodsList:false,
        		list:[],
        		page:1,
        		userCout:{}
        	}

        },
        methods: {
			findStoreList() {
				this.loading = true;
				let data = {
                    pageSize: this.limit ? this.limit : 8,
                    page: this.page
                }
                this.$http.post('/Integral/goodsList.json', data).then((res) => {
                    res = res.json();
                    if(res.status == 1){
                    	this.loaded = false;
                        for(let i of res.data.list){
                            this.list.push(i);
                            this.fullGoodsList =true;
                        }
                        this.$nextTick(() => {

                            if(res.data.list.length == 0){
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
                this.page += 1;
                this.findStoreList();
            }
		}
	}
</script>

<template>
<div class="container">
	<div v-show="loaded" class="loading"></div>
	<div class="content">
		<div class="pointsstore-total">
			我的积分：
			<em>{{userCout.points_left}}</em>
		</div>
		<div v-if="fullGoodsList" id="list_container" class="sc-goods-list list">
			<div class="js-list b-list">
			<ul v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
				<li class="goods-card" v-for="item in list">
					<a class="link" href="javascript:;" v-link="{ name: 'pointsGood', params: {id: item.exchange_id,package: item.is_package} }">
					    <!-- 图片 -->
					    <div class="photo-block">
					        <img class="goods-photo" :src="item.image[0].img_url">
					    </div>

					    <div class="info">
					        <p class="goods-title">{{item.goods_name}}</p>
					        <p class="goods-points">{{item.point}}积分 + ￥{{item.price}}</p>
					        <p class="goods-price">￥<em class="original-price">{{item.shop_price}}</em></p>
					    </div>
					</a>
				</li>
			</ul>
			<div class="load-more">{{loadingText}}</div>
			</div>
		</div>
		<div v-else class="empty-goods-list" :class="{'empty':empty}">
			<div class="empty-list list-finished" style="padding-top:60px;">
			    <div>
			        <h4>积分不足或者没有积分兑换商品</h4>
			    </div>
			</div>
		</div>
	</div>
</div>
</template>

<style scoped>
.pull-left {float: left;}
.pointsstore-item .item-info {width: 60%;}
/* .container {background-color: #f8f8f8;} */
.c-green {color: #06bf04 !important;}
.content {margin: 0 auto;}
.pull-right {float: right;}
.container .content {zoom: 1;}
.clearfix:after {content: '';display: table;clear: both;}
.pointsstore-total {padding: 10px;background-position: left bottom;background-color: #ff4848;background-size: 100% 34px;color: #fff;font-size: 17px;position: relative;margin: 0.5rem;}
.sc-goods-list {font-size: 12px;padding: 5px;list-style: none;margin: 0;}
#list_container {background-color: #fff;}
.sc-goods-list .goods-card {position: relative;margin: 0 5px;}
.sc-goods-list.list .goods-card {margin: 5px;}
.sc-goods-list .link {display: block;background: #fff;min-height: 100px;}
.sc-goods-list.list .link {min-height: 80px;}
.sc-goods-list .photo-block {text-align: center;overflow: hidden;position: relative;background-color: #f8f8f8;background-size: 6px 6px;}
.sc-goods-list.list .goods-card .photo-block {float: left;margin-right: 13px;width: 125px;height: 125px;}
.sc-goods-list.list .goods-card .photo-block {width: 80px;height: 80px;}
.sc-goods-list .photo-block img{position:absolute;left:0;right:0;top:0;bottom:0;margin:auto;vertical-align:bottom;max-width:100%}
.sc-goods-list.list .goods-card .photo-block img{max-width:80px;max-height:80px}
.sc-goods-list .info{position:relative}
.sc-goods-list.list .goods-card .info{border-bottom:1px solid #e5e5e5;height:auto;max-height:103px;margin-left:93px;padding-bottom:5px}
.sc-goods-list .info p{margin:0}
.sc-goods-list .info p.goods-title{line-height:1.3;overflow:hidden;color:#333}
.sc-goods-list.list .goods-card .info .goods-title{font-size:14px;max-height:52px;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:16px;margin-bottom:10px;padding-top:2px}
.sc-goods-list.list .goods-card .info .goods-points{background:url(/public/images/user/icon_cout_r.png) no-repeat;background-size:12px;background-position:left center;font-size:16px;color:#ef0000;padding-left:16px;margin-bottom:5px}
.sc-goods-list .info p.goods-price{font-weight:700;padding:0}
.sc-goods-list.list .goods-card .info .goods-price{font-size:15px;margin-bottom:8px}
.sc-goods-list.list .goods-card .info .goods-price{color:#999;font-size:14px}
.sc-goods-list.list .goods-card .info .goods-price .original-price{color:#999;text-decoration:line-through;font-weight:400}
.empty-goods-list{display: none;}
.empty-goods-list.empty{display: block;}
.empty-list{width:100%;font-size:14px;display:block;text-align:center;padding:30px 10px;color:#999;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
.empty-list div{margin-bottom:20px}
.empty-list h4{font-size:16px;margin-bottom:10px;color:#666}






</style>