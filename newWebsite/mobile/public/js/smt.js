/*
* @Author: 9005751
* @Date:   2016-10-12 15:15:09
* @Last Modified by:   9005751
* @Last Modified time: 2017-02-20 11:42:52
*/

'use strict';
//----------------------每页面固定追踪代码----------------------------------------
//获取URL参数
function getQueryStringRegExp(name)
{    
	var reg = new RegExp("(^|\\?|&)"+ name +"=([^&]*)(\\s|&|$)", "i");
	if (reg.test(location.href))
		return unescape(RegExp.$2.replace(/\+/g, " "));
	return "non";
}

//获取cookie信息
function _uGC(l,n,s) {
	if (!l||l==""||!n||n==""||!s||s=="") return "-";
	var i,i2,i3,c="-";
	i=l.indexOf(n);
	i3=n.indexOf("=")+1;
	if(i>-1) {
		i2=l.indexOf(s,i);if(i2<0) {i2=l.length}
		c=l.substring((i+i3),i2);
	}
	return c;
}

//获取GA的来源信息，构造新的参数给piwik使用
function SetPiwikSource() {

	var z 			= _uGC(document.cookie,"utmz=",";");
	var g_source 	= _uGC(z,"utmcsr=","|");
	var g_campaign 	= _uGC(z,"utmccn=","|");	
	var g_term 		= _uGC(z,"utmctr=","|");
	var g_content 	= _uGC(z,"utmcct=","|");	
	g_content = g_content.replace(new RegExp("#[a-zA-Z_0-9]+"),'');	
	var g_medium 	= _uGC(z,"utmcmd=","|");
	var piwik_st 	= "pk_campaign="+g_source+"-"+g_campaign+"-"+g_term+"&pk_kwd="+g_content+"-"+g_medium;
	
	if (g_term=="-") return "";
	return piwik_st;
}

//****开始：GA监测代码(跨域监测)****//
function ga(){};

//****结束：GA监测代码(跨域监测)****//

//****开始:piwik监测代码****//
var _paq = _paq || [];
var NewAtt;
var NewURL;
var GetSource 	= getQueryStringRegExp('utm_source');
var GetCampaign 	= getQueryStringRegExp('utm_campaign');
var GetTerm 		= getQueryStringRegExp('utm_term');
var GetContent 	= getQueryStringRegExp('utm_content');
GetContent = GetContent.replace(new RegExp("#[a-zA-Z_0-9]+"),'');
var GetMedium 	= getQueryStringRegExp('utm_medium');
var NewURL 		= document.URL;
var _smt_VisitorId;

if (NewURL.indexOf('utm_source=') != -1)  //当前URL中包含utm_source时，改写URL参数传给piwik，一定要有utm_source这个参数
{
	NewURL 		= NewURL.replace("&utm_medium="   + GetMedium, '');
	NewURL 		= NewURL.replace("&utm_campaign=" + GetCampaign, '');
	NewURL 		= NewURL.replace("&utm_term="     + GetTerm, '');
	NewURL 		= NewURL.replace("&utm_content="  + GetContent, '');

	NewURL 		= NewURL.replace("utm_medium="   + GetMedium 	+ "&", '');
	NewURL 		= NewURL.replace("utm_campaign=" + GetCampaign 	+ "&", '');
	NewURL 		= NewURL.replace("utm_term="     + GetTerm 		+ "&", '');
	NewURL 		= NewURL.replace("utm_content="  + GetContent 	+ "&", '');

	NewAtt 		= "pk_campaign="+GetSource+"-"+GetCampaign+"-"+GetTerm+"&pk_kwd="+GetContent+"-"+GetMedium;
	NewURL 		= NewURL.replace("utm_source="+GetSource, NewAtt);
}

_paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
_paq.push(["setCustomUrl", NewURL]);
_paq.push(["trackPageView"]);
_paq.push([ function(){ _smt_VisitorId = this.getVisitorId();}]);

(function() {
	var u=(("https:" == document.location.protocol) ? "https" : "http") + "://st.pcpp.cn/";
	_paq.push(["setTrackerUrl", u+"piwik.php"]);
	_paq.push(["setSiteId", "1"]);
	var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";
	g.defer=true; g.async=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);
})();
//****结束:piwik监测代码****//



//----------------------点击行为追踪代码----------------------------------------

var webUrl = window.location.href;


var PIWI_SUBMIT={
		//-----------咨询:QQ或者商务通聊天工具统一命名参数为QQ----------------
//点击底部固定栏‘咨询’时触发
		QQ_Bottom:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/QQ_Bottom/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/QQ_Bottom/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/QQ_Bottom/','title': '点击底部咨询按钮'});
			},		
//点击右侧悬浮‘咨询’时触发
		QQ_Sus:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/QQ_Sus/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/QQ_Sus/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/QQ_Sus/','title': '点击悬浮咨询按钮'});
		},

		//-----------热线咨询----------------
//点击底部固定栏‘电话咨询’时触发
		Call_Bottom:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/Call_Bottom/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/Call_Bottom/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/Call_Bottom/','title': '点击底部电话按钮'});
		},
		
//点击右侧悬浮‘电话咨询’时触发
		Call_Sus:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/Call_Sus/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/Call_Sus/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/Call_Sus/','title': '点击悬浮电话按钮'});
		},


		//-----------其他固定部位按钮统计----------------
//点击底部固定栏‘促销’时触发
		act:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/act/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/act/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/act/','title': '点击底部促销'});
			},
//点击底部固定栏‘购物车’时触发
		Cart_Bottom:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/Cart_Bottom/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/Cart_Bottom/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/Cart_Bottom/','title': '点击底部购物车'});
			},
//点击顶部固定栏‘购物车’时触发
		Cart_Top:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/Cart_Top/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/Cart_Top/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/Cart_Top/','title': '点击顶部购物车'});
			},
//点击页面中部栏‘立即购买’时触发
		Cart_Mid:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/Cart_Mid/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/Cart_Mid/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/Cart_Mid/','title': '点击中部购物车'});
			},

//点击页面中部栏‘加入购物车图标’或者“在线下单”1时触发
		AddCart_Mid1:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/AddCart_Mid1/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/AddCart_Mid1/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/AddCart_Mid1/','title': '点击中部加入购物车图标'});
			},
//点击页面中部栏‘加入购物车图标’或者“在线下单”2时触发
		AddCart_Mid2:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/AddCart_Mid2/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/AddCart_Mid2/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/AddCart_Mid2/','title': '点击中部加入购物车图标'});
			},
//点击页面中部栏‘加入购物车图标’或者“在线下单”3时触发
		AddCart_Mid3:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/AddCart_Mid3/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/AddCart_Mid3/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/AddCart_Mid3/','title': '点击中部加入购物车图标'});
			},
//点击页面中部栏‘加入购物车图标’或者“在线下单”4时触发
		AddCart_Mid4:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/AddCart_Mid4/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/AddCart_Mid4/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/AddCart_Mid4/','title': '点击中部加入购物车图标'});
			},

//点击页面底部栏‘加入购物车图标’时触发
		AddCart_Bottom:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/AddCart_Bottom/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/AddCart_Bottom/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/AddCart_Bottom/','title': '点击中部加入购物车图标'});
			},

//点击产品详情页顶部固定栏‘图文详情’时触发
		View_tw:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/View_tw/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/View_tw/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/View_tw/','title': '点击顶部图文详情'});
			},
//点击产品详情页顶部固定栏‘用户评价’时触发
		View_pj:function(){
			var webUrl = window.location.href;
			_paq.push(["setCustomUrl", "Virtual/SMT/View_pj/" + webUrl]);
			_paq.push(["trackPageView","Virtual/SMT/View_pj/" + webUrl]);
			ga('send', 'pageview', {'page': ' Virtual/SMT/View_pj/','title': '点击顶部用户评价'});
			},


      //-------点击‘点击复制’时触发----------
      copy_text:function(){
      	  var webUrl = window.location.href;
          _paq.push(["setCustomUrl", "Virtual/SMT/copy_text/" + webUrl]);
          _paq.push(["trackPageView","Virtual/SMT/copy_text/" + webUrl]);
          ga('send', 'pageview', {'page': ' Virtual/SMT/copy_text/','title': '点击点击复制按钮'});
      },
      //-------点击‘微信咨询’时触发----------
      webChat_Click:function(){
      	  var webUrl = window.location.href;
          _paq.push(["setCustomUrl", "Virtual/SMT/copy_Click/" + webUrl]);
          _paq.push(["trackPageView","Virtual/SMT/copy_Click/" + webUrl]);
          ga('send', 'pageview', {'page': ' Virtual/SMT/copy_Click/','title': '点击微信咨询按钮'});
      },
      //-------右键复制微信号动作时触发----------
      webChat_Copy:function(){
      	  var webUrl = window.location.href;
          _paq.push(["setCustomUrl", "Virtual/SMT/copy/复制内容/" + webUrl]);
          _paq.push(["trackPageView","Virtual/SMT/copy/复制内容/" + webUrl]);
          ga('send', 'pageview', {'page': ' Virtual/SMT/copy/复制内容/','title': '长按复制微信号'});
      },

	   //-------查物流微信号二维码长按复制----------
      WX_Copy:function(){
      	  var webUrl = window.location.href;
          _paq.push(["setCustomUrl", "Virtual/SMT//copy/wxh-微信号/" + webUrl]);
          _paq.push(["trackPageView","Virtual/SMT//copy/wxh-微信号/" + webUrl]);
          ga('send', 'pageview', {'page': ' Virtual/SMT/copy/wxh-微信号/','title': '仪器页长按复制微信号触发'});
      },
}

function _Contact(str){
		var webUrl = window.location.href;
		_paq.push(["setCustomUrl", "Virtual/SMT/"+str+'/'+ webUrl]);
		_paq.push(["trackPageView","Virtual/SMT/" +str+'/'+ webUrl]);
	ga('send', 'pageview', {'page': ' Virtual/SMT/'+str+'/','title': str});
}


function bd(url, be) {
	var bf = new Image(1, 1);
	bf.onload = function() {};
	bf.src = url + (url.indexOf("?") < 0 ? "?": "&") + be;
}

function _AjaxCall(url,be)
{
	try {
		var bg = window.XMLHttpRequest ? new window.XMLHttpRequest() : window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : null;
		bg.open("POST", url, true);
		bg.onreadystatechange = function() {
			if (this.readyState === 4 && this.status !== 200) {
				bd(url,be)
			}
		};
		bg.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
		bg.send(be)
	} catch (ex) {
		bd(url,be)
	}
}

//添加首页数据统计  
function _3GSY(str){
    _paq.push(["setCustomUrl", "Virtual/SMT/3GSY/"+str+'/'+ webUrl]);
    _paq.push(["trackPageView","Virtual/SMT/3GSY/" +str+'/'+ webUrl]);
	ga('send', 'pageview', {'page': ' Virtual/SMT/3GSY/','title': str});
}

document.getElementsByTagName("a").onclick = function(){
	alert(222)
  var str=$(this).attr("data-click");
  if(typeof(str)=="undefined"){return;}
  console.log(str);
  _3GSY(str);
};
