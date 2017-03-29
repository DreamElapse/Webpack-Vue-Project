<?php
/**
 * ====================================
 * 支付日记相关信息模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-11-11 17:08
 * ====================================
 * File: PayMultipleLogModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\PaymentsModel;
use Common\Extend\Time;

class PayMultipleLogModel extends PaymentsModel{
	
	/*
     * 查询子订单是否已经支付 
     * @param integer $order_sn_child  子订单号
     * @param $integer $pay_id  支付id
     */
    public function childIsPayed($order_sn_child,$pay_id){
        return $this->where("`order_sn_child`='{$order_sn_child}' AND `pay_id`='{$pay_id}' AND (`synchro_status`=1 or `asynch_status`=1)")->count();
    }
	
	/*
	*	查询某个单号已支付的总金额
	*	@Author 9009123 (Lemonice)
	*	@param  $order_sn 订单号
	*	@return int
	*/
    public function getOrderPayMoney($order_sn){
        return $this->where("`order_sn`='{$order_sn}' AND (`synchro_status`=1 or `asynch_status`=1)")->sum('pay_amount');
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