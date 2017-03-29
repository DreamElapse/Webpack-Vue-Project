<?php
/**
 * ====================================
 * 地区管理
 * ====================================
 * Author: 91336
 * Date: 2014/11/29 9:17
 * ====================================
 * File: RegionController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;
use Cpanel\Model\AdminModel;

class RegionController extends CpanelController {
    protected $region;
    protected $tableName = 'Region';

    public function _after_save() {
        $params = I('post.');
        $adminModel = new AdminModel();
        if(empty($params['id'])) {
            $message = L('add') . L('region') . $params['text'];
        }else {
            $message = L('edit') . L('region') . $params['text'];
        }
        $adminModel->addLog($message);
    }

    public function _before_delete() {
        $item_id = I('request.item_id');
        if(empty($item_id)) return;
        $this->region = $this->dbModel->find($item_id);
    }

    public function _after_delete() {
        $adminModel = new AdminModel();
        $message = L('delete') . L('region') . $this->region['text'];
        $adminModel->addLog($message);
    }
}