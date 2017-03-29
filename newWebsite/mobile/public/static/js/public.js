function js_path(js) {
    var scripts = document.getElementsByTagName("script");
    var path = "";
    for (var i = 0, l = scripts.length; i < l; i++) {
        var src = scripts[i].src;
        if (src.indexOf(js) != -1) {
            var ss = src.split(js);
            path = ss[0];
            break;
        }
    }
    var href = location.href;
    href = href.split("#")[0];
    href = href.split("?")[0];
    var ss = href.split("/");
    ss.length = ss.length - 1;
    href = ss.join("/");
    if (path.indexOf("https:") == -1 && path.indexOf("http:") == -1 && path.indexOf("file:") == -1 && path.indexOf("\/") != 0) {
        path = href + "/" + path;
    }
    return path;
}

/**
 * 设置对象不可用
 * @param element
 */
function disable(element) {
    $(element).linkbutton('disable');
}

/**
 * 设置对象可用
 * @param element
 */
function enable(element) {
    $(element).linkbutton('enable');
}

/**
 * 创建随机密钥
 * @param prefix
 * @returns {string}
 */
function secret_key(prefix, n) {
    var chars = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    var res = "";
    for(var i = 0; i < n ; i ++) {
        var id = Math.ceil(Math.random()*35);
        res += chars[id];
    }
    if(typeof prefix == 'string') {
        res = prefix + res;
    }
    return res;
}

/**
 * 判断字符是否为空
 * @param string
 * @returns {boolean}
 */
function empty(string){
    if(typeof(string) == 'undefined') return true;
    if(string == '') return true;
    if(string == null) return true;
    return false;
}

/**
 * 计算对象的长度
 * @param o
 * @returns {*}
 */
function count(o){
    var t = typeof o;
    if(t == 'string'){
        return o.length;
    }else if(t == 'object'){
        var n = 0;
        for(var i in o){
            n++;
        }
        return n;
    }
    return false;
}

/**
 * 判断函数是否存在
 * @param name
 * @returns {boolean}
 */
function function_exists(name){
    try {
        if (typeof(eval(name)) == "function") {
            return true;
        }
    } catch(e) {}
    return false;
}

/**
 * 创建完整URL
 * @param url
 * @param query
 * @returns {*}
 */
function http_build_query(url, query){
    if($.isEmptyObject(query)) return url;
    query = $.param(query);
    if(url.indexOf('?') > -1) {
        url += '&';
    }else{
        url += '?';
    }
    return url + query;
}

/**
 * 判断searchText是否与targetText匹配
 * @param searchText 检索的文本
 * @param targetText 目标文本
 * @return true-检索的文本与目标文本匹配；否则为false.
 */
function isMatch(searchText, targetText) {
    return $.trim(targetText) != "" && targetText.indexOf(searchText) != -1;
}

/*
*	树状队列，支持disabled  --  Add By Lemonice
*	data = {
*		id:'#grid',  //显示的ID
*		input_name:'menu_id',  //赋值给某个name属性的input
*		default_id:json.role.menu_id,  //选中的ID，字符串，逗号隔开
*		disabled_id:'',  //选中并且不可操作的ID，字符串，逗号隔开
*		list_json:json.menu,  //树列的数据，json格式
*		is_disabled_checked:false,  //把默认选中或者没选中的不可再操作，disabled，有disabled_id值才生效
*	}
*/
function skin_tree(data)
{
	Lme_tree = this;
	this.config = {
		id:data.id,  //显示的ID
		input_name:data.input_name ? data.input_name : null,  //赋值给某个name属性的input
		default_id:data.default_id ? data.default_id : null,  //选中的ID，默认值，字符串，逗号隔开
		disabled_id:data.disabled_id ? data.disabled_id : null,  //选中并且不可操作的ID，字符串，逗号隔开
		list_json:data.list_json,  //树列的数据，json格式
		is_disabled_checked:data.is_disabled_checked==true||data.is_disabled_checked==false ? data.is_disabled_checked : null,  //把默认选中或者没选中的不可再操作，disabled，有默认值才生效
		checked_disabled_class: data.checked_disabled_class ? data.checked_disabled_class : 'tree-checkbox4',
		nochecked_disabled_class: data.nochecked_disabled_class ? data.nochecked_disabled_class : 'tree-checkbox3'
	}
	this.set_value = data.set_value ? data.set_value : function(){};
	this.loadSuccess = data.loadSuccess ? data.loadSuccess : function(){};
	this.grid = $(this.config.id);
	this.disable = function(node_obj, is_checked){
		if(is_checked){
			$(node_obj).children('.tree-checkbox').addClass(this.config.checked_disabled_class).click(function(){return false;});
		}else{
			$(node_obj).children('.tree-checkbox').addClass(this.config.nochecked_disabled_class).click(function(){return false;});
		}
	}
	this.is_disabled = function(node_obj){
		if($(node_obj).children('.tree-checkbox').hasClass(this.config.checked_disabled_class) 
		|| $(node_obj).children('.tree-checkbox').hasClass(this.config.nochecked_disabled_class)){
			return true;
		}else{
			return false;
		}
	}
	this.check = function(node_obj, lock){
		if(lock){
			this.lock = true;
		}
		if(this.is_disabled(node_obj) == false){
			this.grid.tree('check', node_obj);
		}
		if(lock){
			this.lock = false;
		}
	}
	this.uncheck = function(node_obj){
		if(this.is_disabled(node_obj) == false){
			this.grid.tree('uncheck', node_obj);
		}
	}
	this.isLeaf = function(node_obj){
		return this.grid.tree('isLeaf',node_obj);
	}
	this.get_tree_value = function(){
        var rows = this.grid.tree('getChecked');
        var items = [];
        if(rows) {
            for(var i = 0; i < rows.length; i++) {
				if(this.is_disabled(rows[i].target) == false){
                	items.push(rows[i].id);
				}
            }
        }
        return items.join(',');
    }
	this.all_node_handle = function(checked, _function_){
		if(checked == true || checked == null){
			var nodes = this.grid.tree('getChecked');
			for(i in nodes){
				_function_(nodes[i]);
			}
		}
		if(checked == false || checked == null){
			var nodes = this.grid.tree('getChecked', 'unchecked');
			for(i in nodes){
				_function_(nodes[i]);
			}
		}
	}
	this.input_value = function(){
		if(this.config.input_name != null){
			$('input[name="'+this.config.input_name+'"]').val(this.get_tree_value());
		}
		this.set_value();
	}
	this.parents_to_checked = function(node_obj, checked, onece){
		if(!node_obj){
			return false;
		}
		checked = checked ? checked : false;
		onece = onece ? onece : false;
		var parent = this.grid.tree('getParent',node_obj);
		if(parent)
		{
			if(checked){
				this.check(parent.target);
			}else{
				//检查下级是否有选中的，如果有则不取消上级选中
				var children = this.grid.tree('getChildren',parent.target);
				for(i in children){
					if(node_obj != children[i].target && children[i].checked == true){
						checked = true;
					}
				}
				if(checked == false){
					this.uncheck(parent.target);
				}
			}
			if(onece != true){
				this.parents_to_checked(parent.target, checked, onece);
			}
		}
	}
	this.children_to_checked = function(node_obj,checked){
		var children = this.grid.tree('getChildren',node_obj);
		for(i in children){
			if(checked){
				this.check(children[i].target);
			}else{
				this.uncheck(children[i].target);
			}
		}
	}
	this.lock = false;
	//显示功能权限的分类树
	if(!data.id || !data.list_json)
		return this;
	this.grid.tree({
		dnd: true,
		lines: true,
		animate: true,
		checkbox: true,
		cascadeCheck: false,
		data: this.config.list_json,
		onContextMenu: function(e, node) {
			e.preventDefault();
			return false;
		},
		onCheck: function(node, checked){
			Lme_tree.input_value();
			/*处理下级自动同步checked*/
			if(Lme_tree.lock == false && Lme_tree.isLeaf(node.target) == false){
				Lme_tree.children_to_checked(node.target, checked);;
			}
			/*处理上级自动同步checked*/
			if(Lme_tree.lock == false && Lme_tree.isLeaf(node.target) == true){
				Lme_tree.lock = true;
				Lme_tree.parents_to_checked(node.target, checked);
				Lme_tree.lock = false;
			}
		},
		onClick: function(node){
			if(Lme_tree.isLeaf(node.target) == false){
				Lme_tree.grid.tree('toggle',node.target);
			}else{
				if(node.checked){
					Lme_tree.uncheck(node.target);
				}else{
					Lme_tree.check(node.target);
				}
			}
		},
		onLoadSuccess: function(node, data) {
			Lme_tree.lock = true;
			
			if(Lme_tree.config.disabled_id != null){
				var menu_disabled_id = Lme_tree.config.disabled_id;
				if(typeof Lme_tree.config.disabled_id == 'string'){
					menu_disabled_id = menu_disabled_id.split(',');
				}
				for(var i in menu_disabled_id) {
					var node = Lme_tree.grid.tree('find', menu_disabled_id[i]);
					if(node){
						Lme_tree.check(node.target);
					}
				}
			}
			
			if(Lme_tree.config.is_disabled_checked == true){
				Lme_tree.all_node_handle(true, function(node){
					Lme_tree.disable(node.target,true);
				});
			}else if(Lme_tree.config.is_disabled_checked == false){
				Lme_tree.all_node_handle(false, function(node){
					Lme_tree.disable(node.target,false);
				});
			}
			
			if(Lme_tree.config.default_id != null)
			{
				var menu_default_id = Lme_tree.config.default_id;
				if(typeof Lme_tree.config.default_id == 'string'){
					menu_default_id = menu_default_id.split(',');
				}
				for(var i in menu_default_id) {
					var node = Lme_tree.grid.tree('find', menu_default_id[i]);
					if(node){
						Lme_tree.check(node.target);
					}
				}
			}
			
			Lme_tree.lock = false;
			Lme_tree.loadSuccess();
		}
	});
	this.input_value();
	
	return Lme_tree;
}
/*
*	加载日期空间到指定的对象
*   Add By Lemonice
*
*/
function date_ctl(config)
{
	config.formatter = config.formatter ? config.formatter : function (date) {
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		var d = date.getDate();
		var hh = date.getHours();
		var mm = date.getMinutes();
		var ss = date.getSeconds();
		return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d) + ' ' + 
		(hh < 10 ? ('0' + hh) : hh) + ':' + (mm < 10 ? ('0' + mm) : mm) + ':' + (ss < 10 ? ('0' + ss) : ss);
	};
	$(config.id).datebox(config);
}

$(function() {
	/*  在textarea处插入文本--Start */
	(function($) {
		$.fn.extend({
			insertContent : function(myValue, t) {
				var $t = $(this)[0];
				if (document.selection) { // ie
					this.focus();
					var sel = document.selection.createRange();
					sel.text = myValue;
					this.focus();
					sel.moveStart('character', -l);
					var wee = sel.text.length;
					if (arguments.length == 2) {
						var l = $t.value.length;
						sel.moveEnd("character", wee + t);
						t <= 0 ? sel.moveStart("character", wee - 2 * t
								- myValue.length) : sel.moveStart(
								"character", wee - t - myValue.length);
						sel.select();
					}
				} else if ($t.selectionStart
						|| $t.selectionStart == '0') {
					var startPos = $t.selectionStart;
					var endPos = $t.selectionEnd;
					var scrollTop = $t.scrollTop;
					$t.value = $t.value.substring(0, startPos)
							+ myValue
							+ $t.value.substring(endPos,
									$t.value.length);
					this.focus();
					$t.selectionStart = startPos + myValue.length;
					$t.selectionEnd = startPos + myValue.length;
					$t.scrollTop = scrollTop;
					if (arguments.length == 2) {
						$t.setSelectionRange(startPos - t,
								$t.selectionEnd + t);
						this.focus();
					}
				} else {
					this.value += myValue;
					this.focus();
				}
			}
		})
	})(jQuery);
	/* 在textarea处插入文本--Ending */
});