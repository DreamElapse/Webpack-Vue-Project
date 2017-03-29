<?php
/**
 * ====================================
 * 第三方快递接口对接
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-09-26
 * ====================================
 * File: Logistics.class.php
 * ====================================
 */
namespace Common\Extend;


class Logistics {
	private $response = '';  //储存返回结果
	private $response_array = array();  //储存处理后的结果
	
	public $order_sn = '';  //订单号
	public $shipping_name = '';  //物流公司
	public $invoice_no = '';  //物流单号
	
	private $error = array();  //错误信息
	
	private $logistics_header =  "订单编号：{order_sn}\n物流公司：{shipping_name}\n物流单号：{invoice_no}\n";
	
    public function __construct($return_header = true) {
        $this->Logistics($return_header);
    }
	
	/*
    *	构造函数
    *	@Author 9009123 (Lemonice)
	*	@param bool $return_header 是否包含头信息
    *	@return string
    */
    public function Logistics($return_header = true) {
		if($return_header == false){
			$this->logistics_header = '';
		}
    }
	
	/*
    *	设置参数
    *	@Author 9009123 (Lemonice)
	*	@param  string $name 参数名称
	*	@param  string $value 值
    *	@return true or false
    */
	public function setConfig($name = '',$value = ''){
		if($name != '' && isset($this->$name)){
			$this->$name = $value;
			return true;
		}
		return false;
	}
    
	/*
    *	请求EMS物流信息
    *	@Author 9009123 (Lemonice)
    *	@return true or false
    */
    public function ems($params) {
		$this->response_array = array();
		$url = "http://211.156.193.140:8000/cotrackapi/api/track/mail/".$this->invoice_no;
		$header = array(
			"authenticate: 3B6D008B615348C5E050030A240B0478",
			"version: ems_track_cn_1.0"
		);
		$result = $this->callApi($url,'8000',$header);  //请求数据
		
		$response_data = json_decode($this->response, true);
		
		if(!empty($response_data['traces'])){
			foreach($response_data['traces'] as $value){
				$this->response_array[] = $value['acceptTime'];
				$this->response_array[] = $value['remark'];
			}
			return true;
		}
		
		return false;
    }
	
	/*
    *	京东快递
    *	@Author 9009123 (Lemonice)
    *	@return true or false
    */
	public function jingDong(){
		$this->response_array = array();
		$jingdongapi = new \Common\Extend\Logistics\JingDongApi();
		$jd_logistics_data = $jingdongapi->josRequest('jingdong.etms.trace.get', array('waybillCode'=>$this->invoice_no));
		$jd_logistics_details = json_decode($jd_logistics_data, true);
		
		if(isset($jd_logistics_details['error_response']) && !empty($jd_logistics_details['error_response'])){
			$this->error = $jd_logistics_details['error_response'];
			return false;
		}
		$msg_txt = '';
		if(!empty($jd_logistics_details['jingdong_etms_trace_get_responce']['trace_api_dtos'])){
			foreach($jd_logistics_details['jingdong_etms_trace_get_responce']['trace_api_dtos'] as $value){
				$this->response_array[] = $value['ope_time'];
				$this->response_array[] = $value['ope_remark'];
			}
			return true;
		}
		$this->error = $jd_logistics_details;
		return false;
	}
	
	/*
    *	思迈
    *	@Author 9009123 (Lemonice)
    *	@return true or false
    */
	public function sm(){
		$this->response_array = array();
		$url = "http://120.26.204.154:8081/xmlinterface/track.aspx?billcode=".$this->invoice_no;
		$header = array("cache-control: no-cache", "content-type: multipart/form-data; boundary=---011000010111000001101001", "postman-token: 6c6aa7d4-32eb-5126-5056-1f47f09c13a2");
		$result = $this->callApi($url,'8081',$header);  //请求数据
		
		$xml = @simplexml_load_string($this->response);
		$res = json_decode(json_encode($xml),TRUE);
		
		if(!empty($res['track']['detail'])){
			foreach ($res['track']['detail'] as $key => $value) {
				$this->response_array[] = date('Y-m-d H:i:s', strtotime($value['time']));
				$this->response_array[] = $value['memo'];
			}
			return true;
		}
		return false;
	}
	
	/*
    *	顺丰速运
    *	@Author 9009123 (Lemonice)
    *	@return true or false
    */
	public function sf(){
		$this->response_array = array();
		$url = 'http://bsp-ois.sf-express.com/bsp-ois/sfexpressService';
		$header = array("Content-Type: application/x-www-form-urlencoded;charset=UTF-8");
		$xml = '<?xml version="1.0" encoding="utf-8" ?>';
		$xml .= '<Request service="RouteService" lang="zh-CN">';
		$xml .= '<Head>ncgxjxcy</Head>';
		$xml .= '<Body>';
		$xml .= '<RouteRequest tracking_type="1" method_type="1" tracking_number="'.$this->invoice_no.'"/> ';
		$xml .= '</Body>';
		$xml .= '</Request>';
		$verifyCode = base64_encode(md5($xml.'RDolvCAdejZ5An4vlEKdxwm8UlBwVD54', true));
		$post = http_build_query(array('xml'=>$xml, 'verifyCode'=>$verifyCode), '', '&');
		$result = $this->callApi($url,'',$header,'POST',$post);  //请求数据
		
		$return_xml = json_decode(json_encode(simplexml_load_string($this->response)),TRUE);
		
		if(!empty($return_xml['Body']['RouteResponse']['Route'])){
			foreach ($return_xml['Body']['RouteResponse']['Route'] as $key => $value) {
				$this->response_array[] = $value['@attributes']['accept_time'];
				$this->response_array[] = $value['@attributes']['remark'];
			}
			return true;
		}
		return false;
	}
	
	/*
    *	韵达快递
    *	@Author 9009123 (Lemonice)
    *	@return true or false
    */
	public function yunDa(){
		$this->response_array = array();
		$this->response = @file_get_contents('http://join.yundasys.com/query/json.php?partnerid=ciji&mailno='.$this->invoice_no);
		if($this->response != ''){
			$json = json_decode($this->response,TRUE);
			if(isset($json['result']) && $json['result'] != false){
				$json['steps'] = isset($json['steps']) ? $json['steps'] : array();
				if(!empty($json['steps'])){
					foreach($json['steps'] as $value){
						$this->response_array[] = $value['time'];
						$this->response_array[] = '快件在【'.$value['address'].'】'.$value['remark'];
					}
					return true;
				}
			}
		}
		return false;
	}
	
	/*
    *	申通快递
    *	@Author 9009123 (Lemonice)
    *	@return true or false
    */
	public function shengTong(){
		$this->response_array = array();
		$this->response = @file_get_contents('http://58.246.233.209/track.aspx?billcode='.$this->invoice_no);
		//221069866385
		if($this->response != ''){
			$xml = simplexml_load_string($this->response);
			$i = 0;
			while($xml->track->detail[$i]){
				$data = (array)$xml->track->detail[$i];
				if(!empty($data) && isset($data['time']) && $data['time'] != ''){
					$this->response_array[] = $data['time'];
					$this->response_array[] = $data['memo'];
				}
				$i++;
			}
			if($i > 0){
				return true;
			}
		}
		return false;
	}
	
	/*
    *	图灵
    *	@Author 9009123 (Lemonice)
    *	@return true or false
    */
	public function tuRing(){
		$this->response_array = array();
		$ex_info = $this->shipping_name.$this->invoice_no;
		$url = "http://www.tuling123.com/openapi/api";
		$header = array(
			"cache-control: no-cache",
			"content-type: application/json",
			"postman-token: a70a898f-6366-bfac-7f40-623b7a5cd68e"
		);
		$post = '{"key":"c6a67b12571a94f2ec6e405b3783431f","info":"'.$ex_info.'"}';
		$result = $this->callApi($url,'',$header,'POST',$post);  //请求数据
		
		$logistics = json_decode($this->response, true);
		
		if ($logistics['code'] == 100000 && $logistics['text'] != $ex_info && preg_match("/\d{4}-\d{2}-\d{2}/", $logistics['text'])) {
			if(!is_array($logistics['text'])){
				$logistics['text'] = str_replace(array('<br />','<br/>','<br>',"\n\n"),array("\n","\n","\n","\n"),$logistics['text']);  //先去掉任何换行
				$logistics['text'] = explode("\n",$logistics['text']);
			}
			$this->response_array = $logistics['text'];
			return true;
		}
		return false;
	}
	
	/*
    *	获取返回值，格式化之后才返回的
    *	@Author 9009123 (Lemonice)
    *	@return string
    */
	public function getResponse(){
		$content = '';
		if($this->logistics_header != ''){
			$content = str_replace(array('{order_sn}','{shipping_name}','{invoice_no}'),array($this->order_sn,$this->shipping_name,$this->invoice_no),$this->logistics_header);
		}
		if(!empty($this->response_array)){
			$content .= implode("\n",$this->response_array);
			$content = str_replace(array('<br />','<br/>','<br>',"\n\n"),array("\n","\n","\n","\n"),$content);  //先去掉任何换行
			$content = preg_replace("/([0-9\/\-]+ [0-9:]+)/is","\n$1",$content);  //按时间换行
			$content = trim($content);  //去除两边的空格和换行
		}else{
			$content .= "\n订单跟踪通知\n十分抱歉！您的订单".$this->order_sn."正在受理中，请保持电话畅通，我们有可能会给您致电再次核对收货信息！";
		}
		
		return $content;
	}
	
	/*
    *	CURL请求
    *	@Author 9009123 (Lemonice)
	*	@param  string $url  请求地址
	*	@param  int $port 请求端口
	*	@param  array $header 头信息
	*	@param  string $method  请求的HTTP方式 GET OR POST
	*	@param  string $post  POST请求参数
    *	@return true or false
    */
	private function callApi($url = '',$port = '',$header = array(),$method = 'GET',$post = NULL){
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => $header,
		));
		
		if($port != ''){
			curl_setopt($curl,CURLOPT_PORT, $port);
		}
		if(!is_null($post)){
			curl_setopt($curl,CURLOPT_POSTFIELDS, $post);
		}
		$response = curl_exec($curl);
		$this->error = curl_error($curl);
		curl_close($curl);
		$this->response = $response;
		return true;
	}
	
	/*
    *	获取返回错误信息
    *	@Author 9009123 (Lemonice)
    *	@return array
    */
	public function getError(){
		return $this->error;
	}
}