<script>
	import CodesHistory from './promocodes_history.vue';
	export default {
		components: {
			CodesHistory
		},
		ready() {
            
		},
		route: {
            data() {
                document.title = "我的积分";
                this.loading = true;

                this.$http.post('/Global/getUserInfo.json').then((res) => {
	                this.loading = false;
	                res = res.json();
	                if(res.status == 1){
	                    this.userCout = res.data;
	                    this.$set('userCout', res.data);
	                }
	            }); 

	            let data ={
	            	pageSize: this.limit,
	            	page:this.page
	            };
				this.$http.post('/Integral/logList.json' ,data).then((res) => {
	                this.loading = false;
	                res = res.json();
	                if(res.status == 1){
	                    this.list = res.data.list;
	                }
	            }); 
            }
        },
        data() {
        	return {
        		loading: true,
        		list:[],
        		page:1,
        		limit:3,
        		userCout:{}
        	}

        }
	}
</script>

<template>
<div class="container">
	<div v-show="loading" class="loading"></div>
	<div class="content">
		<div class="pointsstore-total">
			我的积分：
			<em>{{userCout.points_left}}</em>
		</div>
		<ul class="tabber clearfix">
			<a class="tabber-item" href="javascript:;" v-link="{ name: 'pointsstore' }"><i class="icon"></i>积分商城</a>
			<a class="tabber-item" href="javascript:;" v-link="{ name: 'codeExchange' }"><i class="icon"></i>兑换记录</a>
		</ul>
		<div class="points-title">
			最近积分记录
			<a href="javascript:;" class="history-list" v-link="{ name: 'codesHistory' }">历史记录</a>
		</div>
		<div id="list_container" >
			<div class="js-list b-list">
			<ul>
				<div v-if="list.length == 0" class="comments-none">暂无积分详情记录</div>
			    <div v-else class="js-list b-list">
			        <ul class="comment-list" v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
			            <li class="pointsstore-item" v-for="item in list">
			                    <div class="item-info pull-left">
			                        <p class="item-desc">
			                            {{item.remark}}
			                        </p>
			                        <p class="item-time">
			                            {{item.add_date}}
			                        </p>
			                    </div>
			                    <div class="item-amount pull-right" :class="item.points > 0 ? 'dec' : 'dac'">
			                        {{item.points}}
			                    </div>
			                </li>
			        </ul>
			        <div v-if="limit == undefined" class="load-more">{{loadingText}}</div>
			   </div>
			</ul>
			</div>
		</div>
	</div>
</div>
</template>

<style scoped>
.pull-left {float: left;}
.pointsstore-item .item-info {width: 60%;}
.container {background-color: #f8f8f8;}
.c-green {color: #06bf04 !important;}
.c-red {color: #ed5050 !important;}
.content {margin: 0 auto;}
.pull-right {float: right;}
.container .content {zoom: 1;}
.clearfix:after {content: '';display: table;clear: both;}
.pointsstore-total {padding: 10px;background: url(/public/images/user/code_bg.png) no-repeat;background-position: left bottom;background-color: #ff4848;background-size: 100% 34px;color: #fff;height: 110px;font-size: 14px;position: relative;margin: 0.5rem;}
.pointsstore-total em{font-size: 36px;position: absolute;top: 0;bottom: 0;left: 0;right: 0;text-align: center;margin: auto;line-height: 130px;}
.tabber {width: 100%;color: #333;font-size: 14px;background-color: #fff;overflow: visible;border-top: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5;}
.tabber button, .tabber a{float: left;width: 50%;line-height: 40px;border: 0px none;outline: 0px none;background-color: #fff;text-align: center;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;}
.tabber .tabber-item {font-size: 16px;color: #666;height: 50px;line-height: 52px;}
.tabber .tabber-item>.icon{display: inline-block;width: 14px;height: 16px;position: relative;top: -1px;margin-right: 4px;background-repeat: no-repeat;background-size: 14px 16px;background-image: url(/public/images/user/cash.png);}
.tabber .tabber-item:last-child>.icon {background-image: url(/public/images/user/gift.png);top: -2px;}

.points-title{padding:10px;padding-top:20px;color:#666;font-size:16px;}
.history-list{float:right;color:#00a0f8;position:relative;padding-right:10px}
.history-list::after{-webkit-transform:rotate(45deg);-moz-transform:rotate(45deg);-ms-transform:rotate(45deg);transform:rotate(45deg);content:'';width:6px;height:6px;right:0;top:7px;border:2px solid #00a0f8;border-left:0;border-bottom:0;position:absolute}
#list_container{background-color:#fff}
.pointsstore-item{margin-left:10px;padding:10px 10px 10px 0;overflow:hidden;/* -webkit-border-image:url(../images/order_list/border-line.png) 2 stretch;-moz-border-image:url(../images/order_list/border-line.png) 2 stretch;border-image:url(../images/order_list/border-line.png) 2 stretch; */border-bottom:1px solid #e5e5e5;position:relative}
.pointsstore-item .item-info {width: 60%;}
.pointsstore-item .item-amount {line-height: 36px;font-size: 18px;position: absolute;right: 10px;top: 50%;margin-top: -18px;}
.pointsstore-item .item-desc {font-size: 16px;line-height: 1.5;color: #333;margin-bottom: 10px;}
.pointsstore-item .item-time {font-size: 12px;color: #666;}
.pointsstore-item .item-amount {line-height: 36px;font-size: 18px;position: absolute;right: 10px;top: 50%;margin-top: -18px;}

.comments-none{padding: 3rem 0; text-align: center;}
.points-title{padding:10px;padding-top:20px;color:#666;font-size:16px;}
.history-list{float:right;color:#00a0f8;position:relative;padding-right:10px}
.history-list::after{-webkit-transform:rotate(45deg);-moz-transform:rotate(45deg);-ms-transform:rotate(45deg);transform:rotate(45deg);content:'';width:6px;height:6px;right:0;top:7px;border:2px solid #00a0f8;border-left:0;border-bottom:0;position:absolute}
#list_container{background-color:#fff}
.pointsstore-item{margin-left:10px;padding:10px 10px 10px 0;overflow:hidden;border-bottom:1px solid #e5e5e5;position:relative}
.pointsstore-item .item-info {width: 60%;}
.pointsstore-item .item-amount{line-height: 36px;font-size: 18px;position: absolute;right: 10px;top: 50%;margin-top: -18px;color: #06bf04 !important;}
.pointsstore-item .item-amount.dec{color: #06bf04 !important;}
.pointsstore-item .item-amount.dac{color: #ed5050 !important;}
.pointsstore-item .item-desc {font-size: 16px;line-height: 1.5;color: #333;margin-bottom: 10px;}
.pointsstore-item .item-time {font-size: 12px;color: #666;}

</style>