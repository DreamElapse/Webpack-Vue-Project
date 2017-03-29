<?php
/**
 * ====================================
 * 品牌调查管理
 * ====================================
 * Author: 9004396
 * Date: 2017-02-17 16:04
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: SurveyController.class.php
 * ====================================
 */
namespace Cpanel\Controller;

use Common\Controller\CpanelController;

class SurveyController extends CpanelController
{
    protected $tableName = 'Survey';

    public function form(){
        $sid = I('param.sid',0);
        $data = $this->dbModel->find($sid);
        if(!empty($data)){
            $data['content'] = unserialize($data['content']);
            $this->data = $data;
        }
        $this->display();
    }

    public function _before_save($parmas)
    {
        if (!is_array($parmas['content'])) {
            $parmas['content'] = '';
        } else {
            foreach ($parmas['content'] as $key => $item) {
                if(empty($item)){
                    unset($parmas['content'][$key]);
                }
            }
            $parmas['content'] = (empty($parmas['content']) || count($parmas['content']) == 0) ? "" : serialize($parmas['content']);
        }
        $parmas['start_time'] = strtotime($parmas['start_time']);
        $parmas['end_time'] = strtotime($parmas['end_time']);
        return $parmas;
    }


    //编辑管理员状态
    public function lock() {
        $params = I('post.');
        if($params['sid']) {
            $where = array(
                'sid' => array('in', $params['sid'])
            );
            $result = $this->dbModel->where($where)->setField('locked', (int)$params['locked']);
            if($result) {
                $this->success(L('EDIT').L('SUCCESS'));
            }else{
                $this->error(L('EDIT').L('ERROR'));
            }
        }
        $this->error(L('SELECT_NODE') . L('ADMIN'));
    }
}