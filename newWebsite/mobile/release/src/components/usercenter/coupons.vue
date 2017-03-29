<script>
	export default {
		ready() {
           
        },
		route: {
            data() {
                document.title = "我的优惠券";
                this.getValCoupList();
            }
        },
        data() {
            return {
                list: '',
                loading: true,
                showCoupList:false,
                activate:true,
                acti:false
            }
        },
        methods:{
            getValCoupList(){
                let data = {
                    used:1
                }
                this.common(data);
                this.activate = true;
            },
            getEmptyCoupList(){
                let data = {
                    used:0
                }
               this.common(data);
               this.activate = false;
            },
            common(data){
                this.loading = true;
                this.$http.post('/User/getBonus.json' ,data).then((res) => {
                    this.loading = false;
                    res = res.json();
                    if(res.status == 1){
                        this.list = res.data;
                        if( this.list.length > 0 ){
                            this.showCoupList = true;
                        }else{
                            this.showCoupList = false; 
                            this.acti = true;
                        }
                    }else{
                         this.$dispatch('popup', '请求数据有误，请重新页面重试！');
                    }
                });    
            }
        }
	}
</script>

<template>
<div class="container " >
    <div v-show="loading" class="loading"></div>

	<div class="content ">
		<div class="content-body">
			<!-- 无优惠券情况下展示 -->
			<div class="promote-card-list-box">
				<div class="promote-nav-box">
					<div class="tabber-ios-gray tabber tabber-ios">
						<a :class="{'active': activate}" href="javascript:;" @click="getValCoupList()">可用优惠券</a>
						<a :class="{'active': !activate}" href="javascript:;" @click="getEmptyCoupList()">已失效</a>
					</div>
				</div>
				<ul v-if="showCoupList" class="promote-card-list">
					<li class="promote-item coupon-style-shop" v-for="item in list">
    					<a class="clearfix" href="javascript:;">
    					<div class="promote-left-part">
    						<div class="inner">
    							<h4 class="promote-shop-name">{{item.type_name}}</h4>
    							<div class="promote-card-value">
    								<span>￥</span><i>{{item.type_money}}</i><em>X{{item.number}}张</em>
    							</div>
    							<div class="promote-condition font-size-12">使用期限：{{item.valid_period}}</div>
    						</div>
    					</div>
    					<div class="promote-right-part center">
    						<div class="promote-use-state">
    						</div>
    						<div class="inner">{{item.min_goods_info}}</div>
    					</div>
    					<i class="expired-icon"></i>
    					<i class="left-dot-line"></i>
    					</a>
					</li>
				</ul>
				<!-- 优惠券为空时 -->
				<div v-else class="empty-coupon-list center" :class="{'actived' : acti}">
					<div>
						<span class="font-size-16 c-black">神马，我还没有券？</span>
					</div>
					<div>
						<span class="font-size-12 c-gray-dark">怎么能忍？</span>
					</div>
					<div>
						<a href="javascript:;" v-link="{ name: 'index' }" class="tag tag-big tag-orange" style="padding:8px 30px;">马上去领取</a>
					</div>
				</div>
				<!-- 优惠券为空时 -->
			</div>
		</div>
	</div>
</div>
</template>

<style scoped>
/* .container {background-color: #f8f8f8;} */
.container .content {zoom: 1;}
.clearfix {zoom: 1;}
.font-size-12 {font-size: 12px !important;}
.clearfix:after {content: '';display: table;clear: both;}
.share-mp-info em, .share-mp-info i {vertical-align: middle;font-style: normal;}
.share-mp-info {position: relative;background: #1e1d22;color: #999;font-size: 0;line-height: 0;padding: 1px 176px 1px 1px;display: -webkit-box;display: -webkit-flex;display: -moz-box;display: -ms-flexbox;display: flex;}
.share-mp-info .page-mp-info, .share-mp-info .links {font-size: 14px;line-height: 24px;color: #999;}
.share-mp-info .page-mp-info {display: block;padding: 4px 0 4px 10px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;vertical-align: top;}
.share-mp-info img.mp-image {vertical-align: middle;margin-right: 3px;width: 24px;height: 24px;border-radius: 100%;-webkit-box-shadow: 0 0 3px rgba(0,0,0,0.25);box-shadow: 0 0 3px rgba(0,0,0,0.25);}
.share-mp-info i {color: #999;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;}
.share-mp-info .page-mp-info, .share-mp-info .links {font-size: 14px;line-height: 24px;color: #999;}
.share-mp-info .links {position: absolute;top: 6px;right: 10px;display: inline-block;}
.share-mp-info .mp-search {position: relative;display: inline-block;vertical-align: middle;width: 25px;height: 27px;}
.share-mp-info .mp-search::before {content: '';position: absolute;top: 6px;left: 6px;width: 12px;height: 12px;background: url(/public/images/user/search.png) no-repeat;background-size: 12px 12px;}
.share-mp-info a {color: #999;}
.share-mp-info .links a {margin-right: 8px;}
.share-mp-info .links .mp-homepage {margin-left: 0;}
.content {width: 100%;margin: 0 auto;}
.content-body {position: relative;z-index: 10;}
.promote-card-list-box .promote-nav-box {margin: 0 0 20px;padding-top: 20px;}
.tabber {width: 100%;color: #333;font-size: 14px;background-color: #fff;overflow: visible;border-top: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5;}
.tabber.tabber-ios {border: 1px solid #f60;border-radius: 3px;margin: 0 10px;width: auto;overflow: hidden;}
.tabber-ios-gray.tabber.tabber-ios {border: 1px solid #c9c9c9;}
.tabber button, .tabber a {float: left;width: 50%;line-height: 40px;border: 0px none;outline: 0px none;background-color: #fff;text-align: center;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;}
.tabber.tabber-ios a {color: #f60;line-height: 2;border: 0px none;background-color: transparent;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;border-right: 1px solid #f60;}
.tabber-ios-gray.tabber.tabber-ios a.active {position: relative;top: 0;color: #fff;float: left;width: 50%;text-align: center;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;background-color: #c9c9c9;border-right: 1px solid #c9c9c9;line-height: 2;outline: 0px none;}
.tabber.tabber-ios a:last-child {border: none !important;}
.tabber-ios-gray.tabber.tabber-ios a {color: #333;border-right: 1px solid #c9c9c9;}
.promote-card-list-box .promote-nav-box .tabber-ios-gray.tabber.tabber-ios a:not(.active) {color: #999;}
.promote-card-list {margin: 0 20px;}
.promote-card-list .promote-item {background: #fff;border: 1px solid #e5e5e5;border-bottom-width: 4px;border-radius: 5px;margin-bottom: 10px;/* height: 92px; */position: relative;overflow: hidden;font-size: 14px;}
.promote-card-list .coupon-style-shop {border-bottom-color: #d54036;}
.promote-card-list .promote-item a {display: block;}
.promote-card-list .promote-left-part {float: left;width: 70%;/* height: 92px; */color: #999;}
.promote-card-list .promote-left-part .inner {padding: 10px;}
.promote-card-list .coupon-style-shop .promote-card-value {color: #d54036;}
.promote-card-list .promote-left-part .promote-card-value {font-size: 35px;line-height: 42px;}
.promote-card-list .coupon-style-shop .promote-card-value em{font-size:12px;color:#f00;margin-left:5px;}
.promote-card-list .promote-left-part .promote-card-value span {font-size: 14px;}
.promote-card-list .promote-right-part {float: right;width: 30%;height: 92px;color: #999;line-height: 1.5;}
.promote-card-list .promote-use-state {text-align: right;padding: 10px;font-size: 14px;color: #333;line-height: 1;}
.promote-card-list .promote-right-part .inner {padding: 6px 10px;position: absolute;top: 50%;margin-top: -15px;text-align: right;right: 0;}
.promote-card-list .promote-left-part .promote-condition {white-space: nowrap;}
.center, .text-center {text-align: center;}
.empty-coupon-list {margin-top: 40px;padding: 30px 10px;display: none;}
.empty-coupon-list div {margin-bottom: 20px;}
.empty-coupon-list.actived{display:block;}
.c-black {color: #333 !important;}
.font-size-16 {font-size: 16px !important;}
.tag {display: inline-block;background-color: transparent;border: 1px solid #e5e5e5;border-radius: 3px;text-align: center;margin: 0;color: #999;font-size: 12px;line-height: 12px;padding: 4px;}
.tag-big {font-size: 14px;line-height: 18px;}
.c-gray-dark {color: #999 !important;}
.tag.tag-orange {color: #f60;border-color: #f60;}














</style>