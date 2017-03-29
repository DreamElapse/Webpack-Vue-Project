<?php
/**
 * ====================================
 * 会员中心 里面的商品属性模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-02-08 13:49
 * ====================================
 * File: GoodsAttrCenterModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\UserCenterModel;
use Common\Extend\Time;

class GoodsAttrCenterModel extends UserCenterModel{
	protected $_config = 'USER_CENTER';
    protected $tableName = 'goods_attr';
}