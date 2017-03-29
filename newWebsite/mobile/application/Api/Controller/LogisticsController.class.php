<?php
/**
 * ====================================
 * 获取物流信息
 * ====================================
 * Author: 9009123
 * Date: 2016-09-27 09:56
 * ====================================
 * File: LogisticsController.class.php
 * ====================================
 */
namespace Api\Controller;
use Common\Controller\ApiController;
use Common\Extend\PhxCrypt;
use Common\Extend\Logistics;

class LogisticsController extends ApiController{
	//密钥权限
	protected $_permission = array(
		//获取物流信息
		'getLogistics'=>array(
			'baida_solt'
		),
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
}
