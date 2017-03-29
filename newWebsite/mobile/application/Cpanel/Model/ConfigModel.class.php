<?php
/**
 * ====================================
 * 系统配置模型
 * ====================================
 * Author: Hugo
 * Date: 14-5-20 下午9:28
 * ====================================
 * File: ConfigModel.class.php
 * ====================================
 */
namespace Cpanel\Model;

use Common\Model\CpanelModel;

class ConfigModel extends CpanelModel {
    protected $_auto = array(
        array('name', 'strtoupper', self::MODEL_BOTH, 'function'),
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    protected $_validate = array(
        array('name','require','{%name_lost}'),
        array('title','require','{%title_lost}'),
        array('name','','{%name_exists}', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
    );

    protected function _after_update($data) {
        S('DB_CONFIG_DATA',null);
    }

    protected function _after_delete($data) {
        S('DB_CONFIG_DATA',null);
    }

    protected function _after_insert($data) {
        S('DB_CONFIG_DATA',null);
    }

    public function filter($params) {
        $where = array();

        if($params['keywords']){
            $where['name'] = array('LIKE', "%{$params['keywords']}%");
            $where['title'] = array('LIKE', "%{$params['keywords']}%");
            $where['_logic'] = 'OR';
        }
        if(isset($params['type']) && $params['type'] > -1) $where['type'] = $params['type'];
        if(isset($params['group']) && $params['group'] > -1) $where['group'] = $params['group'];
        $this->where($where);
        return $where;
    }

    public function format($data) {
        $config_group_list = C('CONFIG_GROUP_LIST');
        $config_type_list = C('CONFIG_TYPE_LIST');
        foreach($data['rows'] as $key => $row){
            $row['group_name'] = $config_group_list[$row['group']];
            $row['type_name'] = $config_type_list[$row['type']];
            $data['rows'][$key] = $row;
        }
        return $data;
    }

}