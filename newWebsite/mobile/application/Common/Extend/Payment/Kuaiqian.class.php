<?php
/**
 * ====================================
 * 快钱 API接口 - 第三方平台
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-04 11:37
 * ====================================
 * File: Kuaiqian.class.php
 * ====================================
 */
namespace Common\Extend\Payment;
use Common\Extend\Time;


class Kuaiqian{
	private $_ROOT_PATH = 'Common/Extend/Payment/';
    
	/*
	*	构造函数
	*	@Author 9009123 (Lemonice)
	*	@return void
	*/
    public function Kuaiqian(){
		$this->_ROOT_PATH = APP_PATH . $this->_ROOT_PATH;  //当前payment目录
    }

    public function __construct(){
        $this->Kuaiqian();
    }

    /*
	*	生成支付代码
	*	@Author 9009123 (Lemonice)
	*	@param   array $order 订单信息
    *	@param   array $payment 支付方式信息
	*	@return string
	*/
    public function getCode($order, $payment){
        $kq_target = "https://www.99bill.com/mobilegateway/recvMerchantInfoAction.htm";

        $kq_merchantAcctId = trim($payment['kq_account']);   //*  商家用户编号		(30)
//        $kq_merchantAcctId = "1001213884201";   //*  沙盒

        $kq_inputCharset = "1";    //1 ->  UTF-8	2 -> GBK	3 -> GB2312   default: 1	(2)
		
		$kq_pageUrl = $this->getRespondUrl('kuaiqian');    //   直接跳转页面	(256)
		$kq_bgUrl = siteUrl() . 'RespondPayment/CallBack.shtml?code=kuaiqian';    //   后台通知页面	(256)
        $kq_version = "mobile1.0";    //*	 版本  固定值 v2.0	(10)
        $kq_language = "1";    //*  默认 1 ， 显示 汉语	(2)
        $kq_signType = "4";   //*  固定值 1 表示 MD5 加密方式 , 4 表示 PKI 证书签名方式	(2)


        $kq_payerName = "";    //   英文或者中文字符	(32)
        $kq_payerContactType = "";  //  支付人联系类型  固定值： 1  代表电子邮件方式 (2)
        $kq_payerContact = "";    //	 支付人联系方式	(50)
        $kq_orderId = $order['order_sn'];    //*  字母数字或者, _ , - ,  并且字母数字开头 并且在自身交易中式唯一	(50)
        $kq_orderAmount = floatval($order['order_amount']) * 100;    //*	  字符金额 以 分为单位 比如 10 元， 应写成 1000	(10)
        //$kq_orderAmount = 1; //测试使用
        $kq_orderTime = Time::localDate('YmdHis', $order['add_time']);  //*  交易时间  格式: 20110805110533
        $kq_productName = "";    //	  商品名称英文或者中文字符串(256)
        $kq_productNum = "";    //	  商品数量	(8)
        $kq_productId = "";   //    商品代码，可以是 字母,数字,-,_   (20)
        $kq_productDesc = "";    //	  商品描述， 英文或者中文字符串  (400)
        $kq_ext1 = "";   //	  扩展字段， 英文或者中文字符串，支付完成后，按照原样返回给商户。 (128)
        $kq_ext2 = "";
//       $kq_payType		= "00";	//*   支付方式 固定值: 00, 10, 11, 12, 13, 14, 15, 16, 17  (2)
//        00: 其他支付
//        10: 银行卡支付
//        11: 电话支付
//        12: 快钱账户支付
//        13: 线下支付
//        14: 企业网银在线支付
//        15: 信用卡在线支付
//        17: 预付卡支付
//        *B2B 支付需要单独申请，默认不开通
        if (!empty($order['bankId'])) {
            if ($order['bankId'] == 'PSBC') {
                $kq_payType = "21-2";
            } else {
                $kq_payType = "21-1";
            }
        } else {
            $kq_payType = "00";
            $kq_bankId = "";
        }
        $kq_redoFlag = "0";   // 同一订单禁止重复提交标志  固定值 1 、 0。1 表示同一订单只允许提交一次 ； 0 表示在订单没有支付成功状态下 可以重复提交； 默认 0
        $kq_pid = "";   //  合作伙伴在快钱的用户编号 (30)


        /* 生成加密签名串 请务必按照如下顺序和规则组成加密串！*/
        $signmsgval = '';
        $signmsgval = $this->append_param($signmsgval, "inputCharset", $kq_inputCharset);
        $signmsgval = $this->append_param($signmsgval, "pageUrl", $kq_pageUrl);
        $signmsgval = $this->append_param($signmsgval, "bgUrl", $kq_bgUrl);
        $signmsgval = $this->append_param($signmsgval, "version", $kq_version);
        $signmsgval = $this->append_param($signmsgval, "language", $kq_language);
        $signmsgval = $this->append_param($signmsgval, "signType", $kq_signType);
        $signmsgval = $this->append_param($signmsgval, "merchantAcctId", $kq_merchantAcctId);
        $signmsgval = $this->append_param($signmsgval, "payerName", $kq_payerName);
        $signmsgval = $this->append_param($signmsgval, "payerContactType", $kq_payerContactType);
        $signmsgval = $this->append_param($signmsgval, "payerContact", $kq_payerContact);
        $signmsgval = $this->append_param($signmsgval, "orderId", $kq_orderId);
        $signmsgval = $this->append_param($signmsgval, "orderAmount", $kq_orderAmount);
        $signmsgval = $this->append_param($signmsgval, "orderTime", $kq_orderTime);
        $signmsgval = $this->append_param($signmsgval, "productName", $kq_productName);
        $signmsgval = $this->append_param($signmsgval, "productNum", $kq_productNum);
        $signmsgval = $this->append_param($signmsgval, "productId", $kq_productId);
        $signmsgval = $this->append_param($signmsgval, "productDesc", $kq_productDesc);
        $signmsgval = $this->append_param($signmsgval, "ext1", $kq_ext1);
        $signmsgval = $this->append_param($signmsgval, "ext2", $kq_ext2);
        $signmsgval = $this->append_param($signmsgval, "payType", $kq_payType);
        $signmsgval = $this->append_param($signmsgval, "bankId", $kq_bankId);
        $signmsgval = $this->append_param($signmsgval, "redoFlag", $kq_redoFlag);
        $signmsgval = $this->append_param($signmsgval, "pid", $kq_pid);

        $signmsgval = rtrim($signmsgval,"&");

        $priv_key   = file_get_contents($this->_ROOT_PATH . 'Kuaiqian/99bill-rsa.pem');
        $pkeyid     = openssl_get_privatekey($priv_key);

        // compute signature
        openssl_sign($signmsgval,$signMsg,$pkeyid);

        // free the key from memory
        openssl_free_key($pkeyid);

        $kq_sign_msg = base64_encode($signMsg);

        $kq_get_url = $kq_target.'?'.$signmsgval."&signMsg=".$kq_sign_msg;


        $def_url = '<div style="text-align:center"><form name="kqPay" style="text-align:center;" method="get" action="'.$kq_get_url.'">';
        $def_url .= "<input type='hidden' name='inputCharset' value='" . $kq_inputCharset . "' />";
        $def_url .= "<input type='hidden' name='bgUrl' value='" . $kq_bgUrl . "' />";
        $def_url .= "<input type='hidden' name='pageUrl' value='" . $kq_pageUrl . "' />";
        $def_url .= "<input type='hidden' name='version' value='" . $kq_version . "' />";
        $def_url .= "<input type='hidden' name='language' value='" . $kq_language . "' />";
        $def_url .= "<input type='hidden' name='signType' value='" . $kq_signType . "' />";
        $def_url .= "<input type='hidden' name='signMsg' value='" . $kq_sign_msg . "' />";
        $def_url .= "<input type='hidden' name='merchantAcctId' value='" . $kq_merchantAcctId . "' />";
        $def_url .= "<input type='hidden' name='payerName' value='" . $kq_payerName . "' />";
        $def_url .= "<input type='hidden' name='payerContactType' value='" . $kq_payerContactType . "' />";
        $def_url .= "<input type='hidden' name='payerContact' value='" . $kq_payerContact . "' />";
        $def_url .= "<input type='hidden' name='orderId' value='" . $kq_orderId . "' />";
        $def_url .= "<input type='hidden' name='orderAmount' value='" . $kq_orderAmount . "' />";
        $def_url .= "<input type='hidden' name='orderTime' value='" . $kq_orderTime . "' />";
        $def_url .= "<input type='hidden' name='productName' value='" . $kq_productName . "' />";
        $def_url .= "<input type='hidden' name='payType' value='" . $kq_payType . "' />";
        $def_url .= "<input type='hidden' name='productNum' value='" . $kq_productNum . "' />";
        $def_url .= "<input type='hidden' name='productId' value='" . $kq_productId . "' />";
        $def_url .= "<input type='hidden' name='productDesc' value='" . $kq_productDesc . "' />";
        $def_url .= "<input type='hidden' name='ext1' value='" . $kq_ext1 . "' />";
        $def_url .= "<input type='hidden' name='ext2' value='" . $kq_ext2 . "' />";
        $def_url .= "<input type='hidden' name='bankId' value='" . $kq_bankId . "' />";
        $def_url .= "<input type='hidden' name='redoFlag' value='" . $kq_redoFlag . "' />";
        $def_url .= "<input type='hidden' name='pid' value='" . $kq_pid . "' />";
        $def_url .= "<input id='kuaiqian_submit' type='submit' name='submit' value='立即使用快钱支付' />";
        $def_url .= "</form></div></br>";

        return $def_url;
    }

    /*
	*	响应操作
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
    public function respond(){
        /*记录接口请求日志*/
        $param = $this->getParam();
        $param['log'] = serialize(array_merge($_GET, $_POST));
        $param['verify_type'] = !empty($_POST) ? 2 : 1;

        //生成加密串。必须保持如下顺序。
        $merchant_signmsgval = '';
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "merchantAcctId", $_REQUEST['merchantAcctId']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "version", $_REQUEST['version']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "language", $_REQUEST['language']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "signType", $_REQUEST['signType']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "payType", $_REQUEST['payType']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "bankId", $_REQUEST['bankId']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "orderId", $_REQUEST['orderId']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "orderTime", $_REQUEST['orderTime']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "orderAmount", $_REQUEST['orderAmount']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "bindCard", $_REQUEST['bindCard']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "bindMobile", $_REQUEST['bindMobile']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "dealId", $_REQUEST['dealId']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "bankDealId", $_REQUEST['bankDealId']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "dealTime", $_REQUEST['dealTime']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "payAmount", $_REQUEST['payAmount']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "fee", $_REQUEST['fee']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "ext1", $_REQUEST['ext1']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "ext2", $_REQUEST['ext2']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "payResult", $_REQUEST['payResult']);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval, "errCode", $_REQUEST['errCode']);


        $trans_body = rtrim($merchant_signmsgval,"&");
        $MAC        = base64_decode($_REQUEST['signMsg']);

		$cert       = file_get_contents($this->_ROOT_PATH . 'Kuaiqian/99bill.cert.rsa.20340630.cer');
        $pubkeyid   = openssl_get_publickey($cert);
        $pay_result = openssl_verify($trans_body,$MAC,$pubkeyid);
		
        $param['pay_result'] = $pay_result;
		return $param;
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
	*	将变量值不为空的参数组成字符串
	*	@Author 9009123 (Lemonice)
	*	@param   string $strs 参数字符串
    *	@param   string $key 参数键名
    *	@param   string $val 参数键对应值
	*	@return string
	*/
    private function append_param($strs, $key, $val){
        if ($strs != "") {
            if ($key != '' && $val != '') {
                $strs .= '&' . $key . '=' . $val;
            }
        } else {
            if ($val != '') {
                $strs = $key . '=' . $val;
            }
        }
        return $strs;
    }

    /*
	*	根据接口返回信息获取需要更新的订单信息
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
    private function getParam(){
		/* 改变订单状态 */
        $param = array();
        $param['order_sn'] = I('request.orderId',0,'trim'); //商户订单号
        $param['pay_id'] = 8; //支付方式
        $param['order_amount'] = round(I('request.orderAmount',0) / 100, 2); //订单金额
		$orderTime = I('request.orderTime',NULL);
		$dealTime = I('request.dealTime',NULL);
        if ($orderTime !== NULL) {
            $param['create_time'] = Time::gmstr2time($orderTime);
        }
        if ($dealTime !== NULL) {
            $param['pay_time'] = Time::gmstr2time($dealTime);
        }
        return $param;
    }
}

?>