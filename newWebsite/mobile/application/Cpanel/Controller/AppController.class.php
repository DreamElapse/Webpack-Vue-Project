<?php
/**
 * Created by PhpStorm.
 * User: 9008389
 * Date: 2015/12/7
 * Time: 14:29
 */

namespace Cpanel\Controller;


use Common\Controller\CpanelController;
use Cpanel\Model\AppModel;

class AppController extends CpanelController{
    protected $tableName = 'App';

    public function _before_save() {
        $appModel = new AppModel();
        $_POST['app_key'] = $_POST['app_key'] ? : $appModel->getRandString();
        $_POST['app_secret'] = $_POST['app_secret'] ? : $appModel->getRandString(64,32);
    }


    public function combo(){
        $appId = I('request.appId');
        $appId = empty($appId) ? array() : explode(',',$appId);
        $this->dbModel->field('app_id AS id, app_name as text');
        $data = $this->dbModel->select();
        foreach($data as $item=>$row){
            if(!empty($appId) && in_array($row['app_id'],$appId)){
                $row['selected'] = true;
            }
            $data[$item] = $row;
        }
        $this->ajaxReturn($data);
    }
}