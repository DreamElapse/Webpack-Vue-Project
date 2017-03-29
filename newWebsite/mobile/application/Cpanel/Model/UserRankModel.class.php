<?php
/**
 * ====================================
 * 会员等级
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-03-21 09:24
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: UserRankModel.class.php
 * ====================================
 */
namespace Cpanel\Model;

use Common\Extend\Time;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\PhxCrypt;

class UserRankModel extends CpanelUserCenterModel {
	
	/*
	*	取得用户等级数组,按用户级别排序
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function getRankList($is_special = false) {
		$where = array();
		if ($is_special) {
			$where['special_rank'] = 1;
		}
		$result = $this->field('rank_id, site_id, rank_name, min_points')->where($where)->order('min_points')->select();
		$rank_list = array();
		if(!empty($result)){
			foreach($result as $value){
				$rank_list[$value['rank_id']] = $value['rank_name'];
			}
		}
		return $rank_list;
	}
}