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
//	qqNumStaticT:-1,
    qq:'',
	stars:[
		{id:1,name:'小熊Ann',nearby:3654},		
		{id:3,name:'Mona',nearby:3228}
	],
	comments:{
		'1':[
			{name:'文绝天',comment:'我天生就是黑皮肤，长这么大试过无数偏方还是没法白，就想知道究竟是什么原因。'},
			{name:'卖姑娘的敏杰_',comment:'天呐，难怪皮肤越来越差了，最近还长斑了，擦~原来是晚上的护肤方法不对啊！看来要好好努力了！'},
			{name:'修复器',comment:'皮肤黑看起来很土，如果有人可以教我变白的法子就好了。'},
			{name:'朱颜纪',comment:'测了竟然有7分！感觉震精了。感觉自己皮肤其实还是不太好的啊~~顾问说我是敏感肌，平时不适合去角质太频繁，应该要选温和一点的产品。'},
			{name:'小雀巢',comment:'我只是肤色不均匀，想要明星那种水光肌，又嫩又白，先咨询一下怎么改善！'},
			{name:'涟漪精灵',comment:'年纪上来了，柴米油盐弄得人都憔悴，脸色发黄，倒是很期待自己能变白，像年轻18岁那样最好了。'},
			{name:'Aarongrong',comment:'测试挺准，看来要用美女的方法试一下护肤了。'},
			{name:'若相思R',comment:'测试结果还不算太差，可以接受，也有很大上升空间，还是听美女的建议，按照她的方法做做看吧。'}
		]
	},
	statistic:function(str, title){
		if(window._paq){
			_paq.push(["setCustomUrl", "Virtual/SMT/"+str+'/'+ webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/" +str+'/'+ webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/'+str,'title': title||str});
		}
	}	
};
ves(function(){
	if(ves.query['test'])ves.alert(document.cookie);
	var viewModel={
		stars:page.stars,
		stars_comment:page.comments,
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
		//根据pc和mobile端显示不同的标题
//	if(!ves('html').hasClass('mobile')){
//		var len=ves('.ask_title span').length;
//		for(var i=0;i<len;i++){
//			ves('.ask_title span').eq(i).html(i+2);
//		}
//		var l=ves('.li_top i').length;
//		console.log()
//	}else{
//		
//	}
});
