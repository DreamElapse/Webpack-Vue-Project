<?php

namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\WechatJsSdk;

class WechatComController extends InitController
{
	private $appId;
	private $appSecret;
	
	public  function __construct(){
		parent::__construct();
		$this->appId = C('appid');
		$this->appSecret = C('appsecret');
	}
	
    public function index()
    {}
	
	/**
	 * 获取微信的权限验证配置
	 *
	 * @return json
	 */
	public function getSignPackage(){
		$jssdk = new WechatJsSdk($this->appId, $this->appSecret);
		$signPackage = $jssdk->getSignPackage();
		
		$data['appId']		= $signPackage['appId'];		// 必填，公众号的唯一标识
		$data['timestamp']	= $signPackage['timestamp'];	// 必填，生成签名的时间戳
		$data['nonceStr']	= $signPackage['nonceStr'];		// 必填，生成签名的随机串
		$data['signature']	= $signPackage['signature'];	// 必填，签名
		
		$this->ajaxReturn($data);
	}
}
