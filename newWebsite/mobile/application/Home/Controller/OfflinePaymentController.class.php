<?php
/**
 * ====================================
 * 离线支付 控制器
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-04 13:50
 * ====================================
 * File: OfflinePaymentController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Order\Order;
use Common\Extend\Time;
use Common\Extend\PhxCrypt;

class OfflinePaymentController extends InitController{
	private $dbModel = NULL;  //储存地址数据表对象
	
	//private $user_id = 0;  //当前登录的ID
	
	public function __construct(){
		parent::__construct();
		$this->dbModel = D('PayInfo');
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
	*	后台生成URL链接，获取URL上的参数详情
	*	@Author 9009123 (Lemonice)
	*	@return exit & 404[not found]
	*/
	public function getOrderInfo(){
		$param = I('param_code');
		if(trim($param) == ''){
			$this->error('请传加密串！');
		}
		
		if(isCheckWechat()){  //检查是否微信
			$param = str_replace(' ','+',$param);
		}
		$param = base64_decode(rawurldecode($param));

        $queryParts = explode('&', $param);
        $params = array();
        foreach ($queryParts as $value){
            $item = explode('=', $value);
            $params[$item[0]] = $item[1];
        }

        $order_sn_array = strstr($params['order_sn'],'_') !== false ? explode('_',$params['order_sn']) : array($params['order_sn']);
        $order_sn = $order_sn_array[0];

        if(!isset($order_sn_array[1]) || empty($order_sn_array[1]) || !is_numeric($order_sn_array[1])){
            $this->error('时间参数缺失！');
        }

        $diff_time = Time::localGettime() - $order_sn_array[1];
        if($diff_time > (60*30)){
            $this->error('此连接超时！');
        }

        $orderInfo = D('OrderInfoCenter')->where(array('order_sn' => $order_sn,'is_chinaskin' => 0))->find();
        if(!empty($orderInfo)){
            if(in_array($orderInfo['order_status'],array(2,3,4))|| in_array($orderInfo['shipping_status'],array(2)) || in_array($orderInfo['pay_status'],array(2))){
                $this->error('该订单状态异常，请联系客服');
            }
        }

		//先保存到session，支付时候需要用到订单金额
		$offline_order_amount = session('offline_order_amount');
		$offline_order_amount = is_array($offline_order_amount)&&!empty($offline_order_amount) ? $offline_order_amount : array();
		$offline_order_amount[$params['order_sn']] = $params['payamount'];

		session('offline_order_amount', $offline_order_amount);

		$this->success(array(
			'payAmount'=>(isset($params['payamount']) ? $params['payamount'] : ''),
			'payerName'=>(isset($params['payerName']) ? $params['payerName'] : ''),
			'orderSn'=>(isset($params['order_sn']) ? $params['order_sn'] : ''),
		));
	}
	
	/*
	*	创建订单 - 下一步：跳转到第三方平台支付
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function create(){
		$result = array('content'=>'','payment'=>7);
		$order  = array();
		$order["order_sn"]      = I('request.order_sn','','trim');
		$order["order_amount"]  = round(I('request.payamount',0,'floatval'),2);
		$order["consignee"]     = substr(I('request.payerName','','trim'),0,10);
		$order["mobile"]        = substr(I('request.payerTelephone','','trim'),0,13);
		$paymentType            = I('request.payment',0,'intval');
				
		if(empty($order["order_amount"])){
			$this->error('亲爱的用户，请填写支付金额！');
		}
		if(empty($order["consignee"])){
			$this->error('亲爱的用户，请填写填写付款人！');
		}
		if(empty($order["mobile"])){
			$this->error('亲爱的用户，请填写联系电话！');
		}
		if(empty($paymentType)){
			$this->error('亲爱的用户，请选择支付方式！');
		}
		if (empty($order["order_sn"])){
			$order["order_sn"] = $order["mobile"] . '_' . date("His");
		}
		switch($paymentType){
			case 4:  //支付宝
				$code = 'alipay';
			break;
			case 7:  //财付通
				$code = 'tenpay';
			break;
			case 8:  //快钱
				$code = 'kuaiqian';
			break;
			case 18:  //微信支付
				$code = 'wechatpay';
				$order['pay_name'] = '微信支付';
			break;
			default:  //网银
				$code = 'tenpay';
				$order["bank_id"]            = $paymentType;  //网银识别
			break;
		}
		$OrderObj = new Order();
		$pay = $OrderObj->getPaymentClass($code);  //实例化网银类
		$payment = D('Payment')->getPayment(strtolower($code));
		
		//$paymentType = $paymentType > 10 ? 7 : $paymentType;//  > 10 是网银，强制使用财付通
		
		
		$PayInfoModel = D('PayInfo');  //payment库
		$PayMultipleLogModel = D('PayMultipleLog');
		$time = Time::gmTime();
		$order_sn_child = (strstr($order["order_sn"],'_')===false) ? $order["order_sn"] .'_'. $time : $order["order_sn"];  //为了避免微信重复支付时候订单号重复的问题
		if(strstr($order["order_sn"],'_') !== false){
			$ordersn_array = explode('_',$order["order_sn"]);
			$ordersn = $ordersn_array[0];
		}else{
			$ordersn = $order["order_sn"];
		}
		
		//为了避免提示重复交易问题
		$order["order_sn"] = (strstr($order["order_sn"],'_')===false) ? $order["order_sn"] .'_'. $time : $order["order_sn"];
		$offline_order_amount = session('offline_order_amount');
		$pay_param = isset($offline_order_amount[$order["order_sn"]]) ? array('payamount'=>$offline_order_amount[$order["order_sn"]]) : array();
		
		$num = $PayMultipleLogModel->childIsPayed($order['order_sn'], $paymentType);  //查看该订单是否已经支付完成
		if ($num > 0){
			$this->error('该订单已经完成支付，无法重复操作！');
		}
		
		//获取当前订单的总金额
		//$order_money = (isset($pay_param['payamount']) && $pay_param['payamount'] > 0) ? $pay_param['payamount'] : 0;
		$order_money = 0;
		if($order_money <= 0){
			$order_money = D('OrderInfoCenter')->getOrderAmount($ordersn);
		}
		
		//检查该单号是否所有金额都支付完成了，如果完成了则不可再次支付
		/*if ($order_money > 0 && $PayInfoModel->orderMoneyIsPayed($ordersn,$paymentType, $order_money)>0){
			$this->error('此订单已经完成支付，无法重复操作！');
        }*/
		
		$order['pay_id'] = $paymentType;
		
		//记录相关信息到session，目的是为了供回调使用
		$array = array('mobile'=>$order["mobile"]);
		session('new_consignee', $array);
		session('pay_online_order_sn', $order["order_sn"]);
		
		//更新订单详情信息
		$order['order_money'] = $order_money;
		$id = $PayInfoModel->insertPayInfo($order, $time);
		
		
		$result["payment"]  = $paymentType;
		$payment = $OrderObj->unserialize_config($payment['pay_config']);
		$result["content"] = $pay->getCode($order,$payment);
		if(!$result["content"]){
			$this->error('您当前的站点不支持使用该支付方式，请重新选择！');
		}
		$this->success($result);
	}
	
	/*
	*	重新支付某个订单
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function RePay(){
		$order_id = I('request.order_id','','trim');  //订单ID
		
		if(intval($order_id) <= 0){
			$this->error('订单不存在');
		}
		
        $order = D('OrderInfoCenter')->field('order_sn,user_id,order_status,pay_status,consignee,pay_id,order_amount')->where("order_id = '$order_id'")->find();
		
		if(empty($order)){
			$this->error('订单不存在');
		}
		if($order['order_amount'] <= 0){
			$this->error('您的订单应付金额剩余0元，无需支付');
		}
		if($order['pay_id'] == 1){
			$this->error('您的订单是货到付款，无需在线支付');
		}
		
		$result = array('content'=>'','payment'=>7);
		$paymentType            = $order['pay_id'];
		
		switch($paymentType){
			case 4:  //支付宝
				$code = 'alipay';
			break;
			case 7:  //财付通
				$code = 'tenpay';
			break;
			case 8:  //快钱
				$code = 'kuaiqian';
			break;
			case 18:  //微信支付
				$code = 'wechatpay';
				$order['pay_name'] = '微信支付';
			break;
			default:  //网银
				$code = 'tenpay';
				$order["bank_id"]            = $paymentType;  //网银识别
			break;
		}
		$OrderObj = new Order();
		$pay = $OrderObj->getPaymentClass($code);  //实例化网银类
		$payment = D('Payment')->getPayment(strtolower($code));
		
		//$paymentType = $paymentType > 10 ? 7 : $paymentType;//  > 10 是网银，强制使用财付通
		
		
		$PayInfoModel = D('PayInfo');  //payment库
		$PayMultipleLogModel = D('PayMultipleLog');
		$time = Time::gmTime();
		$order_sn_child = (strstr($order["order_sn"],'_')===false) ? $order["order_sn"] .'_'. $time : $order["order_sn"];  //为了避免微信重复支付时候订单号重复的问题
		if(strstr($order["order_sn"],'_') !== false){
			$ordersn_array = explode('_',$order["order_sn"]);
			$ordersn = $ordersn_array[0];
		}else{
			$ordersn = $order["order_sn"];
		}
		
		//为了避免提示重复交易问题
		$order["order_sn"] = (strstr($order["order_sn"],'_')===false) ? $order["order_sn"] .'_'. $time : $order["order_sn"];
		$offline_order_amount = session('offline_order_amount');
		$pay_param = isset($offline_order_amount[$order["order_sn"]]) ? array('payamount'=>$offline_order_amount[$order["order_sn"]]) : array();
		
		$num = $PayMultipleLogModel->childIsPayed($order['order_sn'], $paymentType);  //查看该订单是否已经支付完成
		if ($num > 0){
			$this->error('该订单已经完成支付，无法重复操作！');
		}
		
		//获取当前订单的总金额
		//$order_money = (isset($pay_param['payamount']) && $pay_param['payamount'] > 0) ? $pay_param['payamount'] : 0;
		$order_money = 0;
		if($order_money <= 0){
			$order_money = D('OrderInfoCenter')->getOrderAmount($ordersn);
		}
		
		//检查该单号是否所有金额都支付完成了，如果完成了则不可再次支付
		/*if ($order_money > 0 && $PayInfoModel->orderMoneyIsPayed($ordersn,$paymentType, $order_money)>0){
			$this->error('此订单已经完成支付，无法重复操作！');
        }*/
		
		//记录相关信息到session，目的是为了供回调使用
		session('pay_online_order_sn', $order["order_sn"]);
		
		//更新订单详情信息
		$order['order_money'] = $order_money;
		$id = $PayInfoModel->insertPayInfo($order, $time);
		
		
		$result["payment"]  = $paymentType;
		$payment = $OrderObj->unserialize_config($payment['pay_config']);
		$result["content"] = $pay->getCode($order,$payment);
		
		if(!$result["content"]){
			$this->error('您当前的站点不支持使用该支付方式，请重新选择！');
		}
		$this->success($result);
	}
	
	/*
	*	获取订单列表 - 离线支付页面
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function getOrderSnList(){
		$mobile = I('request.mobile','','trim');
		
		if(empty($mobile) || !preg_match("/^1[34578][0-9]{9}$/", $mobile)){
			$this->error('手机号码不存在或格式错误！');
		}
		$mobile = PhxCrypt::phxEncrypt($mobile);
		$asdasd = D('OrderInfoCenter');
        $where = "pay_status in (0,3) AND (tel = '$mobile' OR mobile = '$mobile') AND is_chinaskin = 0";
        $list = $asdasd->field('order_sn,consignee,order_amount,goods_amount,bonus,integral_money,shipping_fee,discount,money_paid,payment_discount,update_time')->where($where)->order('add_time DESC')->select();
		
		if(!empty($list)){
			foreach($list as $key=>$value){
				if($value['update_time'] >= Time::localStrtotime('2016-10-18 16:00:00') || $value['integral_money'] > 0){
					$order_amount = $value['order_amount'] + $value['money_paid'];
				}else{
					$order_amount = $value['goods_amount'] - $value['bonus'] - $value['integral_money'] + $value['shipping_fee'] - $value['discount'] - $value['payment_discount'];
				}
				$tmp = array(
					'order_sn'=>$value['order_sn'],
					'consignee'=>$value['consignee'],
					'order_amount'=>$order_amount,
				);
				$list[$key] = $tmp;
			}
		}
		$this->success($list);
	}
}