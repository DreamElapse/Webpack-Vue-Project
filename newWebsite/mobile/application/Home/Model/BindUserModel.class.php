<?php
/**
 * ====================================
 * 微信绑定手机号码模型
 * ====================================
 * Author: 9009123
 * Date: 2016-10-15
 * ====================================
 * File: BindUserModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Extend\Wechat;
use Common\Model\CommonModel;

class BindUserModel extends CommonModel {

	//手机是否已被绑定
	public function isBind($mobile, $openid = '') {
	    $where['mobile'] = $mobile;
	    if(!empty($openid)){
	        $where['openid'] = $openid;
        }
		$count = $this->where($where)->count();
		return ($count > 0) ? true : false;
	}

    /**
     * 通过openid判断手机是否绑定
     * @param string $openid
     * @return bool
     */
	public function getBindMobile($openid = ''){
	    if(empty($openid)){
	        return false;
        }
        $where['openid'] = $openid;
        $mobile = $this->where($where)->getField('mobile');
        return empty($mobile) ? false : true;
    }

	//查询详情
	public function info($bind_id){
		$row = $this->where("bind_id = '$bind_id'")->find();
		return $row;
	}

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

    /**
     * 获取用户昵称
     * @param int $openid openid
     * @return int|mixed|string  昵称
     */
    public function getUserNickName($openid = 0){
        if(empty($openid)){
            return '';
        }
        $key = md5($openid);
        $nickName = S($key);
        if(empty($nickName)){
            $nickName = $this->where(array('openid'=>$openid))->getField('nickname');
            if(empty($nickName)){
                //调用微信接口
                Wechat::$userOpenId = $openid;
                Wechat::$app_id = APPID;
                Wechat::$app_secret = APPSECRET;
                $user_info = Wechat::getUserInfo();
                $nickName = $user_info['nickname'];
            }
            S($key, $nickName, 86400); //存储1天
        }
        return empty($nickName) ? $openid : $nickName;
    }

    public function SignIn($data){
        $openid = $data['FromUserName'];
        $row = $this->where(array('openid' => $openid))->find();
        if(empty($row)){
            return false;
        }
        $userSigninLogModel = D('UserSigninLog');
        $signInResult = $userSigninLogModel->getSigninPoints($row['bind_id']);
        $domain = 'http://new2.q.chinaskin.cn';
        $url = "<a href='".$domain."/#/checkin/".$row['bind_id']."'>点此查看详情</a>";
        $wechat_num = 'cj117e';
        if($signInResult){
            $result = $row['nickname']."您好，签到成功！\n";
            $result .= "您已连续签到 ".$signInResult['days']." 次。\n";
            $result .= "本次签到，您获得了以下奖励：".$signInResult['add_points']."积分奖励。快保存我";
            $result .= "的微信号{$wechat_num}和020-22005555到您的通讯录哟！他永远是您肌肤过敏的私人医生哟！\n";
            $result .= "签到说明：\n";
            $result .= "小积分兑换大礼，每天坚持签到，积分不仅可以参与兑";
            $result .= "换礼品，还可以通过签到获取活动的一手消息噢！\n";
            $result .= "PS：连续签到积分更多噢‼！\n";
            $result .= "更多优惠戳<a href='".$domain."'>【官网】</a>\n\n";
            $result .= $url;
            $this->where("openid = '{$openid}'")->setInc('points_left',$signInResult['add_points']);        //积分累计
        }else{
            $result = $row['nickname']."您好，您今天已经签到！\n";
            $result .= "要与客服互动或分享获得更多积分哦，快保存我的微";
            $result .= "信号{$wechat_num}和020-22005555到您的通讯录哟！他永远是您肌肤过敏的私人医生哟！\n\n";
            $result .= $url;
        }
        return $result;
    }
}