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

class WxLogCenterModel extends CpanelUserCenterModel {
    protected $tableName = 'wx_log';
	
	
	public function filter($params){
	    $where = array();
        if (!empty($params['invoice_no'])){  //快递单号
            $where['invoice_no'] = $params['invoice_no'];
        }
        if (!empty($filter['order_sn'])){  //订单号
            $where['order_id'] = $params['order_sn'];
        }
		$this->where($where);
		
		$field = 'id,mobile,invoice_no,order_id,msg,add_time,logistics_platform';
        $this->field($field);
        $this->order('id DESC');
		return $this;
	}
	
	public function format($data){
		if(!empty($data['rows'])){
			foreach($data['rows'] as $k=>$v){
				$v['msg'] = addslashes($v['msg']);
				$v['mobile']   = PhxCrypt::phxDecrypt($v['mobile']);
				$v['add_time'] = $v['add_time']!='' ? Time::localDate('Y-m-d H:i:s', strtotime($v['add_time'])) : '';
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
		
		$data['msg'] = str_replace(array("\r\n","\n"),'<br>',$data['msg']);
		
		$data['goods'] = D('OrderGoodsCenter')->getOrderGoods($data['order_id'], $data['site_id']);
		return $data;
	}
}
