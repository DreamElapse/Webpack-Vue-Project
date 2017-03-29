<?php
/**
 * ====================================
 * 微信
 * ====================================
 * Author: 9009221
 * Date: 2016-07-25
 * ====================================
 * File: WechatModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;

class WechatModel extends CommonModel {

    protected $tableName = 'bind_user';

    public function addWxMsg($data) {
        $this->table('ecs_wx_msg')->add($data);
    }

    public function updateUser($data) {
        $ret = $this->where("openid = '".$data['openid']."'")->save($data);
        return $ret;
    }

    public function isSubcribe($openid) {
        $ret = $this->where("openid = '{$openid}'")->find();
        return $ret ? true : false;
    }

    public function SignIn($data){
        $openid = $data['FromUserName'];
        $row = $this->where("openid = '{$openid}'")->find();
        $userSigninLogModel = D('UserSigninLog');
        $signInResult = $userSigninLogModel->getSigninPoints($row['bind_id']);
        if($signInResult){
            $result = "签到成功！\n\n您已连续签到".$signInResult['days']."次\n\n本次签到，您获得了以下奖励：".$signInResult['add_points']."瓷肌币奖励\n\n签到说明：\n".
                "小积分兑换大礼，每天坚持签到，积分不仅可以参与兑换礼品，还可以通过签到获取小瓷家活动的一手消息噢！\n".
                "PS：连续签到积分更多噢！\n更多优惠戳【店铺主页】\n<a href='http://new2.q.chinaskin.cn/#/checkin?bind_id=".$row['bind_id']."'>点此查看详情</a>";
            $this->where("openid = '{$openid}'")->setInc('points_left',$signInResult['add_points']);        //积分累计
        }else{
            $result = "您今天已签到过了！\n<a href='http://new2.q.chinaskin.cn/#/checkin?bind_id=".$row['bind_id']."'>点此查看详情</a>";
        }
        return $result;
    }

    
}