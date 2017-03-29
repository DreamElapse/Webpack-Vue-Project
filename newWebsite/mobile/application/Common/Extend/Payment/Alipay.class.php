<?php
/**
 * ====================================
 * 支付宝 API接口 - 第三方平台
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-05 15:49
 * ====================================
 * File: Alipay.class.php
 * ====================================
 */
namespace Common\Extend\Payment;
use Common\Extend\Time;


class Alipay{
    private $_ROOT_PATH = 'Common/Extend/Payment/';
	private $charset = 'utf-8';  //编码
	
    /*
	*	构造函数
	*	@Author 9009123 (Lemonice)
	*	@return void
	*/
    public function Alipay(){
		$this->_ROOT_PATH = APP_PATH . $this->_ROOT_PATH;  //当前payment目录
    }

    public function __construct(){
        $this->Alipay();
    }

    /*
	*	生成支付代码
	*	@Author 9009123 (Lemonice)
	*	@param   array $order 订单信息
    *	@param   array $payment 支付方式信息
	*	@return string
	*/
    public function getCode($order, $payment){
        $alipay_config = array(
            'partner' => $payment['alipay_partner'],
            'seller_email' => $payment['alipay_account'],
            'key' => $payment['alipay_key'],
            'private_key_path' => $this->_ROOT_PATH . 'Alipay/key/rsa_private_key.pem',
            'ali_public_key_path' => $this->_ROOT_PATH . 'Alipay/key/alipay_public_key.pem',
            'sign_type' => 'rsa',
            'input_charset' => $this->charset,
            'cacert' => $this->_ROOT_PATH . 'Alipay/cacert.pem',
            'transport' => 'http',
        );

        /**************************请求参数**************************/

        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url = siteUrl().'RespondPayment/CallBack/code/alipay.shtml';
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $return_url = $this->getRespondUrl('alipay');
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //商户订单号
        $out_trade_no = $order['order_sn'];
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = $order['order_sn'];
        //必填

        //付款金额
        $total_fee = round(floatval($order['order_amount']),2);
        //必填

        //商品展示地址
        $show_url = $_SERVER['HTTP_REFERER'];
        //必填，需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html

        //订单描述
        $body = '';
        //选填

        //超时时间
        $it_b_pay = '';
        //选填

        //钱包token
        $extern_token = '';
        //选填


        /************************************************************/

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "alipay.wap.create.direct.pay.by.user",
            "partner" => trim($alipay_config['partner']),
            "seller_id" => trim($alipay_config['partner']),
            "payment_type"	=> $payment_type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $return_url,
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "total_fee"	=> $total_fee,
            "show_url"	=> $show_url,
            "body"	=> $body,
            "it_b_pay"	=> $it_b_pay,
            "extern_token"	=> $extern_token,
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new \Common\Extend\Payment\Alipay\AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "支付宝支付");
        return $html_text;

    }

    /*
	*	响应操作
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
	public function respond(){
        /* 记录接口请求日志 */
        $param = $this->getParam();
        $param['log'] = serialize(array_merge($_GET, $_POST));
        $param['verify_type'] = 1;
        
        //获取支付宝信息
        $payment = D('Home/Payment')->getPayment("alipay");
        $alipay_config = array(
            'partner' => $payment['alipay_partner'],
            'seller_email' => $payment['alipay_account'],
            'key' => $payment['alipay_key'],
            'private_key_path' => $this->_ROOT_PATH . 'Alipay/key/rsa_private_key.pem',
            'ali_public_key_path' => $this->_ROOT_PATH . 'Alipay/key/alipay_public_key.pem',
            'sign_type' => 'rsa',
            'input_charset' => $this->charset,
            'cacert' => $this->_ROOT_PATH . 'Alipay/cacert.pem',
            'transport' => 'http',
        );
        $alipayNotify = new \Common\Extend\Payment\Alipay\AlipayNotify($alipay_config);
        if (empty($_GET)) {
            return false;
        }
        $_GET = $this->paraFilterCode($_GET);
        $verify_result = $alipayNotify->verifyReturn();
		
        $param['pay_result'] = 0;  //默认支付失败
		if ($verify_result) {
			$param['pay_result'] = 1;  //支付成功
		}
		return $param;

    }


	/*
	*	异步通知操作
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
    public function notify(){
        //获取支付宝信息
        $payment = D('Home/Payment')->getPayment("alipay");

        $alipay_config = array(
            'partner' => $payment['alipay_partner'],
            'seller_email' => $payment['alipay_account'],
            'key' => $payment['alipay_key'],
            'private_key_path' => $this->_ROOT_PATH . 'Alipay/key/rsa_private_key.pem',
            'ali_public_key_path' => $this->_ROOT_PATH . 'Alipay/key/alipay_public_key.pem',
            'sign_type' => 'rsa',
            'input_charset' => strtolower('utf-8'),
            'cacert' => $this->_ROOT_PATH . 'Alipay/cacert.pem',
            'transport' => 'http',
        );

        $alipayNotify = new \Common\Extend\Payment\Alipay\AlipayNotify($alipay_config);
        if (empty($_POST)) {
            return false;
        }
        $_POST = $this->paraFilterCode($_POST);


        $verify_result = $alipayNotify->verifyNotify();
        if ($verify_result) {

            $param['order_sn'] = I('request.out_trade_no','','trim'); //订单号
            $param['pay_id'] = 4; //支付方式
            $param['order_amount'] = I('request.total_fee',0);
            $param['log'] = serialize($_POST);
            $param['verify_type'] = 2;


			/*
			if (empty($doc->getElementsByTagName("notify")->item(0)->nodeValue)) {
                return false;
            }*/

			$trade_status = I('request.trade_status','','trim');
			$param['pay_result'] = 0;  //默认支付失败
            if(in_array($trade_status, array('TRADE_FINISHED', 'TRADE_SUCCESS'))){
                $param['pay_result'] = 1;  //支付成功
            }
			return $param;
        }
        return false;
    }


    public function F2f($order,$payment){
        import('Common/Extend/QrCode');
        require_once $this->_ROOT_PATH.'Alipay/alipaySdk/AopSdk.php';
        $alipay_config = array(
            'partner' => $payment['alipay_partner'],
            'seller_email' => $payment['alipay_account'],
            'key' => $payment['alipay_key'],
            'private_key_path' => $this->_ROOT_PATH . 'Alipay/key/rsa_private_key_f2f.pem',
            'ali_public_key_path' => $this->_ROOT_PATH . 'Alipay/key/rsa_public_key_f2f.pem',
            'app_id'    => '2016040701274038',
            'sign_type' => 'rsa',
            'input_charset' => strtolower('utf-8'),
            'cacert' => getcwd() . '\\includes\modules\payment\alipay\cacert.pem',
            'transport' => 'http',
        );


        //服务器异步通知页面路径
        $notify_url = siteUrl().'RespondPayment/callBackF2f/code/alipay.shtml';
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        //(必填) 商户网站订单系统中唯一订单号，64个字符以内，只能包含字母、数字、下划线，
        //$outTradeNo = date('YmdHis').mt_rand(1000,9999);
        $outTradeNo = $order['order_sn'];
        //(必填) 订单标题，粗略描述用户的支付目的。如“xxx品牌xxx门店当面付扫码消费”
        //$subject = '瓷肌手机商城@'.$order['order_sn'];
        $subject = $order['subject'];

        //(必填) 订单总金额，单位为元，不能超过1亿元
        //如果同时传入了【打折金额】,【不可打折金额】,【订单总金额】三者,则必须满足如下条件:【订单总金额】=【打折金额】+【不可打折金额】
        //$totalAmount = 0.01;
        $totalAmount = round(floatval($order['order_amount']),2);


        // (不推荐使用) 订单可打折金额，可以配合商家平台配置折扣活动，如果订单部分商品参与打折，可以将部分商品总价填写至此字段，默认全部商品可打折
        // 如果该值未传入,但传入了【订单总金额】,【不可打折金额】 则该值默认为【订单总金额】- 【不可打折金额】
        //String discountableAmount = "1.00"; //

        // (可选) 订单不可打折金额，可以配合商家平台配置折扣活动，如果酒水不参与打折，则将对应金额填写至此字段
        // 如果该值未传入,但传入了【订单总金额】,【打折金额】,则该值默认为【订单总金额】-【打折金额】
        //	$undiscountableAmount = "0.01";

        // 卖家支付宝账号ID，用于支持一个签约账号下支持打款到不同的收款账号，(打款到sellerId对应的支付宝账号)
        // 如果该字段为空，则默认为与支付宝签约的商户的PID，也就是appid对应的PID
        // $sellerId = "";

        // 订单描述，可以对交易或商品进行一个详细地描述，比如填写"购买商品2件共15.00元"
        // $body = "购买商品2件共15.00元";
        $body = '';


        // 支付超时，线下扫码交易定义为5分钟
        $timeExpress = "5m";


        $parameter = array(
            'out_trade_no' => $outTradeNo,
            'total_amount' => $totalAmount,
            'timeout_express' => $timeExpress,
            'subject'       => $subject,
            'body'      => $body
        );

        $aop = new \AopClient();
        $aop->appId = $alipay_config['app_id'];
        $aop->rsaPrivateKeyFilePath = $alipay_config['private_key_path'];
        $aop->alipayPublicKey = $alipay_config['ali_public_key_path'];
        $request = new \AlipayTradePrecreateRequest();
        $request->setNotifyUrl($notify_url);
        $request->setBizContent($this->getBizContent($parameter));
        $qrPay = $aop->execute($request);
        $response = $qrPay->alipay_trade_precreate_response;
        if(!empty($response) && ('10000' == $response->code)){
            return \QRcode::png($response->qr_code,null,'L','5',0);
        }else{
            return false;
        }

    }


    public function F2f_notify(){
        //获取支付宝信息
        $payment = D('Home/Payment')->getPayment("alipay");


        $alipay_config = array(
            'partner' => $payment['alipay_partner'],
            'seller_email' => $payment['alipay_account'],
            'key' => $payment['alipay_key'],
            'private_key_path' => $this->_ROOT_PATH . 'Alipay/key/rsa_private_key_f2f.pem',
            'ali_public_key_path' => $this->_ROOT_PATH . 'Alipay/key/rsa_public_key_f2f.pem',
            'app_id'    => '2016040701274038',
            'sign_type' => 'rsa',
            'input_charset' => strtolower('utf-8'),
            'cacert' => getcwd() . '\\includes\modules\payment\alipay\cacert.pem',
            'transport' => 'http',
        );

        $alipayNotify = new \Common\Extend\Payment\Alipay\AlipayNotify($alipay_config);

        if(empty($_POST)){
            return false;
        }

        $_POST['fund_bill_list'] = stripslashes($_POST['fund_bill_list']); //去掉JSON反斜杠
        $_POST = $this->paraFilterCode($_POST);

        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {

            $param['order_sn'] = I('request.out_trade_no','','trim'); //订单号
            $param['pay_id'] = 4; //支付方式
            $param['order_amount'] = I('request.total_fee',0);
            $param['log'] = serialize($_POST);
            $param['verify_type'] = 2;

            $trade_status = I('request.trade_status','','trim');
            $param['pay_result'] = 0;  //默认支付失败
            if(in_array($trade_status, array('TRADE_FINISHED', 'TRADE_SUCCESS'))){
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
	*	除去数组中的code参数
	*	@Author 9009123 (Lemonice)
	*	@param $para
	*	@return array
	*/
	private function paraFilterCode($para){
		$para_filter = array();
		while (list ($key, $val) = each($para)) {
			if ($key == "code") continue;
			else    $para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
	
	/*
	*	响应操作
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
    private function getParam() {
        /* 改变订单状态 */
        $param = array();
        $param['order_sn'] = I('request.out_trade_no','','trim'); //商户订单号
        $param['pay_id'] = 4; //支付方式
        $discount = I('request.discount',0);
        $total_fee = I('request.total_fee',0);
        $param['order_amount'] = $total_fee + $discount; //订单金额
		$gmt_create = I('request.gmt_create',NULL);
        if ($gmt_create !== NULL) {
            $param['create_time'] = Time::gmstr2time($gmt_create);
        }
		$gmt_payment = I('request.gmt_payment',NULL);
        if ($gmt_payment !== NULL) {
            $param['pay_time'] = Time::gmstr2time($gmt_payment);
        }
        return $param;
    }


    /**
     * json
     * @param $params
     * @return mixed|string
     */
    private function getBizContent($params){
        if(version_compare(PHP_VERSION,'5.4.0','>')){ //兼容5.4一下版本
            $bizContent = json_encode($params,JSON_UNESCAPED_UNICODE);
        }else{
            $bizContent = "{";
            foreach ($params as $k=>$v){
                $bizContent.= "\"".$k."\":\"".$v."\",";
            }
            $bizContent = substr($bizContent,0,-1);

            $bizContent.= "}";
        }
        return $bizContent;
    }
}

?>