
<script>

	export default {
		ready() {
      			
		},
		route: {
            data() {
                document.title = "兑换记录";
                this.findExchangeList()
            }
        },
        data(){
        	return {
        		exchange:[],
        		loading:true,
        		loaded:true,
        		page:1,
        		list:[],
        		loadTry: 0,
				loadingText: '加载中...'
        	}
        },
        methods: {
			findExchangeList() {
				this.loading = true;
				let data = {
                    pageSize: this.limit ? this.limit : 8,
                    page: this.page
                }
                this.$http.post('/Integral/exchangeList.json', data).then((res) => {
                    res = res.json();
                    if(res.status == 1){
                    	this.loaded = false;
                        
                        this.$nextTick(() => {
                        	for(let i of res.data.list){
	                            this.exchange.push(i);
	                        }
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
                this.findExchangeList();
            }
		}

	}
</script>
<template>
	<div class="container" >
	<div v-show="loaded" class="loading"></div>
		<div class="content">
			<div id="order-list-container">
				<div class="js-list b-list">
				<ul v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
				 <li v-for="item in exchange" class="block block-order animated">
				 	<div class="header">
							<span class="font-size-12">订单号：{{item.order_sn}}</span>
					</div>
					<hr class="margin-0 left-10">
					<div class="block block-list border-top-0 border-bottom-0">
						<div class="block-item name-card name-card-3col clearfix">
							<a href="javascript:;" class="thumb">
							<img :src="item.goods_info.image[0].img_url">
							</a>
							<div class="detail">
								<a href="javascript:;">
								<h3>{{item.goods_info.goods_name}}</h3>
								</a>
							</div>
							<div class="right-col">
								<div class="price"><p class="goods-points">{{item.goods_info.point}}积分 + ￥{{item.goods_info.price}}</p></div>
								<div class="num">×<span class="num-txt">{{item.goods_number}}</span></div>
							</div>
						</div>
					</div>
					<hr class="margin-0 left-10">
					<div class="bottom">
						<span class="font-size-12">兑换时间： {{item.adddate}}</span>
						<div class="opt-btn">
							<!-- <a class="btn btn-green btn-in-order-list" href="javascript:;" v-link="{ name: 'pointsGoods', params: {id: item.goods_info.exchange_id,package: item.goods_info.is_package} }">兑换</a> -->
						</div>
					</div>
				 </li>
				</ul>
				<div class="load-more">{{loadingText}}</div>
				</div>
			</div>
		</div>
	</div>
</template>
<style scoped>
.content {margin: 0 auto;}
.clearfix:after {content: '';display: table;clear: both;}
.font-size-12 {font-size: 12px !important;}
.block {border-top: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5;overflow: hidden;margin: 10px 0;background-color: #fff;display: block;position: relative;font-size: 14px;}	
.block:first-child {margin-top: 0;}
.block.block-order:last-of-type {margin-bottom: 0;}
.block.block-order .header{height:37px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding:0 10px;padding-left:10px;line-height:37px}
hr{margin:10px 0;border:0;border-top:1px solid #e5e5e5}
hr.margin-0{margin:0}
hr.left-10{margin-left:10px}
.block.border-top-0{border-top:0}
.block.border-bottom-0{border-bottom:0}
.block.block-list{margin:0;padding:0;padding-left:10px;list-style:none;font-size:14px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}
.name-card{margin-left:0;width:auto;padding:5px 0;overflow:hidden;position:relative}
.name-card.name-card-3col{padding:8px 0;padding-right:85px}
.block-list>.block-item:first-child{border-top:0 none}
.name-card .thumb{width:60px;height:60px;float:left;position:relative;margin-left:auto;margin-right:auto;overflow:hidden;background-size:cover}
.name-card .thumb img{position:absolute;margin:auto;top:0;left:0;right:0;bottom:0;width:auto;height:auto;max-width:100%;max-height:100%}
.name-card .detail{margin-left:68px;width:auto;position:relative}
.name-card .detail a{display:block}
.name-card .detail h3{margin-top:1px;color:#333;font-size:12px;line-height:16px;width:100%}
.name-card.name-card-3col .right-col{position:absolute;right:0;top:8px;width:125px;padding-right:10px;font-size:12px}
.name-card.name-card-3col .right-col .price{font-size:14px;color:#515151;text-align:right;line-height:16px}
.name-card.name-card-3col .right-col .num{font-weight:200;text-align:right;margin-top:3px;padding:0;color:#555}
.name-card.name-card-3col .right-col .num .num-txt{padding:0;line-height:22px;color:#515151}
.block.block-order .bottom{padding:10px;padding-left:10px;height:16px;font-size:14px;line-height:16px;box-sizing:initial}
.opt-btn{display:inline-block;margin-top:-6px;float:right}
.btn{display:inline-block;border-radius:3px;padding:5px 4px;text-align:center;margin:0;font-size:12px;cursor:pointer;line-height:1.5;-webkit-appearance:none;background-color:#fff;border:1px solid #e5e5e5;color:#999}
.btn-green{color:#fff;background-color:#06bf04;border-color:#03b401}
.opt-btn .btn{margin-left:5px;padding:4px 4px;text-align:center;line-height:19px;width:60px;height:28px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}





</style>