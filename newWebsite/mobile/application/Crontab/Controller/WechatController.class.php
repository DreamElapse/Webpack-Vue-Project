<?php
/**
 * ====================================
 * 微信计划任务
 * ====================================
 * Author: 9004396
 * Date: 2017-02-23 08:50
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: WechatController.class.php
 * ====================================
 */
namespace Crontab\Controller;

use Common\Controller\CrontabController;
use Common\Extend\Time;
use Common\Extend\Wechat;
use Cpanel\Model\BindUserModel;
use Cpanel\Model\TimingSendTplModel;
use Cpanel\Model\UserActivityModel;
use Cpanel\Model\UserModel;
use Cpanel\Model\UserReceiveLogModel;
use Cpanel\Model\WechatAccountModel;
use Cpanel\Model\WechatTagBindModel;
use Common\Extend\WechatSendMessageTemplate;

class WechatController extends CrontabController
{

    protected $userModel;
    protected $bindUserModel;
    protected $accessToken = array();

    public function __construct()
    {
        parent::__construct();
        $db_config = C('cpanel');
        C($db_config);
        if (is_null($this->userModel)) {
            $this->userModel = new UserModel();
        }
        if (is_null($this->bindUserModel)) {
            $this->bindUserModel = new BindUserModel();
        }
    }


    /**
     * 同步微信用户数据
     */
    public function syncUser()
    {
        $logid = $this->insertLog(__CLASS__ . '-' . __FUNCTION__, '开始执行');
        $this->getAccessToken();
        $user = $this->userModel->limit(200)->order('update_time asc, bind_id asc')->select();
        if (empty($user)) {
            $this->updateLog($logid, '更新了0条记录');
            exit;
        }
        $mun = 0;
        $up = 0;
        foreach ($user as $item) {
            $index = 0;
            foreach ($this->accessToken as $key => $token) {
                Wechat::$access_token = $token;
                Wechat::$userOpenId = $item['openid'];
                $ret = Wechat::getUserInfo();
                if (!empty($ret)) {
                    $index = 1;
                    $data['wechatAcountId'] = $key;
                    $data['update_time'] = Time::gmTime();
                    $data = array_merge($data, $ret);
                    unset($data['subscribe']);
                    unset($data['subscribe_time']);
                    $ret = $this->userModel->where(array('openid' => $data['openid']))->save($data);
                    if ($ret === false) {
                        $this->insertLog(__CLASS__ . '-' . __FUNCTION__, '微信公众帐号：' . $key . '的用户' . $item['openid'] . "更新失败");
                    }
                    $syncBindRes = $this->syncBindUser($data);
                    if ($syncBindRes === false) {
                        $this->insertLog(__CLASS__ . '-' . __FUNCTION__, '微信公众帐号：' . $key . '的用户' . $item['openid'] . "同步失败");
                    }
                    $this->syncTagBind($data['openid'], $data['tagid_list']);
                    $mun++;
                    break;
                }
            }
            if($index == 0){
                $data['update_time'] = Time::gmTime();
                $data = array_merge($data, $item);
                $ret = $this->userModel->where(array('openid' => $data['openid']))->save($data);
                if ($ret === false) {
                    $this->insertLog(__CLASS__ . '-' . __FUNCTION__, '用户' . $item['openid'] . "更新失败");
                }
                $syncBindRes = $this->syncBindUser($data);
                if ($syncBindRes === false) {
                    $this->insertLog(__CLASS__ . '-' . __FUNCTION__, '用户' . $item['openid'] . "同步失败");
                }
                $up++;
            }
        }
        $this->updateLog($logid, '拉取成功更新了' . $mun . '条记录,拉取失败更新了'.$up.'条记录');
    }



    public function sendServiceMsg()
    {
        $logid = $this->insertLog(__CLASS__ . '-' . __FUNCTION__, '开始执行');
        Wechat::$app_id = APPID;
        Wechat::$app_secret = APPSECRET;
        Wechat::$access_token= Wechat::getAccessToken();
        $timingSendTplModel = new TimingSendTplModel();
        $userActivityModel = new UserActivityModel();
        $userReceiveLogModel = new UserReceiveLogModel();

        //获取活跃用户
        $activeUser = $userActivityModel->where(array(
            'activity_type' => array('neq', USER_ACT_UNSUBSCRIBE),
            'last_activity_time' => array('EGT', time() - 48 * 60 * 60)
        ))->select();
        if(empty($activeUser)){
            $this->updateLog($logid, '发送了0条记录');
            exit;
        }

        $timingSendTpl = $timingSendTplModel->where(array('locked' => 0,'interval_mins' => array('LT',48*60)))->order("interval_mins desc")->select();//获取所有模版
		
        if(empty($timingSendTpl)){
            $this->updateLog($logid, '发送了0条记录');
            exit;
        }
        $mun = 0;
        $err = 0;
		$WechatSendMessageTemplate = new WechatSendMessageTemplate();
        foreach ($activeUser as $user) {
            if(empty($user['openid'])){
                continue;
            }
            Wechat::$userOpenId = $openid = $user['openid'];
            $actionTime = $user['last_activity_time'];
            $silenceTime = ceil((time() - $actionTime) / 60);
            $lastLogId = $userReceiveLogModel->where(array('openid' => $openid,'activity_time' => $actionTime))->order("receive_time desc, log_id desc")->getField('msg_id');
            if(empty($lastLogId)){  //日志没有记录则发送时间
                $sendMsg = $timingSendTpl[count($timingSendTpl)-1];
                if($silenceTime >= $sendMsg['interval_mins']){
					$sendMsg['msg_body'] = $WechatSendMessageTemplate->resolveTpl($sendMsg['msg_body'], $user['openid']);  //解析内容模版
					
                    $ret = Wechat::serviceText($sendMsg['msg_body'], $sendMsg['msg_id'],$actionTime);
                    if($ret['errcode'] == '0'){
                        $mun++;
                    }else{
                        $err++;
                    }
                }
                continue;
            }
            if($lastLogId == $timingSendTpl[0]['msg_id']){
                if($silenceTime > 46*60 && $silenceTime < 48*60){
                    $lastId = $userReceiveLogModel->where(array('openid' => $openid,'activity_time' => $actionTime,'msg_id' => '-1'))->count();
                    if(empty($lastId)){
                        $msg = L('LAST_SEND_MSG');
                        $ret = Wechat::serviceText($msg,'-1',$actionTime);
                        if($ret['errcode'] == '0'){
                            $mun++;
                        }else{
                            $err++;
                        }
                    }
                }
                continue;  //上次推送的消息=最后一条推送消息，则不推送
            }

            foreach ($timingSendTpl as $key=>$sendTpl) {

                if(!empty($sendTpl['max_send_nums'])){
                    $ReceiveLogTotal = $userReceiveLogModel->where(array('openid' => $openid,'msg_id' => $sendTpl['msg_id']))->count();//当前消息推送累计次数
                    if($ReceiveLogTotal == $sendTpl['max_send_nums']){
                        continue;   //当前消息超出发送次数，则不推送当前消息
                    }
                }

                if($lastLogId == $sendTpl['msg_id'] && isset($timingSendTpl[$key-1])){
                    $sendMsg = $timingSendTpl[$key-1];
                    if($silenceTime >= $sendMsg['interval_mins']){
                        $sendMsg['msg_body'] = $WechatSendMessageTemplate->resolveTpl($sendMsg['msg_body'], $user['openid']);  //解析内容模版
                        $ret = Wechat::serviceText($sendMsg['msg_body'], $sendMsg['msg_id'],$actionTime);
                        if($ret['errcode'] == '0') {
                            $mun++;
                        }else{
                            $err++;
                        }
                        break;
                    }
                }

                if($lastLogId == $sendTpl['msg_id']){
                    break;  //如果上次推送的消息=当前应推送消息，则不重复推送当前消息
                }

            }
        }
        $this->updateLog($logid, '发送了' . $mun . '条记录,发送失败'.$err.'条记录');
    }


    /**
     * 获取所有公众号
     * @return mixed
     */
    public function getWechatAccount()
    {
        $wechatAccountModel = new WechatAccountModel();
        $data = $wechatAccountModel->field('id,app_id,app_secret')->order('defaulted DESC')->select();
        return $data;
    }

    /**
     * 批量获取openid
     */
    protected function getAccessToken()
    {
        $key = 'openid';
        $accessToken = S($key);
        if(empty($accessToken)){
            $wechatAccount = $this->getWechatAccount();
            foreach ($wechatAccount as $account) {
                Wechat::$app_id = $account['app_id'];
                Wechat::$app_secret = $account['app_secret'];
                $accessToken = Wechat::getAccessToken();
                if (!empty($accessToken)) {
                    $this->accessToken[$account['id']] = $accessToken;
                    S($key,serialize($this->accessToken),5400);
                }
            }
        }else{
            $this->accessToken = unserialize($accessToken);
        }
    }

    /**
     * 同步微信用户
     * @param array $data
     * @return bool|mixed
     */
    private function syncBindUser($data = array())
    {
        if (empty($data)) {
            return false;
        }
        $bindUser = $this->bindUserModel->where(array('openid' => $data['openid']))->find();
        unset($data['bind_id']);
        if (empty($bindUser)) {
            $reselt = $this->bindUserModel->add($data);
        } else {
            $reselt = $this->bindUserModel->where(array('openid' => $data['openid']))->save($data);
        }
        return $reselt;
    }

    private function syncTagBind($openid, $taglist = array())
    {
        if (empty($taglist) || !is_array($taglist)) {
            return false;
        }
        $tagBindModel = new WechatTagBindModel();
        $result = $tagBindModel->where(array('openid' => $openid))->delete();
        if ($result) {
            $data = array();
            foreach ($taglist as $item) {
                $row['tag_id'] = $item;
                $row['openid'] = $openid;
                $data[] = $row;
            }
            $tagBindModel->addAll($data);
        }
    }
}