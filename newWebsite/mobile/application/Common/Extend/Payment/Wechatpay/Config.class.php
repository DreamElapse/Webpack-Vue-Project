<?php
namespace Common\Extend\Payment\Wechatpay;
use Home\Model\PaymentModel;
/**
 *    配置账号信息
 */
class Config{
    public $weChat_appId;  //微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
    public $weChat_appSecret; //JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
    public $weChat_mchId; //受理商ID，身份标识
    public $weChat_key; //商户支付密钥Key。审核通过后，在微信发送的邮件中查看
    public $api_call_url; //获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
    public $notify_url; //异步通知url，商户根据实际开发过程设定
	public $return_url;  //最终跳转显示结果的页面地址
    public $curl_timeout = 30; //本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒

    /**
     * 构造函数
     */
    public function __construct(){
        $this->weChat_appId = APPID;
		$this->weChat_appSecret = APPSECRET;
		
		$this->weChat_mchId = WECHAT_MACHINE_ID;
		$this->weChat_key = WECHAT_PAY_KEY;
		
		//$this->api_call_url = substr(siteUrl(),0,-1).$_SERVER['REQUEST_URI'];
		$this->api_call_url = 'http://q.chinaskin.cn'.$_SERVER['REQUEST_URI'];
		
		$this->return_url = siteUrl() . 'RespondPayment/Skip.shtml?code=wechatpay';
		
		$this->notify_url = siteUrl() . 'RespondPayment/CallBack/code/wechatpay.shtml';			//支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径
    }
}
?>