$.extend($.fn.form.methods, {
    getData: function(jq, params){
        var formArray = jq.serializeArray();
        var oRet = {};
        for (var i in formArray) {
            if (typeof(oRet[formArray[i].name]) == 'undefined') {
                if (params) {
                    oRet[formArray[i].name] = (formArray[i].value == "true" || formArray[i].value == "false") ? formArray[i].value == "true" : formArray[i].value;
                }
                else {
                    oRet[formArray[i].name] = formArray[i].value;
                }
            }
            else {
                if (params) {
                    oRet[formArray[i].name] = (formArray[i].value == "true" || formArray[i].value == "false") ? formArray[i].value == "true" : formArray[i].value;
                }
                else {
                    oRet[formArray[i].name] += "," + formArray[i].value;
                }
            }
        }
        return oRet;
    }
});

var shadow = {
    dgShadow: '', dgMask: '', dgContainer: '', dgButton: '', dgForm: '', dgFrame: '', dgFrameBody: '',
    iFrame: '',
	removeButton: function(jq, params){
		shadow.dgButton.remove();
		shadow.dgContainer.css('height','100%');
	},
    options: {width:640, modal:true, data: [],
        queryParams: {},
		hideButtons: false,
        buttons: [{  //快速保存
			id: 'saveBt',
			text: '保存',
			display: 'none',
			iconCls: 'fa fa-check-circle',
			handler : function() {
				if(shadow.dgForm.length > 0) {
					try{
						shadow.dgFrame.onSubmit();
					}catch (ex){}
					
					var action = shadow.dgForm.attr('data-url');
					action = action.replace('?saveBt_true=true','');
					action = action.replace('&saveBt_true=true','');
					
					action += (action.indexOf('?')>=0 ? '&' : '?') + 'saveBt_true=true';
					shadow.dgForm.attr('data-url',action);
					
					result = shadow.dgFrame.easyui.submit(shadow.dgForm);
					//{"info":{"msg":"\u4fdd\u5b58\u6210\u529f","article_id":"25"},"status":1,"url":""}
					if(!result) return false;
					
					if(typeof result.info == 'object'){
						var info = result.info;
						var id_name = info.id_name;
						var id_value = info.id_value;
						
						shadow.dgForm.children('[name="'+info.id_name+'"]').val(id_value);
						
						result.info = info.msg;
					}
					
					parent.easyui.alert(result.info, function(){
						if(result.status){
							//如果提交成功，关闭弹出窗口
							//shadow.destroy();
							try{
								//如果回调函数存在则调用
								shadow.options.handler.call(this, result);
							}catch (e){}
							
							try{
								//执行确认函数
								shadow.dgFrame.onConfirm();
							}catch (e){}
						}
					});
					return false;
				}else {
					try{
						//获取返回数据
						result = shadow.dgFrame.onReturn();
						try{
							shadow.options.handler.call(this, result);
						}catch (e){}
						
						try{
							//执行确认函数
							shadow.dgFrame.onConfirm();
						}catch (e){}
					}catch (e){}
					try{
						//执行确认函数
						shadow.dgFrame.onConfirm();
					}catch (e){}
					//shadow.destroy();
				}
			}
		},{
            text: '确定',
            iconCls: 'fa fa-check',
            handler : function() {
                if(shadow.dgForm.length > 0) {
                    try{
                        shadow.dgFrame.onSubmit();
                    }catch (ex){}
                    result = shadow.dgFrame.easyui.submit(shadow.dgForm);
                    if(!result) return false;
                    parent.easyui.alert(result.info, function(){
                        if(result.status){
                            //如果提交成功，关闭弹出窗口
                            shadow.destroy();
                            try{
                                //如果回调函数存在则调用
                                shadow.options.handler.call(this, result);
                            }catch (e){}
                        }
                    });
                    return false;
                }else {
                    try{
                        //获取返回数据
                        result = shadow.dgFrame.onReturn();
                        try{
                            shadow.options.handler.call(this, result);
                        }catch (e){}
                    }catch (e){}
                    try{
                        //执行确认函数
                        shadow.dgFrame.onConfirm();
                    }catch (e){}
                    shadow.destroy();
                }
            }
        },{
            text: ' 取消 ',
            iconCls: 'fa fa-ban',
            handler: function(){
                shadow.destroy();
            }
        }],
        handler: false
    },
	reload: function(){  //Add By Lemonice
        //重新加载html
        if(!empty(shadow.options.href)) {
            var url = http_build_query(shadow.options.href,shadow.options.queryParams);
			var rand = Math.random() + '_';
			var rand_value = rand.replace('.','');
			url += (url.indexOf('?')>=0 ? '&' : '?') + 'random=' + rand_value;
			
            if(shadow.dgContainer.find(shadow.iFrame).length < 1) {
                shadow.iFrame = $('<iframe/>');
                shadow.iFrame.attr({width:'100%',height:'100%', src: url, frameBorder: 0})
                    .appendTo(shadow.dgContainer);
            }else {
                shadow.iFrame.attr('src', url);
            }

            shadow.iFrame.load(function(){
                shadow.dgFrame = shadow.iFrame.get(0).contentWindow;
                shadow.dgFrameBody = shadow.dgFrame.$(document.body);
                shadow.dgFrame.$(function(){
                    shadow.dgForm = shadow.dgFrame.$('form');
                    if(shadow.dgForm.length > 0) {
                        shadow.dgForm.form('load', shadow.options.data);
                    }
                    if(function_exists(shadow.dgFrame.setData)) {
                        shadow.dgFrame.setData(shadow.options.data);
                    }
                });
            });
        }
    },
    open: function(options) {
        shadow.options = $.extend(shadow.options, options);
        shadow.options.data = empty(shadow.options.data) ? [] : shadow.options.data;
        //创建覆盖层
        if(empty(shadow.dgMask)) {
            if(shadow.options.modal) {
                shadow.dgMask = $('<div/>');
                shadow.dgMask
                    .addClass('shadow-mask')
                    .appendTo($(document.body));
            }
        }

        if(empty(shadow.dgShadow)) {
            shadow.dgShadow = $('<div/>');
            shadow.dgShadow
                .addClass('shadow')
                .appendTo($(document.body));
        }
        shadow.dgShadow
            .animate({width:shadow.options.width},"slow", function(){
                //创建内容框
                if(shadow.dgShadow.find(shadow.dgContainer).length < 1) {
                    shadow.dgContainer = $('<div/>')
                        .addClass('shadow-container').appendTo(shadow.dgShadow);
                }
                shadow.html();

                //创建按钮框
                if(shadow.dgShadow.find(shadow.dgButton).length < 1) {
                    shadow.dgButton = $('<div/>')
                        .addClass('shadow-button').appendTo(shadow.dgShadow);
                }else{
                    shadow.dgButton.empty();
                }

                //创建按钮
				if(shadow.options.hideButtons == false){
					for(var ins in shadow.options.buttons) {
						var button = shadow.options.buttons[ins];
						var btn = $('<a/>').linkbutton({
							text: button.text,
							iconCls: button.iconCls,
							onClick: button.handler
						});
						if(button.display && button.display == 'none'){
							btn.hide();
						}
						if(button.id){
							btn.attr('id',button.id);
						}
						shadow.dgButton.append(btn);
					}
				}else{
					shadow.removeButton();
				}
            });
    },
    html: function(){
        //加载内容
        if(!empty(shadow.options.href)) {
            var url = http_build_query(shadow.options.href,shadow.options.queryParams);
            if(shadow.dgContainer.find(shadow.iFrame).length < 1) {
                shadow.iFrame = $('<iframe/>');
                shadow.iFrame.attr({width:'100%',height:'100%', src: url, frameBorder: 0})
                    .appendTo(shadow.dgContainer);
            }else {
                shadow.iFrame.attr('src', url);
            }

            shadow.iFrame.load(function(){
                shadow.dgFrame = shadow.iFrame.get(0).contentWindow;
                shadow.dgFrameBody = shadow.dgFrame.$(document.body);
                shadow.dgFrame.$(function(){
                    shadow.dgForm = shadow.dgFrame.$('form');
                    if(shadow.dgForm.length > 0) {
                        shadow.dgForm.form('load', shadow.options.data);
                    }
                    if(function_exists(shadow.dgFrame.setData)) {
                        shadow.dgFrame.setData(shadow.options.data);
                    }
                });
            });
        }
    },
    destroy: function() {
        shadow.dgButton.remove();
        shadow.dgShadow.hide('slow', function(){
            try {
                //清除框架
                shadow.dgFrame.document.write('');
                shadow.dgFrame.close();
                shadow.iFrame.remove();
                if(/msie/.test(navigator.userAgent.toLowerCase())){
                    CollectGarbage();
                }
                shadow.dgShadow.remove();
                shadow.dgShadow = '';
                shadow.dgMask.remove();
                shadow.dgMask = '';
            }catch(e) {}
        });
    }
};