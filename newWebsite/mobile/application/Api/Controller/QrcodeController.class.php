<?php
/**
 * ====================================
 * 客服二维码数据接收控制器（后台推送）
 * ====================================
 * Author: 9006758
 * Date: 
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: QrcodeController.class.php
 * ====================================
 */
namespace Api\Controller;
use Common\Controller\ApiController;
class QrcodeController extends ApiController{

    public function index(){
        $config = C('db_config.1');
        $connection = array_merge($config['CONFIG'], array('DB_TYPE' => C('DB_TYPE')));
		$qrcodeModel = M('customer_qrcode',$connection['DB_PREFIX'],$connection);
		$params = I('request.');

        $resturn = true;
		$data = $params['data'];
		if(!empty($data)){
            $insert_data = array();
            $resturn = true;
//            $i = 0;
            $checkId = array();
            foreach($data as $key=>$val){
                $qrcode_id = $val['id'];

                $where['qrcode_id'] = $qrcode_id;
                $is_exist = $qrcodeModel->where($where)->count();
                $time = time();
                $_data['job_number']  = $val['job_number'];
                $_data['real_name']   = $val['real_name'];
                $_data['weixin']      = $val['weixin'];
                $_data['kefu_qrcode'] = $val['qrcode'];
                $_data['create_time'] = $time;
                $_data['is_show']     = $val['is_show'];
                $_data['show_time']   = $time;
                $_data['locked']      = $val['locked'];
                if($is_exist){
                    $res = $qrcodeModel->where($where)->save($_data);
                    /*if($res){
                        $i++;
                    }*/
                }else{
                    $checkId[] = $_data['qrcode_id'] = $qrcode_id;
                    $insert_data[] = $_data;
                }
            }

            if(!empty($insert_data)){
                $res = $qrcodeModel->addAll($insert_data);
            }
            if(!$res){
                $resturn = false;
            }
            /*$addnum = 0;
            if(!empty($checkId) && !empty($insert_data)){
                if($insert_data == $checkId){
                    $addnum = count($insert_data);
                    $i += count($insert_data);
                }else{
                    $where['qrcode_id'] = array('IN',$checkId);
                    $total = $qrcodeModel->where($where)->count();
                    $addnum = $total;
                    $i += $total;
                }
            }*/
        }
        $this->ajaxReturn($resturn);
    }
	
}