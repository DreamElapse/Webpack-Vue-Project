<?php
/**
 * ====================================
 * 回调支付、支付跳转 控制器
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-05 11:34
 * ====================================
 * File: RespondPaymentController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;
use Common\Extend\Order\Member;

class RespondPaymentController extends InitController{
	private $dbModel = NULL;  //储存地址数据表对象
	private $MemberObject = NULL;  //储存订单操作会员类库
	
	//private $user_id = 0;  //当前登录的ID
	
	/* 
	*	对应返回信息给第三方 - 异步回调返回
	*/
	private $pay_type_array = array(
		'kuaiqian'=>'<result>1</result><redirecturl>{GetRespondUrl}</redirecturl>',
		'alipay'=>'success',
		'tenpay'=>'success',
		'wechatpay'=>'System Error',  //微信支付返回，需要动态获取再返回
	);	
	
	/* 
	*	语言包
	*/
	private $_LANG = array(
		'pay_status'=>'支付状态',
		'pay_not_exist'=>'此支付方式不存在或者参数错误',
		'pay_disabled'=>'此支付方式还没有被启用',
		'pay_success'=>'您此次的支付操作已成功',
		'pay_fail'=>'支付操作失败，请返回重试！',
	);
	
	public function __construct(){
		parent::__construct();
		$this->dbModel = D('PayInfo');
		$this->MemberObject = new Member();
	}
	
	/*
	*	暂时不使用本控制器默认方法，预留
	*	@Author 9009123 (Lemonice)
	*	@return exit & 404[not found]
	*/
	public function index(){
		send_http_status(404);
	}
	
	/*
	*	测试的
	*	@Author 9009123 (Lemonice)
	*	@return exit & 404[not found]
	*/
	public function test1(){
		$campaign = I('request.campaign');
		$data = getAdvisoryInfo($campaign);
		$this->assign('tel',$data['tel']);
		
		$this->assign('error_msg',$msg);
		$this->display('pay_error');
	}
	
	/*
	*	测试的
	*	@Author 9009123 (Lemonice)
	*	@return exit & 404[not found]
	*/
	public function test2(){
		$campaign = I('request.campaign');
		$data = getAdvisoryInfo($campaign);
		$this->assign('tel',$data['tel']);
		
		$this->assign('error_msg',$msg);
		$this->display('pay_success');
	}
	
	/*
	*	支付跳转处理 - 离线支付、在线支付    同步返回
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function skip(){
		/* 获取和检查code */
		$pay_code = $this->checkCode();
		
		$payment = $this->getPaymentClass($pay_code);  //实例化网银类
		
		$function = 'respond';
		if($pay_code == 'wechatpay'){
			$function = 'synchro';  //微信支付的回调函数不一样
		}
		$result = $payment->$function();  //获取第三方结果、校验
		
		if(is_array($result)){
			D('PayLog')->orderPayLog($result);  //记录日记，无论成功失败
		}
		
		if(isset($result['pay_result']) && $result['pay_result'] == 1){  //支付成功
			$this->dbModel->orderPaidNew($result);  //更新订单状态、信息
			$this->skipSuccess($result);
		}else{  //支付失败
			$this->skipError($this->_LANG['pay_fail']);
		}
	}
	
	/*
	*	支付回调处理 - 离线支付、在线支付   异步返回
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function callBack(){
		/* 获取和检查code */
		$pay_code = $this->checkCode();
		$payment = $this->getPaymentClass($pay_code);  //实例化网银类
		$pay_code = strtolower($pay_code);
		
		$function = 'respond';
		if($pay_code == 'alipay'){
			$function = 'notify';  //支付宝的回调函数不一样
		}
		$result = $payment->$function();  //检查支付结果
		if(is_array($result)){
			D('PayLog')->orderPayLog($result);  //记录日记，无论成功失败
		}
		
		if(isset($result['pay_result']) && $result['pay_result'] == 1){  //支付成功
			$this->dbModel->orderPaidNew($result);  //更新订单状态、信息
			//如果是微信支付，把返回信息储存到属性，等到最后返回给微信
			if($pay_code == 'wechatpay' && isset($result['returnXml'])){
				$this->pay_type_array[$pay_code] = $result['returnXml'];
			}
			$this->callBackSuccess($pay_code);  //返回给第三方平台的请求  -  异步请求的返回
		}
		send_http_status(404);  //支付失败显示404页面
	}

    /**
     * 支付宝当面付异步校验接口
     */
	public function callBackF2f(){
        /* 获取和检查code */
        $pay_code = $this->checkCode();
        $pay_code = strtolower($pay_code);
        $payment = $this->getPaymentClass($pay_code);  //实例化网银类
        $result = $payment->F2f_notify();
        if(is_array($result)){
            D('PayLog')->orderPayLog($result);  //记录日记，无论成功失败
        }

        if(isset($result['pay_result']) && $result['pay_result'] == 1){  //支付成功
            $this->dbModel->orderPaidNew($result);  //更新订单状态、信息
            //如果是微信支付，把返回信息储存到属性，等到最后返回给微信
            if($pay_code == 'wechatpay' && isset($result['returnXml'])){
                $this->pay_type_array[$pay_code] = $result['returnXml'];
            }
            $this->callBackSuccess($pay_code);  //返回给第三方平台的请求  -  异步请求的返回
        }
        send_http_status(404);  //支付失败显示404页面
    }

	/*
	*	异步返回 - 成功返回给第三方
	*	@Author 9009123 (Lemonice)
	*	@param  string $pay_code  支付代码
	*	@return string  [code]
	*/
	private function callBackSuccess($pay_code){
		if(isset($this->pay_type_array[$pay_code])){
			if(strstr($this->pay_type_array[$pay_code],'{GetRespondUrl}')){
				//快钱需要返回地址
				$GetRespondUrl = $this->getPaymentClass($pay_code)->getRespondUrl($pay_code);  //获取URL
				$this->pay_type_array[$pay_code] = str_replace('{GetRespondUrl}',$GetRespondUrl,$this->pay_type_array[$pay_code]);
			}
			echo $this->pay_type_array[$pay_code];
			exit;
		}
	}
	
	/*
	*	支付跳转处理 - 离线支付、在线支付    同步返回
	*	@Author 9009123 (Lemonice)
	*	@param  string $pay_code  支付代码
	*	@return string  [code]
	*/
	private function checkCode(){
		$pay_code = I('request.code','','trim');
		if($pay_code == ''){
			$this->skipError($this->_LANG['pay_not_exist']);
		}
		if($pay_code != 'wechatpay'){
			$count = D('Payment')->where("pay_code = '$pay_code' AND enabled = 1")->count();
			if ($count == 0){
				$this->skipError($this->_LANG['pay_disabled']);
			}
		}
		//检查code里面是否存在问号
		if (strpos($pay_code, '?') !== false){
			$arr1 = explode('?', $pay_code);
			$arr2 = explode('=', $arr1[1]);
	
			$_REQUEST['code']   = $arr1[0];
			$_REQUEST[$arr2[0]] = $arr2[1];
			$_GET['code']       = $arr1[0];
			$_GET[$arr2[0]]     = $arr2[1];
			$pay_code           = $arr1[0];
		}
		return $pay_code;
	}
	
	/*
	*	显示支付成功的页面
	*	@Author 9009123 (Lemonice)
	*	@param  string $data  相关的支付信息
	*	@return exit
	*/
	private function skipSuccess($data){
		$pay_online_order_sn = session('pay_online_order_sn');    //新增订单的order_sn
		$user_id = $this->dbModel->getUser('user_id');  //用户ID
		$user_id = $user_id ? $user_id : 0;
		
		$new_user = array();

		//用户尚未登陆，查看该收货手机号在会员中心是否存在，不存在则初始注册（订单归属问题）
		if($user_id == 0){
			//判断是否是通过新增地址而添加的新用户，提示初始账号密码
			$new_consignee = session('new_consignee');
			if(!empty($new_consignee)){   //收获地址，初始注册
				$password = substr($new_consignee['mobile'],-6);  //获取手机号码后六位做为密码
				$data = array(
					'mobile'=>PhxCrypt::phxEncrypt($new_consignee['mobile']),
					'sms_mobile'=>$new_consignee['mobile'],
					'ip'=>get_client_ip(),
					'email'=>isset($new_consignee['email']) ? $new_consignee['email'] : '',
					'source'=>$_SERVER['HTTP_HOST'],
					'sex'=>0,
					'password'=>$password,
				);
				//验证存在或初始注册，存在，则返回user_id,不存在，则返回初始注册的user_id和随机密码
				$reg_res = $this->MemberObject->addNewMember($data);
				$user_id = isset($reg_res['user_id']) ? $reg_res['user_id'] : (isset($reg_res['new_user_id']) ? $reg_res['new_user_id'] : 0);
				
				if($user_id > 0){
					if(isset($reg_res['new_user_id'])){	//新自动注册用户
						$new_user = array(
							'new_user'=>substr($new_consignee['mobile'],0,-4) . '****',
							'password'=>substr($password,0-4) . '****',
						);
					}
					//保存地址到会员中心（user_id：为刚初始注册或以新增地址中的手机号的user_id）
					$new_address_id = $this->MemberObject->saveAddress($user_id, $new_consignee);
					//session('new_address_id', $new_address_id);
					
					//去除修改默认的收货地址ID，以便用新增的地址作为收货地址
					//if($new_address_id > 0){
					//	session('has_change_default_id', NULL);
					//}
					//更新本地订单user_id
					if($pay_online_order_sn != ''){
						D('OrderInfo')->where("order_sn = '$pay_online_order_sn'")->update(array('user_id'=>$user_id));
					}
					
					//更新会员中心订单中的user_id
					D('OrderInfoCenter')->where("order_sn = '$pay_online_order_sn'")->update(array('user_id'=>$user_id));
					
					//非初始注册,包涵初始注册密码
					$never_login = D('Users')->where("user_id = '$user_id'")->getField('state');  //为1，说明是初始注册但未登陆过
				}
			}
		}
		$new_arr = !empty($new_user) ? $new_user : array();
		
		//获取联系电话
		$campaign = I('request.campaign');
		$data = getAdvisoryInfo($campaign);
		$this->assign('tel',$data['tel']);
		
		$this->assign('new_user',$new_arr);
		$this->assign('never_login',$never_login);
		$this->display('pay_success');
		exit;
	}	
	
	/*
	*	显示支付失败、错误的页面
	*	@Author 9009123 (Lemonice)
	*	@param  string $msg  错误信息
	*	@return exit
	*/
	private function skipError($msg = ''){
		//获取联系电话
		$campaign = I('request.campaign');
		$data = getAdvisoryInfo($campaign);
		$this->assign('tel',$data['tel']);
		
		$this->assign('error_msg',$msg);
		$this->display('pay_error');
		exit;
	}
	
	/*
	*	实例化对应的网银类
	*	@Author 9009123 (Lemonice)
	*	@param  string $pay_code  支付代码
	*	@return Object
	*/
	private function getPaymentClass($pay_code){
		$pay_code = ucfirst($pay_code);
		$name = '\Common\Extend\Payment\\' . $pay_code;  //对应网银类
		return new $name();
	}
}