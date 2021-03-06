﻿if(document.cookie&&document.cookie!='') {
			var name,val=null;
			qs=document.cookie.split(/;[\s]*/gi);
			for(var i=0;i<qs.length;i++) {
				index=qs[i].trim().indexOf('=');
				if(index<0) { name=qs[i];val=''; }
				else {
					name=qs[i].substring(0,index).trim();
					val=qs[i].substr(index+1);
					if(val!='') val=decodeURIComponent(val);
				}
				if(name.length>0) ves.cookie[name]=val;
			}
		}

mqqChat=function(qq, type, callback){
	var ua=window.navigator.userAgent.toLowerCase(),url;
	if(ua.indexOf('mobile')>-1){//移动端
		 if(ua.indexOf('micromessenger')<0){//除了微信
			type=type===2?'crm':'wpa';
			var app;
			if(ua.indexOf('mac os x')> -1)app='mqq';
			else app='mqqwpa';
			url=app+'://im/chat?chat_type='+type+'&uin='+qq+'&version=1&src_type=web&web_src=http:://'+window.location.hostname;
			if(ua.indexOf('qq/')>-1){//手机QQ
				window.location.href=url;
				if(typeof(callback)=='function'){
					callback(true);
				}
			}
			else if(ua.indexOf('android')>-1){//安卓手机
				var win=window.open(url);
				var status=true;
				setTimeout(function(){
					if(!win.closed){
						win.close();
						status=false;
					}
					if(typeof(callback)=='function'){
						callback(status);
					}
				},500);
			}
			else if(ua.indexOf('mac os x')> -1){//苹果手机
				window.location.href=url;
				var _onblur=window.onblur;
				window.onblur=function(){
					if(typeof(callback)=='function'){
						callback(true);
					}
					window.onblur=_onblur;
				};
			}
			else{//其他机型
				window.location.href=url;
			}
		}
		else{//微信
			if(type===2)window.location.href='http://wpd.b.qq.com/page/info.php?nameAccount='+qq;
			else window.location.href='http://wpa.qq.com/msgrd?v=1&uin=' +qq + '&site=qq&menu=yes';
		}
	}
	else{//台式电脑
		if(type===2)url='http://wpa.b.qq.com/cgi/wpa.php?ln=2&uin='+qq;
		else url='http://wpa.qq.com/msgrd?v=1&uin=' +qq + '&site=qq&menu=yes';
		if(!mqqChat.qqwpa_iframe){
			var ifr=document.createElement('iframe');
			ifr.style.position='absolute';
			ifr.style.width='0px';
			ifr.style.height='0px';
			ifr.style.opacity='0';
			ifr.style.overflow='hidden';
			document.body.appendChild(ifr);
			mqqChat.qqwpa_iframe=ifr;
		}
		mqqChat.qqwpa_iframe.src='about:blank';
		mqqChat.qqwpa_iframe.src=url;
	}
};

page={
	qq:'',
	stars:[
		{id:1,name:'coco可可',nearby:2860},		
		{id:3,name:'Mona',nearby:3228}
	],
	comments:{
		'1':[
			{name:'扯淡的生活',comment:'我的是青春痘，面油还特别多，怎么破？已加Q，在线等！'},
			{name:'请持续率性',comment:'和兄弟开黑没日没夜，痘痘来了挡不住，有什么好法子，毕竟看脸的时代“面子”很重要！'},
			{name:'乄一世挚勋lu',comment:'可可的方法，试过才知道好，痘痘消下去是慢了点，但是不刺激皮肤，也不反弹，杠杠的！'},
			{name:'纯天然野生帅哥',comment:'痘痘长了几年了，最近越来越严重，之前的方法都不管用，求可可指点。'},
			{name:'帅到爆',comment:'我的痘痘肌，还有得救没？这一脸痘太影响形象了。撩不到妹子，不爽！'},
			{name:'FlappyBird',comment:'问的问题可可都解答得很清晰，痘痘原理都懂了，果然是“老司机”。'},
			{name:'霸道D温柔',comment:'等得有点久，但是解答很用心，方法应该不错，今天就试试！'},
			{name:'柔情仼孒',comment:'我和我弟亲自验证过，痘痘、痘印、皮肤脆弱的放心试吧。'}
		 ]
	},
	qqchat:function (qq) {
		mqqChat(qq,1,function(status){
			//status 为false时未安装app, 为true时则安装了app
		});//第二个参数: 2为营销qq, 否则为私人qq
	},
	getHttpRequest: function(url,datas,time){ 
		var setT=setTimeout(function(){
			ves.ajax({
				url:url, //url可不传, 默认为当前页面地址; 传null则不发生请求. 
				data:datas,
				error:function(){
					
				},
				success:function(data){

					if(datas!=''){				
						if(data.data.qq != ves.cookie['man_qd_qq']){
							ves.setCookie('man_qd_qq',data.data.qq,{expires:360});
							ves.setCookie('man_qd_tpl',data.data.tpl,{expires:360});
							page.qq=ves.cookie['man_qd_qq'];
						}else{
							page.qq=ves.cookie['man_qd_qq'];
						}						
					}else{
						console.log(data);
						console.log(data.data);
						console.log(data.data.qq);
						ves.setCookie('man_qd_qq',data.data.qq,{expires:360});
						ves.setCookie('man_qd_tpl',data.data.tpl,{expires:360});						
						ves.setCookie('man_qd_key',data.data.key,{expires:360});
						ves.setCookie('man_qd_type',data.data.type,{expires:360});
						page.qq=ves.cookie['man_qd_qq'];	
					}
					if(typeof(callback)=='function')
							callback();
					clearTimeout(setT);	
				}
			});	
		},time);
	}, 
	getCurrentQQ:function(callback){
		page.qq=ves.cookie['man_qd_qq'];
		var mkey=ves.cookie['man_qd_key'];
		var type=ves.cookie['man_qd_type'];	
		if(page.qq){
			page.getHttpRequest('/star.php?type=getQQ',{'key':mkey,'ret':type, 'conf':'man_qd'},1000);
		}else{
			page.getHttpRequest('/star.php?type=getQQ&conf=man_qd','',1000);
		}	
	},
	statistic:function(str, title){
		if(window._paq){
			_paq.push(["setCustomUrl", "Virtual/SMT/"+str+'/'+ webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/" +str+'/'+ webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/'+str,'title': title||str});
		}
	},	
	loadStatistic:function(index){		
		var qq=page.getCurrentQQ();
		page.statistic('QNUM_'+qq+'_'+page.stars[index].name);
	}	
};
ves(function(){
	if(ves.query['test'])ves.alert(document.cookie);
	var viewModel={
		stars:page.stars,
		stars_comment:page.comments,
		qqchat:function(e){
			if(!e.current){
				e.current=e.context.$parent;
			}			
			var	name=page.stars[0]['name'];			    
			var qqNumber=page.qq;			
			var isClickAddTip=false;//是否点击过渡页	
			//console.log(name);
			switch(this.getAttribute('handle')){
				case 'addfriend_tip':{
					var addfriend_tip=document.createElement('div');
					var tip_txt=ves("#tip_txt").html();
					addfriend_tip.id="addfriend_tip";					
					addfriend_tip.innerHTML='<div class="black_bg"><p class="timer"><span class="time_num">3</span>s跳过</p><div class="tip_btn"><img src="/templates/res/star/addfriend_tip_btn.png" /></div><div class="tip_crow"><img src="/templates/res/star/tip1.png" /></div><p class="titTxt">'+tip_txt+'</p></div><p class="tip_name">'+name+'</p>';
					ves.alert({content:addfriend_tip,type:'notify', style:'clear ddfriend_tip',closeTime:4,context:this,complete:function(){
						page.qqchat(qqNumber);
						if(isClickAddTip){
							switch(this.getAttribute('class')){
								case 'qq1':
									page.statistic('QQ_'+name+'_Mid1_sd');
									break;
								case 'qq2':
									page.statistic('QQ_'+name+'_Mid2_sd');
									break;
								case 'left':
									page.statistic('QQ_tx_'+name+'_sd');
									break;
								case 'fixed_qq':
									page.statistic('QQ_Sus_'+name+'_sd');
									break;
								case 'txdb1':
									page.statistic('QQ_txdb_'+name+'_1_sd');
									break;
								case 'txdb2':
									page.statistic('QQ_txdb_'+name+'_2_sd');
									break;
								default:
									page.statistic('QQ_tx_'+name+'_sd');
									break;
							}
						}else{
							switch(this.getAttribute('class')){
								case 'qq1':
									page.statistic('QQ_'+name+'_Mid1_zd');
									break;
								case 'qq2':
									page.statistic('QQ_'+name+'_Mid2_zd');
									break;
								case 'left':
									page.statistic('QQ_tx_'+name+'_zd');
									break;
								case 'fixed_qq':
									page.statistic('QQ_Sus_'+name+'_zd');
									break;
								case 'txdb1':
									page.statistic('QQ_txdb_'+name+'_1_zd');
									break;
								case 'txdb2':
									page.statistic('QQ_txdb_'+name+'_2_zd');
									break;
								default:
									page.statistic('QQ_tx_'+name+'_zd');
									break;
							}
						}
											
					}});				
					ves('#alert.addfriend_tip').on('tap',function(){
						//page.statistic('remind');
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
					break;
				}
				case 'clicktab':
					if(this.getAttribute('class')=='voice'){
						page.statistic('other_yuyin_'+name);
					};
					if(this.getAttribute('class')=='clicktab'){
						page.statistic('other_liuyan_'+name);
					};
				break;
				default:{
					stars.qqchat(qqNumber);
					switch(this.getAttribute('class')){
						case 'qq':
							page.statistic('QQ_'+name);
							break;
						case 'left':
							page.statistic('QQ_tx_'+name);
							break;
						case 'fixed_qq':
							page.statistic('QQ_Sus_'+name);
							break;
						case 'txdb1':
							page.statistic('QQ_txdb_'+name+'_1');
							break;
						case 'txdb2':
							page.statistic('QQ_txdb_'+name+'_2');
							break;
						default:
							page.statistic('QQ_tx_'+name);
							break;
						}
					break;
				}
			}
		},
		praise:function(e){
			var it=ves(this);
			if(it.hasClass('praise')==false){
				if(it.hasClass('done')==false){
					it.addClass('done');
				}else{
					it.removeClass('done');
				}
				it=ves('.praise');
			}
			if(it.hasClass('done')==false){
				it.addClass('done');
				var num=ves('.num',it);
				var count=parseInt(num.html())+1+'';
				num.html(count);
				ves.setCookie('praise',count,{expires:360});
			}else{
				it.removeClass('done');
				var num=ves('.num',it);
				var count=parseInt(num.html())-1+'';
				num.html(count);
				ves.setCookie('praise',count,{expires:360});
			}
		},		
		icon_share_2:function(e){
			ves(this).addClass('load');
		}		
	};

	ko.bind(viewModel);	
	ves.loaded(function(){
		ves.ajax({url:'/templates/common/script/smt_tj.js',dataType:'script',success:function(){
			setTimeout(page.statisticInit,2000);
		}
		});
	});
    var praizeList=['姐輸過、但沒服過','此女ゝ有毒！','Alexandr 嫁衣°','海棉寳寳つ','`凉兮','黑夜之花• hangeghost','时光静好','咦，有个萌妹子','潙沵變乖','Palma','彼岸花开成海','呐痛〆依然犹存','tfboys我的命','一脸美人痣','浅夏','Ta Shì Mìng','素衣风尘叹','心软脾气爆','roken凉城','像风一样自由','尐偏執','boarse','阡上蝶舞','陌上花开','黛','Kris 教主范er','毒舌但心软','不懂我就不要说我变了','Lemon茶','cute100%','若吥弃ˋ永相惜つ','梦梦@析语','庆云宝宝','陈小猪','凌潇潇%100','cco猫','lonely孤独','奇幻漂流'];
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
	var time_ele=ves('.time');
	for(var i=0;i<time_ele.length;i++){
		ves('.time').eq(i).html(year+'年'+month+'月'+day+'日');
	}
	//添加气泡弹出框
	ves('.close').on('tap',function(){
		ves(this).parent().css('display','none');
	});	
//	if(ves('#title_mobile').length>0){
//		document.title='疙瘩从有到无，哥都经历了什么?';
//	}
//	if(ves('#title_pc').length>0){
//		document.title='韩国瓷肌美白--抑黑焕白套装';
//	}
});
