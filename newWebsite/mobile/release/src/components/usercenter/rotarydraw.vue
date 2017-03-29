
<script>
import { updateAppHeader } from 'vuex_path/actions.js';
var KinerLottery = require('exports?KinerLottery!kinerLottery.js');
var header = document.querySelector('.header');
// var display = header.style.display;


export default {
	vuex: {
		actions: {
			updateAppHeader
		}
	},
	ready() {

    },
    route:{
    	deactivate(transition) {
			this.goodsName = this.prizeName;
			this.getPrizeName = this.gradeName;
			transition.next();
		},
    	data(transition){
    		this.updateAppHeader({
                type: 1
            });

            this.couts = 0;

            // header.style.display = 'none';
            // document.querySelector('#app').style.paddingTop = 0;
			this.$http.post('/WechatBindMobile/show.json').then((res) => {
				res = res.json();
				if(res.status == 1){
					this.loading = false;
					if(res.data.isCheckWechat == 1){
						this.showtLotate = true;
					}else{
						this.showtLotate = false;
						$('.error-tips').show();
					}
				}
			});

			if(transition.from.name == 'rotateOrder'){
				this.$nextTick(() => {
					this.prizeName = this.goodsName;
					this.gradeName = this.getPrizeName;
				});
			}

    		this.$http.post('/rotate/index.json').then((res) => {
				res = res.json();
				if(res.status == 1){
					this.$nextTick(() => {
						this.rotateList = res.data.data.prize_cfg; 
						this.rotateDes = res.data.data.des_info; 
						this.rotate_id = res.data.data.id;
						this.nums = res.data.data.num;
						this.getLogs();
					});
				}else{
					this.$dispatch('popup', res.msg);
				}
			})
    		
    	}
    },
    data(){
    	return{
    		rotate_id:'',
    		prize_id:'',
    		grade:'',
    		rotateList:[],
    		rotateDes:'',
    		winning:[],
    		winningList:[],
    		winnList:'',
    		rotateNum: 5, //转盘转动圈数
    		defNum:'',
    		realDeg:0,
    		deg:'',
    		degs:'',
    		nums:'',
    		couts:0,
    		prizeName:'',
    		gradeName:'',
    		goodsName:'',
    		getPrizeName:'',
    		doing:false,
    		loading: true,
    		showtLotate: false,
			showList:false
    	}
    },
    methods:{
    	toBuy() {

			let rotate_id = parseInt(this.rotate_id);
			let prize_id = parseInt(this.prize_id);
			this.$route.router.go({name: 'rotateOrder' , params:{ id:rotate_id , prize:prize_id }});
		},
		toStart(){
				if($('.KinerLotteryBtn').hasClass('doing')){
					 return;
				}
				let data = {
					rotate_id : parseInt(this.rotate_id)
				}
		 		this.$http.post('/rotate/rotating.json',data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$nextTick(() => {
							this.grade = res.data.prize.grade;
							this.prize_id = res.data.prize.prize_id;
							// console.log(this.grade)
							this.winning = res.data.prize;
							this.prizeName = res.data.prize.prize_name;
							this.gradeName = res.data.prize.grade_name;
							if(this.grade == 1){
			 					this.degs = 330
							}else if(this.grade == 2){
								this.degs = 270
							}else if(this.grade == 3){
								this.degs = 210
							}else if(this.grade == 4){
								this.degs = 150
							}else if(this.grade == 5){
								this.degs = 90
							}else{
								this.degs = 30
							}
							this.couts++;
							this.KinerLotteryHandler(this.degs,this.nums,this.couts)

						});
					}else{
						this.$dispatch('popup', res.msg);
					}
				})

		},
		KinerLotteryHandler(_deg,_num,_cout){
			var deg = _deg;
			var num = _num;
			var cout = _cout;
			new KinerLottery({
		        rotateNum: 8, //转盘转动圈数
		        body: "#box", //大转盘整体的选择符或zepto对象
		        direction: 0, //0为顺时针转动,1为逆时针转动
		        disabledHandler: function(key) {
		            switch (key) {
		                case "noStart":
		                    alert("活动尚未开始");
		                    break;
		                case "completed":
		                    alert("活动已结束");
		                    break;
		            }
		        }, //禁止抽奖时回调
		        clickCallback: function() {
		            //此处访问接口获取奖品
					$('.KinerLotteryBtn').addClass('doing');
		            this.goKinerLottery(deg);

		        }, //点击抽奖按钮,再次回调中实现访问后台获取抽奖结果,拿到抽奖结果后显示抽奖画面
		        KinerLotteryHandler: function() {
		        	$(".winn-box").show();
        		 	$(".closed").show();
        		 	$(".lines").show();
        		 	$('.KinerLotteryBtn').removeClass('doing');
        		 	// console.log(num)
        		 	// console.log(cout)


		        }//抽奖结束回调
		    });
		},
		oneTime(){
			if(this.nums == this.couts){
				$(".winn-box").hide()
				$(".closed").hide()
				$(".lines").hide()
				this.$dispatch('popup', '今天次数已用完，明天继续吧！');
			}else{
				$(".winn-box").hide()
				$(".closed").hide()
				$(".lines").hide()
			}
		},
		getLogs(){
			let data = {
				rotate_id:parseInt(this.rotate_id)
			}
			this.$http.post('/rotate/getLog.json',data).then((res) => {
				res = res.json();
					this.$nextTick(() => {
						this.showList=true;
						this.winningList = res;
						this.getList();
					});
			})
		},
		getList(){
			this.$nextTick(() => {
				let speedi = 2500;
				let cur=0;
				let countNum=this.winningList.length;
				function Marquee1() {
					var v_cur=cur;
					cur++;
					if(cur>=countNum){
						cur=0;
					}
					var i=100;
					clearInterval(tt);
					var tt=setInterval(function(){
						if(i>=1){
							i-=5;
						}else{
							clearInterval(tt);
							i=0;
						}
						$('#winning').find('li').eq(v_cur).css({'display':'block','z-index':'1','top':(i-100)+'%'});
						$('#winning').find('li').eq(cur).css({'display':'block','z-index':'2','top':i+'%'});
					},20);
				}
				var MyMar1 = setInterval(Marquee1, speedi);
			});
		}

    }
}

</script>


<template>
<div v-show="loading" class="loading"></div>
<div class="container">
	<div v-if="showtLotate" class="showtLotate">
		<img src="/public/images/rotarydraw/img_01.jpg" alt="">
		<div class="pr_pra">
			<img src="/public/images/rotarydraw/img_02.jpg" alt="">
			<div id="box" class="box">
		        <div class="outer KinerLottery KinerLotteryContent">
		        	<img src="/public/images/rotarydraw/lanren.png">
		        </div>
		        <div class="inner KinerLotteryBtn start" @click="toStart()"></div>
		    </div>
		</div>
		<img src="/public/images/rotarydraw/img_03.jpg" alt="">
		<div class="prize-list">
			<ul>
				<li>{{{ rotateDes }}}</li>
			</ul>
		</div>
		<img src="/public/images/rotarydraw/img_04.jpg" alt="">
		<div class="winning">
			<ul id="winning" v-if="showList">
				<li v-for="item in winningList">{{item.nick_name}} 喜中{{item.prize_name}}</li>
			</ul>
		</div>
		<div class="winn-box">
			<div class="winn-img">
				<img src="/public/images/rotarydraw/win_box.jpg">
				<div class="winn-comment" v-if="grade != 0 ">
					<div class="pro">
						<h3>恭喜您抽中了--><i>{{ gradeName }}</i></h3>
						<h4>{{ prizeName }}</h3>
						<a href="javascript:;" @click="toBuy()">点击领取</a>
					</div>
				</div>
				<div class="winn-comment" v-else>
					<div class="pro thanks">
						<h3 v-else>{{winning.prize_name}}</h3>
						<a href="javascript:;" @click="oneTime()" >再抽一次</a>
					</div>
				</div>
			</div>
		</div>
		<div class="closed" id="closed" @click="oneTime()"><span>X</span></div>
		<div class="lines"></div>
	</div>
	<div v-else><p class="error-tips">请用微信打开！</p></div>
</div>
</template>


<style scoped>
.container{background: #fff;}
.showtLotate{background: #e94c43;padding-bottom: 2.5rem;width: 96%;margin: 0.5rem auto 0;}
.container img{display:block;}
.pr_pra{position: relative;}
.box { width: 18rem; height: 18rem; position: relative; margin: 0 auto; position: absolute; top: 49%; left: 50%; transform: translate(-50%, -50%); -webkit-transform: translate(-50%, -50%); -moz-transform: translate(-50%, -50%); -ms-transform: translate(-50%, -50%); -o-transform: translate(-50%, -50%); }
.box .outer { width: 100%; height: 100%; position: absolute; z-index: 1; top: 0; left: 0; transform: rotate(0deg); -webkit-transform: rotate(0deg); -moz-transform: rotate(0deg); -ms-transform: rotate(0deg); -o-transform: rotate(0deg); }
.box .outer img { width: 100%; }
.box .inner { position: relative; width: 8rem; height: 8rem; left: 51%; top: 50%; -webkit-transform: translate(-50%, -50%); -moz-transform: translate(-50%, -50%); -ms-transform: translate(-50%, -50%); -o-transform: translate(-50%, -50%); transform: translate(-50%, -50%); z-index: 2; background-image: url(/public/images/rotarydraw/arrow.png); background-size: auto 8rem; background-repeat: no-repeat; }
.box .inner.start:active { -webkit-transform: translate(-50%, -50%) scale(.95); -moz-transform: translate(-50%, -50%) scale(.95); -ms-transform: translate(-50%, -50%) scale(.95); -o-transform: translate(-50%, -50%) scale(.95); transform: translate(-50%, -50%) scale(.95); }
.box .inner.start { background-position: 0 0; }
.box .inner.no-start { background-position: -5rem 0; }
.box .inner.completed { background-position: -10rem 0; }
.prize-list,.winning{background:#f5ef77;overflow: hidden;margin:0.5rem 1.25rem 0;border-radius:0.25rem;padding:0.15rem;}
.prize-list ul,.winning ul{background:#f5ef77;border:1px dashed #e94c43;color:#e94c43;border-radius:0.25rem;overflow:hidden;padding: 0.4rem 0.4rem 1rem;}
.prize-list ul li{font-size:14px; margin-top:0.6rem; text-align: justify;}
.winning{border:1px dashed #e94c43;height:35px;}
.winning ul{border:0;width: 10000px;}
.winning ul{overflow:hidden;padding:0 .4rem;}
.winning ul li{height:32px;line-height:32px;margin-right:10px;font-size:12px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;}
.winn-box{position: fixed;width:100%;height:100%;top:0;left:0;background:#544d48;text-align:center;z-index: 9999; display:none; }
.winn-box .winn-img{position: relative;display:inline-block;width: 300px;margin-top:35%;}
.pro{position: relative;}
.winn-comment{position: absolute;top:0%;left:0%;padding:35px 40px 0;display:inline-block;width:100%;}
.winn-comment h3{color:#fff;font-size: 20px;font-weight: bold;}
.winn-comment h3 i{color:#EDC327;}
.winn-comment h4{color:#fff;font-size: 18px;margin-top:8px;}
.winn-comment .pro a{position: absolute;top:118px;left:24px; display:inline-block;width:80%;padding:0.7rem 1rem;border-radius:0.25rem;background:#f4da2f;color:#9d4f43;box-shadow:0px 4px 0px #edc327;}
.thanks h3{text-align:center;}
.closed{position: fixed;top:12%;right:25px;z-index: 10000;width: 25px;height: 25px;text-align: center;border-radius: 20px;font-size:18px;display:none;}
.closed span{display:inline-block; width: 25px;height: 25px;text-align: center;border-radius: 20px;background:#dc3a36;color:#fff;}
.lines{position: fixed;top:0%;right:37px;width:1px;height:12%;background:#dc3a36; z-index:9999;display:none;}
.error-tips{text-align:center;font-size:1.2rem;padding-top:1rem;display:none;}

.winning{height: 2.2rem;overflow: hidden;z-index: 100;position: relative;left: 0;top: 0;}
.winning ul li{line-height: 32px;box-sizing: border-box;}
.winning ul li{position: absolute;z-index: 0;display: none; top: 100%;}
.winning ul li:nth-of-type(1){display: block;top: 0%;}
</style>