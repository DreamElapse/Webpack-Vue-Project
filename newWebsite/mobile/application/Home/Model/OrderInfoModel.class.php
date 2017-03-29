<?php
/**
 * ====================================
 * 订单详情信息模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-06 15:37
 * ====================================
 * File: OrderInfoModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;

class OrderInfoModel extends CommonModel{
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