<script>
	export default {
		ready() {
			
			
		},
		route: {
            data() {
                document.title = "我的签到";
                this.loading = true;
				let data ={
					bind_id:this.$route.params.id
				}

				if(this.$route.params.id != undefined && this.$route.params.id != '' && this.$route.params.id != null){
					this.$http.post('/SignIn/info.json',data).then((res) => {
						res = res.json();
						if(res.status ==1){
							// this.loading = false;
							if(res.data.is_sign == 1){
								this.loading = false;
								this.checkinCode = res.data;
								this.appCheckin = true;
								
								this.$nextTick(() => {
										var e, n, s, o;
										s = 5 > parseInt(res.data.days) ? ~~ (parseInt(res.data.days) / 5) : 3 * (~~ ((parseInt(res.data.days) + 1) / 3) - 1),$('.apps-checkin-day').each(function(e) {
											var c = ~~s + e;
											$(this).html(c), c == parseInt(res.data.days) && (n = e, $(this).addClass("apps-checkin-day-at"))
										}), 0 === s && $(".apps-checkin-progress").addClass("apps-checkin-progress-fromzero"), 0 === n ? (e = 10, $(".apps-checkin-progress-filled-wrap").width(e + 2)) : (e = $('.apps-checkin-day').eq(n).position().left + 24, $(".apps-checkin-progress-filled-wrap").width(e)), $(".apps-checkin-man").css({
											left: e
										})
								});
							}else{
								this.loading = false;
								this.tips = true;
							}
						}else{
							this.loading = false;
							this.$dispatch('popup', res.msg);
						}
						
					});	
				}else{
					this.loading = false;
					this.tips = true;
				}
            }
        },
        data(){
        	return{
        		appCheckin:false,
        		loading: true,
        		tips:false,
        		dayAt:false,
        		checkinCode:{}
        	}
        },
        methods:{
        	getCodes(){
        		
        	}
        }
	}
</script>

<template>
<div class="container">
<div v-show="loading" class="loading"></div>
	<div class="apps-game">
		<div class="apps-checkin" v-if="appCheckin">
			<div class="apps-checkin-nav">
				<a href="">
					<div class="apps-checkin-avatar">
						<img :src="checkinCode.headimgurl" alt="">
					</div>
				</a>
				<div class="apps-checkin-nav-opt">
					<a class="btn btn-opt" href="JavaScript:;" v-link="{name:'checkin-rule'}">活动规则</a>
				</div>
				<div class="apps-checkin-userinfo">
					<p class="apps-checkin-userinfo-row">{{checkinCode.nickname }}</p>
					<p class="apps-checkin-userinfo-row apps-checkin-userinfo-points">积分：<span class="js-points">{{ checkinCode.points_left }}</span></p>
				</div>
			</div>
			<div class="apps-checkin-content">
				<div class="apps-checkin-center-content" style="visibility: visible;">
					<div class="apps-checkin-center-block">
		                <div class="apps-checkin-center-info">
		                    <h4 class="apps-checkin-center-info-title">已连续签到</h4>
		                    <p class="apps-checkin-center-info-row">
		                        <span class="apps-checkin-center-info-days">{{ checkinCode.days }}</span>
		                        <span class="apps-checkin-center-info-small">天</span>
		                    </p>
		                </div>
		            </div>
		            <div class="apps-checkin-runway">
		                <div class="apps-checkin-progress"></div>
		                <!-- <div class="apps-checkin-progress-filled-wrap" :style="{width: 12 + 55*checkinCode.days +'px'}" > -->
		                <div class="apps-checkin-progress-filled-wrap"  >
		                    <div class="apps-checkin-progress-filled"></div>
		                </div>
		                <!-- <div class="apps-checkin-prize-wrap" style="left: 65.5px;">
		                    再签到<span class="js-prize-need">1</span>天，有惊喜！
		                </div> -->
		                <ul class="apps-checkin-days-wrap">
		                    <li class="apps-checkin-day">&nbsp;</li>
		                    <li class="apps-checkin-day">&nbsp;</li>
		                    <li class="apps-checkin-day">&nbsp;</li>
		                    <li class="apps-checkin-day">&nbsp;</li>
		                    <li class="apps-checkin-day">&nbsp;</li>
		                    <li class="apps-checkin-day">&nbsp;</li>
		                    <li class="apps-checkin-day">&nbsp;</li>
		                </ul>
		                <!-- <div class="apps-checkin-man" :style="{left: 12 + 56*checkinCode.days +'px'}"></div> -->
		                <div class="apps-checkin-man" ></div>
		            </div>
				</div>
			</div>
			<div class="apps-checkin-footer">
		        <button class="btn btn-checkin js-checkin" disabled="true" v-if=" checkinCode.is_sign == 1 ">已签到</button>
		        <!-- <button class="btn btn-checkin js-checkin" v-if=" checkinCode.is_sign == 0 " @click="getCodes()">签到</button> -->
		    </div>
		</div>
		<div v-else class="un-check" :class="{'checkin-tips': tips}"><h2>您好，你还没签到，请返回公众号发送"签到"才有签到积分详情哦！</h2></div>
	</div>
</div>
</template>

<style scoped>
.container {min-height: 100%;background: transparent;}
.apps-game {width: auto;margin: 0;min-height: initial;}
.apps-checkin .apps-checkin-nav {position: relative;background-color: #c68674;padding: 10px 20px;height: 40px;z-index: 150;box-sizing: initial;}
.apps-checkin .apps-checkin-nav .apps-checkin-avatar{position: absolute;top: 5px;left: 20px;width: 75px;height: 75px;background-color: #c68674;border-radius: 50%;border: 3px solid #fff;-webkit-box-shadow: 0 0 0 3px #c68674;box-shadow: 0 0 0 3px #c68674;overflow: hidden;text-align: center;}
.apps-checkin .apps-checkin-nav .apps-checkin-nav-opt{float: right;margin-top: 21px;}
.apps-checkin .apps-checkin-nav .apps-checkin-userinfo{margin-left: 87px;margin-right: 60px;color: #fff;}
.apps-checkin .btn.btn-opt {padding: 3px 8px;line-height: 12px;font-size: 12px;background-color: #e1a797;color: #ba6f5a;-webkit-box-shadow: none;box-shadow: none;border-radius: 8px;}
.apps-checkin .apps-checkin-nav .apps-checkin-userinfo {margin-left: 87px;margin-right: 60px;color: #fff;}
.apps-checkin .apps-checkin-nav .apps-checkin-userinfo .apps-checkin-userinfo-row {font-size: 14px;line-height: 20px;}
.apps-checkin .apps-checkin-nav .apps-checkin-userinfo .apps-checkin-userinfo-row.apps-checkin-userinfo-points { /* display: inline-block; */ }
.apps-checkin .apps-checkin-content {background-color: #34c79f;background-size: 600px 352px;background-position: center;background-image: url(/public/images/common/checkin_bg.png);}
.apps-checkin .apps-checkin-content .apps-checkin-center-content {position: relative;overflow: hidden;width: 380px;height: 352px;margin: 0 auto;z-index: 100;visibility: hidden;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;}
.apps-checkin .apps-checkin-content .apps-checkin-center-block {position: absolute;width: 298px;height: 230px;background-image: url(/public/images/common/center_block.png);left: 50%;top: 45px;margin-left: -70px;}
.apps-checkin .apps-checkin-content .apps-checkin-center-info {margin-top: 37px;}
.apps-checkin .apps-checkin-content .apps-checkin-center-info-title {margin-left: 35px;margin-bottom: 20px;font-size: 18px;line-height: 18px;color: #9e3b14;}
.apps-checkin .apps-checkin-content .apps-checkin-center-info-row {width: 148px;text-align: center;color: #fff;}
.apps-checkin .apps-checkin-content .apps-checkin-center-info-days {display: inline-block;min-width: 60px;font-size: 54px;}
.apps-checkin .apps-checkin-content .apps-checkin-center-info-small {font-size: 12px;}
.apps-checkin .apps-checkin-runway {position: absolute;bottom: 20px;left: 0;right: -66px;height: 36px;padding-top: 13px;color: #95614d;list-style: none;    box-sizing: initial;}
.apps-checkin .apps-checkin-runway .apps-checkin-progress, .apps-checkin .apps-checkin-runway .apps-checkin-progress-filled-wrap {position: absolute;top: 0;left: 0;right: 0;height: 3px;background-color: #8c7777;}
.apps-checkin .apps-checkin-runway .apps-checkin-progress.apps-checkin-progress-fromzero, .apps-checkin .apps-checkin-runway .apps-checkin-progress-filled-wrap.apps-checkin-progress-fromzero {left: 12px;}
.apps-checkin .apps-checkin-runway .apps-checkin-progress, .apps-checkin .apps-checkin-runway .apps-checkin-progress-filled-wrap {position: absolute;top: 0;left: 0;right: 0;height: 3px;background-color: #8c7777;}
.apps-checkin .apps-checkin-runway .apps-checkin-progress-filled-wrap {overflow: hidden;right: initial;background: none;-webkit-transition: 1s ease width;-moz-transition: 1s ease width;transition: 1s ease width;}
.apps-checkin .apps-checkin-runway .apps-checkin-progress-fromzero+.apps-checkin-progress-filled-wrap {left: 12px;}
.apps-checkin .apps-checkin-runway .apps-checkin-progress-filled-wrap .apps-checkin-progress-filled {width: 380px;height: 3px;background: -webkit-repeating-linear-gradient(150deg, #e95014, #e95014 3px, #ee652f 3px, #ee652f 6px);background: -moz-repeating-linear-gradient(150deg, #e95014, #e95014 3px, #ee652f 3px, #ee652f 6px);background: repeating-linear-gradient(-60deg, #e95014, #e95014 3px, #ee652f 3px, #ee652f 6px);}
.apps-checkin .apps-checkin-content .apps-checkin-prize-wrap {position: absolute;top: -40px;background-color: #9c827a;padding: 5px 8px;border-radius: 5px;color: #fff;font-size: 12px;line-height: 12px;-webkit-transition: .3s ease opacity;-moz-transition: .3s ease opacity;transition: .3s ease opacity;}
.apps-checkin .apps-checkin-runway .apps-checkin-day {position: relative;float: left;width: 28px;margin: 0 15px;text-align: center;font-size:12px;}
.apps-checkin .apps-checkin-runway .apps-checkin-day:first-child {margin-left: 0;}
.apps-checkin .apps-checkin-runway .apps-checkin-day::after{content: "";position: absolute;left: 50%;top: -15px;width: 6px;height: 6px;margin-left: -3px;border-radius: 50%;background-color: #fdfffe;border: 1px solid #ee652f;}
.apps-checkin .apps-checkin-runway .apps-checkin-day.apps-checkin-day-at::before {content: "";position: absolute;left: 50%;top: -5px;margin-left: -1px;border: 3px solid transparent;border-top: 4px solid #fde55d;}
.apps-checkin .apps-checkin-runway .apps-checkin-day::after {content: "";position: absolute;left: 50%;top: -15px;width: 6px;height: 6px;margin-left: -3px;border-radius: 50%;background-color: #fdfffe;border: 1px solid #ee652f;}
.apps-checkin .apps-checkin-runway .apps-checkin-day.apps-checkin-day-at::after {top: -18px;margin-left: -5px;border: 4px solid #fde55d;}
.apps-checkin .apps-checkin-runway .apps-checkin-man {position: absolute;bottom: 0;margin-left: -8px;width: 33px;height: 35px;background-size: cover;background-image: url(/public/images/common/man.png);}

.apps-checkin .apps-checkin-footer {background-color: #fde55d;padding: 15px 0;text-align: center;}
.apps-checkin .btn {padding: 0;border: 0px none;border-radius: 5px;-webkit-box-shadow: 0 2px #a9a9a9;box-shadow: 0 2px #a9a9a9;color:#fff !important;}
.apps-checkin .btn.btn-checkin {height: 40px;width: 150px;font-size: 21px;background-color: #409c7d;color: #fde55d;border-radius: 20px;-webkit-box-shadow: 0 4px #317e64;box-shadow: 0 4px #317e64;}
.apps-checkin .btn.btn-checkin:disabled {position: relative;top: 5px;color: #6bbba0;border: 0px none !important;background-color: #409c7d !important;letter-spacing: 3px;-webkit-box-shadow: none;box-shadow: none;}
.un-check{display:none;}
.un-check h2{text-align:center;font-size:12px;color:#666;margin-top:20px;}
.un-check.checkin-tips{display: block;}
</style>