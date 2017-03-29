<?php
//========================================
// 短信发送日志
//========================================
// Author: 9006758
//
// File: SendLogController.class.php
//=======================================

namespace Cpanel\Controller;
use Common\Controller\CpanelController;

class SendLogController extends CpanelController{

	protected $tableName = 'SendLog';
	
	
	public function sendStatus(){
	    $data = array(
	       array(
               'id' => '',
               'text' => '--请选择状态--',
               'selected' => true,
           )
        );
		$Model = D('SendLog');
		$type = I('request.type');
		if(is_numeric($type)){
			$status = $Model->langStatus($type);
		
			foreach($status as $k=>$v){
				array_push($data, array('id'=>$k, 'text'=>$v));
			}
		}
		
        $this->ajaxReturn($data);
    }
	
}