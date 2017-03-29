<?php
/**
 * ====================================
 * 获取物流信息
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-10-31 14:02
 * ====================================
 * File: WechatsController.class.php
 * ====================================
 */
namespace Api\Controller;
use Common\Controller\ApiController;
use Common\Extend\PhxCrypt;
use Common\Extend\Logistics;
use Common\Extend\Wechat;

class WechatController extends ApiController{
	//密钥权限
	protected $_permission = array(
		//获取物流信息
		'getLogistics'=>array(
			'baida_solt'
		),
		//发送发货提醒给微信用户 - 模版消息
		'shippingNotice'=>array(
			'wechat'
		),
		//发送配货提醒给微信用户 - 模版消息
		'distributionNotice'=>array(
			'wechat'
		),
        'getWeChatAccessToken' => array(
            'wechat'
        )
	);
	
	/*
	*	获取物流信息
	*	@Author 9009123 (Lemonice)
	*	@return exit & json
	*/
	public function getLogistics(){
		$mobile = I('request.mobile','','trim');  //手机号码
		$order_sn = I('request.order_sn','','trim');  //订单号
		$invoice_no = I('request.invoice_no','','trim');  //快递单号
		
		if($mobile == '' && $order_sn == '' && $invoice_no == ''){
			$this->error('10020');
		}
		
		$params = array();
		if($mobile != ''){
			$params['mobile'] = PhxCrypt::phxEncrypt($mobile);
		}
		if($order_sn != ''){
			$params['order_sn'] = $order_sn;
		}
		if($invoice_no != ''){
			$params['invoice_no'] = $invoice_no;
		}
		
		$orders = D('Home/OrderInfoCenter')->getLogisticsOrder($params);
		
		if(empty($orders)){
			$this->error('10100');
		}
		$Logistics = new Logistics();
		$content = array();
		foreach ($orders as $k => $v) {
			$logistics_platform = $v['shipping_name'];
			$functio_name = '';
			switch($v['shipping_name']){
				case 'EMS特快专递':
					$functio_name = 'ems';
				break;
				case '京东瓷肌快递':
					$functio_name = 'jingDong';
				break;
				case '思迈':
					$functio_name = 'sm';
				break;
				case '顺丰速运':
					$functio_name = 'sf';
				break;
				case '韵达快运':
					$functio_name = 'yunDa';
				break;
				case '申通快递':
					$functio_name = 'shengTong';
				break;
			}
			$Logistics->setConfig('order_sn',$v['order_sn']);
			$Logistics->setConfig('invoice_no',$v['invoice_no']);
			$Logistics->setConfig('shipping_name',$v['shipping_name']);
			$result = $functio_name!='' ? $Logistics->$functio_name() : false;  //请求发送
			if($result == false){  //如果都获取不到，试着去图灵找
				if($v['shipping_name'] == 'EMS特快专递'){
					$Logistics->setConfig('shipping_name','EMS快递');
				}
				$result = $Logistics->tuRing();
			}
			$content[] = $Logistics->getResponse();  //获取返回值
			
        }
		
		if(empty($content)){
			$this->error('10101');
		}
		$this->success($content);
	}
	
	/*
	*	发送发货提醒给微信用户 - 模版消息
	*	@Author 9009123 (Lemonice)
	*	@return exit & json
	*/
	public function shippingNotice(){
		Wechat::$app_id = APPID;
        Wechat::$app_secret = APPSECRET;
		//Wechat::$app_id = 'wx9e0efb00afc3989a';
		//Wechat::$app_secret = 'a711d9d859925538037129e8b6cd8cd4';
		
		/*
		发货提醒模版:
		
		您好，您的包裹已发出，请及时关注物流动态。
		快递公司：*****
		物流编号：******
		发货日期：******
		请隔天查询进度，如有疑问，请拨打售后热线400-1608-088。
		*/
		$template_id = 'C6lb9hwhdEy0Zc9KUpjNF4ADFp8mAHyWkhN81PNjjh8';  //正式ID
		//$template_id = 'OVVAs2eMrfgTgxqQDvpMr8OviACL_72HV7RiUUJ2G64';  //测试的
		
		$mobile = I('request.mobile','','trim');  //手机号码
		$shipping_name = I('request.shipping_name','','trim');  //快递公司
		$shipping_sn = I('request.shipping_sn','','trim');  //快递单号
		$issuance_date = I('request.issuance_date','','trim');  //发货日期
		$url = I('request.url','','trim');  //跳转的URL地址
		
		if($mobile == '' || $shipping_name == '' || $shipping_sn == '' || $issuance_date == ''){
			$this->error('10020');  //缺少必要参数
		}
		
		$openid = D('BindUser')->getOpenId($mobile);
		if(!$openid){
			$this->error('10102');  //手机号码没绑定微信号
		}
		Wechat::$userOpenId = $openid;
		
		$data = array(
			'first'=>array(
				'value'=>'您好，您的包裹已发出，请及时关注物流动态。',
				'color'=>'#173177',
			),
			'keyword1'=>array(
				'value'=>$shipping_name,  //快递公司 - 顺丰快递
				'color'=>'#173177',
			),
			'keyword2'=>array(
				'value'=>$shipping_sn,  //物流编号 - 8002536905
				'color'=>'#173177',
			),
			'keyword3'=>array(
				'value'=>$issuance_date,  //发货日期 - 2016-10-31
				'color'=>'#173177',
			),
			'remark'=>array(
				'value'=>'请隔天查询进度，如有疑问，请拨打售后热线020-22005555。',
				'color'=>'#173177',
			),
		);
		$result = Wechat::sendTemplate($template_id, $data, $url);
		
		if($result['errcode'] != 0){  //微信发送模版消息失败了
			$error_code_array = include(COMMON_PATH.'Conf/wechatCode.php');  //加载微信错误码
			$result['message'] = isset($error_code_array['templateMsg'][$result['errcode']]) ? $error_code_array['templateMsg'][$result['errcode']] : '';
			$this->error('10103',$result);
		}else{
			$result['message'] = '请求成功';
			$this->success($result);
		}
	}
	
	/*
	*	发送配货提醒给微信用户 - 模版消息
	*	@Author 9009123 (Lemonice)
	*	@return exit & json
	*/
	public function distributionNotice(){
		Wechat::$app_id = APPID;
        Wechat::$app_secret = APPSECRET;
		//Wechat::$app_id = 'wx9e0efb00afc3989a';
		//Wechat::$app_secret = 'a711d9d859925538037129e8b6cd8cd4';
		
		/*
		预定成功提醒:
		
		您好，您已预订成功，我们在努力为您配货，感谢选择韩国瓷肌。
		订单编号：********
		订单金额：******
		订购日期：******
		如有疑问，请拨打售后热线400-1608-088。
		*/
		$template_id = 'qCMMjHsA77j_2XdLpyqX5VjHpQjYfDDfXl2-F9rg4QM';  //正式ID
		//$template_id = '25DhY8AUb3UqmzWTps8K-secdKqBSyZQ-7CEsPgf3Rw';  //测试的ID
		
		$mobile = I('request.mobile','','trim');  //手机号码
		$order_sn = I('request.order_sn','','trim');  //订单编号
		$order_amount = I('request.order_amount','','trim');  //订单金额
		$buy_date = I('request.buy_date','','trim');  //发货日期
		$url = I('request.url','','trim');  //跳转的URL地址
		
		if($mobile == '' || $order_sn == '' || $order_amount == '' || $buy_date == ''){
			$this->error('10020');  //缺少必要参数
		}
		
		$openid = D('BindUser')->getOpenId($mobile);
		if(!$openid){
			$this->error('10102');  //手机号码没绑定微信号
		}
		Wechat::$userOpenId = $openid;
		
		$data = array(
			'first'=>array(
				'value'=>'您好，您已预订成功，我们在努力为您配货，感谢选择韩国瓷肌。',
				'color'=>'#173177',
			),
			'keyword1'=>array(
				'value'=>$order_sn,  //订单编号 - 20161031212012
				'color'=>'#173177',
			),
			'keyword2'=>array(
				'value'=>$order_amount,  //订单金额 - 168.00元
				'color'=>'#173177',
			),
			'keyword3'=>array(
				'value'=>$buy_date,  //订购日期 - 2016-10-31
				'color'=>'#173177',
			),
			'remark'=>array(
				'value'=>'如有疑问，请拨打售后热线020-22005555。',
				'color'=>'#173177',
			),
		);
		$result = Wechat::sendTemplate($template_id, $data, $url);
		
		if($result['errcode'] != 0){  //微信发送模版消息失败了
			$error_code_array = include(COMMON_PATH.'Conf/wechatCode.php');  //加载微信错误码
			$result['message'] = isset($error_code_array['templateMsg'][$result['errcode']]) ? $error_code_array['templateMsg'][$result['errcode']] : '';
			$this->error('10103',$result);
		}else{
			$result['message'] = '请求成功';
			$this->success($result);
		}
	}

    /**
     * 统一获取微信accessToken
     */
	public function getWeChatAccessToken(){
	    $params = I('request.setData');
	    $setData = unserialize(base64_decode($params));
	    if(!isset($setData) || empty($setData)){
	        $this->error('参数不存在');
        }
        $appId = isset($setData['appid']) ? $setData['appid'] : '';
	    $appSecret = isset($setData['appsecret']) ? $setData['appsecret'] : '';

	    if(empty($appId) || empty($appSecret)){
	        $this->error('参数异常');
        }

        Wechat::$app_id = $appId;
	    Wechat::$app_secret = $appSecret;
	    $this->success(Wechat::getAccessToken());
    }
}
