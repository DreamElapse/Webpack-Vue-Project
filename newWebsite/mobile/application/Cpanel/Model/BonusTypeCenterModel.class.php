<?php
/**
 * ====================================
 * 优惠券类型 管理模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-03-17 14:46
 * ====================================
 * File: BonusTypeCenterModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\Time;

class BonusTypeCenterModel extends CpanelUserCenterModel {
    protected $tableName = 'bonus_type';
	
	
	public function filter($params){
		$params['sort'] = !empty($params['sort']) ? $params['sort'] : 'type_id';
		$params['order'] = !empty($params['order']) ? $params['order'] : 'desc';
		
        //查询条件
        $where = array();

        if (isset($params['coupon_type']) && $params['coupon_type'] != '-1'){
            $where['coupon_type'] = intval($params['coupon_type']);
        }
        if (isset($params['send_type']) && $params['send_type'] != '-1'){
            $where['send_type'] = intval($params['send_type']);
        }
		if (isset($params['coupon_range']) && $params['coupon_range'] != '-1'){
            $where['coupon_range'] = intval($params['coupon_range']);
        }
		if (isset($params['is_package']) && $params['is_package'] != '-1'){
            $where['is_package'] = intval($params['is_package']) ;
        }
		if (isset($params['reuse']) && $params['reuse'] != '-1'){
            $where['reuse'] = intval($params['reuse']) ;
        }
		if (!empty($params['type_name'])){
            $where['type_name'] = array('LIKE',"%".trim($params['type_name'])."%");
        }
		$this->where($where);
		return $this->order($params['sort'].' '.$params['order']);
	}
	
	public function format($data){
		if(!empty($data['rows'])){
			$UserBonusCenterModel = D('UserBonusCenter');
			$send_by = L('send_by');
			$coupon_type = L('coupon_type');
			$coupon_range = L('coupon_range');
			$send_count = $UserBonusCenterModel->getSendCount();  //获取发放统计
			$use_count_info = $UserBonusCenterModel->getUseCount();  //获取使用统计
			
			foreach($data['rows'] as $k=>$v){
				$v['send_count'] = isset($send_count[$v['type_id']]) ? $send_count[$v['type_id']] : 0;
				if($v['reuse'] == 0) {
					$v['use_count'] = isset($use_count_info[$v['type_id']]['used_count']) ? $use_count_info[$v['type_id']]['used_count'] : 0;
				}else{
					$v['use_count'] = isset($use_count_info[$v['type_id']]['use_sum']) ? $use_count_info[$v['type_id']]['use_sum'] : 0;
				}
				$v['get_sum'] = isset($use_count_info[$v['type_id']]['get_sum']) ? $use_count_info[$v['type_id']]['get_sum'] : 0;
				$v['send_by'] = $send_by[$v['send_type']];
				$v['add_time'] = $v['add_time']!='' ? Time::localDate('Y-m-d H:i:s', strtotime($v['add_time'])) : '';
				
				$v['reuse_name'] = '<span style="font-size:18px;" class="fa '.($v['reuse']==1 ? 'fa-check green' : 'fa-close red').'"> </span>';  //重复使用
				//$v['is_member_discount_name'] = '<span style="font-size:18px;" class="fa '.($v['is_member_discount']==1 ? 'fa-check green' : 'fa-close red').'"> </span>';  //可否和会员优惠同时使用
				//$v['is_payonline_discount_name'] = '<span style="font-size:18px;" class="fa '.($v['is_payonline_discount']==1 ? 'fa-check green' : 'fa-close red').'"> </span>';  //可否和在线支付优惠同时使用
				//$v['is_other_gift_name'] = '<span style="font-size:18px;" class="fa '.($v['is_other_gift']==1 ? 'fa-check green' : 'fa-close red').'"> </span>';  //可否和其他优惠产品同时使用
				$v['is_package_name'] = '<span style="font-size:18px;" class="fa '.($v['is_package']==1 ? 'fa-check green' : 'fa-close red').'"> </span>';  //是否只要套装才能参与
				
				$v['use_site_name'] = $v['use_site'] == '0' ? '<span style="color:green;">所有站点</span>' : $v['use_site'];  //可使用站点
				
				/*$v['coupon_type_name'] = isset($coupon_type[$v['coupon_type']]) ? $coupon_type[$v['coupon_type']] : '';
				$v['coupon_range_name'] = isset($coupon_range[$v['coupon_range']]) ? $coupon_range[$v['coupon_range']] : '';
				
				if(isset($v['min_amount']) && isset($v['max_amount'])){
					$v['amount_name'] = '';
					if($v['min_amount'] == 0 && $v['max_amount'] == 0){
						$v['amount_name'] = '<span style="color:green;">不限制</span>';
					}else if($v['min_amount'] == 0 && $v['max_amount'] > 0){
						$v['amount_name'] = '最大'.$v['max_amount'].'元';
					}else if($v['min_amount'] > 0 && $v['max_amount'] == 0){
						$v['amount_name'] = '最小'.$v['min_amount'].'元';
					}else if($v['min_amount'] > 0 && $v['max_amount'] > 0){
						$v['amount_name'] = $v['min_amount'].' - '.$v['max_amount'].'元';
					}
				}*/
				
				
				$data['rows'][$k] = $v;
			}
		}
		return $data;
	}
	
	/*
	*	查询优惠券类型详情
	*	@Author 9009123 (Lemonice)
	*	@param  int $id 订单表的记录ID
	*	@return array
	*/
	public function info($id = 0){
		if($id <= 0){
			return array();
		}
		$data = $this->field($field)->where(array('type_id'=>$id))->find();
		
		$data['add_time'] = $data['add_time'] > 0 ? date('Y-m-d H:i:s',$data['add_time']) : '';  //此处的时间必须用当前的date函数，兼容$this->format()
		
		$format = array(
			'rows'=>array(0=>$data),
		);
		$format = $this->format($format);
		$data = $format['rows'][0];
		
		return $data;
	}
	
	/*
	*	取得红包类型数组（用于生成下拉列表）
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function getBonusTypeList($send_type = 3) {
		$where = array(
			'send_type'=>$send_type,
		);
		$result = $this->field('type_id, type_name, type_money')->where($where)->select();
		$bonus_type = array();
		if(!empty($result)){
			foreach($result as $value){
				$bonus_type[$value['type_id']] = $value['type_name'].' [' .$value['type_money'].']';
			}
		}
		return $bonus_type;
	}
}
