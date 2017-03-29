<?php
/**
 * ====================================
 * 管理员操作
 * ====================================
 * Author: 9006765
 * Date: 2017/3/15 15:58
 * ====================================
 * File: OrderController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;


class OrderController extends CpanelController {
	protected $tableName = 'OrderInfoCenter';
	protected $allowAction = array('getSite');

	
	/*
	*	获取站点下拉菜单
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function getSite(){
        $siteList = L('site_name');
        $ret = array(
            array('id' => 0,'text' => L('SELECT_NODE').L('SITE'))
        );
        if(!empty($siteList) && is_array($siteList)){
            $siteData = array();
            foreach ($siteList as $key=>$list){
                $siteData[] = array('id' => $key,'text' =>$list);
            }
            $ret = array_merge($ret,$siteData);
        }
        $this->ajaxReturn($ret);
	}

    public function form(){
        $id = I('request.id', 0, 'intval');
        if($id <= 0){
            $this->error('ID不存在');
        }
        $data = $this->dbModel->info($id);
        if(empty($data)){
            $this->error('订单不存在');
        }
        $data['goods'] = json_encode($data['goods']);
        $this->assign('data', $data);
        $this->display();
    }


}
