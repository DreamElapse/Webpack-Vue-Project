<?php
/**
 * ====================================
 * 支付方式信息模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-05 15:46
 * ====================================
 * File: PaymentModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;
use Common\Extend\Time;

class PaymentModel extends CommonModel{
	/*
	*	检查对应的支付方式是否可用
	*	@Author 9009123 (Lemonice)
	*	@param  string  $code   支付ID 或 支付方式代码 
	*	@return int [count number]
	*/
    public function isPayEnable($code){
		$field = is_numeric($code) ? 'pay_id' : 'pay_code';
		return $this->where("$field = '$code' and enabled = '1'")->count();
    }
   
   /*
	*	取得某支付方式信息
	*	@Author 9009123 (Lemonice)
	*	@param  string  $code   支付ID 或 支付方式代码
	*	@return array
	*/
	public function getPayment($code){
		$field = is_numeric($code) ? 'pay_id' : 'pay_code';
		$payment = $this->where("$field = '$code' AND enabled = '1'")->find();
		if (!empty($payment)){
			$config_list = unserialize($payment['pay_config']);
			foreach ($config_list as $config){
				$payment[$config['name']] = $config['value'];
			}
		}
       return $payment;
	}
}