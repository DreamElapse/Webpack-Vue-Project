<?php
/**
 * ====================================
 * 维权
 * ====================================
 * Author: 9004396
 * Date: 2017-02-15 17:59
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: RightsController.class.php
 * ====================================
 */
namespace Home\Controller;

use Common\Controller\InitController;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;
use Common\Extend\Curl;

class  RightsController extends InitController
{

    private $api_url = 'http://api.chinaskin.cn/Comment/';
    private $curl_key = 'CHSURL#*GO888';

    public function __construct()
    {
        parent::__construct();
//        $this->user_id = 1;
        if (empty($this->user_id)) {
            $this->error('用户未登陆！');
        }
    }

    public function index()
    {
        $order_sn = I('param.order_sn');
        $mobile = I('param.mobile');
        $remark = I('param.remark');

        if (empty($order_sn) && empty($mobile)) {
            $this->error('订单号或手机号码最少填写一个！');
        }

        if (!empty($mobile) && !is_phone($mobile)) {
            $this->error('您填写的手机号码不正确!');
        }

        if (empty($remark)) {
            $this->error('请输入投诉信息!');
        }
        if (mb_strlen($remark,'UTF8') < 20) {
            $this->error('投诉信息最少20个字');
        }


        if(!empty($order_sn)){
            $where['order_sn'] = $order_sn;
        }

        if(!empty($mobile)){
            $mobile = PhxCrypt::phxEncrypt($mobile);
            $map['mobile'] = $mobile;
            $map['tel'] = $mobile;
            $map['_logic'] = 'OR';
            $where['_complex'] = $map;
        }
        $orderInfo = D('OrderInfoCenter')->where($where)->find();
        if (!empty($mobile) && empty($orderInfo)) {
            $this->error('该手机号未找到对应的订单！');
        }

        if (empty($orderInfo)) {
            $this->error('该订单号不是一个正确的订单号或订单不存在！');
        }


        $ip = get_client_ip();
        $ipKey = md5($ip);
        $IpCount = S($ipKey);
        $filter = array(
            '14.23.61.75'
        );
        if (empty($IpCount)) {
            S($ipKey, 1, array('expire' => 600));
        } else {
            if(!in_array($ip,$filter)){
                $this->error('不能频繁的提交信息');
            }
        }

        $data['user_id'] = $this->user_id;
        $data['site_id'] = C('SITE_ID');
        $data['order_sn'] = $order_sn;
        $data['order_id'] = $orderInfo['order_id'];
        $data['mobile'] = $mobile;
        $data['remark'] = $remark;
        $data['ip'] = $ip;
        $data['create_time'] = Time::gmTime();
        $result = D('Rights')->add($data);
        if ($result) {
            $this->success();
        } else {
            $this->error('操作失败，请重新操作。若多次失败，请联系客服');
        }
    }

    public function getList()
    {
        $page = I('param.page', 1);
        $pageSize = I('param.pageSize', 10);
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $pageSize = (empty($pageSize) || $pageSize <= 0) ? 10 : $pageSize;
        $rightsModel = D('Rights');
        $where = array(
            'type' => 0,
            'user_id' => $this->user_id,
        );
        $total = $rightsModel->where($where)->count();
        $data = $rightsModel->page($page, $pageSize)->where($where)->order('rid DESC')->field('rid,complaint_no,follow_kefu,create_time,status,order_sn,mobile,remark')->select();
        if (empty($data)) {
            $this->error('暂无维权数据！');
        }
        foreach ($data as &$item) {
            $item['handle'] = $item['status'];
            $statusText = $this->getState();
            switch ($item['status']){
                case 1:
                    if(empty($item['follow_kefu'])){
                        $item['status'] = $statusText[0];
                    }else{
                        $item['status'] = $statusText[1];
                    }
                    break;
                default:
                    $item['status'] = $statusText[$item['status']];
            }
            $item['create_time'] = Time::localDate('Y-m-d H:i:s', $item['create_time']);

        }
        $result = array(
            'total' => (int)$total,
            'rows' => $data,
            'pagecount' => ceil($total / $pageSize)
        );
        $this->success($result);
    }

    public function confirm(){
        $rid = I('param.rid',0);
        $level = I('param.level',0);
        $content = I('param.content','');
        if(empty($rid)){
            $this->error('该记录不存在！');
        }
        if(empty($level)){
            $this->error('请选择评分等级！');
        }
        $rightsModel = D('Rights');
        $data = $rightsModel->field('rid,complaint_no,follow_kefu,create_time,status,order_sn,mobile,remark')->where(array('rid' => $rid))->find();

        
        if(empty($data)){
            $this->error('该记录不存在！');
        }
        if($data['status'] == 4){
            $this->error('该记录已确认！');
        }

        $bindUserModel = D('BindUser');
        $evalPerson = $bindUserModel->getUserNickName($this->openId);

        $params['comment'] = array(
            array(
                'evalTarget'    => "02",
                'evalDime'      => "0203",
                'name'          => empty($data['follow_kefu'])? '未知客服':$data['follow_kefu'],
                'channel'       => "0305",
                'evalType'      => "04",
                'content'       => '投诉建议',
                'scope'         => $level,
                'evalPerson'    => $evalPerson,
                'msgContent'    => $content,
                'rid'           => $data['order_sn'],
            )
        );
        Curl::$key = $this->curl_key;
        $ret = Curl::getApiResponse($this->api_url.'pageComment', $params);
        if($ret['error'] == 'A00000'){
            $result = $rightsModel->where(array('rid' => $rid))->save(array('status' => 4,'sync' => 2));
            if($result){
                $statusText = $this->getState();
                switch ($data['status']){
                    case 1:
                        if(empty($data['follow_kefu'])){
                            $data['status'] = $statusText[0];
                        }else{
                            $data['status'] = $statusText[1];
                        }
                        break;
                    default:
                        $item['status'] = $statusText[$data['status']];
                }
                $this->success($data);
            }
        }
        $this->error('操作失败，请重新操作。若多次失败，请联系客服');
    }

    private function getState()
    {
        return array(
            '正在派单', '已受理', '已完成','撤消','已确认'
        );
    }

}