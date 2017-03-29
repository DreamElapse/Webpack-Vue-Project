<?php
/**
 * ====================================
 * 计划任务日志模型
 * ====================================
 * Author: 9004396
 * Date: 2017-03-02 10:38
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: CronLogModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Extend\Time;
use Common\Model\CpanelUserCenterModel;

class CronLogModel extends CpanelUserCenterModel{

    public function filter($params){
        $where = array();
        $beginTime = !empty($params['begin_time']) ? Time::localStrtotime($params['begin_time']) : 0;
        $endTime =  !empty($params['end_time']) ? Time::localStrtotime($params['end_time']) : 0;

        if ($beginTime > 0 && $endTime > 0 && $beginTime <= $endTime) {
            $where['start_time'] = array(array('EGT', $beginTime), array('ELT', $endTime));
        } elseif ($beginTime > 0 && $endTime == 0) {
            $where['start_time'] = array('EGT', $beginTime);
        } elseif ($endTime > 0 && $beginTime == 0) {
            $where['start_time'] = array('ELT', $endTime);
        }

        if(!empty($params['name_keyword'])){
            $where['name'] = array('LIKE', "%{$params['name_keyword']}%");
        }
        if(!empty($params['title_keyword'])){
            $where['title'] = array('LIKE', "%{$params['title_keyword']}%");
        }
        if(!empty($params['content_keyword'])){
            $where['content'] = array('LIKE', "%{$params['content_keyword']}%");
        }
        $day = 0;
        if(!empty($params['log_date']) && $params['log_date'] > 0){
            switch ($params['log_date']){
                case 1:
                    $day = 7;
                    break;
                case 2:
                    $day = 30;
                    break;
                case 3:
                    $day = 90;
                    break;
                case 4:
                    $day = 180;
                    break;
                case 5:
                    $day = 365;
                    break;
            }
        }
        if($day > 0){
            $tims = Time::gmTime()-(3600*24*$day);
            $where['start_time'] = array('EGT',$tims);
        }

        $this->order('id desc');
        return $this->where($where);
    }

    public function format($data){
        if (!empty($data)) {
            foreach ($data['rows'] as &$item) {
                $item['start_time'] = Time::localDate('Y-m-d H:i:s',strtotime($item['start_time']));
                $item['add_time'] = Time::localDate('Y-m-d H:i:s',strtotime($item['add_time']));
            }
        }
        return $data;
    }
}