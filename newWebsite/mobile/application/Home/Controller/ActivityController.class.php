<?php
/**
 *	活动接口
 *
 *
 */
namespace Home\Controller;
use Common\Controller\InitController;

class ActivityController extends InitController
{
	private $actModel;
	
	public function __construct(){
		
		parent::__construct();
		$this->actModel = M('FavourableActivity');
	}

    public function index(){}
    
	/*
	 *	获取活动类型信息
	 *	return array
	 */
	public function getActInfo(){
		$act_type = I('request.act');
		$config_act = load_config(CONF_PATH.'config_activity.php');
		if(empty($act_type)){
			$this->error('param error!');
		}
		
		$config = $config_act[$act_type][C('SITE_ID')];
		if(empty($config)){
			$this->error('act not exist!');
		}
		
		// $now_time = time();
		$data = array();
		foreach($config as $key=>$val){
			$activity_info = $this->actModel
						->field('act_id,act_name,start_time,end_time,user_rank,min_amount,max_amount,gift,gift_package,is_join_amount')
						->find($val);
			$activity_info['gift'] = unserialize($activity_info['gift']);
			$activity_info['gift_package'] = unserialize($activity_info['gift_package']);
			$data[$key] = $activity_info;
		}
		
		unset($activity_info);
		$this->success($data);
	}
}
