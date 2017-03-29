<?php
/**
 * ====================================
 * 地区 控制器
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-06-27 14:32
 * ====================================
 * File: RegionController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;


class RegionController extends InitController{
	private $dbModel = NULL;  //储存地址数据表对象
	
	public function __construct(){
		parent::__construct();
		$this->dbModel = D('Region');
	}
	
	/*
	*	暂时不使用本控制器默认方法，预留
	*	@Author 9009123 (Lemonice)
	*	@return exit & 404[not found]
	*/
	public function index(){
		send_http_status(404);
	}
	
	/*
	*	获取地区列表
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function lists(){
		$region_type = I('request.region_type',0,'intval');  //地区等级，默认1级
		$parent_id = I('request.parent_id',0,'intval');      //上一级地区ID
		
		//检查是否有传_field_参数，如果有则按照传的字段返回
		$field = $this->getSearchField();
		$field = $field == '*' ? 'region_id,parent_id,region_name,region_type' : $field; 
		$this->dbModel->field($field);
		
		$where_array = array();
		if($region_type > 0 && $region_type < 5){  //级别暂时只有1-4等级
			$where_array[] = "region_type = '$region_type'";
		}
		if($parent_id > 0){  //大于0，不取出“中国”
			$where_array[] = "parent_id = '$parent_id'";
		}
		
		$where_array[] = "region_id != 33 and region_id != 34 and region_id != 35 and region_id != 48620 and region_id != 39205";
		
		if(empty($where_array)){
			$this->error('您请求的地区不存在！');
		}
		
		$list = $this->dbModel->where(implode(' and ', $where_array))->order('region_id asc,parent_id asc')->select();
		
		$this->success($list);
	}
	
	/*
	*	获取地址详情 - 需要登录
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function info(){
		$region_id = I('request.region_id',0,'intval');  //地区ID
		if($region_id <= 0){
			$this->error('地区不存在');
		}
		//检查是否有传_field_参数，如果有则按照传的字段返回
		$field = $this->getSearchField();
		$field = $field == '*' ? 'region_id,parent_id,region_name,region_type' : $field; 
		$this->dbModel->field($field);
		$this->dbModel->where("region_id = '$region_id'");
		$info = $this->dbModel->find();
		if(empty($info)){
			$this->error('地址不存在');
		}
		$this->success($info);
	}
	
	/*
	*	过滤获取_field_参数，查询字段可选择
	*	@Author 9009123 (Lemonice)
	*	@return string
	*/
	private function getSearchField($field_list = array()){
		$field = I('request._field_','','trim');
		$field_list = !empty($field_list) ? $field_list : $this->dbModel->getDbFields();
		$field_array = array();
		if($field != ''){
			$field = explode(',',$field);
			foreach($field as $f){
				if(in_array($f, $field_list)){
					$field_array[] = $f;
				}
			}
		}
		return !empty($field_array) ? implode(',',$field_array) : '*';
	}
}