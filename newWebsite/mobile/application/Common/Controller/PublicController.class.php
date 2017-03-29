<?php
/**
 * ====================================
 * 公共控制器
 * ====================================
 * Author: 9004396
 * Date: 2017-01-10 19:49
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: PublicController.class.php
 * ====================================
 */
namespace Common\Controller;

use Think\Controller;
use Common\Extend\Base\Config;

class PublicController extends Controller {
    public $jumpUrl = '';

    public function __construct() {
        parent::__construct();
        Config::init();
    }

    public function _initialize(){
        $db_config = C('cpanel');
        C($db_config);
    }

}