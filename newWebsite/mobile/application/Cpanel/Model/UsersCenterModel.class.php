<?php
/**
 * ====================================
 * 会员等级
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-03-21 09:24
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: UsersCenterModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\PhxCrypt;

class UsersCenterModel extends CpanelUserCenterModel {
	protected $tableName = 'users';
	
	/*
	*	按用户字段查询用户列表
	*	@Author 9009123 (Lemonice)
	*	@param string $keyword_field 查询的字段
	*	@param string $keyword 查询的关键字
	*	@param string $select_field 显示的字段
	*	@param int    $limit 查询的条数
	*	@return exit
	*/
	public function searchList($keyword_field = '', $keyword = '', $select_field = '*', $limit = 0) {
		$where = array();
		
		if($keyword_field == 'mobile') {
			$where['mobile'] = PhxCrypt::phxEncrypt($keyword);
		}else if($keyword_field == 'user_name') {
			$where['user_name'] = array('LIKE', "%$keyword%");
		}else{
			$where[$keyword_field] = $keyword;
		}
		if($limit > 0){
			$this->limit($limit);
		}
		$info = $this->field($select_field)->where($where)->select();
		return $info;
	}
}