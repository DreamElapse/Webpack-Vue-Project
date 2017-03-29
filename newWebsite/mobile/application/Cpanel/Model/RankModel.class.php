<?php
/**
 * ====================================
 * 微信会员模型
 * ====================================
 * Author: 9004396
 * Date: 2017-01-11 13:52
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: RankModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;

class RankModel extends CpanelUserCenterModel
{
    protected $tableName = 'user_rank';

    protected $_validate = array(
        array('rank_name', 'require', '会员等级名称不能空'),
        array('max_points', 'maxMin', '下限不能大于等于上限',1,'callback'),

    );

    protected function maxMin(){
        $params = I('param.');
        return ($params['min_points'] >= $params['max_points']) ? false : true;
    }

    /**
     * 获取会员等级
     * @return mixed
     */
	public function getRank(){
		return $this->field('rank_id, rank_name')->select();
	}
	
	//获取会员等级
	public function getAllRank($rank_id=0, $fields='',$site_id=0){
		if(empty($fields)){
			$fields = 'rank_id,site_id,rank_name,min_points,max_points,discount';
		}
		$this->field($fields)->order('rank_id asc');
		if($site_id){
			$where['site_id'] = $site_id;
		}
		if($rank_id){
			$where['rank_id'] = $rank_id;
			$result = $this->where($where)->find();
		}else{
			$result = $this->where($where)->select();
		}
		
		return $result;
	}

    /**
     * 获取最低的等级
     * @return mixed
     */
    public function getMinRand(){
        return $this->order('min_points asc')->getField('rank_name');
    }

}
