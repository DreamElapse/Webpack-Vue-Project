<?php
/**
 * ====================================
 * 微信公众帐号管理
 * ====================================
 * Author: 9004396
 * Date: 2017-02-22 13:55
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: WechatAccountController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;

class  WechatAccountController extends CpanelController{
    protected $tableName = 'WechatAccount';


    public function _after_save($parmas){
        $account = $this->dbModel->where(array('default' => 1))->find();
        if(empty($account)){
            $this->dbModel->where(array('id' => $parmas['id']))->setField(array('defaulted' => 1));
        }
    }

    public function defaulted(){
        $id = I('param.id',0);
        if(!empty($id)){
            $res = $this->dbModel->where(array('id' => array('gt',0)))->setField(array('defaulted' => 0));
            if(!$res){
                $this->error(L('EDIT').L('ERROR'));
            }
            $result = $this->dbModel->where(array('id' => $id))->setField(array('defaulted' => 1));
            if($result) {
                $this->success(L('EDIT').L('SUCCESS'));
            }else{
                $this->error(L('EDIT').L('ERROR'));
            }
        }
        $this->error('请选择需要操作的选项！');
    }
}