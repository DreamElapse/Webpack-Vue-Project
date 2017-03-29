<?php
/**
 * ====================================
 * 快递API查询日记操作
 * ====================================
 * Author: 9006765
 * Date: 2017/3/15 15:58
 * ====================================
 * File: ApiLogisticsLogController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Extend\Time;
use Common\Extend\PhxCrypt;
use Common\Controller\CpanelController;


class ApiLogisticsLogController extends CpanelController {
	protected $tableName = 'ApiLogisticsLogCenter';
	
	protected $allowAction = array('getSite');
	
	public function __construct() {
        parent::__construct();
    }
	
	
	/*
	*	日记详情
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function form(){
		$id = I('request.id', 0, 'intval');
		if($id <= 0){
			$this->error('ID不存在');
		}
		$info = $this->dbModel->info($id);
		if(empty($info)){
			$this->error('日记不存在');
		}
		$this->assign('info', $info);
		$this->display();
	}
}
