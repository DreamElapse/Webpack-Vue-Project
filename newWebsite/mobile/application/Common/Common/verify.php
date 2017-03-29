<?php
/**
 * ====================================
 * 校验方法库
 * ====================================
 * Author: 9004396
 * Date: 2016-06-25 11:38
 * ====================================
 * File: verify.php
 * ====================================
 */

/**
 * 验证移动端访问
 * @return bool
 */
function is_mobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))  return true;
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array ('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * 验证手机号码是否合法
 * @param $phoneNumber
 * @return bool
 */
function is_phone($phoneNumber){
    return preg_match("/^1[34578]\d{9}$/", $phoneNumber) ? true : false;
}

/**
 * 验证电子邮箱是否合法
 * @param $email
 * @return bool
 */
function is_email($email){
    return preg_match("/^[_.0-9a-z-a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,4}$/",$email) ? true : false;
}

/**
 * 获取验证码
 * @param int $expires  验证码有效时间 0:无失效时间
 * @param string $name  验证名称
 * @param int $length   验证码长度
 * @param int $type     验证码类型（1：数字，2：字母：3：数字和字母）
 * @return mixed|string
 */
function getCode($expires = 0, $name = '', $length = 6, $type = 1){

    $type = ($type <=0 || $type > 3) ? $type = 1 : $type;
    $expires = !$expires ? 0 : $expires;
    $length = empty($length) ? 6 : $length;

    $codeKey = $name.'Code';
    $codeData = session($codeKey);
    $code = $codeData['code'];
    if(empty($code)){
        $codes['code'] = verifyCode($length,$type);
        $codes['time'] = \Common\Extend\Time::gmTime();
        $codes['expires'] = $expires;
        session($codeKey,$codes);
        $code = $codes['code'];
    }else{
        if($expires > 0){
            $codeTime = $codeData['time'];
            $nowTime = \Common\Extend\Time::gmTime();
            $second = floor(($nowTime - $codeTime) % 86400); //计算生成验证码与当前时间差
            if($second > $expires){ //过期生成新的验证码
                $codes['code'] = verifyCode($length,$type);
                $codes['time'] = \Common\Extend\Time::gmTime();
                $codes['expires'] = $expires;
                session($codeKey,$codes);
                $code = $codes['code'];
            }
        }
    }
    return $code;
}

/**
 * 校验验证码
 * @param $code
 * @param string $name
 * @return bool
 */
function checkCode($code,$name = ''){
    $codeKey = $name.'Code';
    $codeData = session($codeKey);
    $s_code = $codeData['code'];
    if(empty($s_code)){
        return false;
    }
    $expires = $codeData['expires'];
    $codeTime = $codeData['time'];
    $nowTime = \Common\Extend\Time::gmTime();
    $second = floor(($nowTime - $codeTime) % 86400); //计算生成验证码与当前时间差
    if($second > $expires){
        session($codeKey,null);
        $s_code = '';
        return false;
    }
    if($code == $s_code){
        session($codeKey,null);
        return true;
    }else{
        return false;
    }

    
}

/**
 * 产生验证码
 * @param int $length   验证码长度
 * @param int $type     验证码类型（1：数字，2：字母：3：数字和字母）
 * @return mixed|string 验证码
 */
function verifyCode($length = 6, $type = 1){

    //生成验证码
    $letter = 'abcdefghijklmnopqrstuvwxyz';
    $num    = '0123456789';
    $strArr = array(
        '1' => str_repeat($num,3),
        '2' => $letter,
        '3' => substr($num,2).$letter,
    );
    $str = $length > 10 ? ($type == 1 ? str_repeat($strArr[$type],$length) : str_repeat($strArr[$type],5)) : $strArr[$type];
    $str = str_shuffle($str);
    return substr($str,0,$length);
}

/*
*	检测是否微信打开网页
*	@Author 9009123 (Lemonice)
*	@return true or false
*/
function isCheckWechat(){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if(strpos($user_agent,'MicroMessenger') === false){
        return false;
    }else{
        return true;
    }
}

