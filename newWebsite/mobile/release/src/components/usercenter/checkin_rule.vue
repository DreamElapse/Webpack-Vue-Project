<script>
	export default {
		ready() {
			
		},
		route: {
            data() {
                document.title = "签到规则";
                let data ={
					bind_id:1
				}
				this.$http.post('/SignIn/info.json',data).then((res) => {
					res = res.json();
					if(res.status ==1){
						this.checkinCode = res.data;
					}
					
				});	
            }
        },
        data(){
        	return{
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
<div class="container" >
	<div class="apps-game">
		<div class="checkin-rule-wrap">
			<div class="checkin-rule-title">
            活动规则：
			</div>
			<div class="checkin-rule-content">
				<ul class="checkin-rule-list">
					<li class="checkin-rule-item">1.第一天签到送5积分，明天再来领6积分</li>
					<li class="checkin-rule-item">2.连续2天签到，第2天送6积分,明天再来领7积分</li>
					<li class="checkin-rule-item">3.连续3天签到，第3天送7积分,明天再来领8积分</li>
					<li class="checkin-rule-item">4.连续4天签到，第4天送8积分,明天再来领9积分</li>
					<li class="checkin-rule-item">5.连续5天签到，第5天送9积分,明天再来领10积分</li>
					<li class="checkin-rule-item">6.连续N(N>5)天签到，第N天送10积分,明天再来领10积分</li>
					<li class="checkin-rule-item">7.当中间有一天未签，则第二天签到从5积分重新开始累计。</li>
					<li class="checkin-rule-item">签到积分实时到账。当天签到时间为00：01分-23：59分。</li>
				</ul>
			</div>
			<div class="checkin-rule-footer">
            备注：积分和奖品请到
				<a href="javascript:;" v-link="{name:'user'}">会员中心</a>查看
				<div class="checkin-rule-footer-opt">
					<a href="javascript:history.back();" class="btn btn-opt">返回签到</a>
				</div>
			</div>
		</div>
	</div>
</div>
</template>

<style scoped>
#app{background: #bc7966;}
div.container {min-height: 100%;background: #bc7966;}
.checkin-rule-wrap {color: #fff;width: 270px;margin: 0 auto;font-size: 14px;letter-spacing: 1px;padding: 20px 25px 140px;}
.btn {display: inline-block;border-radius: 3px;padding: 5px 4px;text-align: center;margin: 0;font-size: 12px;cursor: pointer;line-height: 1.5;-webkit-appearance: none;background-color: #fff;border: 1px solid #e5e5e5;color: #999;}
.btn-opt {min-width: 50px;}
.checkin-rule-wrap .checkin-rule-title {margin-bottom: 10px;}
.checkin-rule-wrap .checkin-rule-content {margin-bottom: 20px;}
.checkin-rule-wrap .checkin-rule-footer a {color: #ffd658;}
.checkin-rule-wrap .checkin-rule-footer .checkin-rule-footer-opt {margin-top: 20px;}
.checkin-rule-wrap .checkin-rule-footer .btn-opt {padding: 3px 8px;line-height: 12px;font-size: 12px;background-color: #e1a797;color: #ba6f5a;-webkit-box-shadow: none;box-shadow: none;border-radius: 8px;border:0;}
.checkin-rule-list li{margin-bottom:4px;}






</style>