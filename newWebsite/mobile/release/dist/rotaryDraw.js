webpackJsonp([48],{466:function(t,i,e){var n,o,r={};e(467),n=e(469),o=e(471),t.exports=n||{},t.exports.__esModule&&(t.exports=t.exports.default);var a="function"==typeof t.exports?t.exports.options||(t.exports.options={}):t.exports;o&&(a.template=o),a.computed||(a.computed={}),Object.keys(r).forEach(function(t){var i=r[t];a.computed[t]=function(){return i}})},467:function(t,i,e){var n=e(468);"string"==typeof n&&(n=[[t.id,n,""]]);e(22)(n,{});n.locals&&(t.exports=n.locals)},468:function(t,i,e){i=t.exports=e(3)(),i.push([t.id,".container[_v-d0675fc4]{background:#fff}.showtLotate[_v-d0675fc4]{background:#e94c43;padding-bottom:2.5rem;width:96%;margin:.5rem auto 0}.container img[_v-d0675fc4]{display:block}.pr_pra[_v-d0675fc4]{position:relative}.box[_v-d0675fc4]{width:18rem;height:18rem;position:relative;margin:0 auto;position:absolute;top:49%;left:50%;transform:translate(-50%,-50%);-webkit-transform:translate(-50%,-50%);-moz-transform:translate(-50%,-50%);-ms-transform:translate(-50%,-50%);-o-transform:translate(-50%,-50%)}.box .outer[_v-d0675fc4]{width:100%;height:100%;position:absolute;z-index:1;top:0;left:0;transform:rotate(0deg);-webkit-transform:rotate(0deg);-moz-transform:rotate(0deg);-ms-transform:rotate(0deg);-o-transform:rotate(0deg)}.box .outer img[_v-d0675fc4]{width:100%}.box .inner[_v-d0675fc4]{position:relative;width:8rem;height:8rem;left:51%;top:50%;-webkit-transform:translate(-50%,-50%);transform:translate(-50%,-50%);z-index:2;background-image:url(/public/images/rotarydraw/arrow.png);background-size:auto 8rem;background-repeat:no-repeat}.box .inner.start[_v-d0675fc4]:active{-webkit-transform:translate(-50%,-50%) scale(.95);transform:translate(-50%,-50%) scale(.95)}.box .inner.start[_v-d0675fc4]{background-position:0 0}.box .inner.no-start[_v-d0675fc4]{background-position:-5rem 0}.box .inner.completed[_v-d0675fc4]{background-position:-10rem 0}.prize-list[_v-d0675fc4],.winning[_v-d0675fc4]{background:#f5ef77;overflow:hidden;margin:.5rem 1.25rem 0;border-radius:.25rem;padding:.15rem}.prize-list ul[_v-d0675fc4],.winning ul[_v-d0675fc4]{background:#f5ef77;border:1px dashed #e94c43;color:#e94c43;border-radius:.25rem;overflow:hidden;padding:.4rem .4rem 1rem}.prize-list ul li[_v-d0675fc4]{font-size:14px;margin-top:.6rem;text-align:justify}.winning[_v-d0675fc4]{border:1px dashed #e94c43;height:35px}.winning ul[_v-d0675fc4]{border:0;width:10000px;overflow:hidden;padding:0 .4rem}.winning ul li[_v-d0675fc4]{height:32px;line-height:32px;margin-right:10px;font-size:12px;text-overflow:ellipsis;white-space:nowrap;overflow:hidden}.winn-box[_v-d0675fc4]{position:fixed;width:100%;height:100%;top:0;left:0;background:#544d48;text-align:center;z-index:9999;display:none}.winn-box .winn-img[_v-d0675fc4]{position:relative;display:inline-block;width:300px;margin-top:35%}.pro[_v-d0675fc4]{position:relative}.winn-comment[_v-d0675fc4]{position:absolute;top:0;left:0;padding:35px 40px 0;display:inline-block;width:100%}.winn-comment h3[_v-d0675fc4]{color:#fff;font-size:20px;font-weight:700}.winn-comment h3 i[_v-d0675fc4]{color:#edc327}.winn-comment h4[_v-d0675fc4]{color:#fff;font-size:18px;margin-top:8px}.winn-comment .pro a[_v-d0675fc4]{position:absolute;top:118px;left:24px;display:inline-block;width:80%;padding:.7rem 1rem;border-radius:.25rem;background:#f4da2f;color:#9d4f43;box-shadow:0 4px 0 #edc327}.thanks h3[_v-d0675fc4]{text-align:center}.closed[_v-d0675fc4]{position:fixed;top:12%;right:25px;z-index:10000;font-size:18px;display:none}.closed[_v-d0675fc4],.closed span[_v-d0675fc4]{width:25px;height:25px;text-align:center;border-radius:20px}.closed span[_v-d0675fc4]{display:inline-block;background:#dc3a36;color:#fff}.lines[_v-d0675fc4]{position:fixed;top:0;right:37px;width:1px;height:12%;background:#dc3a36;z-index:9999;display:none}.error-tips[_v-d0675fc4]{text-align:center;font-size:1.2rem;padding-top:1rem;display:none}.winning[_v-d0675fc4]{height:2.2rem;overflow:hidden;z-index:100;position:relative;left:0;top:0}.winning ul li[_v-d0675fc4]{line-height:32px;box-sizing:border-box;position:absolute;z-index:0;display:none;top:100%}.winning ul li[_v-d0675fc4]:first-of-type{display:block;top:0}",""])},469:function(t,i,e){(function(t){"use strict";Object.defineProperty(i,"__esModule",{value:!0});var n=e(31),o=e(470);document.querySelector(".header");i.default={vuex:{actions:{updateAppHeader:n.updateAppHeader}},ready:function(){},route:{deactivate:function(t){this.goodsName=this.prizeName,this.getPrizeName=this.gradeName,t.next()},data:function(i){var e=this;this.updateAppHeader({type:1}),this.couts=0,this.$http.post("/WechatBindMobile/show.json").then(function(i){i=i.json(),1==i.status&&(e.loading=!1,1==i.data.isCheckWechat?e.showtLotate=!0:(e.showtLotate=!1,t(".error-tips").show()))}),"rotateOrder"==i.from.name&&this.$nextTick(function(){e.prizeName=e.goodsName,e.gradeName=e.getPrizeName}),this.$http.post("/rotate/index.json").then(function(t){t=t.json(),1==t.status?e.$nextTick(function(){e.rotateList=t.data.data.prize_cfg,e.rotateDes=t.data.data.des_info,e.rotate_id=t.data.data.id,e.nums=t.data.data.num,e.getLogs()}):e.$dispatch("popup",t.msg)})}},data:function(){return{rotate_id:"",prize_id:"",grade:"",rotateList:[],rotateDes:"",winning:[],winningList:[],winnList:"",rotateNum:5,defNum:"",realDeg:0,deg:"",degs:"",nums:"",couts:0,prizeName:"",gradeName:"",goodsName:"",getPrizeName:"",doing:!1,loading:!0,showtLotate:!1,showList:!1}},methods:{toBuy:function(){var t=parseInt(this.rotate_id),i=parseInt(this.prize_id);this.$route.router.go({name:"rotateOrder",params:{id:t,prize:i}})},toStart:function(){var i=this;if(!t(".KinerLotteryBtn").hasClass("doing")){var e={rotate_id:parseInt(this.rotate_id)};this.$http.post("/rotate/rotating.json",e).then(function(t){t=t.json(),1==t.status?i.$nextTick(function(){i.grade=t.data.prize.grade,i.prize_id=t.data.prize.prize_id,i.winning=t.data.prize,i.prizeName=t.data.prize.prize_name,i.gradeName=t.data.prize.grade_name,1==i.grade?i.degs=330:2==i.grade?i.degs=270:3==i.grade?i.degs=210:4==i.grade?i.degs=150:5==i.grade?i.degs=90:i.degs=30,i.couts++,i.KinerLotteryHandler(i.degs,i.nums,i.couts)}):i.$dispatch("popup",t.msg)})}},KinerLotteryHandler:function(i,e,n){var r=i;new o({rotateNum:8,body:"#box",direction:0,disabledHandler:function(t){switch(t){case"noStart":alert("活动尚未开始");break;case"completed":alert("活动已结束")}},clickCallback:function(){t(".KinerLotteryBtn").addClass("doing"),this.goKinerLottery(r)},KinerLotteryHandler:function(){t(".winn-box").show(),t(".closed").show(),t(".lines").show(),t(".KinerLotteryBtn").removeClass("doing")}})},oneTime:function(){this.nums==this.couts?(t(".winn-box").hide(),t(".closed").hide(),t(".lines").hide(),this.$dispatch("popup","今天次数已用完，明天继续吧！")):(t(".winn-box").hide(),t(".closed").hide(),t(".lines").hide())},getLogs:function(){var t=this,i={rotate_id:parseInt(this.rotate_id)};this.$http.post("/rotate/getLog.json",i).then(function(i){i=i.json(),t.$nextTick(function(){t.showList=!0,t.winningList=i,t.getList()})})},getList:function(){var i=this;this.$nextTick(function(){function e(){var i=o;o++,o>=r&&(o=0);var e=100;clearInterval(n);var n=setInterval(function(){e>=1?e-=5:(clearInterval(n),e=0),t("#winning").find("li").eq(i).css({display:"block","z-index":"1",top:e-100+"%"}),t("#winning").find("li").eq(o).css({display:"block","z-index":"2",top:e+"%"})},20)}var n=2500,o=0,r=i.winningList.length;setInterval(e,n)})}}}}).call(i,e(24))},470:function(t,i,e){(function(i){"use strict";!function(t,i,e){function n(t){this.opts=e.extend(!0,{},o,t),this.doing=!1,this.init()}var o={rotateNum:5,body:"",disabledHandler:function(){},clickCallback:function(){},KinerLotteryHandler:function(t){}};n.prototype.setOpts=function(t){this.opts=e.extend(!0,{},o,t),this.init()},n.prototype.init=function(){var t=this;this.defNum=360*this.opts.rotateNum,t.opts.clickCallback.call(t);var i=e(this.opts.body).find(".KinerLotteryContent");i.off("webkitTransitionEnd"),i.on("webkitTransitionEnd",function(){t.doing=!1;var i=e(t.opts.body).attr("data-deg");0==t.opts.direction?(e(t.opts.body).attr("data-deg",360-i),e(t.opts.body).find(".KinerLotteryContent").css({"-webkit-transition":"none",transition:"none","-webkit-transform":"rotate("+i+"deg)",transform:"rotate("+i+"deg)"}),t.opts.KinerLotteryHandler()):(console.log(2),e(t.opts.body).attr("data-deg",i),e(t.opts.body).find(".KinerLotteryContent").css({"-webkit-transition":"none",transition:"none","-webkit-transform":"rotate("+-i+"deg)",transform:"rotate("+-i+"deg)"}),t.opts.KinerLotteryHandler())})},n.prototype.goKinerLottery=function(t){if(!this.doing){var i=t+this.defNum,n=0==this.opts.direction?i:-i;this.doing=!0,e(this.opts.body).find(".KinerLotteryContent").css({"-webkit-transition":"all 5s",transition:"all 5s","-webkit-transform":"rotate("+n+"deg)",transform:"rotate("+n+"deg)"}),e(this.opts.body).attr("data-deg",t)}},t.KinerLottery=n}(window,document,i),t.exports=KinerLottery}).call(i,e(24))},471:function(t,i){t.exports=' <div v-show=loading class=loading _v-d0675fc4=""></div> <div class=container _v-d0675fc4=""> <div v-if=showtLotate class=showtLotate _v-d0675fc4=""> <img src=/public/images/rotarydraw/img_01.jpg alt="" _v-d0675fc4=""> <div class=pr_pra _v-d0675fc4=""> <img src=/public/images/rotarydraw/img_02.jpg alt="" _v-d0675fc4=""> <div id=box class=box _v-d0675fc4=""> <div class="outer KinerLottery KinerLotteryContent" _v-d0675fc4=""> <img src=/public/images/rotarydraw/lanren.png _v-d0675fc4=""> </div> <div class="inner KinerLotteryBtn start" @click=toStart() _v-d0675fc4=""></div> </div> </div> <img src=/public/images/rotarydraw/img_03.jpg alt="" _v-d0675fc4=""> <div class=prize-list _v-d0675fc4=""> <ul _v-d0675fc4=""> <li _v-d0675fc4="">{{{ rotateDes }}}</li> </ul> </div> <img src=/public/images/rotarydraw/img_04.jpg alt="" _v-d0675fc4=""> <div class=winning _v-d0675fc4=""> <ul id=winning v-if=showList _v-d0675fc4=""> <li v-for="item in winningList" _v-d0675fc4="">{{item.nick_name}} 喜中{{item.prize_name}}</li> </ul> </div> <div class=winn-box _v-d0675fc4=""> <div class=winn-img _v-d0675fc4=""> <img src=/public/images/rotarydraw/win_box.jpg _v-d0675fc4=""> <div class=winn-comment v-if="grade != 0 " _v-d0675fc4=""> <div class=pro _v-d0675fc4=""> <h3 _v-d0675fc4="">恭喜您抽中了--&gt;<i _v-d0675fc4="">{{ gradeName }}</i></h3> <h4 _v-d0675fc4="">{{ prizeName }}</h4> <a href=javascript:; @click=toBuy() _v-d0675fc4="">点击领取</a> </div> </div> <div class=winn-comment v-else="" _v-d0675fc4=""> <div class="pro thanks" _v-d0675fc4=""> <h3 v-else="" _v-d0675fc4="">{{winning.prize_name}}</h3> <a href=javascript:; @click=oneTime() _v-d0675fc4="">再抽一次</a> </div> </div> </div> </div> <div class=closed id=closed @click=oneTime() _v-d0675fc4=""><span _v-d0675fc4="">X</span></div> <div class=lines _v-d0675fc4=""></div> </div> <div v-else="" _v-d0675fc4=""><p class=error-tips _v-d0675fc4="">请用微信打开！</p></div> </div> '}});