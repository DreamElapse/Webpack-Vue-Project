<?php
/**
 * ====================================
 * 财付通 API接口 - 第三方平台
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-05 14:59
 * ====================================
 * File: Tenpay.class.php
 * ====================================
 */
namespace Common\Extend\Payment;
use Common\Extend\Time;


class Tenpay{
    
	/*
	*	构造函数
	*	@Author 9009123 (Lemonice)
	*	@return void
	*/
    public function Tenpay(){
		
    }

    public function __construct(){
        $this->Tenpay();
    }

    /*
	*	生成支付代码
	*	@Author 9009123 (Lemonice)
	*	@param   array $order 订单信息
    *	@param   array $payment 支付方式信息
	*	@return string
	*/
    public function getCode($order, $payment){
        //支付配置
        $spname     = "财付通支付";
        $partner    = $payment['tenpay_account'];   //财付通商户号
        $key        = $payment['tenpay_key'];		//财付通密钥
		
		$return_url = $this->getRespondUrl('tenpay');			//显示支付结果页面,*替换成payReturnUrl.php所在路径
        $notify_url = siteUrl().'RespondPayment/CallBack.shtml?code=tenpay';			//支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径
        $bank_id    = empty($order["bank_id"]) ? "DEFAULT" : $order["bank_id"];

        /* 获取提交的订单号 */
        $out_trade_no   = $order['order_sn'];
        /* 获取提交的商品价格 */
        $order_price    = floatval($order['order_amount']);
        /* 支付方式 */
        $trade_mode     = 1;

        /* 商品价格（包含运费），以分为单位 */
        $total_fee = $order_price*100;

        /* 商品名称 */
        if(!empty($order['consignee'])){
            $desc = $order['consignee'];
        }else{
            $desc = "支付订单：".$order['order_sn'];
        }

        /* 创建支付请求对象 */
        $reqHandler = new \Common\Extend\Payment\Tenpay\RequestHandler();
        $reqHandler->init();
        $reqHandler->setKey($key);
        $reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");

        //----------------------------------------
        //设置支付参数 
        //----------------------------------------
        $reqHandler->setParameter("partner", $partner);
        $reqHandler->setParameter("out_trade_no", $out_trade_no);
        $reqHandler->setParameter("total_fee", $total_fee);  //总金额
        $reqHandler->setParameter("return_url", $return_url);
        $reqHandler->setParameter("notify_url", $notify_url);
        $reqHandler->setParameter("body", $desc);
        $reqHandler->setParameter("bank_type", $bank_id);  	  //银行类型，默认为财付通
        //用户ip
        $reqHandler->setParameter("spbill_create_ip", get_client_ip());//客户端IP
        $reqHandler->setParameter("fee_type", "1");               //币种
        $reqHandler->setParameter("subject",$order['order_sn']);          //商品名称，（中介交易时必填）

        //系统可选参数
        $reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
        $reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
        $reqHandler->setParameter("input_charset", "utf-8");   	  //字符集
        $reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号

        //业务可选参数
        $reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
        $reqHandler->setParameter("product_fee", "");        	  //商品费用
        $reqHandler->setParameter("transport_fee", "0");      	  //物流费用
//        $reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
//        $reqHandler->setParameter("time_expire", "");             //订单失效时间
        $reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
        $reqHandler->setParameter("goods_tag", "");               //商品标记
        $reqHandler->setParameter("trade_mode",$trade_mode);              //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
        $reqHandler->setParameter("transport_desc","");              //物流说明
        $reqHandler->setParameter("trans_type","1");              //交易类型
        $reqHandler->setParameter("agentid","");                  //平台ID
        $reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
        $reqHandler->setParameter("seller_id","");                //卖家的商户号
        $reqUrl = $reqHandler->getRequestURL();                   //请求的URL
        $params = $reqHandler->getAllParameters();
        $button  = '<br /><form style="text-align:center;" action="https://gw.tenpay.com/gateway/pay.htm" style="margin:0px;padding:0px" >';

        foreach ($params as $key=>$val){
            $button  .= "<input type='hidden' name='$key' value='$val' />";
        }

        $button  .= '<input id="tenpay_button" type="submit" value="财付通支付"></form><br />';

        return $button;
    }

    /*
	*	响应操作
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
    public function respond(){
    	/*记录接口请求日志*/
    	$param = $this->getParam();
    	$param['log'] = serialize(array_merge($_GET,$_POST));
    	$param['verify_type'] = !empty($_POST) ? 2 : 1;
    	
        $payment  = D('Home/Payment')->getPayment("tenpay");

        /* 创建支付应答对象 */
        $resHandler = new \Common\Extend\Payment\Tenpay\ResponseHandler();
        $resHandler->setKey($payment['tenpay_key']);

        //判断签名
        if($resHandler->isTenpaySign()) {

//             //通知id
//             $notify_id = $resHandler->getParameter("notify_id");
//             //商户订单号
//             $out_trade_no = $resHandler->getParameter("out_trade_no");
//             //财付通订单号
//             $transaction_id = $resHandler->getParameter("transaction_id");
//             //金额,以分为单位
//             $total_fee = $resHandler->getParameter("total_fee");
//             //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
//             $discount = $resHandler->getParameter("discount");
            //支付结果
            $trade_state = $resHandler->getParameter("trade_state");
            //交易模式,1即时到账
            $trade_mode = $resHandler->getParameter("trade_mode");

//             $time = date('Y-m-d H:i:s');            
//             error_log("tenpay_trde_no:$out_trade_no -- time:$time \n",3,ROOT_PATH.'temp/pay.log');
			$param['pay_result'] = 0;  //默认支付失败
			if(($trade_mode == '1' || $trade_mode == '2') && $trade_state == '0'){
				$param['pay_result'] = 1;  //支付成功
			}
			return $param;
        } 
		
        return false;
    }
    
	/*
	*	获取跳转返回的地址 - 独立方法的目的是为了异步返回
	*	@Author 9009123 (Lemonice)
	*	@param  $pay_code  支付代码
	*	@return array
	*/
	public function getRespondUrl($pay_code){
		return siteUrl() . 'RespondPayment/Skip.shtml?code=' . $pay_code;
	}
    
	/*
	*	根据接口返回信息获取需要更新的订单信息
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
    private function getParam(){
    	/* 改变订单状态 */
    	$param = array();
    	$param['order_sn'] = I('request.out_trade_no',0,'intval'); //商户订单号
    	$param['pay_id'] = 7; //支付方式
    	$discount = I('request.discount',0);
    	$total_fee = I('request.total_fee',0);
    	$param['order_amount'] = round(($total_fee+$discount)/100,2); //订单金额
		$time_start = I('request.time_start',NULL);
    	if ($time_start !== NULL){
    		$param['create_time'] = Time::gmstr2time($time_start);
    	}
		$time_end = I('request.time_end',NULL);
    	if ($time_end !== NULL){
    		$param['pay_time'] = Time::gmstr2time($time_end);
    	}
    	return $param;
    }
}

?>