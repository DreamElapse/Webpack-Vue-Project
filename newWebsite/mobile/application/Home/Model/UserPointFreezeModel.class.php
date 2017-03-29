<?php
/**
 * ====================================
 * 会员中心 里面的积分模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-02-08 13:49
 * ====================================
 * File: IntegralCenterModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\UserCenterModel;
use Common\Extend\Time;

class UserPointFreezeModel extends UserCenterModel{
	protected $_config = 'USER_CENTER';
    protected $tableName = 'user_point_freeze';
	
	/*
	*	获取
	*	@Author Lemonice
	*	@param  int    $user_id 会员ID
	*	@return act_id
	*/
	public function getUserFreezeSum($user_id = 0) {
		$freeze = 0;
		if($user_id <= 0){
			return $freeze;
		}
		$where = array('sync = 0','integral < 0');
		if($user_id > 0){
			$where[] = "user_id = '$user_id'";
		}
		
		$freeze = $this->where(implode(' and ',$where))->sum('integral');
		return ($freeze ? $freeze : 0);
    }
}
