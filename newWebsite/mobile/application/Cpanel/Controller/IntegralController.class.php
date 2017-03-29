<?php
/**
 * ====================================
 * 积分管理
 * ====================================
 * Author: 9004396
 * Date: 2017-02-24 09:54
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: IntegralController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;

class IntegralController extends CpanelController{
    protected $tableName = 'Integral';


    public function log(){
        $dbModel = D('UserPointLog');
        $accountId = I('param.id',0);
        if(IS_AJAX) {
            $params = I('request.');
            //先判断Model层是否存在
            if(method_exists($dbModel, 'grid')) {
                if(method_exists($dbModel, 'filter')){
                    $dbModel->filter($params);
                }

                $data =$dbModel->grid($params);
                if(method_exists($dbModel, 'format')) {
                    $data = $dbModel->format($data);
                }
            }
            $this->ajaxReturn($data);
            exit;
        }
        $this->accountId = $accountId;
        $this->display('log');
    }

}