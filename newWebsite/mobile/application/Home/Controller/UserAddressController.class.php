<?php
/**
 * ====================================
 * 发货地址 控制器
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-06-27 17:14
 * ====================================
 * File: UserAddressController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Time;
use Common\Extend\Order\Order;


class UserAddressController extends InitController{	
	private $not_login_msg = '您还未登录，请先登录';  //当前没登录的提示信息
	
	private $dbModel = NULL;  //储存地址数据表对象
	
	//private $user_id = 0;  //当前登录的ID
	
	//不用登录的方法名称
	private $not_login = array(
		'save','info','defaults'
	);
	
	public function __construct(){
		parent::__construct();
		$this->dbModel = D('UserAddress');
		$this->user_id = $this->checkLogin();  //检查登录，获取用户ID
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
	*	获取当前登录用户的地址列表 - 需要登录
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function lists(){
		$page = I('request.page',1,'intval');
        $pageSize = I('request.pageSize',0,'intval');
		
		$where = "user_id = '".$this->user_id."'";
		
		//检查是否有传_field_参数，如果有则按照传的字段返回
		$field = $this->getSearchField();
		$field = $field == '*' ? 'address_id,user_id,consignee,province,city,district,town,address,mobile,add_time,update_time' : $field; 
		$data = $this->dbModel->field($field)->getPage($where,$page,$pageSize);
		$this->success($data);
	}

	/*
	*	保存当前登录帐号的地址 - 不需要登录
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function save(){
		$user_id = $this->user_id;
		$address_id = I('request.address_id',0,'intval');  //地址ID
		$data = I('request.');  //更新、添加的数据
		
		$result = $this->dbModel->saveAddress($data, $user_id);
		
		if(is_array($result)){
			$this->success($result);
		}else{
			$this->error($result);
		}
	}
	
	/*
	*	删除当前登录的发货地址 - 需要登录
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function delete(){
		$user_id = $this->user_id;
		$address_id = filterInt(I('request.address_id'));  //地址ID，多个ID之间逗号隔开
		if($address_id == ''){
			$this->error('请选择最少一个地址');
		}
		$result = $this->dbModel->where("address_id = '$address_id' and user_id = '$user_id'")->delete();
		if($result){
			$UserInfo = D('UserInfo');
			$user = $UserInfo->field('default_address_id')->where("user_id = '$user_id'")->find();
			
			if(isset($user['default_address_id']) && $user['default_address_id'] == $address_id){  //当前删除的地址是默认地址
				$UserInfo->create(array(
					'user_id'=>$user_id,
					'default_address_id'=>'0'
				));
				$UserInfo->where("user_id = '$user_id'")->save();  //把默认地址更新成0
			}
		}
		$this->success(array(
			'address_id'=>$address_id,
			'affected'=>$result,
		));
	}
	
	/*
	*	获取地址详情 - 不需要登录
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function info(){
		$address_id = I('request.address_id',0,'intval');  //地址ID
		$user_id = $this->user_id;
		//如果没登录，则获取session的返回
		if($user_id == 0){
			$OrderObj = new Order();
			$info = $OrderObj->getRealUserAddress();  //获取session
			if($info){
				$info = $this->dbModel->infoFormat($info);
			}
			$this->success($info);
		}
		if($address_id <= 0){
			$this->error('地址不存在');
		}
		//检查是否有传_field_参数，如果有则按照传的字段返回
		$field = $this->getSearchField();
		$field = $field == '*' ? 'address_id,user_id,consignee,province,city,district,town,address,mobile,add_time,update_time' : $field; 
		$this->dbModel->field($field);
		$this->dbModel->where("address_id = '$address_id' and user_id = '$user_id'");
		$info = $this->dbModel->find();
		if(!empty($info)){
			$info = $this->dbModel->infoFormat($info);
		}else{
			$this->error('地址不存在');
		}
		$this->success($info);
	}
	
	/*
	*	获取当前登录用户的默认地址详情 - 需要登录
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function defaults(){
		$field = $this->getSearchField();
		$info = $this->dbModel->getDefaultAddress($this->user_id, $field);
		
		$this->success($info);
	}
	
	/*
	*	设置地址为默认地址 - 需要登录
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function setDefaults(){
		$address_id = filterInt(I('request.address_id'));  //地址ID，多个ID之间逗号隔开
		$user_id = $this->user_id;  //用户ID
		
		$result = D('UserAddress')->where("address_id = '$address_id' and user_id = '$user_id'")->getField('address_id');
		if($result == false || $result <= 0){
			$this->error('地址不存在');
		}
		$UserInfo = D('UserInfo');
		
		$user = $UserInfo->field('user_id')->where("user_id = '$user_id'")->find();
		
		$UserInfo->create(array(
			'user_id'=>$user_id,
			'default_address_id'=>$address_id
		));
		
		if(!$user){
			$result = $UserInfo->add();
		}else{
			$result = $UserInfo->where("user_id = '$user_id'")->save();
		}
		
		$this->success(array(
			'address_id'=>$address_id,
			'affected'=>$result,
		));
	}
	
	/*
	*	检查当前是否登录
	*	@Author 9009123 (Lemonice)
	*	@return int [user_id]
	*/
	private function checkLogin(){
		$user_id = $this->dbModel->getUser('user_id');  //用户ID
		//检查当前方法是否不用登录
		if(in_array(strtolower(ACTION_NAME), $this->not_login)){
			return $user_id;  //不用强制登录
		}
		if(is_null($user_id) || $user_id <= 0){
			$this->error($this->not_login_msg);  //没登录
		}
		return $user_id;
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