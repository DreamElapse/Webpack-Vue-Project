<?php
/**
 * ====================================
 * 客服二维码控制器
 * ====================================
 * Author:
 * Date: 
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: QrcodeController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
class QrcodeController extends InitController
{
    public function index()
    {

        $is_jsonp = trim(I('request.jsonp'));
        $callback = trim(I('request.callback'));

		//获取显示次数最低的二维码，并更新最后一次展示的时间，
		$qrcodeModel = M('customer_qrcode');
		$info = $qrcodeModel
				->where(array('is_show'=>0, 'locked'=>0))
				->order('show_time asc')
				->field('id,kefu_qrcode,weixin')
				->find();



        if($info){
            $data['qrcode_id'] = $info['id'];
            $data['weixin'] = $info['weixin'];
            $data['show_time'] = time();

            $qrcodeModel->where(array('id'=>$info['id']))->setField('show_time',$data['show_time']);

            //记录显示日志
            M('customer_qrcode_log')->data($data)->add();
            $return = json_encode($info);
        }else{
            $return = 'null';
        }

        if($is_jsonp=='true'){
            echo 'jsonp('.$return.')';
        }else if(!empty($callback)){
            echo "'".$callback."(".$return.")'";
        }else{
            $this->ajaxReturn($return);
        }
        exit;
    }
}
