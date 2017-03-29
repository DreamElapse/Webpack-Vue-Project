<?php
/**
 * ====================================
 * 积分日志
 * ====================================
 * Author: 9004396
 * Date: 2017-02-06 16:01
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: UserPointLogModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Extend\Time;
use Common\Model\UserCenterModel;

class UserPointLogModel extends UserCenterModel{
	//积分日记的类型
	private $point_type = array(
		0=>'订单积分',
		1=>'签到积分',
		2=>'评论积分',
	);
	
	//积分变动状态
	private $state = array(
		'-4'=>'客服消费',
		'-3'=>'自主消费',
		'-2'=>'积分过期',
		'-1'=>'删除（订单退货）',
		'0'=>'正常',
		'2'=>'积分商品删除',
		'3'=>'订单无效',
		'4'=>'取消订单',
	);
	
	/*
	*	分页处理
	*	@Author Lemonice
	*	@param  string $field 查询的字段
	*	@param  string $where 查询条件
	*	@param  string $order 排序的字段
	*	@param  int $page 当前页，第几页
	*	@param  int $pageSize 每页显示多少条
	*	@return array
	*/
    public function getPage($field = '*', $where = '',$order = '', $page = 1, $pageSize = 0) {
		$total = 0;
		$pageTotal = 1;
		
		//是否启用分页
		if($pageSize > 0){
			$total = $this->where($where)->count();  //统计总记录数
			$this->page($page.','.$pageSize);
			$pageTotal = ceil($total / $pageSize);  //计算总页数
		}else{
			$page = 1;
		}
		if($order != ''){
			$this->order($order);
		}
		$list = $this->field($field)->where($where)->select();
		$total = $total > 0 ? $total : count($list);
		if(!empty($list)){
			foreach($list as $key=>$info){
				$list[$key] = $this->logFormat($info);
			}
		}
		
        return array('page' => $page, 'pageSize' => $pageSize, 'total' => (int)$total, 'pageTotal' => $pageTotal, 'list' => $list);
    }
	
	public function logFormat($info){
		if(isset($info['add_time']) && $info['add_time'] > 0){
			$info['add_date'] = Time::localDate('Y-m-d H:i:s',$info['add_time']);
		}elseif(isset($info['add_time']) && $info['add_time'] == 0){
			$info['add_date'] = '';
		}
		if(isset($info['point_type'])){
			$info['point_type_name'] = isset($this->point_type[$info['point_type']]) ? $this->point_type[$info['point_type']] : '未知类型';
		}
		if(isset($info['state'])){
			$info['state_name'] = isset($this->state[$info['state']]) ? $this->state[$info['state']] : '未知变动状态';
		}
		return $info;
	}
}