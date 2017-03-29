var rightTabs,commonLayout,westLayout;
$(document.body).ready(function(){
    rightTabs = $('#right-tabs');
    initMenu();
    commonLayout = $('#common-layout');
    westLayout = $('#commom-west');
    $('#resize-bars').click(function(){
        if(westLayout.css('display') == 'none') {
            commonLayout.layout('expand','west');
        }else {
            commonLayout.layout('collapse','west');
        }
    });
});

function initMenu() {
    var url = '/index.php?m=cpanel&c=index&a=menu';
    $.get(url, function(data) {
        if(data.length > 0) {
            for(var i in data) {
                var menu = data[i];
                var li = $('<li></li>');
                var a = $('<a></a>');
                a.attr('item', i);
                a.html('<i class="fa '+menu.icon+'"></i>' + ' ' + menu.text);
                a.click(function(){
                    if(westLayout.css('display') == 'none') {
                        commonLayout.layout('expand','west');
                    }
                    $('#nav > li > a').removeClass('active');
                    $(this).addClass('active');
                    var item = $(this).attr('item');
                    $('#nav-title').html($(this).text());
                    $('#menu').empty();
                    var menus = data[item].children;
                    if(menus.length > 0) {
                        for(var i in menus) {
                            var menu = menus[i];
                            var li = $('<li></li>');
                            var a = $('<a></a>');
                            a.attr('url', menu.href);
                            a.html('<i class="fa '+menu.icon+'"></i>' + ' ' + menu.text);
                            a.appendTo(li);
                            $('#menu').append(li);
                        }
                        bindleftclick();
                    }
                }).appendTo(li);
                $('#nav').append(li);
            }
            $('#nav > li > a').eq(1).click();
        }
    });
}

function bindleftclick() {
    var leftMenu = $('#menu > li > a');
    leftMenu.each(function(){
        $(this).bind('click', function(){
            leftMenu.removeClass('active');
            $(this).addClass('active');
            var tabName = $.trim($(this).text());
            var tabHref = $(this).attr('url');
            openTab(rightTabs, tabName, tabHref, true);
        })
    });
}

//首页标签右键菜单
function onRightTabsContextMenu(e, title,index){
    e.preventDefault();
    $('#rightTabsMenu').menu('show', {
        left:e.pageX,
        top:e.pageY
    });
}

//首页标签右键菜单点击事件
function onRightTabsHandler(item){
    switch (item.name)
    {
        case 'reload':
            reflashTab();
            break;
        case 'close':
        case 'closeAll':
            closeTab(null, item.name);
            break;
    }

}

/**
 * 刷新标签页
 * @param _e
 */
function reflashTab(tabElement)
{
    tabElement = tabElement || rightTabs;
    var tab = tabElement.tabs('getSelected');
    if(tab.find('iframe').length > 0){
        tab.find('iframe').attr('src', tab.find('iframe').attr('src'));
    }else{
        tab.panel('refresh');
    }
}

function closeTab(tabElement, closeType)
{
    tabElement = tabElement || rightTabs;
    switch (closeType)
    {
        case 'closeAll':
            var tabs = tabElement.tabs("tabs");
            var length = tabs.length;
            for(var i = 0; i < length; i++) {
                var oneTab = tabs[1];
                if(empty(oneTab)) break;
                removeTabFrame(oneTab);
                var title = oneTab.panel('options').tab.text();
                tabElement.tabs("close", title);
            }
            break;
        default :
            var oneTab = tabElement.tabs('getSelected');
            var index = tabElement.tabs('getTabIndex', oneTab);
            if(index == 0) break;

            removeTabFrame(oneTab);
            var title = oneTab.panel('options').tab.text();
            tabElement.tabs("close", title);
            break;
    }
}

function removeTabFrame(tab)
{
    var frame=$('iframe', tab);
    if(frame.length > 0) {
        frame[0].contentWindow.document.write('');
        frame[0].contentWindow.close();
        frame.remove();
        if(/msie/.test(navigator.userAgent.toLowerCase())){
            CollectGarbage();
        }
    }
}

/**
 * 创建新标签页
 * @param _e
 * @param tabName
 * @param tabHref
 * @param iframe
 */
function openTab(tabElement, tabName, tabHref, iframe)
{
    tabElement = tabElement || rightTabs;
    if(tabElement.tabs('exists', tabName))
    {
        tabElement.tabs('select', tabName);
        return;
    }

    var options = {
        title: tabName,
        closable:true,
        fit: true,
        tools:[{
            iconCls:'icon-mini-refresh',
            handler:function(){
                reflashTab(tabElement);
            }
        }]
    };
    if(iframe){
        options.content = '<iframe src="' + tabHref + '" width="100%" height="100%" frameborder="0"></iframe>';
    }else{
        options.href = tabHref;
    }
    tabElement.tabs('add',options);
}