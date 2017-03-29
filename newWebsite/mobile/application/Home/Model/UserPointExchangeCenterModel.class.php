<?php
/**
 * ====================================
 * 会员中心 里面的积分兑换记录模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-02-10 13:53
 * ====================================
 * File: UserPointExchangeCenterModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\UserCenterModel;
use Common\Extend\Time;

class UserPointExchangeCenterModel extends UserCenterModel{
	protected $_config = 'USER_CENTER';
    protected $tableName = 'user_point_exchange';
	
	
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
				$list[$key] = $this->infoFormat($info);
			}
		}
		
        return array('page' => $page, 'pageSize' => $pageSize, 'total' => (int)$total, 'pageTotal' => $pageTotal, 'list' => $list);
    }
	
	public function infoFormat($info){
		if(isset($info['addtime']) && $info['addtime'] > 0){
			$info['adddate'] = Time::localDate('Y-m-d H:i:s',$info['addtime']);
		}elseif(isset($info['addtime']) && $info['addtime'] == 0){
			$info['adddate'] = '';
		}
		if(isset($info['data']) && !empty($info['data'])){
			$info['goods_info'] = unserialize($info['data']);
			unset($info['data']);
		}
		return $info;
	}
}