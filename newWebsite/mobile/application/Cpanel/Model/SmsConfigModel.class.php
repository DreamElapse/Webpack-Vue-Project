<?php
/**
 * ====================================
 * 短信配置模型
 * ====================================
 * Author: 9006758
 * Date: 
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: SmsConfigModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\Time;

class SmsConfigModel extends CpanelUserCenterModel
{
    protected $tableName = 'sms_config';

	protected $_validate = array(
        array('config_name', 'require', '配置名称不能为空'),
		array('flag', 'checkFlag', '配置标识错误', 1, 'callback'),
        array('interval', '/^\d+$/', '时间间隔必须是正整数'),
        array('intervalMax', '/^\d+$/', '间隔发送量必须是正整数'),
        array('mobileMax', '/^\d+$/', '手机发送量必须是正整数'),
        array('sendMax', '/^\d+$/', '发送临界点必须是正整数'),
        array('systemMobile', '/^1[34578]\d{9}$/', '请输入正确的手机号'),
        array('closeMax', '/^\d+$/', '接口关闭量必须是正整数'),
    );

    //配置表示验证
    protected function checkFlag($value){
        if(empty($value)){
            return false;
        }
        if(preg_match("/[^\x{4e00}-\x{9fa5}]+/iu", $value)){
            return true;
        }
        return false;
    }
	
	public function filter(&$params){
		$params['sort'] = !empty($params['sort']) ? $params['sort'] : 'id';
		$params['order'] = !empty($params['order']) ? $params['order'] : 'desc';
		
		$field = 'id,interval,intervalMax,mobileMax,sendMax,systemMobile,closeMax,flag,config_name,create_time,set_user';
		return $this->order($params['sort'].' '.$params['order'])->field($field);
	}
	
	public function format($data){
		if(!empty($data['rows'])){
			foreach($data['rows'] as &$v){
				$v['create_time'] = Time::localDate('Y-m-d H:i:s', strtotime($v['create_time']));
				$v['id_num'] = $v['id'];
			}
		}
		return $data;
	}

}
