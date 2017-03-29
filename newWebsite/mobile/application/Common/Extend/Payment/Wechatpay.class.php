<?php
/**
 * ====================================
 * 微信支付 API接口 - 第三方平台
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-28 14:59
 * ====================================
 * File: Wechatpay.class.php
 * ====================================
 */
namespace Common\Extend\Payment;
use Common\Extend\Time;
class Wechatpay{
	public $JsApi_pub_object = NULL;
	
    public function __construct(){
        $this->Wechatpay();
    }
    
    public function Wechatpay(){
		import('Common/Extend/Payment/Wechatpay/WxPay');
		$this->JsApi_pub_object = new \JsApi_pub();
    }

    /**
     * 面对面支付
     * @param $order
     * @return bool
     */
    public function F2f($order){
        $code_url =  $this->get_code_url($order);
        if(is_array($code_url)){
            return json_encode($code_url);
        }
        if(!empty($code_url)){
//            import('Common/Extend/Payment/Wechatpay/QrCode');
            import('Common/Extend/QrCode');
            return \QRcode::png($code_url,null,'L','5',0);
        }else{
            return false;
        }
    }

    /**
     * 取支付连接
     * @param $order
     * @return mixed
     */
    public function get_code_url($order){


        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub();
        $returnUrl = $unifiedOrder->notify_url;
        if(strpos($returnUrl,'http') === false){
            $returnUrl = 'http://'.$returnUrl;
        }

        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        //$order['order_amount'] = 0.01;测试设置
        if(isset($order['subject']) && !empty($order['subject'])){
            $unifiedOrder->setParameter('body', "{$order['subject']}");//商品描述
        }else{
            $unifiedOrder->setParameter('body', "{$order['order_sn']}");//商品描述
        }
        $unifiedOrder->setParameter('out_trade_no', "{$order['order_sn']}");//订单号
        $unifiedOrder->setParameter("total_fee",intval($order['order_amount'] * 100));//总金额
        //$unifiedOrder->setParameter("total_fee", 1);//测试设置为1分钱
        $unifiedOrder->setParameter("notify_url", $returnUrl);//通知地址
        $unifiedOrder->setParameter("trade_type", "NATIVE");//交易类型

        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID

        return $unifiedOrder->getCode_url();

    }
	/*
	*	生成支付代码
	*	@Author 9009123 (Lemonice)
	*	@param   array $order 订单信息
    *	@param   array $payment 支付方式信息
	*	@return string
	*/
	public function getCode($order, $payment){
		/*if(session('?sopenid')){  //openid有存在
			echo $this->goToPay($order, $payment);  //如果openid存在，直接进行支付
			exit;
		}*/
		$result = isCheckWechat();
		if($result == false){  //不是微信打开网页
			return false;  //不可支付
		}
		//则保存一下订单信息
		$session = array(
			'order'=>array(
				'order_sn'=>$order['order_sn'],
				'order_amount'=>$order['order_amount'],
			),
			'payment'=>array()
		);
		session('wechatpay_data', $session);
		$button = '<form action="'.siteUrl().'OnlinePayment/getOpenId.shtml">';  //此地址是为了先获取openid，然后再操作其他微信的接口
		$button .= "<input type='hidden' name='code' value='wechatpay' />";
        $button .= '<input id="wechatpay_button" type="submit" value="微信支付"></form>';
		return $button;
    }
	
	/*
	*	开始支付微信
	*	@Author 9009123 (Lemonice)
	*	@param   array $order 订单信息
    *	@param   array $payment 支付方式信息
	*	@return string
	*/
    public function goToPay($order, $payment){
        @$openId = session('sopenid');
		
        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub();
        $returnUrl = $unifiedOrder->notify_url;
        if(strpos($returnUrl,'http') === false){
            $returnUrl = 'http://'.$returnUrl;
        }

        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
		
        $unifiedOrder->setParameter('openid', "{$openId}");
        $unifiedOrder->setParameter('body', "{$order['order_sn']}");//商品描述
        $unifiedOrder->setParameter('out_trade_no', "{$order['order_sn']}");//订单号
        $unifiedOrder->setParameter("total_fee",$order['order_amount'] * 100);//总金额
        //$unifiedOrder->setParameter("total_fee", 1);//测试设置为1分钱
        $unifiedOrder->setParameter("notify_url", $returnUrl);//通知地址
        $unifiedOrder->setParameter("trade_type", "JSAPI");//交易类型

        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID

        $prepay_id = $unifiedOrder->getPrepayId();
        $jsApi = $this->JsApi_pub_object;
        $jsApi->setPrepayId($prepay_id);
        $jsApiParameters = $jsApi->getParameters();
        $pay_online = $this->getButton($jsApiParameters, $unifiedOrder->return_url);
        return $pay_online;
    }
	
	/*
	*	响应操作  -  异步操作
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
    public function respond(){
        $notify = new \Notify_pub();
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        /*记录接口请求日志*/
        $param['order_sn'] = $notify->data['out_trade_no'];
        $param['pay_id'] = 18;
        $param['order_amount'] = ($notify->data['total_fee']/100);
        $param['log'] = serialize(json_decode(json_encode(@simplexml_load_string($xml,NULL,LIBXML_NOCDATA)),true));
        $param['verify_type'] = 2;
		
		
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "FAIL");//返回状态码
            $notify->setReturnParameter("return_msg", "签名失败");//返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();
		$param['returnXml'] = $returnXml;  //返回给微信的信息
        //以log文件形式记录回调信息
        if ($notify->checkSign() == TRUE) {
			$param['pay_result'] = 0;  //默认支付失败
            if ($notify->data["return_code"] == "SUCCESS" || $notify->data["result_code"] == "SUCCESS") {  //通信出错  ||  业务出错
				$param['pay_result'] = 1;  //支付成功
            }
			return $param;
        }
		return false;
    }
	
	/*
	*	响应操作  -  同步操作
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
	public function synchro(){
		$order_sn = session('pay_online_order_sn');    //新增订单的order_sn
		if($order_sn == ''){
			return false;
		}
		//为了避免微信重复支付时候订单号重复的问题 Add By 9009123(Lemonice)
		$order_sn_child = $order_sn;
		if(strstr($order_sn,'_') !== false){
			$tmp = explode('_', $order_sn);
			$order_sn = $tmp[0];
		}
		//获取支付状态
		$order = D('Home/PayInfo')->field('status,order_amount,pay_time')->where("`order_sn`='$order_sn' AND `pay_id` = 18")->find();
		$status = isset($order['status']) ? $order['status'] : 0;
		$order_amount = isset($order['order_amount']) ? $order['order_amount'] : 0;
		$pay_time = isset($order['pay_time']) ? $order['pay_time'] : 0;
		//获取支付状态
		$log = D('Home/PayLog')->where("`order_sn` = '$order_sn_child' AND `pay_id` = 18")->count();
		
		$param = array();
    	$param['order_sn'] = $order_sn_child; //商户订单号
    	$param['pay_id'] = 18; //支付方式
    	$param['order_amount'] = $order_amount; //订单金额
		if(!$pay_time || $pay_time <= 0){
			$param['pay_time'] = Time::gmTime();
		}
    	$param['log'] = serialize(array_merge($_GET,$_POST));
    	$param['verify_type'] = 1;
		
		$param['pay_result'] = 0;  //默认支付失败
		if($status == 1 && $log > 0){
			$param['pay_result'] = 1;  //支付成功
			return $param;
		}
		return false;
	}
	
	/*
	*	获取唤起微信支付的JS代码
	*	@Author 9009123 (Lemonice)
	*	@param  $params  微信接口的相关参数
	*	@param  $notify_url  获取到openid后的跳转回来地址
	*	@return string
	*/
	public function getButton($params, $notify_url){
		//调用微信JS api 支付
		$string = '<script type="text/javascript">
		function jsApiCall(){
			WeixinJSBridge.invoke("getBrandWCPayRequest",'.$params.',function(res){
				WeixinJSBridge.log(res.err_msg);
				if(res.err_desc) {
					alert(res.err_code+"调试信息："+res.err_desc+res.err_msg);
				}
				if(res.err_msg.indexOf("ok")>0){
					window.location.href="'.$notify_url.'";
				}
			});
		}
		function callpay(){
			if (typeof WeixinJSBridge == "undefined"){
			    if( document.addEventListener ){
			        document.addEventListener("WeixinJSBridgeReady", jsApiCall, false);
			    }else if (document.attachEvent){
			        document.attachEvent("WeixinJSBridgeReady", jsApiCall);
			        document.attachEvent("onWeixinJSBridgeReady", jsApiCall);
			    }
			}else{
			    jsApiCall();
			}
		}
		callpay();
		</script>';
		return $string;
	}
	
	/*
	*	获取微信code的地址
	*	@Author 9009123 (Lemonice)
	*	@return string
	*/
	public function createOauthUrlForCode($scope = "snsapi_base"){
		return $this->JsApi_pub_object->createOauthUrlForCode($scope);
	}
	
	/*
	*	设置微信code
	*	@Author 9009123 (Lemonice)
	*	@return string
	*/
	public function setCode($code){
		return $this->JsApi_pub_object->setCode($code);
	}
	
	/*
	*	获取微信openid
	*	@Author 9009123 (Lemonice)
	*	@return string
	*/
	public function getOpenid(){
		return $this->JsApi_pub_object->getOpenid();
	}
    
    //isCheckWechat
}