<?php
/**
 * ====================================
 * API查询快递日记 管理模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-03-17 14:46
 * ====================================
 * File: ApiLogisticsLogCenterModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\Time;
use Common\Extend\PhxCrypt;

class ApiLogisticsLogCenterModel extends CpanelUserCenterModel {
    protected $tableName = 'api_logistics_log';
	
	public function filter($params){
	    $where = array();

		if (!empty($params['mobile'])){//手机号码
            $where['mobile'] = PhxCrypt::phxEncrypt($params['mobile']);

		}
		if (!empty($params['invoice_no'])){//快递单号
            $where['invoice_no'] = $params['invoice_no'];
		}
		if (!empty($params['order_sn'])){//订单号
            $where['order_sn'] = $params['order_sn'];
		}
		if (!empty($params['status'])){//状态码
            $where['status'] = $params['status'];
		}
		$this->where($where);
		
		$field = 'id,mobile,invoice_no,order_sn,status,add_time';
		$this->field($field);
		$this->order('id DESC');
		return $this;
	}
	
	public function format($data){
		if(!empty($data['rows'])){
			foreach($data['rows'] as $k=>$v){
				$v['add_time'] = $v['add_time']!='' ? Time::localDate('Y-m-d H:i:s', strtotime($v['add_time'])) : '';
				
				$v['mobile']   = PhxCrypt::phxDecrypt($v['mobile']);
				if(isset($v['data'])){
					$v['data'] = $v['data'] != '' ? unserialize(base64_decode($v['data'])) : array();
				}
				
				$data['rows'][$k] = $v;
			}
		}
		return $data;
	}
	
	/*
	*	查询日记详情
	*	@Author 9009123 (Lemonice)
	*	@param  int $id 日记ID
	*	@return array
	*/
	public function info($id = 0){
		if($id <= 0){
			return array();
		}
		$data = $this->where(array('id'=>$id))->find();
		
		$data['add_time'] = $data['add_time'] > 0 ? date('Y-m-d H:i:s',$data['add_time']) : '';
		
		$format = array(
			'rows'=>array(0=>$data),
		);
		$format = $this->format($format);
		$data = $format['rows'][0];
		return $data;
	}
}
