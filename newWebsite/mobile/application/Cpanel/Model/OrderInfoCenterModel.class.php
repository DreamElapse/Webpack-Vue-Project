<?php
/**
 * ====================================
 * 订单 管理模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-03-17 14:46
 * ====================================
 * File: OrderInfoCenterModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\Time;
use Common\Extend\PhxCrypt;

class OrderInfoCenterModel extends CpanelUserCenterModel {
    protected $tableName = 'order_info';

	public function filter($params){
	    $where = array();

	    $where['is_chinaskin'] = empty($params['is_chinaskin']) ? 0 : $params['is_chinaskin'];

	    if(!empty($params['site_id'])){
	        $where['site_id'] = $params['site_id'];
        }

		if (!empty($params['keyword'])) {
			switch($params['keyword_type']){
				case 1:  //订单号搜索
                    $where['order_sn'] = $params['keyword'];
				break;
//				case 2:  //订单ID搜索
//                    $where['order_id'] = $params['keyword'];
//				break;
				case 3:  //收货手机号搜索
                    $mobile = PhxCrypt::phxEncrypt($params['keyword']);
                    $_map['mobile'] = $mobile;
                    $_map['tel'] = $mobile;
                    $_map['_logic'] = 'OR';
                    $where['_complex'] = $_map;
				break;
				case 4:  //收货人搜索
                    $where['consignee'] = $params['keyword'];
				break;
				case 5:  //会员ID搜索
                    $where['user_id'] = $params['keyword'];
				break;
			}
        }


        $begin_time = empty($params['begin_time']) ? 0 : strtotime($params['begin_time']);
        $end_time = empty($params['end_time']) ? 0 : strtotime($params['end_time']);

        if(!empty($begin_time) && !empty($end_time) && $begin_time <= $end_time){
            $where['add_time'] = array(array('EGT',$begin_time),array('ELT',$end_time));
        }elseif(!empty($begin_time) && empty($end_time)){
            $where['add_time'] = array('EGT',$begin_time);
        }elseif (empty($begin_time) && !empty($end_time)){
            $where['add_time'] = array('ELT',$end_time);
        }

        $this->where($where);

		$field = 'id,site_id,order_sn,order_id,user_id,order_status,shipping_status,pay_status,consignee,pay_name,goods_amount,order_amount,add_time,update_time,real_shipping_time,shipping_name';
		$this->field($field);
		$this->order('id desc');

		return $this;
	}



	
	public function format($data){
		if(!empty($data['rows'])){
			$site_name = L('site_name');
			$order_status = L('order_status');
			$order_shipping = L('order_shipping');
			$pay_status = L('pay_status');
			foreach($data['rows'] as $k=>$v){
				$v['add_time'] = $v['add_time']!='' ? Time::localDate('Y-m-d H:i:s', strtotime($v['add_time'])) : '';
				$v['update_time'] = $v['update_time']!='' ? Time::localDate('Y-m-d H:i:s', strtotime($v['update_time'])) : '';
				$v['real_shipping_time'] = $v['real_shipping_time']!='' ? Time::localDate('Y-m-d H:i:s', strtotime($v['real_shipping_time'])) : '';
				
				$v['site_name']   = isset($site_name[$v['site_id']])? $site_name[$v['site_id']] : $v['site_id'];
				$v['order_state_name'] = isset($order_status[$v['order_status']])? $order_status[$v['order_status']] :$v['order_status'];
				$v['shipping_status_text']  = isset($order_shipping[$v['shipping_status']])? $order_shipping[$v['shipping_status']] : $v['shipping_status'];
				$v['pay_status'] = isset($pay_status[$v['pay_status']])? $pay_status[$v['pay_status']] : $v['pay_status'];
				$v['pay_name'] =  preg_replace("/<(.*?)>/","",$v['pay_name']);
				$data['rows'][$k] = $v;
			}
		}
		return $data;
	}
	
	/*
	*	查询订单详情
	*	@Author 9009123 (Lemonice)
	*	@param  int $id 订单表的记录ID
	*	@return array
	*/
	public function info($id = 0){
		if($id <= 0){
			return array();
		}
		$data = $this->where(array('id'=>$id))->find();
		
		$data['add_time'] = $data['add_time'] > 0 ? date('Y-m-d H:i:s',$data['add_time']) : '';
		$data['update_time'] = $data['update_time'] > 0 ? date('Y-m-d H:i:s',$data['update_time']) : '';
		$data['real_shipping_time'] = $data['shipping_time'] > 0 ? date('Y-m-d H:i:s',$data['real_shipping_time']) : '';
		$data['shipping_time'] = $data['shipping_time'] > 0 ? date('Y-m-d H:i:s',$data['shipping_time']) : '';
		
		$format = array(
			'rows'=>array(0=>$data),
		);
		$format = $this->format($format);
		$data = $format['rows'][0];
		$data['goods'] = D('OrderGoodsCenter')->getOrderGoods($data['order_id'], $data['site_id']);
		return $data;
	}
}
