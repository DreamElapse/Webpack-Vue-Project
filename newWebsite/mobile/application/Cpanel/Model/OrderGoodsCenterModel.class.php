<?php
/**
 * ====================================
 * 订单商品 管理模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-03-17 14:46
 * ====================================
 * File: OrderGoodsCenterModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;

class OrderGoodsCenterModel extends CpanelUserCenterModel {
    protected $tableName = 'order_goods';

	/*
	*	根据订单号查询对应商品
	*	@Author 9009123 (Lemonice)
	*	@param  int $order_id 订单ID
	*	@param  int $site_id 站点ID
	*	@return array
	*/
	public function getOrderGoods($order_id = 0, $site_id = 0){
		if($order_id <= 0 || $site_id <= 0){
			return array();
		}
		$goods = $this->where(array('site_id'=>$site_id,'order_id'=>$order_id))->select();
		if(!empty($goods)){
			foreach($goods as $k => $v){
				if($goods[$k]['extension_code'] == 'package_buy'){
					$goods[$k]['extension_code'] = '套装';
				}else{
					$goods[$k]['extension_code'] = '单品';
				}
			}
		}
		return $goods;
//		return array('total' => count($goods),'rows' => $goods,'pagecount' => 0);
	}
}
