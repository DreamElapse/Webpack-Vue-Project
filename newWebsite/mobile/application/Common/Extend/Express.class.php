<?php
/**
* ====================================
* 查询快递接口
* ====================================
* Author: 9009123 (Lemonice)
* Date: 2016-09-21 14:28
* ====================================
* File: Express.class.php
* ====================================
*/
namespace Common\Extend;
use Common\Extend\Time;

class Express{
	private $rqsn = '';  //队列序列号
	private $errorMsg = '';  //错误信息储存
	
	/*
	*	服务端地址
	*/
	private $ExchangeServiceUrl = 'http://14.18.206.138:8080/services/ExchangeService?wsdl';
    
	/*
	*	头信息
	*/
	private $header = '<BHIA_CIIMS:AuthenticationToken xmlns:BHIA_CIIMS="http://ciims.bhia.itdcl.com/ExchangeService"><BHIA_CIIMS:Username>CNWX</BHIA_CIIMS:Username><BHIA_CIIMS:Password>CNWXPASS</BHIA_CIIMS:Password></BHIA_CIIMS:AuthenticationToken>';
		
	/*
	*	构造函数
	*	@Author 9009123 (Lemonice)
	*	@return void
	*/
    public function Express(){
		
    }

    public function __construct(){
        $this->Express();
    }
	
	/*
	*	发送消息到队列 - 等待获取快递信息
	*	@Author 9009123 (Lemonice)
	*	@return true or false
	*/
	public function send($mobile = '', $order_sn = '', $express_sn = '', $phone = ''){
		if($mobile == '' && $order_sn == '' && $express_sn == '' && $phone == ''){
			$this->error('请输入手机号码或者订单号等');
			return false;
		}
		
		$objClient = $this->getClientObject($this->header);
		
		$time = Time::gmTime();
		
		$this->rqsn = $time . rand(100000,999999);  //消息队列序列号
		
		//组装报文
		$p0 = '<?xml version="1.0" encoding="UTF-8"?>';
		$p0 .= '<MSG xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:targetNamespaceSchemaLocation="CHSK.xsd">';
			$p0 .= '<META>';
				$p0 .= '<SNDR>CNWX</SNDR>';
				$p0 .= '<SEQN>'.$this->rqsn.'</SEQN>';
				$p0 .= '<DTTM>'.date('YmdHis',$time).'</DTTM>';
				$p0 .= '<TYPE>RQST</TYPE>';
				$p0 .= '<STYP>CODI</STYP>';
			$p0 .= '</META>';
			$p0 .= '<CODI>';
				$p0 .= '<CTMP>'.($mobile!='' ? $mobile : '').'</CTMP>';
				$p0 .= '<CTTP>'.($phone!='' ? $phone : '').'</CTTP>';
				$p0 .= '<CTOI>'.($order_sn!='' ? $order_sn : '').'</CTOI>';
				$p0 .= '<CTBC>'.($express_sn!='' ? $express_sn : '').'</CTBC>';
			$p0 .= '</CODI>';
		$p0 .= '</MSG>';
		
		$p1 = array(
			'ack' => false,
			'priority' => 1,  //优先级
			'routeId' => 'RQST-CODI-CNWX-ALL',  //路由规则
			'valXml' => false  //是否需要校验
		);
		
		try{
			$res = $objClient->send(array('in0'=>$p0, 'in1'=>$p1));
			return true;
		}catch(SoapFault $fault){
			$this->error("Error Message:\n" . $fault->getMessage() . "\n\n" . $fault->getTraceAsString());
			return false;
		}
	}
	
	/*
	*	接收对了消息
	*	@Author 9009123 (Lemonice)
	*	@return string
	*/
    public function receive(){	
		$objClient = $this->getClientObject($this->header);
		
		try{
			$res = $objClient->receive(array('in0'=>1));
			if(isset($res->out->string)){
				return $this->response($res->out->string);
			}
			return '';
		}catch(SoapFault $fault){
			$this->error("Error Message:\n" . $fault->getMessage() . "\n\n" . $fault->getTraceAsString());
			return false;
		}
    }
	
	/*
	*	获取序列号
	*	@Author 9009123 (Lemonice)
	*	@return string
	*/
	public function getRqsn(){
		return $this->rqsn;
	}
	
	/*
	*	解析返回的XML
	*	@Author 9009123 (Lemonice)
	*	@return string  [Content]
	*/
	private function response($response){
		$response = trim($response);
		
		if($response == ''){
			return '';
		}
		//暂时不做处理，调用的地方再去处理
		/*if(!function_exists('simplexml_load_string')){
			$this->error('Function simplexml_load_string No exists!');
			return '';
		}
		$xml = simplexml_load_string($response);*/
		return $response;
	}
	
	/*
	*	连接 - 设置头信息
	*	@Author 9009123 (Lemonice)
	*	@return Object
	*/
	private function getClientObject($header = ''){
		$objClient = new \SoapClient($this->ExchangeServiceUrl, array('trace' => true)); 
		$objVar_Inside = new \SoapVar($header, XSD_ANYXML, null, null, null);
		$objHeader_Outside = new \SoapHeader('namespace.com', 'Header', $objVar_Inside);
		$objClient->__setSoapHeaders(array($objHeader_Outside));
		return $objClient;
	}
	
	/*
	*	设置错误信息
	*	@Author 9009123 (Lemonice)
	*	@return nothing
	*/
	private function error($msg = ''){
		$this->errorMsg = $msg;
	}
	
	/*
	*	获取错误信息
	*	@Author 9009123 (Lemonice)
	*	@return string
	*/
	public function getError(){
		return $this->errorMsg;
	}
}
