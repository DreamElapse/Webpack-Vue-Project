<?php
/**
 * ====================================
 * 角色模型
 * ====================================
 * Author: Hugo
 * Date: 14-5-20 下午9:28
 * ====================================
 * File: RoleModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Extend\Base\Common;
use Common\Model\CpanelModel;

class RoleModel extends CpanelModel {
    protected $_validate = array(
        array('text','require','{%role_name_lost}'),
        array('text','','{%role_name_exists}', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
        array('pid', 'validateEq', '{%parent_error}', self::EXISTS_VALIDATE, 'callback', self::MODEL_UPDATE)
    );

    /**
     * 判断父级ID正确性
     * @param $pid
     * @param $id
     * @return bool
     */
    protected function validateEq($pid){
        return $pid != I('post.id');
    }

    public function grid($params) {
        $data = $this->field('*, id as role_id')->order("pid ASC, orderby DESC")->getAll();
        Common::tree($data, $params['selected'], $params['type']);
        return $data;
    }
}