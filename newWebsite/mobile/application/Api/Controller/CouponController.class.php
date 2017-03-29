<?php
/**
 * ====================================
 * 微信电子优惠券接口
 * ====================================
 * Author: 9004396
 * Date: 2016-11-09 10:47
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: CouponController.php
 * ====================================
 */
namespace Api\Controller;

use Common\Controller\ApiController;
use Common\Extend\Wechat;
use Common\Extend\Send;
use Common\Extend\PhxCrypt;

class CouponController extends ApiController {

    protected $_permission = array(
        'checkMobile' => array('wechat_coupon'),
        'supplement' => array('wechat_coupon'),
        'pushWechat' => array('wechat_coupon'),
    );

    protected $params;
    protected $customer;
    private $lang_coupon = array(
        //短信消息
        'lang_sms' => '尊敬的韩国瓷肌尊贵会员，感谢您参加本次瓷肌大客户感恩回馈活动，您领取的代金券可抵扣医疗美容项目费用。此短信用于抵现验证，请保存，转发无效。韩国4EVER技术连锁，助你寻找你的美。美丽咨询热线：020-66622222。退订回T',

        //微信推送消息
        'lang_wechat' => "恭喜您领取成功！您的代金券金额为#offers_total#元。有效期截止为2017年3月31日。感谢您长久以来对韩国瓷肌的支持。医疗美容门诊地址：广东省广州市天河区珠江新城黄埔大道西118号。咨询预约热线：020-66622222。",
    );

    public function __construct()
    {
        parent::__construct();
        if(is_null($this->customer)){
            $this->customer =D('Customer');
        }
        $this->params = I('post.');
        if(!isset($this->params['timestamp']) || empty($this->params['timestamp'])){
            $this->error('30001');
        }
        $time = time();
        if($time - $this->params['timestamp'] > 180){
            $this->error('30001');
        }

    }


    public function index(){
        $str = '<h1 style="text-align: center; padding-top: 20%">Coupon Wechar API Interface</h1>';
        die($str);
    }

    /**
     * 检测手机号是否有资格领取优惠卷
     */
    public function checkMobile(){

        if(!isset($this->params['mobile']) || empty($this->params['mobile'])){
            $this->error('30002');
        }

        $para = $this->paraFilter($this->params);
        $isSign = $this->verify($para,$this->params['sign'],$para['key']);
        if(!$isSign){
            $this->error('10003');
        }
        $where['_string'] = "FIND_IN_SET('{$this->params['mobile']}',mobile)";
		$total = $this->customer->where($where)->field('id,status,offers_total,send_type')->find();
        if(!empty($total)){
            $this->success($total);
        }else{
            $this->error('30003');
        }

    }

    /**
     * 发放优惠券
     */
    public function dispense(){
        if(!isset($this->params['mobile']) || empty($this->params['mobile'])){
            $this->error('30002');
        }

        if(!isset($this->params['idNumber']) || empty($this->params['idNumber'])){
            $this->error('30004');
        }

        if(!isset($this->params['bind_mobile']) || empty($this->params['bind_mobile'])){
            $this->error('30002');
        }

        if(!isset($this->params['name']) || empty($this->params['name'])){
            $this->error('30005');
        }

        if(!isset($this->params['openid']) || empty($this->params['openid'])){
            $this->error('30006');
        }
        $para = $this->paraFilter($this->params);
        $isSign = $this->verify($para,$this->params['sign'],$para['key']);
        if(!$isSign){
            $this->error('10003');
        }
        $where['_string'] = "FIND_IN_SET('{$this->params['mobile']}',mobile)";
        $total_amount = $this->customer->where($where)->field('total_amount')->find();
        $price = intval($total_amount['total_amount']/100)*100;

        $data['bind_mobile'] = $this->params['bind_mobile'];
        $data['bind_id'] = $this->params['idNumber'];
        $data['bind_name'] = $this->params['name'];
        $data['open_id'] = $this->params['openid'];
        $data['status'] = 1;
        $data['update_time'] = time();
        $data['offers_total'] = $price;

        $result = $this->customer->where($where)->save($data);
        if($result !== false){
            $this->success(array('price' => $price));
        }else{
            $this->error('30007');
        }

    }

    /**
     * 消息推送
     * @param mobile 领取电子卷的手机号
     * @param sign   签名
     * @param key
     *
     * 说明：微信推送优先，微信推送失败则以短信信息通知用户，否则走计划任务，两小时后推送短信通知
     */
    public function pushWechat(){

        if(!isset($this->params['mobile']) || empty($this->params['mobile'])){
            $this->error('30002');
        }
        $para = $this->paraFilter($this->params);
        $isSign = $this->verify($para,$this->params['sign'],$para['key']);
        if(!$isSign){
            $this->error('10003');
        }
        $where['_string'] = "FIND_IN_SET('{$this->params['mobile']}',mobile)";
        $info = $this->customer->where($where)->find();
        if(!$info){
            $this->error('30003');
        }

        //已发送过短信的不再微信推送及短信通知
        if($info['send_type'] == 1){
            $this->error('30008');
        }

        //微信推送通知
        Wechat::$app_id = APPID;
        Wechat::$app_secret = APPSECRET;
        Wechat::$userOpenId = $info['open_id'];
        $token = Wechat::getAccessToken();
        Wechat::$access_token = $token;
        $wechat_msg = str_replace('#offers_total#', $info['offers_total'], $this->lang_coupon['lang_wechat']);
        $res_push = Wechat::serviceText($wechat_msg);

        if($res_push['errcode'] === 0){
            $this->customer->where($where)->save(array('send_type'=>2));
        }else{
            //微信推送失败，立即发送短信通知
            $sms_msg = $this->lang_coupon['lang_sms'];
            $smsClass = new Send();
            $ret = $smsClass->send_sms($info['bind_mobile'], 0, '', $sms_msg);

            //短信发送成功，则修改发送状态
            if($ret['error'] == 'M000000'){
                $this->customer->where($where)->save(array('send_type'=>1));
            }
            $this->success($ret);
        }
        $this->success();
    }
}