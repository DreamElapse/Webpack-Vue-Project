<?php
/**
 * ====================================
 * 会员中心 里面的套装子商品模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-02-08 13:49
 * ====================================
 * File: PackageGoodsCenterModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\UserCenterModel;
use Common\Extend\Time;

class PackageGoodsCenterModel extends UserCenterModel{
	protected $_config = 'USER_CENTER';
    protected $tableName = 'package_goods';
}