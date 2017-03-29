<?php
/**
 * ====================================
 * 积分操作 类
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-14 17:32
 * ====================================
 * File: Integral.class.php
 * ====================================
 */
namespace Common\Extend\Order;
use Common\Extend\Time;
use Common\Extend\PhxCrypt;
use Common\Extend\Send;

class Member{
	private $sessionId = NULL;                  //session ID
	private $user_id = 0;                       //当前登录的用户ID
	
	public function __construct(){
		$this->sessionId = session_id();  //获取当前session ID
		$this->user_id = D('Home/OrderInfo')->getUser('user_id');
    }
	
    public function Member(){
        $this->__construct();
    }
	
	/*
	*	新增会员（购物车模块添加）
	*	@Author 9009123 (Lemonice)
	*	@param  string $data  用户相关的信息
	*	@return exit
	*/
    public function addNewMember($data){
		$UsersModel = D('Home/Users');
    	$user_id = $UsersModel->where("mobile = '$data[mobile]'")->getField('user_id');	//是否已存在
    	if($user_id > 0){
    		return array('user_id'=>$user_id);  //用户存在
    	}
    	$password = $data['password'];
    	$data['password'] = md5(md5($data['password']));
		$data['state'] = 1;  //自动注册状态
		$data['auto_reg_time'] = Time::gmTime();  //注册时间
		$user_id = $UsersModel->reg($data);  //新注册帐号
		if($user_id){
			$msg = "下单成功！登陆瓷肌会员中心激活账号即可查看订单物流，享受积分、生日礼包等福利，账号为当前手机号，密码：".$password;
			Send::send_sms($data['sms_mobile'],$user_id,$data['ip'],$msg);  //发短信，不管结果是否成功
		}
		return array('new_user_id'=>$user_id);  //新用户，返回字段不同，区别
    }
	
	/*
	*	保存地址信息到会员中心的收货地址列表
	*	@Author 9009123 (Lemonice)
	*	@param  int  $user_id 用户ID
	*	@param  string $address  地址详情
	*	@return int [address ID]
	*/
	public function saveAddress($user_id, $address){
    	$address_id = isset($address['address_id']) ? intval($address['address_id']) : 0;
    	$data = array();
    	$data['consignee'] = isset($address['consignee']) ? $address['consignee'] : '';
    	$data['province'] = isset($address['province']) ? $address['province'] : 0;
    	$data['city'] = isset($address['city']) ? $address['city'] : 0;
    	$data['district'] = isset($address['district']) ? $address['district'] : 0;
    	$data['town'] = isset($address['town']) ? $address['town'] : 0;
    	$data['address'] = isset($address['address']) ? $address['address'] : '';
    	$data['attribute'] = isset($address['attribute']) ? $address['attribute'] : '';
		$data['mobile'] = isset($address['mobile']) ? $address['mobile'] : '';
    	$data['update_time'] = Time::gmTime();
    	if ($address_id > 0) {
			if(isset($address['address_id'])){
				unset($address['address_id']);
			}
			D('Home/UserAddress')->where("user_id = '$user_id' AND address_id = '$address_id'")->update($data);  //更新
    	}else {
    		$data['user_id'] = $user_id;
    		$data['add_time'] = Time::gmTime();
    		$address_id = D('Home/UserAddress')->insert($data);  //添加新的地址
    	}
		return $address_id;
	}
}
?>