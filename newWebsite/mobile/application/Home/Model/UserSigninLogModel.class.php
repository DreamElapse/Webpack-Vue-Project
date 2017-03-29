<?php
/**
 * Created by PhpStorm.
 * User: 9009078
 * Date: 2017/2/5
 * Time: 11:22
 */
namespace Home\Model;

use Common\Extend\Integral;
use Common\Model\CommonModel;

class UserSigninLogModel extends CommonModel
{

    protected $tableName = 'user_signin_log';

    //积分增长配置
    protected $signInConf = array(
        1 => 5,
        2 => 6,
        3 => 7,
        4 => 8,
        5 => 9,
        6 => 10,
    );

    /**
     * 会员签到操作
     * @param $bind_id
     * @return array|bool
     */
    public function getSigninPoints($bind_id)
    {
        $row = $this->where("bind_id = '{$bind_id}'")->order("add_time desc")->find();
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $beginYesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        //今天已签到
        if ($row['add_time'] >= $beginToday) {
            return false;
        }
        if ($row['add_time'] < $beginYesterday) {
            $row['days'] = 0;       //不是连续签到
        }
        $days = $row['days'] + 1;
        $maxdays = max(array_keys($this->signInConf));
        if ($days >= $maxdays) {
            $add_points = $this->signInConf[$maxdays];
        } else {
            $add_points = $this->signInConf[$days];
        }
        //记录签到日志
        $data['bind_id'] = $bind_id;
        $data['points'] = $add_points;
        $data['days'] = $days;
        $data['add_time'] = time();
        $result = $this->add($data);
        $user_id = $this->checkBingUser($bind_id);
        if ($result && !empty($user_id)) {
            $integra = new Integral();
            $integra->variety(C('SITE_id'), $add_points, '签到积分', 0, false, array('user_id' => $user_id, 'type' => 1));
        }

        return array('days' => $days, 'add_points' => $add_points);
    }

    /**
     * 获取签到详情
     * @param $bind_id
     * @return array
     */
    public function getSigninInfo($bind_id)
    {
        $row = $this->where("bind_id = '{$bind_id}'")->order("add_time desc")->find();
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $beginYesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        if ($row['add_time'] >= $beginToday) {
            $days = $row['days'];          //连续签到天数
            $is_sign = 1;                  //今天是否签到
        } else {
            if ($row['add_time'] < $beginYesterday) {
                $days = 0;
                $is_sign = 0;
            } else {
                $days = $row['days'];
                $is_sign = 0;
            }
        }
        return array('days' => $days, 'is_sign' => $is_sign, 'last_signin_time' => $row['add_time']);
    }

    /**
     * 检测并获取USER_ID
     * @param $bind_id
     * @return bool|mixed
     */
    private function checkBingUser($bind_id)
    {
        $mobile = D('BindUser')->where(array('bind_id' => $bind_id))->getField('mobile');
        if (empty($mobile)) {
            return false;
        }
        $user_id = D('users')->where(array('mobile' => $mobile))->getField('user_id');
        return $user_id;
    }
}