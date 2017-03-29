<?php
/**
 * ====================================
 * 菜单管理
 * ====================================
 * Author: Hugo
 * Date: 14-5-20 下午9:58
 * ====================================
 * File: MenuController.class.php
 * ====================================
 */

namespace Cpanel\Controller;

use Common\Controller\CpanelController;

class MenuController extends CpanelController {
    protected $tableName = 'Menu';
    protected $allowAction = array('icon');
}