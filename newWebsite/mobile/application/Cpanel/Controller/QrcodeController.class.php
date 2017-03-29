<?php
/**
 * ====================================
 * 客服二维码
 * ====================================
 * Author: 9006758
 * Date: 
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: QrcodeController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;
class QrcodeController extends CpanelController{

    protected $tableName = 'Qrcode';


    /**
     * 控制显示，锁定
     */
    public function lockShow(){
        $type = I('request.type');
        $id = I('request.id');
        $status = I('request.status');
        $where['id'] = $id;
        if($type=='lock'){
            $data['locked'] = $status;
        }else if($type=='show'){
            $data['is_show'] = $status;
        }
        $result = D('Qrcode')->where($where)->save($data);
//        print_r(D('Qrcode')->getLastSql());exit;
        if($result){
            $this->success();
        }
        $this->error();
    }
}