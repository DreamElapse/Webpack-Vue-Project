<?php
namespace Cpanel\Model;
use Common\Model\CpanelModel;
use Common\Extend\Base\Common;

class GroupModel extends CpanelModel {
    protected $_validate = array(
        array('text','require','{%GROUP_NAME_EMPTY}'),
        array('text','','{%GROUP_NAME_EXISTS}', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
        array('pid', '_validatePid', '{%PARENT_ERROR}', self::EXISTS_VALIDATE, 'callback', self::MODEL_UPDATE)
    );

    public function grid($params) {
        if($params['keyword']) {
            $this->where(array(
                'text' => array('LIKE', "%{$params['keyword']}%")
            ));
        }
        $this
            ->field('*,id as group_id')
            ->order("pid ASC, orderby DESC");
        $data = $this->getAll();
        Common::tree($data, $params['selected'], $params['type']);
        return $data;
    }

}