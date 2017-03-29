page={
	comments:[	
			{name:'kobe飞侠',comment:'不解释了，兄弟们有痘的，快加！'},
			{name:'1K赛艇',comment:'我之前痘满多的，加了COCO,好很多！'},
			{name:'段子手黎达',comment:'有没有人试过？'},
			{name:'Simon李超',comment:'推荐！祛痘效果好！'},
			{name:'白猫社',comment:'准备叫男友试下~'},
			{name:'贝贝贝贝贝勒爷',comment:'上次教的祛痘小妙招炒鸡好用！！'},
			{name:'毁灭大宇宙',comment:'我先加，看看情况啊！'},
			{name:'陈家二公子 ',comment:'什么方法啊？'}
		],
	statistic:function(str, title){
		if(window._paq){
			_paq.push(["setCustomUrl", "Virtual/SMT/"+str+'/'+ webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/" +str+'/'+ webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/'+str,'title': title||str});
		}
	},		
	getCurrentQQ:function(qqArr){
		var count=qqArr.length;
		var index=ves.cookie['qq_qd'];	
		if(index && index<count){
			index=parseInt(index);
		}else{
			index=parseInt(Math.random()*count);
		}
		ves.setCookie('qq_qd',index,{expires:360});		
		return qqArr[index];
	},
	qqchat:function (qq) {
		if(ves.browser.app.qq||ves.browser.app.qqb){
			ves.alert({content:'<span class="ic-load"></span>',type:'wait',style:'clear'});
			var _ifr=document.createElement('iframe');
			_ifr.style.display='none';
			var it=this;
			_ifr.onload=function(){
				setTimeout(function(){
					if(it.iframe)ves.body[0].removeChild(it.iframe);
					it.iframe=_ifr;
					ves.alert.close();
				},1500);
			};
			_ifr.src='http://wpa.qq.com/msgrd?v=1&uin=' +qq + '&site=qq&menu=yes';
			ves.body.append(_ifr);
		}
		else{
			window.location.href='http://wpa.qq.com/msgrd?v=1&uin=' +qq + '&site=qq&menu=yes';
		}
	}	
};
ves(function(){
	var viewModel={
		qqNumber:'',
		name:"coco可可",
		nearby:4036,
		qqList:[2916596136,2198297155,2743926378],
		comments:page.comments,
		init:function(){
			this.qqNumber=page.getCurrentQQ(this.qqList);
		},
		qqchat:function(e){	
			var name=e.current.name;				    
			var qqNumber=viewModel.qqNumber;			
			var tipTxt=e.current.tipTxt;
			var isClickAddTip=false;
			var addfriend_tip=document.createElement('div');
			var tip_txt=ves("#tip_txt").html();
			console.log(name+"||"+qqNumber)	;				
			addfriend_tip.id="addfriend_tip";					
			addfriend_tip.innerHTML='<div class="black_bg"><p class="timer"><span class="time_num">3</span>s跳过</p><div class="tip_btn"><img src="templates/res/star/addfriend_tip_btn.png" /></div><div class="tip_crow"><img src="templates/res/star/tip1.png" /></div><p class="titTxt">'+tip_txt+'</p></div><p class="tip_name">'+name+'</p>';
			ves.alert({content:addfriend_tip,type:'notify', style:'clear ddfriend_tip',closeTime:4,context:this,complete:function(){
				page.qqchat(qqNumber);
				if(isClickAddTip){//手动
					switch(this.getAttribute('class')){
						case 'qq':
							page.statistic('QQ_sd');
							break;							
						case 'fixed_qq':
							page.statistic('QQ_Sus_sd');
							break;
						case 'left':
							page.statistic('QQ_tx_sd');
							break;
						case 'mid1':
							page.statistic('QQ_Mid1_sd');
							break;
						case 'mid2':
							page.statistic('QQ_Mid2_sd');
							break;
						case 'img_01':
							page.statistic('QQ_txdb_1_sd');
							break;
						case 'img_02':
							page.statistic('QQ_txdb_2_sd');
							break;
						case 'img_03':
							page.statistic('QQ_txdb_3_sd');
							break;
						case 'img_04':
							page.statistic('QQ_txdb_4_sd');
							break;
						case 'img_05':
							page.statistic('QQ_txdb_5_sd');
							break;
						case 'img_06':
							page.statistic('QQ_txdb_6_sd');
							break;
						case 'img_07':
							page.statistic('QQ_txdb_7_sd');
							break;
						case 'img_08':
							page.statistic('QQ_txdb_8_sd');
							break;
						case 'img_09':
							page.statistic('QQ_txdb_9_sd');
							break;
						default:
							page.statistic('QQ_txdb_sd');
							break;
					}
				}else{//自动
					switch(this.getAttribute('class')){
						case 'qq':
							page.statistic('QQ_zd');
							break;							
						case 'fixed_qq':
							page.statistic('QQ_Sus_zd');
							break;
						case 'left':
							page.statistic('QQ_tx_zd');
							break;
						case 'mid1':
							page.statistic('QQ_Mid1_zd');
							break;
						case 'mid2':
							page.statistic('QQ_Mid2_zd');
							break;
						case 'img_01':
							page.statistic('QQ_txdb_1_zd');
							break;
						case 'img_02':
							page.statistic('QQ_txdb_2_zd');
							break;
						case 'img_03':
							page.statistic('QQ_txdb_3_zd');
							break;
						case 'img_04':
							page.statistic('QQ_txdb_4_zd');
							break;
						case 'img_05':
							page.statistic('QQ_txdb_5_zd');
							break;
						case 'img_06':
							page.statistic('QQ_txdb_6_zd');
							break;
						case 'img_07':
							page.statistic('QQ_txdb_7_zd');
							break;
						case 'img_08':
							page.statistic('QQ_txdb_8_zd');
							break;
						case 'img_09':
							page.statistic('QQ_txdb_9_zd');
							break;
						default:
							page.statistic('QQ_txdb_zd');
							break;
					}
				}												
			}});					
			ves('#alert.addfriend_tip').on('tap',function(){
				isClickAddTip=true;
				ves.alert.close();
			});
			var timer_holder=addfriend_tip.firstChild.firstChild.firstChild;
			var timer_number=parseInt(timer_holder.innerHTML);
			var timer=setInterval(function(){
				timer_number-=1;
				if(timer_number<0){
					clearInterval(timer);
					timer=null;
				}
				timer_holder.innerHTML=timer_number;
			},1000);			
		},		
		praise:function(e){
			var it=ves(this);
			if(it.hasClass('done')==false){
				it.addClass('done');
				var num=ves('.num');
				var count=parseInt(num.html())+1;
				num.html(count);
				ves.setCookie('qpraise',count,{expires:360});
			}else{
				it.removeClass('done');
				var num=ves('.num');
				var count=parseInt(num.html())-1;
				num.html(count);
				ves.setCookie('qpraise',count,{expires:360});
			}
		}			
	};
	viewModel.init();
	ko.bind(viewModel);	
	ves.loaded(function(){
		ves.ajax({url:'/templates/common/script/smt_tj.js',dataType:'script',success:page.statisticInit});
	});
	var praizeList=['姐輸過、但沒服過','此女ゝ有毒！','Alexandr 嫁衣°','海棉寳寳つ','`凉兮','黑夜之花• hangeghost','时光静好','咦，有个萌妹子','潙沵變乖','Palma','彼岸花开成海','呐痛〆依然犹存','tfboys我的命','一脸美人痣','浅夏','Ta Shì Mìng','素衣风尘叹','心软脾气爆','roken凉城','像风一样自由','尐偏執','boarse','阡上蝶舞','陌上花开','黛','Kris 教主范er','毒舌但心软','不懂我就不要说我变了','Lemon茶','cute100%','若吥弃ˋ永相惜つ'];
	function makeRandomArr(arrList,num){
	    if(num>arrList.length){
	       return;
	    }
	    var tempArr=arrList.concat();    
	    var newArrList=[];    
	    for(var i=0;i<num;i++){
	        var random=Math.floor(Math.random()*(tempArr.length-1));
	        var arr=tempArr[random];
	        tempArr.splice(random, 1);
	        newArrList.push(arr);    
	    }
	    return newArrList;
	}
    function removeLastOne(str){
        return str.substring(0,str.length - 1);
    }
    var new_arr=makeRandomArr(praizeList,7);
    var str='';
    for(var i=0;i<new_arr.length;i++){
        str+=new_arr[i]+'、';
    }
    str=removeLastOne(str); 
    ves('#praize_p').html(str);
     //根据系统时间设置发布与评论时间
    var oDate = new Date();
	var year=oDate.getFullYear(),
		month=format(oDate.getMonth()+1),
		day=format(oDate.getDate());
	function format(str){
		return str.toString().replace(/^(\d)$/,"0$1")
	}
	var time_ele=ves('.time');
	for(var i=0;i<time_ele.length;i++){
		ves('.time').eq(i).html(year+'年'+month+'月'+day+'日');
	}
	//qzone_527添加气泡弹出框
	ves('.close').on('tap',function(){
		ves(this).parent().css('display','none');
	});	
	//根据pc和mobile端显示不同的标题
	if(!ves('html').hasClass('mobile')){
		var title=ves('#title_pc').html();
		document.title=title;
	}else{
		var title=ves('#title_mobile').html();
		document.title=title;
	}
});
