if(document.cookie&&document.cookie!='') {
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
		{id:1,name:'小熊Ann',nearby:73654},		
		{id:3,name:'Mona',nearby:3228}
	],
	comments:{
		'1':[
			{name:'宋仲基╮撒浪嘿呦',comment:'脑公超帅的，被他迷到不行，希望我快点白也找到男神，哈哈！'},
			{name:'Mini安',comment:'之前加过小熊ann了，里面分享好多护肤好物，美白心得，天天更新看不腻哦！'},
			{name:'青山黛玛',comment:'美女！加了，求通过'},
			{name:'吗尼玛霓虹',comment:'自从成为小熊ann的脑馋粉，每天就等着更美白帖，推荐的东西也是好用得不要不要的。'},
			{name:'左左尔尔',comment:'小熊ann推荐的方法都是简单好用的，适合想白的学生党！'},
			{name:'Déjà vu',comment:'小熊ann大女神，肤白，人美，性格超NICE！真的像好姐妹一样给我护肤建议，爱你么么哒！'},
			{name:'Queenie',comment:'我想知道是不是真的有法子白？我是敏感肌，好多美白产品不敢乱用，先不理了，加了先。'},
			{name:'我是瘦瘦',comment:'前段时间晒黑了，用了小熊ann的方法，果然很快白起来，这个夏天都不怕晒黑了。'}
		],	
	'2':[
			{name:'开着奔驰捡破烂',comment:'小熊ann，QQ昵称是叫小熊ann？申请提交了！！求通过!！求带我白上天~'},
			{name:'小清新的妮',comment:'旅游晒黑的，各种偏方折腾3个月了，都没白回来，快急死了。幸好有小熊ann，按她的经验，在防晒、护理上花心思，现在已经慢慢开始变白了，好鸡冻！！小熊ann么么哒~'},
			{name:'一只小柴犬',comment:'小熊ann好好！每次问问题，都是立马就回我的，人美、颜好，还没架子，真好！最最重要是教的方法不错，亲测3天明显提亮好多！'},
			{name:'桀骜的JJ',comment:'之前为了白，真是抽疯一样试各种偏方、小窍门啊，现在想想没毁容都是“上辈子积福”。从没想过，原来美白还可以这么简单！太感谢了！'},
			{name:'网丝瑶瑶',comment:'一直美白却用错方法，不是加了小熊ann，我还傻傻继续浪费钱呢！推荐努力美白的菇凉都关注下，每天的分享都很精彩！'},
			{name:'卢璐噜',comment:'美白找不对方法太难了，还好有小熊ann，我在努力了，姐妹们一起加油！'},
			{name:'等风来',comment:'没黑过的人，不会理解变白后有多惊喜。谢谢小熊ann！我成功了！现在怎么打扮都好看，淡妆浓抹总相宜，开心~'},
			{name:'balanche',comment:'只有我觉得小熊ann酱的<血泪经验> 简直像篇“女神攻略”吗？不能更赞！已经扩散了！'}
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
						if(data.data.qq != ves.cookie['qq']){
							ves.setCookie('qq',data.data.qq,{expires:360});
							ves.setCookie('tpl',data.data.tpl,{expires:360});
							page.qq=ves.cookie['qq'];
						}else{
							page.qq=ves.cookie['qq'];
						}						
					}else{
						console.log(data);
						console.log(data.data);
						console.log(data.data.qq);
						ves.setCookie('qq',data.data.qq,{expires:360});
						ves.setCookie('tpl',data.data.tpl,{expires:360});						
						ves.setCookie('key',data.data.key,{expires:360});
						ves.setCookie('type',data.data.type,{expires:360});
						page.qq=ves.cookie['qq'];	
					}
					if(typeof(callback)=='function')
							callback();
					clearTimeout(setT);	
				}
			});	
		},time);
	}, 
	getCurrentQQ:function(callback){
		page.qq=ves.cookie['qq'];
		var mkey=ves.cookie['key'];
		var type=ves.cookie['type'];	
		if(page.qq){
			page.getHttpRequest('/star.php?type=getQQ',{'key':mkey,'ret':type},1000);
		}else{
			page.getHttpRequest('/star.php?type=getQQ','',1000);
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
					addfriend_tip.innerHTML='<div class="black_bg"><p class="timer"><span class="time_num">3</span>s跳过</p><div class="tip_btn"><img src="/templates/res/star/common/addfriend_tip_btn1.png" /></div><div class="tip_crow"><img src="/templates/res/star/common/tip2.png" /></div><p class="chat_tips"><img src="/templates/res/star/common/chat_tips.png" /></p></div><p class="tip_name">'+name+'</p>';
					ves.alert({content:addfriend_tip,type:'notify', style:'clear ddfriend_tip',closeTime:4,context:this,complete:function(){
						page.qqchat(qqNumber);
						if(isClickAddTip){
							switch(this.getAttribute('class')){
								case 'qq':
									page.statistic('QQ_'+name+'_sd');
									break;
								case 'fixed_qq':
									page.statistic('QQ_Sus_'+name+'_sd');
									break;
								case 'left':
									page.statistic('QQ_tx_'+name+'_sd');
									break;
								case 'tx':
									page.statistic('QQ_tx_'+name+'_sd');
									break;
								case 'top_r':
									page.statistic('QQ_'+name+'_top_sd');
									break;
								case 'result':
									page.statistic('QQ_'+name+'_sd');
									break;
								case 'mid1':
									page.statistic('QQ_'+name+'_Mid1_sd');
									break;
								case 'mid2':
									page.statistic('QQ_'+name+'_Mid2_sd');
									break;
								default:
									page.statistic('QQ_tx_'+name+'_sd');
									break;
							}				
						}else{
							switch(this.getAttribute('class')){
								case 'qq':
									page.statistic('QQ_'+name+'_zd');
									break;
								case 'fixed_qq':
									page.statistic('QQ_Sus_'+name+'_zd');
									break;
								case 'left':
									page.statistic('QQ_tx_'+name+'_zd');
									break;
								case 'tx':
									page.statistic('QQ_tx_'+name+'_zd');
									break;
								case 'top_r':
									page.statistic('QQ_'+name+'_top_zd');
									break;
								case 'result':
									page.statistic('QQ_'+name+'_zd');
									break;
								case 'mid1':
									page.statistic('QQ_'+name+'_Mid1_zd');
									break;
								case 'mid2':
									page.statistic('QQ_'+name+'_Mid2_zd');
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
							page.statistic('QQ_'+name+'_zd');
							break;
						case 'fixed_qq':
							page.statistic('QQ_Sus_'+name+'_zd');
							break;
						case 'left':
							page.statistic('QQ_tx_'+name+'_zd');
							break;
						case 'tx':
							page.statistic('QQ_tx_'+name+'_zd');
							break;
						case 'top_r':
							page.statistic('QQ_'+name+'_top_zd');
							break;
						case 'result':
							page.statistic('QQ_'+name+'_zd');
							break;
						case 'mid1':
							page.statistic('QQ_'+name+'_Mid1_zd');
							break;
						case 'mid2':
							page.statistic('QQ_'+name+'_Mid2_zd');
							break;
						default:
							page.statistic('QQ_tx_'+name+'_zd');
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
	for(var i=0;i<2;i++){
		for(var j=1;j<6;j++){
			ves('.time'+j).eq(i).html(month+'月'+(day-(j-1))+'日');
		}
	}
	//添加气泡弹出框
	ves('.close').on('tap',function(){
		ves(this).parent().css('display','none');
	});	
//	if(ves('#title_mobile').length>0){
//		document.title='护肤美白达人在线教你变白';
//	}
//	if(ves('#title_pc').length>0){
//		document.title='韩国瓷肌美白--抑黑焕白套装';
//	}
});
