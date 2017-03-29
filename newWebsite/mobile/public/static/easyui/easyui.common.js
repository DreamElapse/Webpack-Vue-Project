//给easyui创建扩展属性
var easyui = easyui || {};

/**
 * 系统确认提示框
 * @param message
 * @param callback
 */
easyui.confirm = function(message, callBack){
    try{
        parent.$.messager.confirm('系统提示', message, function(isOk){
            if(!isOk) return false;
            try{
                callBack.call(this, isOk);
            }catch (e){return true;}
        });
    }catch (e){
        if(confirm(message)){
            try{
                callBack.call(this, true);
            }catch (e){return true;}
        }
    }
};

/**
 * 系统提示框
 * @param message
 * @param callback
 * @param icon
 */
easyui.alert = function(message, callBack, icon){
    icon = empty(icon) ? 'info' : icon;
    try{
        parent.$.messager.alert('提示信息', message, icon, function(){
            try{
                callBack.call(this);
            }catch (e){return true;}
        });
    }catch (e){
        alert(message);
        try{
            callBack.call(this);
        }catch (e){return true;}
    }
};

/**
 * 进度条
 * @param type
 */
easyui.progress = function(type){
    type = empty(type) ? 'close' : type;
    try{
        parent.$.messager.progress(type);
    }catch(e){}
};

/**
 * 封装dialog弹出框，默认以iframe方式加载
 * @param options
 * @returns {*|$}
 */
easyui.dialog = function(options){
    var dgWidows = parent.$('<div/>'), formObj = {}, result, frameObj = {}, frameBody, frameName;
    dgWidows.addClass('dl');
    var opts = $.extend({title: '新窗口', width:640, height:480, modal:true, handler:false, maximizable:true,closable:true,
        data: [],
        buttons: [{
            text: '确定',
            iconCls: 'fa fa-check',
            handler : function() {
                //尝试提交表单，如果存在的话
                if(formObj.length > 0){
                    try{
                        frameObj.onSubmit();
                    }catch (ex){}
                    result = frameObj.easyui.submit(formObj);
                    if(!result) return false;
                    parent.easyui.alert(result.info, function(){
                        if(result.status){
                            //如果提交成功，关闭弹出窗口
                            dgWidows.dialog('destroy');
                            try{
                                //如果回调函数存在则调用
                                opts.handler.call(this, result);
                            }catch (e){}
                        }
                    });
                    return false;
                }else{
                    try{
                        //获取返回数据
                        result = frameObj.onReturn();
                        try{
                            opts.handler.call(this, result);
                        }catch (e){}
                    }catch (e){}
                    try{
                        //执行确认函数
                        frameObj.onConfirm();
                    }catch (e){}
                    dgWidows.dialog('destroy');
                }
            }
        },{
            text: ' 取消 ',
            iconCls: 'fa fa-ban',
            handler: function(){
                dgWidows.dialog('destroy');
            }
        }],
        toolbar: false,
        onOpen: function() {
            var iFrame = dgWidows.find('iframe');
            if(iFrame.length > 0) {
                iFrame.load(function(){
                    frameObj = iFrame.get(0).contentWindow;
                    frameBody = frameObj.$(document.body);
                    frameObj.$(function(){
                        formObj = frameObj.$('form');
                        if(formObj.length > 0) {
                            formObj.form('load', opts.data);
                        }
                        if(function_exists(frameObj.setData)) {
                            frameObj.setData(opts.data);
                        }
                    });
                });
            }
        }
    }, options);

    //创建完整的URL
    if(!empty(opts.params) && !empty(opts.href)){
        opts.href = http_build_query(opts.href, opts.params);
        delete opts.params;
    }

    //创建窗口内容
    if(!empty(opts.href)){
        opts.content = '<iframe src="' + opts.href + '" width="100%" height="100%" frameborder="0"></iframe>';
        delete opts.href;
    }

    opts.cache = false; //强制不缓存数据
    if(typeof(options) == 'string' && options == 'destroy'){
        parent.$('.dl').dialog('destroy');
    }else{
        dgWidows.dialog(opts);
        return dgWidows;
    }
    // dgWidows.dialog(opts);
    // return dgWidows;
};

/**
 * 提交表单
 * @param Url
 * @param ItemObj
 * @constructor
 */
easyui.submit = function(formObj, callBack){
    formObj = typeof formObj == 'object' ? formObj : easyui.element(formObj);
    var url = formObj.attr('data-url');
    if(empty(url)) {
        easyui.alert('The form data-url must setting！');
        return false;
    }
    easyui.progress();	//显示进度条
    if (!formObj.form('validate')) {
        easyui.progress('close'); //结束进度条
        return false;
    }

    var result = easyui.post(url, formObj.serialize(), function(){
        easyui.progress('close');
    },'post');
    try{
        callBack.call(this, result);
    }catch(e) {}
    return result;
};

/**
 * 重置表单
 * @param formObject
 */
easyui.reset = function(obj){
    obj = easyui.element(obj);
    obj.form('clear');
    obj.form('reset');
};

/**
 * 创建对象
 * @param element
 * @returns {*|jQuery|HTMLElement}
 */
easyui.element = function(element){
    if(
        typeof(element) == 'string'
        && element.indexOf('#') < 0
        && element.indexOf('.') < 0
    ){
        element = '#' + element;
    }
    return $(element);
};

/**
 * 全选树
 * @param _e
 */
easyui.checkAll = function(element){
    element = easyui.element(element);
    var roots = element.tree('getRoots');
    for(var i = 0; i < roots.length; i++){
        element.tree('check', roots[i].target);
    }
};

/**
 * 反选树
 * @param _e
 */
easyui.unCheck = function(element){
    element = easyui.element(element);
    var roots = element.tree('getRoots');
    for(var i = 0; i < roots.length; i++){
        element.tree('uncheck', roots[i].target);
    }
};

/**
 * 发起ajax请求，并返回数据
 * @param url
 * @param data
 * @param callback
 * @param type
 * @returns {*}
 */
easyui.post = function(url, data, callBack, callType, returnType) {
    callType = empty(callType) ?
        'POST' : callType.toUpperCase();

    returnType = empty(returnType) ?
        'JSON' : returnType.toUpperCase();

    $.ajaxSetup({async : false});
    var result = $.ajax({
        type: callType,
        data: data,
        url: url,
        async: false,
        cache: false
    }).responseText;
    result = returnType == 'JSON' ? $.parseJSON(result) : result;

    try{
        callBack.call(this, result);
    }catch(e){}
    return result;
};

/**
 * 执行AJAX请求
 * @param url
 * @param data
 * @param callback
 */
easyui.doAjax = function(url, data, callBack){
    easyui.confirm('请再次确认是否需要继续进行您的操作!', function(){
        easyui.post(url, data, function(result){
            try{
                if(result.info){
                    easyui.alert(result.info);
                }
                callBack.call(this, result);
            }catch (e){}
        });
    });
};

/**
 * 生成表格
 * @param options
 */
easyui.datagrid = function(options) {
    var config = {
        url: location.href,
        fit:true,
        border:false,
        toolbar: '#toolbar',
        pagination: true,
        fitColumns: true,
        singleSelect:true,
        rownumbers:true,
        selectOnCheck:false,
        checkOnSelect: false
    };
    options = $.extend(config, options);
    options.element.datagrid(options);
};

/**
 * 生成树型表格
 * @param options
 */
easyui.treegrid = function(options) {
    var config = {
        url: location.href,
        fit:true,
        border:false,
        idField:'id',
        treeField:'text',
        toolbar: '#toolbar',
        fitColumns: true,
        rownumbers:true,
        selectOnCheck:false,
        checkOnSelect: false
    };
    options = $.extend(config, options);
    options.element.treegrid(options);
};