<?php
/**
 * ====================================
 * 微信查询物流日记 管理模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-03-17 14:46
 * ====================================
 * File: WxLogCenterModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\Time;
use Common\Extend\PhxCrypt;

class UserBonusCenterModel extends CpanelUserCenterModel {
    protected $tableName = 'user_bonus';
	
	public function filter($params){
		$params['sort'] = !empty($params['sort']) ? $params['sort'] : 'add_time';
		$params['order'] = !empty($params['order']) ? $params['order'] : 'desc';
		
        //查询条件
        $where = array();
        if (isset($params['type_id']) && $params['type_id'] > 0){
            $where['bonus_type_id'] = $params['type_id'];
        }
		$this->where($where);
		return $this->order($params['sort'].' '.$params['order']);
	}
	
	public function format($data){
		if(!empty($data['rows'])){
			$UsersCenterModel = D('UsersCenter');
			foreach($data['rows'] as $k=>$v){				
				if(isset($v['user_id']) && $v['user_id'] > 0){
					$user_info = $UsersCenterModel->field('user_name,mobile')->where("user_id = '$v[user_id]'")->find();
					$v['user_name'] = !empty($user_info['user_name']) ? PhxCrypt::phxDecrypt($user_info['user_name']) : PhxCrypt::phxDecrypt($user_info['mobile']);
				}
				
				$v['order_id'] = $v['order_id'] ? $v['order_id'] : '';
				$v['used_time'] = $v['used_time']!='' ? Time::localDate('Y-m-d H:i:s', strtotime($v['used_time'])) : '';
				
				$data['rows'][$k] = $v;
			}
		}
		return $data;
	}
		
	/*
	*	查询优惠券类型的发放数据
	*	@Author 9009123 (Lemonice)
	*	@param  int $type_id 优惠券类型ID，如果不传则获取所有类型
	*	@return array
	*/
	public function getSendCount($type_id = 0){
		$where = array();
		if($type_id > 0){
			$where['bonus_type_id'] = $type_id;
		}
		$data = $this->field('bonus_type_id, COUNT(*) AS sent_count')->where($where)->group('bonus_type_id')->select();
		
		if($type_id > 0){
			return isset($data[0]['sent_count']) ? $data[0]['sent_count'] : 0;
		}
		$count = array();
		if(!empty($data)){
			foreach($data as $value){
				$count[$value['bonus_type_id']] = $value['sent_count'];
			}
		}
		return $count;
	}
	
	/*
	*	查询优惠券类型的发放数据
	*	@Author 9009123 (Lemonice)
	*	@param  int $type_id 优惠券类型ID，如果不传则获取所有类型
	*	@return array
	*/
	public function getUseCount($type_id = 0){
		$where = array();
		if($type_id > 0){
			$where['bonus_type_id'] = $type_id;
		}
		$where['used_time'] = array('GT',0);
		$data = $this->field('bonus_type_id, COUNT(*) AS used_count, SUM(use_amount) AS use_sum, SUM(get_amount) AS get_sum')->where($where)->group('bonus_type_id')->select();
		
		if($type_id > 0){
			return isset($data[0]) ? $data[0] : array();
		}
		$count = array();
		if(!empty($data)){
			foreach($data as $value){
				$count[$value['bonus_type_id']] = $value;
			}
		}
		return $count;
	}
}
