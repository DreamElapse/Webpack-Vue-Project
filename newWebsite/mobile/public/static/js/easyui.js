var bootPATH = js_path("easyui.js");
var rootPATH = bootPATH.replace(/static(.*)?/g, 'static/');

//加载JS文件
document.write('<script src="' + rootPATH + 'easyui/jquery.easyui.min.js" type="text/javascript" ></sc' + 'ript>');
document.write('<script src="' + rootPATH + 'easyui/locale/easyui-lang-zh_CN.js" type="text/javascript" ></sc' + 'ript>');
document.write('<script src="' + rootPATH + 'easyui/easyui.common.js" type="text/javascript" ></sc' + 'ript>');
document.write('<script src="' + rootPATH + 'easyui/easyui.plugins.js" type="text/javascript" ></sc' + 'ript>');
//加载CSS样式
document.write('<link href="' + rootPATH + 'easyui/themes/icon.css" rel="stylesheet" type="text/css" />');
//加载主题
document.write('<link href="' + rootPATH + 'easyui/themes/gray/easyui.css" rel="stylesheet" type="text/css" />');
document.write('<link href="' + rootPATH + 'style/pc.css" rel="stylesheet" type="text/css" />');

//禁止右键
/*window.document.oncontextmenu = function(event){
    event = event || window.event;
    if(!event.ctrlKey){
        return false;
    }
}*/

//屏蔽F5刷新
/*
window.document.onkeydown = function(e){
    var ev = window.event|| e;
    if(ev.keyCode == 116){
        ev.keyCode = 0;
        ev.cancelBubble = true;
        return false;
    }
};*/
