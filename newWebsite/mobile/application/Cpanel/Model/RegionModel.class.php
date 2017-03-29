<?php
/**
 * ====================================
 * 地区数据模型
 * ====================================
 * Author: 91336
 * Date: 2014/11/29 9:34
 * ====================================
 * File: RegionModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelModel;
use Common\Extend\Base\Common;

class RegionModel extends CpanelModel {
    protected $_validate = array(
        array('text','require','{%region_name_lost}'),
        array('pid', '_validatePid', '{%parent_error}', self::EXISTS_VALIDATE, 'callback', self::MODEL_UPDATE),
        array('text','_validateName','{%region_name_exists}', self::MUST_VALIDATE, 'callback', self::MODEL_BOTH),
    );

    public function grid($params) {
        $where = array();
        if($params['id']) {
            $where['b.pid'] = (int)$params['id'];
        }elseif($params['keyword']) {
            $where['b.text'] = array('LIKE', "%{$params['keyword']}%");
        }else{
            $where['b.pid'] = 0;
        }

        if(empty($params['type'])) {
            $data = $this->alias(' AS b')
                ->where($where)
                ->join("__REGION__ AS s ON s.pid = b.id", 'left')
                ->field("b.id, b.pid, b.text, count(s.id) as have_children")
                ->group('b.id')
                ->order('b.pid ASC')
                ->select();

            foreach($data as $key => $row){
                if($row['have_children']) {
                    $row['state'] = 'closed';
                    $data[$key] = $row;
                }
            }
        }else {
            $data = $this->alias(' AS b')
                ->field("b.id, b.pid, b.text")
                ->order('b.pid ASC')
                ->select();
            Common::tree($data, $params['selected']);
        }

        return $data;
    }


    function getRegion($pid){
        $data = $this->where(array('pid' => $pid))->select();
        return $data;
    }
}