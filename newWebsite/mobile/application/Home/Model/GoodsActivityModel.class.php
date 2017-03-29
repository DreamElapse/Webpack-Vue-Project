<?php
/**
 * ====================================
 * 套装商品相关数据模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-03-06 18:19:30
 * ====================================
 * File: GoodsActivityModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;

class GoodsActivityModel extends CommonModel{

    /**
	 *	获取套装的商品ID
	 *  @Author: 9009123 (Lemonice) 
	 *	@param int $act_id 套装ID
	 *	return int
	 */
    public function getPackageGoodsId($act_id = 0){
        if(intval($act_id) == 0){
			return 0;
		}
		$goods_id = $this->where("act_id = '$act_id'")->getField('goods_id');
        return ($goods_id ? $goods_id : 0);
    }
}