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

page={
	qq:'',
	stars:[
		{id:1,name:'小熊Ann',nearby:3654},		
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
			var	name=e.current.name;			    
			var qqNumber=page.qq;	
			var isClickAddTip=false;//是否点击过渡页		
//			console.log(qqNumber);
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
								case 'qq':
									page.statistic('QQ_'+name+'_sd');
									break;
								case 'fixed_qq':
									page.statistic('QQ_Sus_'+name+'_sd');
									break;
								case 'left':
									page.statistic('QQ_tx_'+name+'_sd');
									break;
								case 'mid1':
									page.statistic('QQ_'+name+'_Mid1_sd');
									break;
								case 'mid2':
									page.statistic('QQ_'+name+'_Mid2_sd');
									break;
								case 'img_01':
									page.statistic('QQ_txdb_'+name+'_1_sd');
									break;
								case 'img_02':
									page.statistic('QQ_txdb_'+name+'_2_sd');
									break;
								case 'img_03':
									page.statistic('QQ_txdb_'+name+'_3_sd');
									break;
								case 'img_04':
									page.statistic('QQ_txdb_'+name+'_4_sd');
									break;
								case 'img_05':
									page.statistic('QQ_txdb_'+name+'_5_sd');
									break;
								default:
									page.statistic('QQ_other_'+name+'_sd');
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
								case 'mid1':
									page.statistic('QQ_'+name+'_Mid1_zd');
									break;
								case 'mid2':
									page.statistic('QQ_'+name+'_Mid2_zd');
									break;
								case 'img_01':
									page.statistic('QQ_txdb_'+name+'_1_zd');
									break;
								case 'img_02':
									page.statistic('QQ_txdb_'+name+'_2_zd');
									break;
								case 'img_03':
									page.statistic('QQ_txdb_'+name+'_3_zd');
									break;
								case 'img_04':
									page.statistic('QQ_txdb_'+name+'_4_zd');
									break;
								case 'img_05':
									page.statistic('QQ_txdb_'+name+'_5_zd');
									break;
								default:
									page.statistic('QQ_other_'+name+'_zd');
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
					break;
				}
				default:{
					stars.qqchat(qqNumber);
					switch(this.getAttribute('class')){
							case 'qq':
								page.statistic('QQ_'+name);
								break;
							case 'fixed_qq':
								page.statistic('QQ_Sus_'+name);
								break;
							case 'left':
								page.statistic('QQ_tx_'+name);
								break;
							case 'mid1':
								page.statistic('QQ_'+name+'_Mid1');
								break;
							case 'mid2':
								page.statistic('QQ_'+name+'_Mid2');
								break;
							case 'img_01':
								page.statistic('QQ_txdb_'+name+'_1');
								break;
							case 'img_02':
								page.statistic('QQ_txdb_'+name+'_2');
								break;
							case 'img_03':
								page.statistic('QQ_txdb_'+name+'_3');
								break;
							case 'img_04':
								page.statistic('QQ_txdb_'+name+'_4');
								break;
							case 'img_05':
								page.statistic('QQ_txdb_'+name+'_5');
								break;
							default:
								page.statistic('QQ_other_'+name);
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
	//随机生成8条点赞数
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
	//添加气泡弹出框
	ves('.close').on('tap',function(){
		ves(this).parent().css('display','none');
	});	
	
});
