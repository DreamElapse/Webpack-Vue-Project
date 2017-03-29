<?php
/**
 * ====================================
 * 大转盘模型
 * ====================================
 * Author:9006758
 * Date:
 * ====================================
 * File: RotateLogModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;

class RotateLogModel extends CpanelUserCenterModel {
    protected $tableName = 'wx_rotates_log';

    public function filter(&$param){
        $keywords = !empty($param['keywords']) ? trim($param['keywords']) : '';
        $start_time = !empty($param['start_time']) ? strtotime(trim($param['start_time'])) : 0;
        $end_time = !empty($param['end_time']) ? strtotime(trim($param['end_time'])) : 0;
        if(!empty($keywords)){
            $where['_string'] = "(prize_name like '%$keywords%' or act_name like '%$keywords%' or nick_name like '%$keywords%')";
        }
        if($start_time && $end_time){
            $where['add_time'] = array('between', array($start_time, $end_time));
        }
        if(!empty($param['id'])){
            $where['rotate_id'] = intval($param['id']);
        }

        $this->where($where);
    }

}