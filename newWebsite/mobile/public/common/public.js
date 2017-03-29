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

