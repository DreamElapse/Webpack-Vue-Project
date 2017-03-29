<?php
/**
 * ====================================
 * 用户红包模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-07 16:46
 * ====================================
 * File: UserBonusCenterModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CustomizeModel;
use Common\Extend\Time;

class UserBonusCenterModel extends CustomizeModel{
	protected $_config = 'USER_CENTER';
    protected $_table = 'UserBonus';
	
	/*
	*	获取用户可以使用的优惠劵
	*	@Author Lemonice
	*	@param int $user_id   用户id
	*	@param int $page  当前分页
	*	@param int $pageSize 每页显示多少条
	*	@return array
	*/
	public function getUserBonusPage($user_id, $page = 1, $pageSize = 0){
		$return = array(
			'page' => $page,
			'pageSize' => $pageSize,
			'total' => 0,
			'pageTotal' => 0,
			'list' => array(),
			'type_count'=>array()
		);
		if(empty($user_id)){
			return $return;
		}
		
		$day = Time::localGetdate();
		$today  = Time::localMktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
		
		// AND t.send_type != 3 
		$where = "t.type_id = b.bonus_type_id AND (t.send_type != 3 OR (t.send_type = 3 and t.reuse = 0)) AND t.use_end_date >= '$today' AND b.user_id = '$user_id' AND b.order_id = 0";
		
		//统计相关信息
		$this->alias(' AS b')->join("__BONUS_TYPE__ AS t ON t.type_id = b.bonus_type_id", 'left');
		$result = $this->field('b.bonus_type_id,count(b.bonus_type_id) as count')->where($where)->group('b.bonus_type_id')->select();  //统计总记录数
		
		//如果有值，count(b.bonus_type_id) as count是顺便统计每个类型总共有多少张优惠券可以使用
		$type_count = array();  //储存统计
		if(!empty($result)){
			foreach($result as $value){
				$type_count[$value['bonus_type_id']] = $value['count'];
			}
		}
		$total = count($result);
		
		
		//是否启用分页
		$pageTotal = 0;
		if($pageSize > 0){
			$this->page($page.','.$pageSize);
			$pageTotal = ceil($total / $pageSize);  //计算总页数
		}else{
			$page = 1;
		}
		
		$this->field('t.*, b.bonus_id, b.bonus_sn, b.start_time, b.end_time, b.site_id, b.order_id');
		$this->alias(' AS b')->join("__BONUS_TYPE__ AS t ON t.type_id = b.bonus_type_id", 'left');
		// AND t.use_start_date <= '$today' AND t.min_goods_amount <= '$goods_amount' AND b.user_id<>0
		$list = $this->where($where)->group('b.bonus_type_id')->select();
		
		$total = $total > 0 ? $total : count($list);
		
		$return['total'] = $total;
		$return['pageTotal'] = $pageTotal;
		$return['list'] = $list;
		$return['type_count'] = $type_count;
				
		return $return;
	}
	
	/*
	*	获取优惠劵信息
	*	@Author Lemonice
	*	@param $bonusId:优惠劵id
	*	@param $bonusSn:优惠劵号
	*	@return array
	*/
    public function bonusInfo($bonusId=0, $bonusSn = ''){
        if($bonusId == 0 && $bonusSn == ''){
            return false;
        }
		$where = array();
		if($bonusId != 0){
			$where[] = "b.bonus_id = '$bonusId'";
        }
		if($bonusSn != ''){
			$where[] = "b.bonus_sn = '$bonusSn'";
        }
		
		$this->field('t.*, b.*');
		$this->alias(' AS b')->join("__BONUS_TYPE__ AS t ON t.type_id = b.bonus_type_id", 'left');
		$data = $this->where(implode(',',$where))->find();
		
        return $data;
    }
	
	/* @access pubic
     * 使用优惠劵
     * @author Ocean
     * @param $post:post过来的优惠劵信息 类型array()
     */
    public function useBonus($post){
        if(!empty($post) && is_array($post)){
            $update = $post;
            $update['used_time'] = Time::gmTime();
            unset($update['reuse']);
			$data = array(
				'order_id'=>$update['order_id'],
				'site_id'=>$update['site_id'],
				'user_id'=>$update['user_id'],
				'used_time'=>$update['used_time'],
			);
			if($post['reuse']==1){
				$use_amount = $this->where("bonus_id = '$update[bonus_id]'")->getField('use_amount');
				$data['use_amount'] = $use_amount+1;
			}
            return $this->where("bonus_id = '$update[bonus_id]'")->update($data);
        }else{
            return false;
        }
    }
	
	/*
	*	处理红包的详情信息
	*	@Author Lemonice
	*	@param  array $bonus 红包详情
	*	@return array
	*/
	public function bonusFormat($bonus){
		
		return $bonus;
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