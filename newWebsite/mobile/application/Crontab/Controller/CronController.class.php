<?php
/**
 * ====================================
 * 微信电子优惠券接口
 * ====================================
 * Author: 9004396
 * Date: 2016-11-09 10:47
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: CronController.php
 * ====================================
 */
namespace Crontab\Controller;

use Common\Controller\CrontabController;
use Common\Extend\Wechat;
use Common\Extend\Send;
use Common\Extend\PhxCrypt;

class CronController extends CrontabController {

    /**
     * 电子优惠卷计划任务
     * 微信推送成功，2小时候后发送短信
     *
     * 微信推送成功并且未发送过短信通知，超过两小时的用户
     *
     */
    public function coupon(){
        $lang_sms = '尊敬的韩国瓷肌尊贵会员，感谢您参加本次瓷肌大客户感恩回馈活动，您领取的代金券可抵扣医疗美容项目费用。此短信用于抵现验证，请保存，转发无效。韩国4EVER技术连锁，助你寻找你的美。美丽咨询热线：020-66622222。退订回T';

        $time = time();
        $update_time = $time - (2 * 60 * 60);
        $where['update_time'] = array('elt', $update_time);
        $where['status'] = 1;
        $where['send_type'] = array('neq', 1);
        $customerModel = M('Customer', null, 'USER_CENTER');
        $customer = $customerModel->where($where)->field('id,bind_mobile,send_type,update_time')->select();

        $send_succ = array();   //存放短信发送成功的id，后面做一次新修改发送状态
        $send_fail = array();   //短信发送失败
        $send_fail_msg = array();
        if(!empty($customer)){
            //发送短信通知
            $smsClass = new Send();
            foreach($customer as $value){
                $ret = $smsClass->send_sms($value['bind_mobile'], 0, '', $lang_sms);
                if($ret['error'] == 'M000000'){
                    $send_succ[] = $value['id'];
                }else{
                    $send_fail[] = $value['id'];
                    $send_fail_msg[] = 'id-'.$value['id'].':'.$ret['message'];
                }
            }
            //修改短信发送成功的发送状态
            if(!empty($send_succ)){
                $res = $customerModel->where(array('id'=>array('in', $send_succ)))->save(array('send_type'=>1));
            }
        }

        //计划任务日志记录
        $log_content = '短信成功发送'.count($send_succ).'条，失败'.count($send_fail).'条';
        if(!empty($send_fail_msg)){
            foreach($send_fail_msg as $val){
                $log_content .= ','.$val;
            }
        }
        $cron_log['name'] = CONTROLLER_NAME.'/'.ACTION_NAME;
        $cron_log['content'] = $log_content;
        $cron_log['start_time'] = $time;
        $cron_log['add_time'] = time();
        $cron_log['title'] = '微信优惠券领取短信通知';
        M('CronLog', null, 'USER_CENTER')->add($cron_log);
    }

    /**
     * 清楚测试数据
     */
    public function delData(){
        $customerModel = M('Customer', null, 'USER_CENTER');
        $data['bind_mobile'] = '';
        $data['bind_id'] = '';
        $data['bind_name'] = '';
        $data['status'] = 0;
        $data['update_time'] = 0;
        $data['open_id'] = '';
        $data['offers_total'] = 0;
        $data['send_type'] = 0;
        $customerModel->where(array('id'=>array('in', array(1,2))))->setField($data);
    }
    public function showTest(){
        $customerModel = M('Customer', null, 'USER_CENTER');
        $customer = $customerModel->where(array('id'=>array('in', array(1,2))))->select();
        dump($customer);
    }
    public function showALlTest(){
        $customerModel = M('Customer', null, 'USER_CENTER');
        $customer = $customerModel->where(array('status'=>1))->select();

        $update = I('get.upt');
        if($update){
            $upt_id = array();
            foreach($customer as $val){
                $upt_id[] = $val['id'];
            }
            $data['bind_mobile'] = '';
            $data['bind_id'] = '';
            $data['bind_name'] = '';
            $data['status'] = 0;
            $data['update_time'] = 0;
            $data['open_id'] = '';
            $data['offers_total'] = 0;
            $data['send_type'] = 0;

            $customerModel->where(array('id'=>array('in', $upt_id)))->save($data);
        }
        $id = I('get.uptid', 0, 'intval');
        if($id){
            $data['bind_mobile'] = '';
            $data['bind_id'] = '';
            $data['bind_name'] = '';
            $data['status'] = 0;
            $data['update_time'] = 0;
            $data['open_id'] = '';
            $data['offers_total'] = 0;
            $data['send_type'] = 0;

            if($id == 1){
                $data['mobile'] = 'bc1ea8161526f41853a9784111ad2157,62258463037985038281a6dedf3514e8';
            }elseif($id == 2){
                $data['mobile'] = 'c2af8de890ddb224e56011953f830c87';
            }

            $customerModel->where(array('id'=>$id))->save($data);
        }
        dump($customer);
    }

}