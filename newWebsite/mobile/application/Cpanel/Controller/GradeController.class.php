<?php
//=================================================================================
// 会员等级管理控制器
// filename:GradeController.class.php
//=================================================================================

namespace Cpanel\Controller;
use Common\Controller\CpanelController;

class GradeController extends CpanelController{
	protected $tableName = 'Rank';
	
	public function __construct(){
		parent::__construct();
	}

    public function log(){
        $dbModel = D('UserRankLog');
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
        $this->display('log');
    }

    public function combo(){
	    $data = array(
	       array(
               'id' => 0,
               'text' => L('select_node'),
               'selected' => true,
           )
        );
        $this->dbModel->field('rank_id AS id, rank_name AS text');
        $res = $this->dbModel->order('min_points asc')->select();
        if(!empty($data) && is_array($res)){
            $data = array_merge($data,$res);
        }
        $this->ajaxReturn($data);
    }
}
