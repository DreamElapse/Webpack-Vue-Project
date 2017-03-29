<?php
/**
 * ====================================
 * 会员中心 里面的订单商品模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-07 14:58
 * ====================================
 * File: OrderGoodsCenterModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CustomizeModel;
use Common\Extend\Time;

class OrderGoodsCenterModel extends CustomizeModel{
	protected $_config = 'USER_CENTER';
    protected $_table = 'OrderGoods';
	
	
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