<?php
/**
 * ====================================
 * 后台公共方法
 * ====================================
 * Author: 9004396
 * Date: 2017-01-10 19:40
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: function.php
 * ====================================
 */
/**
 * 获取登录信息
 * @param $key
 * @return null
 */
function login($key = '') {
    static $cookie;
    if(!$cookie) {
        $cookie = unserialize(session('login_cookie'));
    }
    return empty($key) ?
        $cookie : (isset($cookie[$key]) ? $cookie[$key] : null);
}

/**
 * 验证逻辑权限
 * @param $item
 * @return bool
 */
function power($item, $show_error = false) {
    static $menu = array();
    if(login('is_open')) return true;

    if(empty($menu)) {
        $menu = array_reduce(login('menu'), 'array_merge', array());
    }
    $result = false;
    if(is_array($item)) {
        foreach($item as $key => $sitem) {
            if(in_array(strtolower($sitem), $menu)) {
                $result = true;
                break;
            }
        }
    }else {
        $result = in_array(strtolower($item), $menu);
    }
    if(!$result && $show_error) {
        if(IS_AJAX) {
            $data['info']   =   L('_NOT_ACCESS_');
            $data['status'] =   0;
            exit(json_encode($data));
        }else {
            $view = \Think\Think::instance('Think\View');
            $view->assign('msgTitle', L('_OPERATION_FAIL_'));
            $view->assign('status', 0);   // 状态
            $view->assign('error', L('_NOT_ACCESS_'));// 提示信息
            $view->display(C('TMPL_ACTION_ERROR'));
        }
        exit;
    }
    return $result;
}

/**
 * 密码处理
 * @param $password
 * @return bool|string
 */
function password($password) {
    if(empty($password)) return false;
    return md5(md5($password) . C('CRYPT_KEY'));
}

function RL($item) {
    if(strpos($item, '.') !== false) {
        $item = explode('.', $item);
        $lang = L($item[0]);
        array_shift($item);
        foreach($item as $next) {
            if(isset($lang[$next])) {
                $lang = $lang[$next];
            }
        }
        return $lang;
    } else {
        return L($item);
    }
}

/**
 * 随机生成字符串函数
 * @param $len
 * @return string
 */
function randomString($len)
{
    $possible=str_shuffle("QnEKu5eAXqzJZC81pFvbROyaghomc6sr3dN7kPWDfiTYG9IjwtSx0HL2MUVB4l");
    $str="";
    while(strlen($str)<$len)
    {
        $str.=substr($possible,(rand()%(strlen($possible))),1);
    }
    return($str);
}

/**
 * 格式化时间
 * @param $data
 * @param string $format
 * @return bool
 */
function formatTime(& $data, $format = 'Y-m-d H:i:s') {
    if(empty($data)) return false;
    $format = empty($format) ? C('DATE_FORMAT') : $format;
    foreach($data as $key => $item){
        if(is_array($item)){
            formatTime($data[$key], $format);
        }else{
            if(strpos($key, 'time')){
                $data[$key] = $item > 0 ? date($format, $item) : null;
            }
        }
    }
}


/**
 * 分析枚举类型配置值 格式 a:名称1,b:名称2
 * @param $string
 * @return array
 */
function parseConfigAttr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}