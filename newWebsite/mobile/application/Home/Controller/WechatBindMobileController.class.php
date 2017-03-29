<?php
/**
 * ====================================
 * 微信绑定手机号码 - 当前只供Q站使用
 * ====================================
 * Author: 9009221
 * Date: 2016-07-25
 * ====================================
 * File: WechatBindMobileController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Wechat;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;
use Common\Extend\Logistics;
use Common\Extend\WechatJsSdk;
use Common\Extend\Send;
use Common\Extend\Integral;
//use Common\Extend\Curl;

class WechatBindMobileController extends InitController {
	
    public function __construct() {
        parent::__construct();
        Wechat::$app_id = APPID;
        Wechat::$app_secret = APPSECRET;
    }
	
	/*
    *	获取绑定详情
    *	@Author 9009123 (Lemonice)
    */
    public function show() {
		/*if (!isCheckWechat()) {
			$this->error('请用微信打开！');
		}*/
		
		$jssdk = new WechatJsSdk(APPID, APPSECRET);
		$signPackage = $jssdk->getSignPackage();
		$open_id = session('sopenid');
		//$open_id = 'oFJj4s0r20ii06W7NqL-yLl_rBm0';//测试
		$data = D('BindUser')->field('nickname,mobile,sex,headimgurl,subscribe')->where("openid = '$open_id'")->find();
		if(isset($data['mobile'])){
			$data['mobile'] = PhxCrypt::phxDecrypt($data['mobile']);
		}
		$this->success(array(
			'signPackage'=>$signPackage,
			'data'=>$data,
			'isCheckWechat'=>(!isCheckWechat() ? 0 : 1),
		));
    }
	
	/*
    *	发送手机验证码
    *	@Author 9009123 (Lemonice)
    */
    public function sendSms() {
		$mobile = I('request.mobile','','trim');
		if(!is_phone($mobile)){
			$this->error('手机号码不正确');
		}
		$open_id = session('sopenid');
		$BindUser = D('BindUser');
		//$open_id = 'oFJj4s0r20ii06W7NqL-yLl_rBm0';//测试
		if (!$BindUser->isSubcribe($open_id)) {
			$this->error('请先关注公众号');
		}
		if ($BindUser->isBind(PhxCrypt::phxEncrypt($mobile), $open_id)) {
			$this->error('该手机已被绑定');
		}
		
		$mobile_array = session('wechat_bind_mobile');
		if(isset($mobile_array['sended_mobile']) && $mobile_array['sended_mobile'] == $mobile){
			$code = isset($mobile_array['sended_code'])&&$mobile_array['sended_code']!='' ? $mobile_array['sended_code'] : mt_rand(100000,999999);
		}else{
			$code = mt_rand(100000,999999);
		}
		
		$sms_content = '您的验证码是'.$code.'，请确保是本人操作以防信息泄露！';
		$user_id = 0;
		$ip = get_client_ip();
		
		Send::send_sms($mobile,$user_id,$ip,$sms_content,'code');  //发短信，不管结果是否成功
		
		$mobile_session_array = array(
			'sended_code'=>$code,
			'sended_mobile'=>$mobile,
			'sended_time'=>Time::gmtime(),
		);
		session('wechat_bind_mobile',$mobile_session_array);
		$this->success('发送成功！');
    }
	
	/*
    *	校验手机验证码
    *	@Author 9009123 (Lemonice)
    */
	public function verify(){
		$mobile = I('request.mobile','','trim');
		$code = I('request.code','','trim');
		
		if($mobile == ''){
			$this->error('请输入手机号码！');
		}
		if($code == ''){
			$this->error('请输入手机验证码！');
		}
		$mobile_s = $mobile;
		$mobile = PhxCrypt::phxEncrypt($mobile);		//加密电话
		
		$mobile_array = session('wechat_bind_mobile');
		if (!isset($mobile_array['sended_code']) || $code != $mobile_array['sended_code']) {
			$this->error('验证码不正确！');
		}
		$open_id = session('sopenid');
		$BindUser = D('BindUser');
		//$open_id = 'oFJj4s0r20ii06W7NqL-yLl_rBm0';//测试
		if (!$BindUser->isSubcribe($open_id)) {
			$this->error('请先关注公众号！');
		}
		if ($BindUser->isBind($mobile, $open_id)) {
			$this->error('该手机已被绑定！');
		}
		//绑定手机号码，更新数据到数据库
		$ret = $BindUser->where("openid = '$open_id'")->save(array(
			'mobile'=>$mobile,
		));
		
		if($ret > 0){	
			//添加会员
			$real_ip = get_client_ip();
			$password = substr($mobile_s,-6);  //获取手机号码后六位做为密码
			$data = array(
				'mobile'=>$mobile,
				'sms_mobile'=>$mobile_s,
				'ip'=>$real_ip,
				'email'=>'',
				'source'=>$_SERVER['HTTP_HOST'],
				'sex'=>0,
				'password'=>$password,
			);
			//验证存在或初始注册，存在，则返回user_id,不存在，则返回初始注册的user_id和随机密码
			$reg_res = D('users')->addNewMember($data);
			$user_id = isset($reg_res['user_id']) ? $reg_res['user_id'] : (isset($reg_res['new_user_id']) ? $reg_res['new_user_id'] : 0);  //会员ID
			
			//签到积分入账
			if($user_id > 0){
				//is_merge_integral=0,1 是否已经合并过签到积分
				$bindUserInfo = $BindUser->field('points_left,is_merge_integral')->where("openid = '$open_id'")->find();  //未绑定时候的签到积分
				$points = isset($bindUserInfo['points_left']) ? $bindUserInfo['points_left'] : 0;
				$is_merge_integral = isset($bindUserInfo['is_merge_integral']) ? $bindUserInfo['is_merge_integral'] : 0;
				
				if($points > 0 && $is_merge_integral <= 0){
					$IntegralObject = new Integral();
					$IntegralObject->variety(C('SITE_ID'), $points, '签到积分到账', 0, false, array('user_id'=>$user_id,'type' => 1));
					$BindUser->where("openid = '$open_id'")->save(array('is_merge_integral'=>1));
				}
			}
			
			$info = array(
				'FromUserName' => $open_id,
				'Content' => $mobile_s,
			);
			//使用wechat控制器中的方法
			A('Wechat')->sendWechatLogistics($info);  // 调用 查询并且发送订单号
			
			session('wechat_bind_mobile',null);
			
			$this->success('绑定成功！');
		}
		$this->error('绑定失败！');
	}
	
	public function unbind(){
		//去掉解绑功能 - Edit By 9009123 (Lemonice) 2017-02-06
		$this->error('解绑失败！');
		/*=================================================*/
		$open_id = session('sopenid');
		//$open_id = 'oFJj4s0r20ii06W7NqL-yLl_rBm0';//测试
		
		//绑定手机号码，更新数据到数据库
		$ret = D('BindUser')->where("openid = '$open_id'")->save(array(
			'mobile'=>'',
		));
		
		if ($ret) {
			$this->success('解绑成功！');
		}
		$this->error('解绑失败！');
	}
	
}