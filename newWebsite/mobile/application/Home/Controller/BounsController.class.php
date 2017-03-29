<?php
/**
 * ====================================
 * 优惠券
 * ====================================
 * Author: 9004396
 * Date: 2017-02-08 16:46
 * ====================================
 * File: BounsController.class.php
 * ====================================
 */
namespace Home\Controller;

use Common\Controller\InitController;
use Common\Extend\Time;

class BounsController extends InitController
{

    private $bouns_type_model;
    private $bonus_model;

    function __construct()
    {
        parent::__construct();
        $this->bouns_type_model = D('BonusTypeCenter');
        $this->bonus_model = D('UserBonusCenter');
    }

    /**
     * 获取列表
     */
    function bouns()
    {
        $act_id = I('request.act_id', 0);
        $bonus = load_config(CONF_PATH . 'bonus_config.php');
        if (!empty($bonus[$act_id]) && is_array($bonus[$act_id])) {
            $bonusInfo = $bonus[$act_id];
            $isWeChat = true;
            if( $bonusInfo['isWeChat']){
                if(isCheckWechat() == false){
                    $isWeChat = false;
                }
            }
            $bonusData = array();
            if($isWeChat){
                $act_id = $bonusInfo['act'];
                $time = Time::gmTime();
                $where = array(
                    'send_type' => 3,
                    'reuse' => 0,
                    'send_start_date' => array('ELT', $time),
                    'send_end_date' => array('EGT', $time),
                    'type_id' => array('IN', $act_id)
                );
                $bonusData = $this->bouns_type_model->where($where)->field('type_id,type_name,type_money')->select();
            }
            if (!empty($bonusData)) {
                $this->success($bonusData);
            } else {
                $this->error('没有符合的优惠券');
            }
        } else {
            $this->error('没有找到对应活动');
        }
    }

    /**
     * 获取优惠券
     */
    function getBouns()
    {
        if (empty($this->user_id)) {
            $this->error('请先登陆');
        }
        $typeId = I('request.type_id', 0);
        $act_id = I('request.act_id', 0);
        $bonus = load_config(CONF_PATH . 'bonus_config.php');
        if (!isset($bonus[$act_id])) {
            $this->error('没有找到对应活动');
        }
        $where = array(
            'send_type' => 3,
            'reuse' => 0,
            'type_id' => $typeId
        );
        $bonusInfo = $this->bouns_type_model->where($where)->find();
        if (empty($bonusInfo)) {
            $this->error('优惠券不存在');
        }
        $time = Time::gmTime();
        if ($bonusInfo['send_start_date'] > $time) {
            $this->error('尚未到领取时间');
        }
        if ($bonusInfo['send_end_date'] < $time) {
            $this->error('该优惠券已不在领取时间');
        }
        $userbouns = $this->bonus_model->where(array('bonus_type_id' => $typeId,'user_id' => $this->user_id))->find();
        if (!empty($userbouns)) {
            $this->error('您已领取过优惠券');
        }
        //$bonusNum = $this->bonus_model->where(array('bonus_type_id' => $typeId))->field('bonus_sn')->select();
		$bonusNum = $this->bonus_model->where(array('bonus_type_id' => $typeId,'used_time' => array('ELT',0)))->field('bonus_sn')->select();
        if(!empty($bonusNum)){
            $voucher = $bonusNum[array_rand($bonusNum)]['bonus_sn'];
            $data = array(
                'user_id' => $this->user_id,
                'receive_time' => Time::gmTime(),
                'get_amount' => 1,
            );
            $result = $this->bonus_model->where(array('bonus_sn' => $voucher,'bonus_type_id' => $typeId))->save($data);
            if($result){
                $this->success($voucher);
            }else{
                $this->error('领取失败');
            }
        }else{
            $this->error('优惠券尚未发放，请耐心等候');
        }
    }

}