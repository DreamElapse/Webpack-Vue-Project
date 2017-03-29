<?php
/**
 * ====================================
 * 钱包管理模型
 * ====================================
 * Author: 9006758
 * Date: 
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: WalletModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\Time;

class WalletModel extends CpanelUserCenterModel
{
    protected $tableName = 'wallet_order_info';

	protected $lang_pay_status = array(
		0 => '未支付',
		2 => '已支付',
		3 => '已支付部分货款',
		4 => '异常支付',
	);
	protected $lang_order_status = array(
		0 => '未确定',
		1 => '已确定',
		2 => '已取消',
	);

	
	public function filter(&$params){
		$params['sort'] = !empty($params['sort']) ? $params['sort'] : 'id';
		$params['order'] = !empty($params['order']) ? $params['order'] : 'desc';
		
		$field = 'id,w_order_id,w_order_sn,custom_id,manager_group_id,consignee,amount,money_paid,';
		$field .= 'order_status,pay_status,add_kefu,accept_kefu,add_time,pay_time,cancel_time';
		
		if(!empty($params['keyword'])){
			$where['w_order_sn'] = trim($params['keyword']);
		}
		if(!empty($params['start_time']) && !empty($params['end_time'])){
			$start_time = Time::localStrtotime($params['start_time']);
			$end_time = Time::localStrtotime($params['end_time']);
			$where['add_time'] = array('between', array($start_time, $end_time));
		}
		if(!empty($where)){
			$this->where($where);
		}
		
		return $this->order($params['sort'].' '.$params['order'])->field($field);
	}
	
	public function format($data){
		if(!empty($data['rows'])){
			foreach($data['rows'] as &$v){
				$v['add_time'] = Time::localDate('Y-m-d H:i:s', strtotime($v['add_time']));
				$v['pay_time'] = Time::localDate('Y-m-d H:i:s', strtotime($v['pay_time']));
				$v['cancel_time'] = Time::localDate('Y-m-d H:i:s', strtotime($v['cancel_time']));
				$v['pay_status'] = (array_key_exists($v['pay_status'], $this->lang_pay_status) === TRUE) ? $this->lang_pay_status[$v['pay_status']] : $this->lang_pay_status[0];
				$v['order_status'] = (array_key_exists($v['order_status'], $this->lang_order_status) === TRUE) ? $this->lang_order_status[$v['order_status']] : $this->lang_order_status[0];
			}
		}
		return $data;
	}

}
