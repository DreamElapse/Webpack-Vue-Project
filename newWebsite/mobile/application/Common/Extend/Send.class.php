<?php
/**
 * 发送类
 * ====================================
 * Author: 9004396
 * Date: 2014/12/31 15:18
 * ====================================
 * File: Send.php
 * ====================================
 */
namespace Common\Extend;


Class Send{

    /**
     * 发送短信
     * @param $mobile               手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
     * @param string $ip            客户端IP
     * @param string $sms_content   发送的内容
     * @param string $ext
     * @param string $rrid          默认空 如果空返回系统生成的标识串 如果传值保证值唯一 成功则返回传入的值
     * @param string $stime         定时时间 格式为2011-6-29 11:09:21
     * @param string $type 短信类型  type的为code 为验证类短信，其他或不填为信息类短信
     * @return array|mixed          返回值   array('M000000'=>'发送成功','M000001'=>'标识错误','M000002'=>'手机号码不存在','M000003'=>'手机号码不正确','M000004'=>'操作过于频繁','M000005'=>'请不要恶意操作','M000006'=>'发送失败','M000007'=>'记录失败',);
     */
    function send_sms($mobile, $user_id = 0, $ip = '', $sms_content = '', $type = '' , $ext = '', $rrid = '', $stime = ''){
        if(empty($mobile)){
			return false;
		}
		if(empty($ip)){
			$ip = get_client_ip();
		}
        $data = array(
            'mobile'        => $mobile,
            'user_id'       => $user_id,
            'ip'            => $ip,
            'source'        => $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            'sms_content'   => $sms_content,
            'ext'           => $ext,
            'rrid'          => $rrid,
            'stime'         => $stime,
            'flag'          => 'API_CHINASKIN-002',
            'type'          => $type

        );
		Curl::$key = 'SMSAPI#*GO2821119';
        $ret = Curl::getApiResponse('http://api.chinaskin.cn/apiSms/index',$data);
        return $ret;
    }


    /**
     * 产生手机验证码
     * @param int $Expires 有效时间
	 * @param string $mobile 手机号码
     * @return int
     */
    public static function setMobileCode($Expires = 0, $mobile = '')
    {
        if (!isset($_SESSION['mobileCode_time']) || !isset($_SESSION['mobileCode'])) {
            $code = rand(100000, 999999);
			session('mobileCode',$code);
			session('mobileCode_time',Time::gmTime());
			if($mobile != ''){
				session('mobile',$mobile);  //保存手机号码，避免被换
			}
        } else {
            $n_time = Time::gmTime();
            $s_time = session('mobileCode_time');
            $second = floor(($n_time - $s_time) % 86400);
			$phone = session('mobile');
			if($mobile != ''){
				session('mobile',$mobile);  //保存手机号码，避免被换
			}
            if ((empty($phone) && $phone != $mobile) || $second > $Expires && $Expires != 0) {
                $code = rand(100000, 999999);
				session('mobileCode',$code);
				session('mobileCode_time',Time::gmTime());
            } else {
                $code = session('mobileCode');
            }
        }
        return $code;
    }

    /**
     * 检测手机验证码
     * @param $mobileCode
     * @param int $Expires 有效时间
	 * @param string $mobile 手机号码
     * @return bool
     */
    public static function checkMobileCode($mobileCode, $Expires = 0, $mobile = '')
    {
        if (!isset($_SESSION['mobileCode_time']) || empty($_SESSION['mobileCode_time'])) {
            return false;
        }
        $n_time = Time::gmTime();
        $s_time = session('mobileCode_time');
        $second = floor(($n_time - $s_time) % 86400);
        if ($second > $Expires && $Expires != 0) {
            session('mobileCode',null);
			session('mobileCode_time',null);
			session('mobile',null);
        }
		$code = session('mobileCode');
        if (!$code || empty($code)) {
            return false;
        } else {
            if ($code == $mobileCode) {
				$phone = session('mobile');
				if($mobile != '' && $phone != '' && $mobile != $phone){
					return false;
				}
				//session('mobileCode',null);
				//session('mobileCode_time',null);
				//session('mobile',null);
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * 发送邮件
     * @param $toEmail  收件人地址
     * @param $MSubject  邮件主题
     * @param $MBody   邮件内容
     * @return bool
     */
//    public static function send_mail($toEmail, $MSubject, $MBody)
//    {
//        $client = new SoapClient('http://service.respread.com/Service.asmx?WSDL');
//        $sendParam = array(
//            'LoginEmail' => $GLOBALS['config']['mail']['LoginEmail'],
//            'Password' => $GLOBALS['config']['mail']['Password'],
//            'From' => $GLOBALS['config']['mail']['From'],
//            'FromName' => $GLOBALS['config']['mail']['FromName'],
//            'To' => $toEmail,
//            'Subject' => $MSubject,
//            'Body' => $MBody
//        );
//
//        $sendResult = $client->Send($sendParam);
//
//        if ($sendResult->SendResult == 'Sent success') {
//            return true;
//        } else {
//            return false;
//        }
//    }


    public static function send_mail($toEmail, $MSubject, $MBody){
        $client = new \SoapClient('http://service1.rspread.com/Service.asmx?WSDL');
        $sendParam = array(
            'LoginEmail'=>'chinaskincrm@pcpp.com.cn',
            'Password'  =>'21EFE8BF-9BFC-4848-B040-E8B6CBFEDABF',
            'From'      =>'chinaskincrm@pcpp.com.cn',
            'FromName'  =>'瓷肌会员中心',
            'To' => $toEmail,
            'Subject' => $MSubject,
            'Body' => $MBody
        );
        $sendResult = $client->Send($sendParam);
        
        if ($sendResult->SendResult == 'Sent success' || strpos($sendResult->SendResult, 'successfully')) {
            return true;
        } else {
            return false;
        }
    }

}