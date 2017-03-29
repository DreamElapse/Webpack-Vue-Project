<?php
/**
 * ====================================
 * 品牌调查模型
 * ====================================
 * Author: 9004396
 * Date: 2017-02-17 16:06
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: SurveyModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelModel;

class SurveyModel extends CpanelModel{
    protected $_validate = array(
        array('name','require','{%name_lost}'),
        array('content','content','{%content_lost}',self::MUST_VALIDATE,'callback'),
        array('start_time','require','{%stime_lost}'),
        array('end_time','require','{%etime_lost}'),
    );


    protected function content($content){
        if(!empty($content)){
            return true;
        }else{
            return false;
        }
    }
}