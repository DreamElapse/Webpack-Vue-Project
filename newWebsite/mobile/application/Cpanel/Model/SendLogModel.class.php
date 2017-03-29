<?php
//=====================================
// 短信发送日志模型
//====================================
// Author: 9006758
//
//
// File: SendLogModel.class.php
//===================================

namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\Time;
use Common\Extend\PhxCrypt;

class SendLogModel extends CpanelUserCenterModel{
	protected $tableName = 'send_log';

	protected $lang_status = array(
		'com' => array(
			99 => '发送成功',
			1 => '测试短信',
		),
		0 => array(//联通
			2 => 'IP地址非法',
			3 => '用户认证错误',
			4 => '代理商编号不能为空',
			5 => '手机号码不能为空',
			6 => '发送内容不能为空',
			7 => '短信内容含有非法关键字',
			8 => '一次群发数量不能超过2000个',
			9 => '发送的内容过长',
			10 => '余额不足',
			11 => '计费失败',
			12 => '数据库异常',
			13 => '序列号长度过长',
			14 => '扩展号过长',
		),
		1 => array(//漫道
			2 => '发送中',
			-2 => '帐号密码错误',
			-4 => '余额不足',
			-5 => '数据格式错误',
		)
	);
	

	public function filter(&$params){
		
		$params['sort'] = empty($params['sort']) ? 'id' : $params['sort'];
		$params['order'] = empty($params['order']) ? 'desc' : $params['order'];
		
		if(is_numeric($params['channel'])){
			$where['log.channel'] = intval($params['channel']);
		}
		if(is_numeric($params['state'])){
			if($params['state'] == '99'){
				$params['state'] = 0;
			}
			$where['log.status'] = intval($params['state']);
		}
		//echo '<pre>';
		//print_r($where);exit;
		if(is_numeric($params['smsType'])){
			$smsType = intval($params['smsType']);
			if($smsType == 1){
				$where['log.type'] = $smsType;
			}else{
				$where['log.type'] = array('neq', 1);
			}
		}
		if(!empty($params['start_time']) && !empty($params['end_time'])){
			$start_time = Time::localStrtotime(trim($params['start_time']));
			$end_time = Time::localStrtotime(trim($params['end_time']));
			$where['log.add_time'] = array('between', array($start_time, $end_time));
		}
		if(!empty($params['keyword'])){
			$keyword = trim($params['keyword']);
		}
		if(is_numeric($params['type'])){
			switch(intval($params['type'])){
				case 1:
					$where['log.user_id'] = intval($keyword);
					break;
				case 2:
					$where['user.email'] = array('like', "%$keyword%");
					break;
				case 3:
					$where['log.mobile'] = array('like', "%".PhxCrypt::phxEncrypt($keyword)."%");
					break;
				case 4:
					$where['user.user_num'] = $keyword;
					break;
				case 5:
					$where['log.content'] = array('like', "%$keyword%");
					break;
				case 6:
					$where['user.user_name'] = array('like', "%$keyword%");
					break;
				// case 7:
					// $where['log.order_sn'] = $keyword;
					// break;
				case 8:
					$where['log.flag'] = $keyword;
					break;
				case 9:
					$where['log.source'] = array('like', "%$keyword%");
					break;
					default:
					$where['_string'] = "log.content like '%$keyword%' or log.mobile like '%".PhxCrypt::phxEncrypt($keyword)."%'";
			}
		}
		// print_r($where);exit;
		$field = 'log.id,log.user_id,log.mobile,log.content,log.flag,log.status,';
		$field .= 'log.delivery_status,log.channel,log.type,log.ip,log.add_time,log.source,';
		$field .= 'user.user_num,user.email,user.user_name';
		
		return $this->alias('log')
				->join('left join __USERS__ as user on log.user_id = user.user_id')
				->field($field)
				->where($where);
	}
	

	public function format($data){
		foreach($data['rows'] as &$val){
			$val['channel'] = $val['channel']==1 ? '漫道' : '联通';
			$val['delivery_status'] = $val['delivery_status']==1 ? '<b style="color:red;">异常</b>' : '<b style="color:green;">正常</b>';
			$lang_status = $this->langStatus($val['type'], $val['status']);
			if($val['status'] == 0){
				$val['status'] = '<b style="color:green;">'.$lang_status.'</b>';
			}else{
				$val['status'] = '<b style="color:red;">'.$lang_status.'</b>';
			}
			$val['type'] = $val['type'] == 1 ? '验证类' : '信息类';
			$source = explode('/', $val['source']);
			$val['source'] = $source[0];
			$val['mobile'] = PhxCrypt::phxDecrypt($val['mobile']);
			$val['add_time'] = Time::localDate('Y-m-d H:i:s', strtotime($val['add_time']));
		}
		return $data;
	}
	
	public function langStatus($type, $status=null){
		$lang_arr = $this->lang_status['com'] + $this->lang_status[$type];
		if(is_null($status)){
			return $lang_arr;
		}
		if($status == '0'){
			$status = 99;
		}
		return $lang_arr[$status];
	}

	
}

