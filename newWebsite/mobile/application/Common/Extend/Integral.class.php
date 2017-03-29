<?php
/**
 * ====================================
 * 积分类
 * ====================================
 * Author: 9004396
 * Date: 2017-02-06 15:00
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: Integral.class.php
 * ====================================
 */
namespace Common\Extend;

class Integral{

    private $user_id;

    public function __construct()
    {
        if(is_null($this->user_id)){
            $this->user_id = session('user_id');
        }
    }

    /**
     * 积分变化
     * @param $site_id  站点ID
     * @param int $point  增减的积分(增加不加符号，减少在积分前面加'-')
     * @param string $remark 积分说明
     * @param int $state 状态 -1：删除（订单退货），0：正常，-2（积分过期），-3自主消费，-4客服消费,2:积分商品删除
     * @param bool $multiple 生日是否获取双倍积分
     * @param array $extra 扩展字段：暂时支持order_sn,order_id,$user_id,$type(积分类型 0:订单积分，1签到积分，2，评论积分)
     * @return bool
     */
    public function variety($site_id,$point = 0,$remark = '',$state = 0,$multiple = false, $extra= array()){

        $extra['user_id'] = isset($extra['user_id']) ? $extra['user_id'] : 0;
        $this->user_id = empty($this->user_id) ? $extra['user_id'] : $this->user_id;
        if(empty($this->user_id)){
            return false;
        }
        $printLog['user_id'] = $this->user_id;
        $printLog['site_id'] = $site_id;
        $printLog['order_sn'] = isset($extra['order_sn']) ? $extra['order_sn'] : '';
        $printLog['order_id'] = isset($extra['order_id']) ? $extra['order_id'] : 0;
        $printLog['state'] = $state;
        $printLog['point_type'] = isset($extra['type']) ? $extra['type'] : 0;
        $printLog['points'] = $point;
        if($multiple){
            $printLog['points'] = $point*$this->multiple();
        }
        $printLog['remark'] = $remark;
        $printLog['add_time'] = Time::gmTime();
        $pointLogModel = D('UserPointLog');
        if($logId = $pointLogModel->add($printLog)){
            if($userAccountId = $this->userPoint($point)){
                $pointLogModel->where(array('log_id' => $logId))->setField('integralrecord_id',$userAccountId);
                return true;
            }else{
                $pointLogModel->where(array('log_id' => $logId))->delete();
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 生日获取双倍积分
     * @return int
     */
    private function multiple(){
        $multiple = 1;
        $userInfo = D('Home/users')->where(array('user_id'=>$this->user_id))->find();
        $birthday = date('m-d',$userInfo['birthday']);
        $addTime = Time::gmTime();
        if($birthday == date('m-d',$addTime)){
            $multiple *= 2;
        }
        return $multiple;
    }


    /**
     * 会员积分
     * @param $point
     * @return bool
     */
    private function userPoint($point){
        $accountModel = D('UserAccount');
        $userAccount = $accountModel->where(array('user_id' => $this->user_id))->find();
        $rank = 1;
        $accountData = array();
        $accountData['user_id'] = $this->user_id;
        if(empty($userAccount)){
            $accountData['total_points'] = $point;
            $accountData['points_left'] = $point;
            $userAccountId = $result = $accountModel->add($accountData);
        }else{
            if($point < 0){
                $accountData['points_left'] = $userAccount['points_left']+$point;
                $accountData['pay_points']  = $userAccount['pay_points']-$point;
            }else{
                $accountData['total_points'] = $userAccount['total_points']+$point;
                $accountData['points_left'] = $userAccount['points_left']+$point;
            }
            $result = $accountModel->where(array('id' => $userAccount['id']))->save($accountData);
            $userAccountId = $userAccount['id'];
            $rank = $userAccount['rank'];
        }
        if($result){
            //会员等级变化写入等级变化表
            //根据积分来处理等级
            $level = $this->getLevel(); //更新后的等级
            if($level > $rank){
                $IsExist = D('UserRankLog')->where(array('user_id' => $this->user_id,'old_rank' => $rank,'new_rank' => $level))->count();
                if(empty($IsExist)){
                    $rankLog['user_id'] = $this->user_id;
                    $rankLog['old_rank'] = $rank;
                    $rankLog['new_rank'] = $level;
                    $rankLog['add_time'] = Time::gmTime();
                    D('UserRankLog')->add($rankLog);
                }
            }elseif($level < $rank){ //降级时将其对应的变级记录删了
                D('UserRankLog')->where(array('user_id' => $this->user_id,'old_rank' => $level))->delete();
            }

            //更新用户等级
            $accountModel->where(array('user_id' => $this->user_id))->save(array('rank' => $level));
			
			//更新登录用户信息到session
			//D('Home/Users')->setUserInfo($this->user_id);
            return $userAccountId;
        }else{
            return false;
        }
    }

    /**
     * 获取积分等级
     * @return int|mixed
     */
    private function getLevel(){
        $level = 0;
        $accountModel = D('UserAccount');
        $points = $accountModel->where(array('user_id' => $this->user_id))->getField('total_points'); //获取会员总积分
        if(!empty($points)){
            $where = array(
                'min_points' => array('ELT',$points),
                'max_points' => array('EGT',$points)
            );
            $level = D('UserRank')->where($where)->getField('rank_id');
        }
        return $level;
    }


}