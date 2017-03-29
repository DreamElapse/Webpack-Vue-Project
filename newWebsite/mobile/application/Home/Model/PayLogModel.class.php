<?php
/**
 * ====================================
 * 订单日记模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-04 13:59
 * ====================================
 * File: PayLogModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\PaymentsModel;
use Common\Extend\Time;

class PayLogModel extends PaymentsModel{
	/*
	*	检查支付的金额是否与订单相符
	*	@Author 9009123 (Lemonice)
	*	@param   string   $log_id      支付编号
    *	@param   float    $money       支付接口返回的金额
	*	@return true or false
	*/
	public function checkMoney($log_id, $money){
		$amount = $this->where("log_id = '$log_id'")->getField('order_amount');
		if ($money == $amount){
			return true;
		} else{
			return false;
		}
	}
   
	/*
	*	插入订单日记
	*	@Author 9009123 (Lemonice)
	*	@param array $param  订单详情
	*	@return int [id]
	*/
	public function orderPayLog($param){
		if (!isset($param['pay_id'],$param['order_sn'],$param['verify_type'],$param['log'])){
			return false;
		}
		$id = $this->where("pay_id = '$param[pay_id]' AND order_sn = '$param[order_sn]' AND verify_type = '$param[verify_type]'")->getField('id');
		if($id){
			return false;
		}
		
		$nowtime = Time::gmTime();
		$this->create(array(
			'pay_id'=>$param['pay_id'],
			'order_sn'=>$param['order_sn'],
			'verify_type'=>$param['verify_type'],
			'log'=>$param['log'],
			'add_time'=>$nowtime,
		));
		$id = $this->add();
		return $id;
	}
	
	/*
	*	编辑
	*	@Author 9009123 (Lemonice)
	*	@param  $data array  详情
	*	@return int [Affected Number]
	*/
	public function update($data = array()){
		$this->create($data);
		$result = $this->save();
		return $result==false ? 0 : $result;
	}
	
	/*
	*	插入
	*	@Author 9009123 (Lemonice)
	*	@param  $data array  详情
	*	@return int [insert_id]
	*/
	public function insert($data = array()){
		$this->create($data);
		$insert_id = $this->add();
		return $insert_id;
	}
}