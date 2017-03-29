<?php
/**
 * ====================================
 * 微信菜单模型
 * ====================================
 * Author: 9004396
 * Date: 2017-01-13 10:00
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: WechatMenuModel.class.php
 * ====================================
 */
namespace Cpanel\Model;

use Common\Model\CpanelModel;
use Common\Extend\Base\Common;

class WechatMenuModel extends CpanelModel{

    protected $_auto = array(
        array('parent_id','autoParent',self::MODEL_BOTH,'callback'),
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    protected $_validate = array(
        array('menu_name','require','{%menu_name_lost}'),
        array('action_param','checkParmas','{%params_url_lost}',self::EXISTS_VALIDATE,'callback'),
        array('parent_id','fullRecode','{%level_upper_limit}',self::MUST_VALIDATE,'callback'),
    );

    /**
     * 自动补充父级ID
     * @param $value
     * @return int
     */
    protected function autoParent($value){
        return empty($value) ? 0 : $value;
    }

    /**
     * 检测属性参数
     * @param $value
     * @return bool
     */
    protected function checkParmas($value){
        $params = I('post.');
        if($params['action'] == 'url'){
            if(strpos($value,'http://') !== false || strpos($value,'https://') !== false){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }

    /**
     * 检测栏目数量
     * @param $value
     * @return bool
     */
    protected function fullRecode($value){
        $params = I('post.');
        $level = $params['level'];
        $parent_id = (!isset($params['pid']) || empty($params['pid'])) ? 0 : $params['pid'];
        $where = array('pid' => $parent_id);
        if(!empty($params['id'])){
            $where['id'] = array('neq',$params['id']);
        }
        if($level == 0){
            $count = $this->where($where)->count();
            if($count == 3 ){
                return false;
            }
        }else{
            $count = $this->where($where)->count();
            if($count == 5){
                return false;
            }
        }
        return true;
    }


    public function filter($parmas = array()){
        $where = array();
        if(!empty($parmas['level'])){
            switch ($parmas['level']){
                case 1:
                    $level = 0;
                    break;
            }
            $where['pid'] = $level;
        }
        if(isset($parmas['locked'])){
            $where['locked'] = $parmas['locked'];
        }
        $this->where($where);
        return $this;
    }


    public function grid($params = array()){
        $data = $this->order("pid DESC, orderby ASC,id DESC")->getAll();
        if($data){
            foreach($data as $key => $row){
                $row['level'] = $row['pid'] == 0 ? 0:1;
                $data[$key] = $row;
            }
            Common::tree($data, $params['selected'], $params['type']);
        }
        return $data ? $data : array();
    }
}