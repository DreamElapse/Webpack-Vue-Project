<?php
//=================================================================================
// 短信配置管理控制器
// filename:SmsConfigController.class.php
//=================================================================================

namespace Cpanel\Controller;
use Common\Controller\CpanelController;
use Common\Extend\Time;

class SmsConfigController extends CpanelController{
	protected $tableName = 'SmsConfig';
	
	
	public function _before_save($params){
		
		$params['config_name']  = trim($params['config_name']);
		$params['flag']			= trim($params['flag']);
		$params['interval']		= intval($params['interval']);
		$params['intervalMax']  = intval($params['intervalmax']);
		$params['mobileMax']	= intval($params['mobilemax']);
		$params['sendMax']		= intval($params['sendmax']);
		$params['systemMobile'] = trim($params['systemmobile']);
		$params['closeMax']		= intval($params['closemax']);
		$params['set_user']		= intval($params['set_user']);
		$params['id']			= intval($params['id']);
		$params['create_time']	= Time::gmTime();
		
		unset($params['intervalmax']);
		unset($params['mobilemax']);
		unset($params['sendmax']);
		unset($params['systemmobile']);
		unset($params['closemax']);
		
		return $params;
	}
	
}
