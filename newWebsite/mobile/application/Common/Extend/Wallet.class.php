<?php
/**
* ====================================
* 电子钱包 - API对接类
* ====================================
* Author: 9009123 (Lemonice)
* Date: 2016-10-27 10:12
* ====================================
* File: Wallet.class.php
* ====================================
*/
namespace Common\Extend;

class Wallet{
	private $headers = array('Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg','Connection: Keep-Alive','Content-type: application/x-www-form-urlencoded;charset=UTF-8');
    private $userAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)';
    private $compression = 'gzip';
    private $proxy = '';
	
	/**
     * 返回的数据，curl返回的结果
     */
	private $response = '';
	
	/**
     * 电子钱包服务器API接口的  地址域名以及端口
     */
	private $serverHost = 'http://192.168.62.145:8183/';
	/**
     * 电子钱包服务器API接口的  商户代码
     */
	private $serverMerchantCode = '10000008501';  //商户代码
	/**
     * 电子钱包服务器API接口的  KEY
     */
	private $serverMerchantKey = 'PCYH86BCXWR';  //KEY
	
	/**
     * 错误码对照表
     */
	private $status = array(
		'-999'=>'请求出错了',
		0 => '正常',
		100 => '缺少必要参数',
		101 => '验签不通过',
		102 => '会员开户失败',
		103 => '会员账户不存在',
		104 => '会员余额不足',
		105 => '会员资金账户被冻结',
		106 => '支付失败',
		107 => '重复支付',
	);

    /**
     * 错误码
     * 对应上面的对照表信息
     */
    private $error = -999;
	
	/**
     * CURL错误信息
     * 错误码与错误信息
     */
    private $curlError = array(
		'error'=>0,
		'msg'=>'请求成功',
	);
	


    /**
     * 构造函数
     */
    public function __construct(){
		
    }
	
	/**
     * 请求余额与账单信息
     * @param $callback //回调地址
     * @param int $type //调用类型 0 登陆 1 注册
     * @param bool $auto_attach
     */
    public function getBillInfo($user_id = 0){
		if($user_id <= 0){
			return false;
		}
		$params = array(
			'userId'=>$user_id,
		);
		$this->serverCall('payms/a/getBankbookBalance', $params);
		
		$json = $this->getResponse();
		return $json;
    }
	
	/**
     * 处理返回json格式的字符串
     * @param string $response API接口返回值
     * @param string
     */
	public function getResponse(){
		if($this->response == ''){
			return array();
		}
		$json = json_decode($this->response,true);
		if(isset($json['errorCode'])){
			$this->error = $json['errorCode'];
		}
		return $json;
	}
	
	/**
     * 获取报错信息
     * @param string
     */
	public function getError(){
		$msg = '请求出错了';
		if($this->curlError['error'] != 0){  //curl请求出错了
			$msg = $this->curlError['error'].' : '.$this->curlError['msg'];
		}else{  //请求成功，看下返回的内容是否报错
			if($this->error != 0){
				if(isset($this->status[$this->error])){
					$msg = $this->status[$this->error];
				}else{
					$msg = $this->error.': 未知错误';
				}
			}else{
				$msg = NULL;
			}
		}
		return $msg;
	}
	
	/**
     * 获取签名字符串
     * @param array $signData 加入签名的参数
     * @param string
     */
	private function getSign($signData = array()){
		$data['accountid'] = $this->serverMerchantCode;
		$data['key'] = $this->serverMerchantKey;
		if(!empty($signData)){
			$data = array_merge($data,$signData);
		}
		$http_query = http_build_query($data);
		$sign = md5($http_query);
		return $sign;
	}

	/**
     * 执行CURL请求
     *
     * @param string $url 地址，路径
     * @param array $params Post参数
     * @return array
     *
     */
	private function serverCall($url, $params = array(), $signParam = array()){
		$this->response = '{"balance":100.00,"errorCode":"0","journalList":[{"balance":100.00,"dealDate":"2016-02-12","deal_type":"A","money":100.00,"notes":"充值"},{"balance":0.00,"dealDate":"2016-09-14","deal_type":"D","money":100.00,"notes":"退款"},{"balance":100.00,"dealDate":"2016-03-12","deal_type":"A","money":100.00,"notes":"充值"},{"balance":100.00,"dealDate":"2016-10-13","deal_type":"A","money":100.00,"notes":"充值"},{"balance":100.00,"dealDate":"2016-10-14","deal_type":"A","money":100.00,"notes":"充值"},{"balance":100.00,"dealDate":"2016-10-15","deal_type":"A","money":100.00,"notes":"充值"},{"balance":100.00,"dealDate":"2016-10-16","deal_type":"A","money":100.00,"notes":"充值"},{"balance":100.00,"dealDate":"2016-05-17","deal_type":"A","money":100.00,"notes":"充值"},{"balance":100.00,"dealDate":"2016-10-18","deal_type":"A","money":100.00,"notes":"充值"},{"balance":100.00,"dealDate":"2016-10-19","deal_type":"A","money":100.00,"notes":"充值"},{"balance":100.00,"dealDate":"2016-10-20","deal_type":"A","money":100.00,"notes":"充值"},{"balance":100.00,"dealDate":"2016-10-21","deal_type":"A","money":100.00,"notes":"充值"},{"balance":100.00,"dealDate":"2016-06-22","deal_type":"A","money":100.00,"notes":"充值"}],"msg":"查询成功"}';
		return true;
		
		
		$params['accountid'] = $this->serverMerchantCode;
		$params['key'] = $this->serverMerchantKey;
		if(empty($signParam) && isset($params['userId'])){
			$signParam = array('userId'=>$params['userId'],);
		}
		$params['sign'] = $this->getSign($signParam);
		
        $ch = curl_init($this->serverHost . $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_ENCODING, $this->compression);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if($this->proxy) curl_setopt($ch,CURLOPT_PROXY, $this->proxy);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (strstr($this->serverHost,'https://') ? true : false));
        $this->response = curl_exec($ch);	
		$error = curl_errno($ch);
		if ($error != 0){
			$this->curlError = array(
				'error'=>$error,
				'msg'=>curl_error($ch),
			);
		}	
        curl_close($ch);
    }
}

?>