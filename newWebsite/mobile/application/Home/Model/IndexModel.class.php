<?php
/**
 * ====================================
 * 测试模版
 * ====================================
 * Author: 9004396
 * Date: 2016-07-06 17:29
 * ====================================
 * File:IndexModel.class.php
 * ====================================
 */
namespace Home\Model;

use Common\Model\CustomizeModel;

class IndexModel extends CustomizeModel{

    protected $_config = 'USER_CENTER';
    protected $_table = 'users';

    public function getInfo(){
        return $this->select();
    }
}