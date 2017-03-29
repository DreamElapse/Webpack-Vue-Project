<?php
/**
 * ====================================
 * 扫码支付二维码生成接口
 * ====================================
 * Author: 9004396
 * Date: 2016-09-28 16:09
 * ====================================
 * File:ScanPaymentController.class.php
 * ====================================
 */
namespace Api\Controller;
use Common\Controller\ApiController;
use Common\Extend\Order\Order;
use Common\Extend\Time;

class ScanPaymentController extends ApiController {

    //密钥权限
    protected $_permission = array(
        'index' => array('scan_payment')
    );


    private $_payType = array(
        '4' => 'alipay',
        '7' => 'tenpay',
        '8' => 'kuaiqian',
        '18'=> 'wechatpay',
    );

    public function index(){
        $params = I('post.');
        $pay_id = empty($params['pay_id']) > 100 ? 7 : $params['pay_id'];
        if(empty($params['order_sn'])){
            $this->error('20005');
        }
        if(empty($params['subject'])){
            $this->error('20007');
        }
        if(empty($params['order_amount'])){
            $this->error('20006');
        }
        $para = $this->paraFilter($params);

        $isSign = $this->verify($para,$params['sign'],$para['key']);
        if(!$isSign){
            $this->error('20002');
        }
        $pay = null;
        $payment = null;
        $OrderObj = new Order();
        switch ($pay_id){
            case 4:
                $pay = $OrderObj->getPaymentClass($this->_payType[$pay_id]);  //实例化网银类
                $payment = D('Home/Payment')->getPayment($this->_payType[$pay_id]);
                break;
            case 7:
                break;
            case 8:
                break;
            case 18:
                $pay = $OrderObj->getPaymentClass($this->_payType[$pay_id]);  //实例化网银类
                break;
        }
        if(is_null($pay)){
            $this->error('20001');
        }
        if((is_null($payment) || empty($payment))&& $pay_id!=18){
            $this->error('20012');
        }
		
		
		$PayInfoModel = D('Home/PayInfo');  //payment库
		$PayMultipleLogModel = D('Home/PayMultipleLog');
		$time = Time::gmTime();
		$order_sn_child = (strstr($para["order_sn"],'_')===false) ? $para["order_sn"] .'_'. $time : $para["order_sn"];  //为了避免微信重复支付时候订单号重复的问题
		if(strstr($para["order_sn"],'_') !== false){
			$ordersn_array = explode('_',$para["order_sn"]);
			$ordersn = $ordersn_array[0];
		}else{
			$ordersn = $para["order_sn"];
		}
		
		//为了避免提示重复交易问题
		$para["order_sn"] = (strstr($para["order_sn"],'_')===false) ? $para["order_sn"] .'_'. $time : $para["order_sn"];
		$offline_order_amount = session('offline_order_amount');
		$pay_param = isset($offline_order_amount[$para["order_sn"]]) ? array('payamount'=>$offline_order_amount[$para["order_sn"]]) : array();
		$num = $PayMultipleLogModel->childIsPayed($para['order_sn'], $pay_id);  //查看该订单是否已经支付完成
		if ($num > 0){
			$this->error('20003');
		}
		
		//获取当前订单的总金额
		//$order_money = (isset($params['order_amount']) && $params['order_amount'] > 0) ? $params['order_amount'] : 0;
		$order_money = 0;
		if($order_money <= 0){
			$order_money = D('Home/OrderInfoCenter')->getOrderAmount($ordersn);
		}
		
		//检查该单号是否所有金额都支付完成了，如果完成了则不可再次支付
		/*if ($order_money > 0 && $PayInfoModel->orderMoneyIsPayed($ordersn,$paymentType, $order_money)>0){
			$this->error('20003');
        }*/

        $order = array(
            'site_id'       => $para['site_id'],
            'pay_id'        => $para['pay_id'],
            'order_sn'      => $para['order_sn'],
            'consignee'     => $para['name'],
			'order_money'   => $order_money,
            'order_amount'  => $para['order_amount']
        );
        //更新订单详情信息
        $id = $PayInfoModel->insertPayInfo($order,$time);
        if(empty($id) || $id < 0){
            $this->error('20004');
        }
        if(empty($payment)){
            $result = $pay->F2f($para);
        }else{
            $result = $pay->F2f($para,$payment);
        }
        if($result){
            $this->success(base64_encode($result));
        }else{
            $this->error('20008');
        }
    }
}