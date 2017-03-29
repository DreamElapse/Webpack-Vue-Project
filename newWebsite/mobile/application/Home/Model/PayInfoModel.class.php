<?php
/**
 * ====================================
 * 支付相关信息模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-04 17:46
 * ====================================
 * File: this.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\PaymentsModel;
use Common\Extend\Time;

class PayInfoModel extends PaymentsModel{
	
	/*
	*	订单是否已经支付
	*	@Author 9009123 (Lemonice)
	*	@param integer $order_sn  订单号
    *	@param $integer $pay_id  支付id
	*	@return void
	*/
    public function isPayed($order_sn, $pay_id) {
		return $this->where("order_sn = '$order_sn' AND pay_id = '$pay_id' AND status = 1")->count();
    }
	
	/*
     * 检查订单是否付款全部金额 
     * @param integer $order_sn  订单号
     * @param integer $pay_id  支付id
	 * @param integer $order_money 订单总金额
     */
    public function orderMoneyIsPayed($order_sn,$pay_id, $order_money){
		$sql="SELECT COUNT(*) AS num FROM ".$this->_db->getTable('pay_info')." WHERE `order_sn`='{$order_sn}' AND `pay_id`='{$pay_id}' AND order_amount > 0 AND order_amount >= '{$order_money}' AND `status`=1";
        return $this->_db->getOne($sql);
    }
    
	/*
	*	更新订单详情信息
	*	@Author 9009123 (Lemonice)
	*	@param integer $order_sn  订单号
    *	@param $integer $time  时间戳，子单号
	*	@return int [id]
	*/
    public function insertPayInfo($order, $time = 0){
        if(!is_array($order) || empty($order)){
			return false;
		}
		
		if(strstr($order["order_sn"],'_') !== false){
			$ordersn_array = explode('_',$order["order_sn"]);
			$ordersn = $ordersn_array[0];
		}else{
			$ordersn = $order["order_sn"];
		}
		
		$order_money = isset($order['order_money']) ? $order['order_money'] : 0;
		
		$time = $time > 0 ? $time : Time::gmTime();
		$res = $this->field('id,status,order_money')->where("`order_sn`='{$ordersn}' AND `pay_id`='{$order[pay_id]}'")->find();
		
		$site_id = isset($order['site_id']) ? $order['site_id'] : C('SITE_ID');
		$order_info_id = 0;
		if(!empty($res)){
			if($res['status'] == 0){  //还没有成功支付过的，更新包括金额在内的所有信息，有成功支付过的不更新，因为金额需要在成功后叠加
				$rs = $this->where("id = '$res[id]'")->update(array(
					'site_id'=>$site_id,
					'name'=>$order['consignee'],
					'order_money'=>$order_money,
					'order_amount'=>$order['order_amount'],
				));
			}else{  //更新收货人名称
				$data = array(
					'site_id'=>$site_id,
					'name'=>$order['consignee'],
				);
				if($res['order_money'] <= 0){
					$data['order_money'] = $order_money;
				}
				$rs = $this->where("id = '$res[id]'")->update($data);
			}
			if (!isset($rs->res) || !$rs){ //操作异常,记录异常日志     
				\Think\Log::record('更新订单信息出错了, 订单号：'.$order['order_sn'].', SQL:'.$this->getLastSql());
			}
		}else{  //没有记录，插入新记录
			$data = array(
				'site_id'=>$site_id,
				'pay_id'=>$order['pay_id'],
				'order_sn'=>$ordersn,
				'name'=>$order['consignee'],
				'order_money'=>$order_money,
				'order_amount'=>$order['order_amount'],
				'add_time'=>$time,
			);
			$order_info_id = $this->insert($data);
			if (!isset($rs->res) || !$rs){ //操作异常,记录异常日志
				\Think\Log::record('更新订单信息出错了, 订单号：'.$order['order_sn'].', SQL:'.$this->getLastSql());
			}
		}
		
		//记录到子单号详情
		$PayMultipleLogModel = D('Home/PayMultipleLog');
		$order_info_id = $order_info_id > 0 ? $order_info_id : ($res['id'] ? $res['id'] : 0);
		$log_id = $PayMultipleLogModel->where("site_id = '$site_id' and pay_id = '$order[pay_id]' and order_sn_child = '$order[order_sn]'")->getField('id');
		$data = array(
			'order_info_id'=>$order_info_id,
			'site_id'=>$site_id,
			'pay_id'=>$order['pay_id'],
			'order_sn'=>$ordersn,
			'order_sn_child'=>$order['order_sn'],
			'pay_amount'=>$order['order_amount'],
			'add_time'=>$time,
		);
		if($log_id > 0){
			$PayMultipleLogModel->where("id = '$log_id'")->update($data);
		}else{
			$log_id = $PayMultipleLogModel->insert($data);
		}
		if (!isset($res->res) || !$res){ //操作异常,记录异常日志
			\Think\Log::record('更新订单信息出错了, 订单号：'.$order['order_sn'].', SQL:'.$PayMultipleLogModel->getLastSql());
		}
        return $log_id;
    }
	
	
	/*
	*	修改订单的支付状态
	*	@Author 9009123 (Lemonice)
	*	@param array $param  订单详情
	*	@return int [id or result]
	*/
	public function orderPaidNew($param){
		if (!isset($param['order_sn'],$param['pay_id'],$param['order_amount'])){
			return false;
		}
		
		//为了避免微信重复支付时候订单号重复的问题
		$ordersn = $param['order_sn'];
		if(strstr($param['order_sn'],'_') !== false){
			$tmp = explode('_', $param['order_sn']);
			$param['order_sn'] = $tmp[0];
		}
		
		$order_sn_child = $ordersn;
		$order_sn = $param['order_sn'];
		$pay_id = $param['pay_id'];
		$order_amount = $param['order_amount'];
		$now_time = Time::gmTime();
		$pay_time = isset($param['pay_time']) ? $param['pay_time'] : $now_time;
		$add_time = isset($param['create_time']) ? $param['create_time'] : $now_time;
		
		$site_id = isset($param['site_id']) ? $param['site_id'] : C('SITE_ID');
		
		$PayMultipleLogModel = D('PayMultipleLog');
		
		
		$res = $this->field("`order_amount`,`id`,`status`")->where("`order_sn`='{$order_sn}' AND `pay_id`='{$pay_id}'")->find();
		$result = 1;
		if (!empty($res) && isset($res['order_amount'])){
			//$note = $order_amount == $res['order_amount'] ? '' : '金额有误,原金额：'.$res['order_amount'];
			//$status = $order_amount == $res['order_amount'] ? 1 : 2;
			
			//检查该子单号是否已经支付过了，如果支付过了，则此次支付不再累加"已支付"金额
			$pay_amount = $PayMultipleLogModel->where("`order_sn`='{$order_sn}' AND `pay_id`='{$pay_id}' AND (`synchro_status`=1 or `asynch_status`=1 or `order_sn_child`='{$order_sn_child}')")->sum('pay_amount');
			
			$data = array(
				'pay_time'=>$pay_time,
				'status'=>1,
				'note'=>'',
			);
			if($pay_amount > 0){
				$data['order_amount'] = $pay_amount;
			}
			
			$this->where("`order_sn`='{$order_sn}' AND `pay_id`='{$pay_id}'")->update($data);
		}else{
			if ($pay_time < $now_time-7*86400){ //不再记录一个星期以前的异常订单
				return false;
			}
			$data = array(
				'site_id'=>$site_id,
				'pay_id'=>$pay_id,
				'order_sn'=>$order_sn,
				'name'=>'异常用户',
				'order_amount'=>$order_amount,
				'source'=>3,
				'status'=>1,
				'add_time'=>$add_time,
				'pay_time'=>$pay_time
			);
			$insert_id = $this->insert($data);
		}
		
		//插入子单号详情表
		$id = $PayMultipleLogModel->where("`order_sn_child`='{$order_sn_child}' AND `pay_id`='{$pay_id}'")->getField('id');
		$synchro_status = $param['verify_type'] == 1 ? 1 : 0;
		$asynch_status = $param['verify_type'] == 2 ? 1 : 0;
		if($id > 0){
			$data = array(
				'site_id'=>$site_id,
				'pay_time'=>$now_time,
			);
			if($synchro_status > 0){
				$data['synchro_status'] = $synchro_status;
			}
			if($asynch_status > 0){
				$data['asynch_status'] = $asynch_status;
			}
			if($order_amount > 0){
				$data['pay_amount'] = $order_amount;
			}
			$PayMultipleLogModel->where("id = '$id'")->update($data);
		}else{
			$order_info_id = $res['id'] ? $res['id'] : (isset($insert_id) ? $insert_id : 0);
			$data = array(
				'order_info_id'=>$order_info_id,
				'site_id'=>$site_id,
				'pay_id'=>$pay_id,
				'order_sn'=>$order_sn,
				'order_sn_child'=>$ordersn,
				'pay_amount'=>$order_amount,
				'synchro_status'=>$synchro_status,
				'asynch_status'=>$asynch_status,
				'pay_time'=>$now_time,
			);
			$PayMultipleLogModel->insert($data);
		}
		
		return $result;
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