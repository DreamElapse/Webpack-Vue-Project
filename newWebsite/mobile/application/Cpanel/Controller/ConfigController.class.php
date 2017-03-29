<?php
/**
 * ====================================
 * 配置管理
 * ====================================
 * Author: Hugo
 * Date: 14-5-20 下午9:58
 * ====================================
 * File: SettingController.class.php
 * ====================================
 */
namespace Cpanel\Controller;

use Common\Controller\CpanelController;

class ConfigController extends CpanelController{
    protected $tableName = 'Config';

    public function group() {
        $list = $this->dbModel
            ->field('id,name,title,extra,value,remark,type, group')
            ->order('orderby')
            ->select();
        if($list) {
            $config = array();
            foreach($list as $row){
                $config[$row['group']][] = $row;
            }
            $this->assign('list', $config);
        }
        $this->display();
    }

    public function group_save($config) {
        if($config && is_array($config)){
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $this->dbModel->where($map)->setField('value', $value);
            }
        }
        S('DB_CONFIG_DATA',null);
        $this->success(L('SAVE') . L('SUCCESS'));
    }
}