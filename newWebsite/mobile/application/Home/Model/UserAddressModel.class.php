<?php
/**
 * ====================================
 * 用户发货地址 模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-06-27 17:18
 * ====================================
 * File: UserAddressModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\UserCenterModel;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;
use Common\Extend\Order\Order;
use Common\Extend\Send;

class UserAddressModel extends UserCenterModel{
	private $attribute_ext = '|+_+|';  //用来保存地址属性的分隔符，请不要修改
	private $default_address_id = array();  //用来储存默认地址ID的
	
	/*
	*	保存、添加地址
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
	public function saveAddress($data = array(), $user_id = 0){
		if(!isset($data['consignee']) || $data['consignee'] == ''){
			return '请输入收货人';
		}
		if(mb_strlen(trim($data['consignee']), 'utf8')<2 && !preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', trim($data['consignee']))){
			return '收货人必须最少2位中文';
		}
		$data['mobile'] = trim($data['mobile']);
		if($data['address_id'] > 0){  //编辑
			if($data['mobile'] != ''){
				$mobile = $this->where("user_id = '$user_id' and address_id = '$data[address_id]'")->getField('mobile');
				if($mobile != ''){
					$rs = $this->phxDecode(array('mobile'=>$mobile));
					if($rs['mobile'] != $data['mobile']){
						if(!preg_match('/^((\+86)|(86))?(1[3|4|5|7|8]{1}\d{9})$/', $data['mobile'])){
							return '请输入正确的手机号码';
						}						
					}else{
						unset($data['mobile']);  //等于空表示不修改手机号码
					}
				}else if(!preg_match('/^((\+86)|(86))?(1[3|4|5|7|8]{1}\d{9})$/', $data['mobile'])){
					return '请输入正确的手机号码';
				}				
			}elseif($data['mobile'] == ''){
				unset($data['mobile']);  //等于空表示不修改手机号码
			}
		}else{  //添加
			if(trim($data['mobile']) == ''){
				return '请输入手机号码';
			}
			if(!preg_match('/^((\+86)|(86))?(1[3|4|5|7|8]{1}\d{9})$/', $data['mobile'])){
				return '请输入正确的手机号码';
			}
		}
		
		
		//验证短信验证码
		/*if(isset($data['mobile'])){
			if(!isset($data['code']) || $data['code'] == ''){
				return '请输入验证码';
			}
			$ret = Send::checkMobileCode($data['code'],0,$data['mobile']);
			if (!$ret) {
				return '验证码不正确';
			}
		}*/
		
		
		if(!isset($data['province']) || intval($data['province']) <= 0){
			return '请选择所在省份';
		}
		if(!isset($data['city']) || intval($data['city']) <= 0){
			return '请选择所在城市';
		}
		if(!isset($data['district']) || intval($data['district']) == ''){
			return '请选择所在区域';
		}
		$data['address'] = trim($data['address']);
		if(mb_strlen($data['address'], 'utf8')<5 && !preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $data['address'])){
			return '收货人的详细地址必须大于5位中文';
		}
		
		/*
		$date['tel'] = trim($this->_post('tel'));
		if(strlen($date['tel'])>0 && !preg_match('/^0?1[3|4|5|8]\d{9,}$/', $date['tel']) && !preg_match('/^0\d{2,3}(\-)?\d{7,8}((\-)?\d{1,8})?$/', $date['tel'])){
			return 0, '固定电话不是有效的号码';
		}
		if(strlen($date['tel']) == 0 && strlen($date['mobile']) == 0){
			return 0, '手机和座机至少写一个';
		}*/
		
		if($user_id > 0){  //有登录的，插入到数据库
			$data['user_id'] = $user_id;
			if($data['address_id'] > 0){  //编辑
				$address_id = $data['address_id'];
				$data['update_time'] = Time::gmTime();
				$result = $this->where("user_id = '$user_id' and address_id = '$address_id'")->update($data);
				if($result){
					$this->checkDefaultAddress($user_id, $address_id);
				}
			}else{  //添加
				$data['add_time'] = Time::gmTime();
				$address_id = $this->insert($data);
				
				if($address_id){  //添加成功，检查是否有设置了默认地址，如果没有则自动设置
					$this->checkDefaultAddress($user_id, $address_id);
				}
				
				$result = 1;
			}
		}else{  //没登录的，保存到session
			session('new_consignee', $data);
			$address_id = 0;
			$result = 1;
		}
		
		return array(
			'address_id'=>$address_id,
			'affected'=>$result,
		);
	}
	
	/*
	*	检查是否有存在默认地址，没有则自动设置
	*	@Author 9009123 (Lemonice)
	*	@param int $user_id 用户ID
	*	@param int $address_id  地址ID
	*	@return array
	*/
	public function checkDefaultAddress($user_id = 0, $address_id = 0){
		if($address_id > 0 && $user_id > 0){
			$UserInfo = D('UserInfo');
			$user = $UserInfo->field('default_address_id')->where("user_id = '$user_id'")->find();
			
			if(!isset($user['default_address_id']) || $user['default_address_id'] == 0){
				$UserInfo->create(array(
					'user_id'=>$user_id,
					'default_address_id'=>$address_id
				));
			}else{
				$address_id = $this->where("address_id = '$user[default_address_id]'")->getField('address_id');
				if(!$address_id || $address_id == 0){
					$UserInfo->create(array(
						'user_id'=>$user_id,
						'default_address_id'=>$address_id
					));
				}
			}
			
			if(!isset($user['default_address_id'])){
				$UserInfo->add();
			}else{
				$UserInfo->where("user_id = '$user_id'")->save();
			}
			return true;
		}
		return false;
	}
	
	/*
	*	获取用户地址列表
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
	public function getUserAddress($user_id = 0, $where = ''){
		if($user_id <= 0 && $where == ''){
			return array();
		}
		if($where == ''){
			$where = "user_id = '$user_id'";
		}
		$default_address_id = $this->getUserDefaultAddress($user_id, $where);  //获取这个用户的默认地址ID
		$this->order("address_id = '$default_address_id' desc,update_time desc,add_time desc");
		$user_address_list = D('UserAddress')->where($where)->getAll();
		return $user_address_list;
	}
	
	/*
	*	获取分页列表
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
	public function getPage($where = '', $page = 1, $pageSize = 0){
		$total = 0;
		$pageTotal = 1;
		
		//是否启用分页
		if($pageSize > 0){
			$total = $this->where($where)->count();  //统计总记录数
			$this->page($page.','.$pageSize);
			$pageTotal = ceil($total / $pageSize);  //计算总页数
		}else{
			$page = 1;
		}
		
		$list = $this->getUserAddress(0, $where);
		$total = $total > 0 ? $total : count($list);
        return array('page' => $page, 'pageSize' => $pageSize, 'total' => (int)$total, 'pageTotal' => $pageTotal, 'list' => $list);
	}
	
	/*
	*	设置临时地址到会员
	*	@Author 9009123 (Lemonice)
	*	@param  $user_id int  用户ID
	*	@return true or false
	*/
	public function setRealUserAddress($user_id = 0){
		if($user_id <= 0){
			return false;
		}
		$OrderObj = new \Common\Extend\Order\Order();
		$address = $OrderObj->getRealUserAddress();  //获取session
		if(!empty($address) && isset($address['province']) && $address['province'] > 0){
			$address['user_id'] = $user_id;
			$address_id = $this->insert($address);
			if($address_id > 0){  //添加成功，设置默认地址
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
			}
		}
		return true;
	}
	
	/*
	*	获取所有地址
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
	public function getAll(){
		$list = $this->select();
		if(!empty($list)){
			foreach($list as $key=>$value){
				$list[$key] = $this->infoFormat($value);  //解析详情
			}
		}
		return $list;
	}
	
	/*
	*	编辑地址
	*	@Author 9009123 (Lemonice)
	*	@param  $data array  地址详情
	*	@return int [Affected Number]
	*/
	public function update($data = array()){
		$data = $this->phxEncode($data);
		$this->create($data);
		$result = $this->save();
		return $result==false ? 0 : $result;
	}
	
	/*
	*	插入地址
	*	@Author 9009123 (Lemonice)
	*	@param  $data array  地址详情
	*	@return int [insert_id]
	*/
	public function insert($data = array()){
		$data = $this->phxEncode($data);
		$this->create($data);
		$insert_id = $this->add();
		return $insert_id;
	}
	
	/*
	*	解析地址详情
	*	@Author 9009123 (Lemonice)
	*	@param  $info array  地址详情
	*	@return array
	*/
	public function infoFormat($info){
		//解析出地址属性
		$info = $this->phxDecode($info);
		//获取省份、城市、村镇、街道
		$info = $this->getRegion($info);
		//判断是否默认的地址，增加is_defaults字段
		$info = $this->checkDefault($info);
		
		$info['add_time'] = Time::localDate('Y-m-d H:i:s', $info['add_time']);
		$info['update_time'] = Time::localDate('Y-m-d H:i:s', $info['update_time']);
		
		return $info;
	}
	/*
	*	地址中加密字段的解密
	*	@Author 9009123 (Lemonice)
	*	@param  $info array  地址详情
	*	@return array
	*/
	public function checkDefault($info){
		if(isset($info['user_id']) && $info['user_id'] > 0){  //从自身数据用户里面判断是否默认
			$user_id = $info['user_id'];
		}else{  //获取当前登录的用户ID来判断是否默认
			$user_id = $info['user_id'];
		}
		$default_address_id = $this->getUserDefaultAddress($user_id);
		$info['is_defaults'] = $default_address_id==$info['address_id'] ? 1 : 0;
		return $info;
	}
	
	/*
	*	获取默认地址详情  [用户默认地址 或者 临时地址]
	*	@Author 9009123 (Lemonice)
	*	@param  $user_id int  用户ID
	*	@param  $field   string  查询的字段
	*	@return array
	*/
	public function getDefaultAddress($user_id = 0, $field = 'address_id,user_id,consignee,province,city,district,town,address,mobile,add_time,update_time'){
		if($user_id <= 0){
			$OrderObj = new Order();
			$info = $OrderObj->getRealUserAddress();  //获取session
			if($info){
				$info = $this->infoFormat($info);
			}
			return $info;
		}
		
		$userInfo = D('UserInfo');
		
		//获取当前帐号的默认地址ID，并且校验是否登录和帐号是否存在
		$user = $userInfo->field('user_id,default_address_id')->where("user_id = '$user_id'")->find();
		
		$default_address_id = 0; // 默认地址ID
		if(isset($user['default_address_id'])){  //有用户扩展记录
			$default_address_id = $user['default_address_id'];
		}
		
		//检查是否有传_field_参数，如果有则按照传的字段返回
		$this->field($field);
		
		if($default_address_id > 0){  //有默认地址
			$this->where("address_id = '$default_address_id'");
		}else{  //没默认地址，获取最近更新的一个地址
			$this->where("user_id = '$user_id'");
			$this->order('update_time desc,add_time desc');
		}
		
		$info = $this->find();
		
		if(!empty($info)){
			$info = $this->infoFormat($info);
		}
		return $info;
	}
	
	/*
	*	获取某用户的默认地址ID
	*	@Author 9009123 (Lemonice)
	*	@param  $user_id int  用户ID
	*	@param  $where string  where条件
	*	@return array
	*/
	public function getUserDefaultAddress($user_id = 0, $where = ''){
		$key = '';
		if($user_id > 0){  //传的是用户ID
			$key = $user_id;
			if(!isset($this->default_address_id[$user_id])){
				$this->default_address_id[$user_id] = D('UserInfo')->where("user_id = '$user_id'")->getField('default_address_id');
			}
		}else{  //传的where条件
			$key = md5($where);
			if(!isset($this->default_address_id[$key])){
				$this->default_address_id[$key] = D('UserInfo')->where($where)->getField('default_address_id');
			}
		}
		
		$address_id = $this->default_address_id[$key];
		return $address_id;
	}
	
	/*
	*	地址中加密字段的解密
	*	@Author 9009123 (Lemonice)
	*	@param  $info array  地址详情
	*	@return array
	*/
	public function phxDecode($info){
		//解析出地址属性
		if($info['address'] != ''){
			$info['attribute'] = ($info['attribute']!='' ? $info['attribute'] : '');
			if(strstr($info['address'],$this->attribute_ext)){
				$address = explode($this->attribute_ext,$info['address']);
				$info['attribute'] = isset($address[0]) ? $address[0] : '';
				$info['address'] = isset($address[1]) ? $address[1] : '';
			}
		}
		if(isset($info['mobile']) && $info['mobile'] != ''){
			$info['mobile'] = PhxCrypt::phxDecrypt($info['mobile']);
		}
		return $info;
	}
	
	/*
	*	地址中加密字段的加密
	*	@Author 9009123 (Lemonice)
	*	@param  $info array  地址详情
	*	@return array
	*/
	public function phxEncode($info){
		if(isset($info['mobile'])){
			$info['mobile'] = $info['mobile']!='' ? PhxCrypt::phxEncrypt($info['mobile']) : '';
		}
		if(isset($info['address'])){
			$info['attribute'] = isset($info['attribute']) ? $info['attribute'] : '';
			$info['address'] = $info['attribute'] . $this->attribute_ext . $info['address'];  //拼接属性
		}
		return $info;
	}
	/*
	*	获取地址中的省份、城市、村镇、街道 名称
	*	@Author 9009123 (Lemonice)
	*	@param  $info array  地址详情
	*	@return array
	*/
	public function getRegion($info){
		$where_array = array();
		$region_name = array();
		
		if(isset($info['province']) && $info['province'] > 0){
			$region_name[$info['province']] = 'province_name';
			$where_array[] = "region_id = '$info[province]'";
		}
		if(isset($info['city']) && $info['city'] > 0){
			$region_name[$info['city']] = 'city_name';
			$where_array[] = "region_id = '$info[city]'";
		}
		if(isset($info['district']) && $info['district'] > 0){
			$region_name[$info['district']] = 'district_name';
			$where_array[] = "region_id = '$info[district]'";
		}
		if(isset($info['town']) && $info['town'] > 0){
			$region_name[$info['town']] = 'town_name';
			$where_array[] = "region_id = '$info[town]'";
		}
		
		if(!empty($where_array)){
			$regionModel = D('Region');
			$regionModel->where(implode(' or ', $where_array));
			$regionModel->limit(count($where_array));
			$region = $regionModel->field('region_id,region_name')->select();
			if(!empty($region)){
				foreach($region as $v){
					if(isset($region_name[$v['region_id']])){
						$info[$region_name[$v['region_id']]] = $v['region_name'];
					}
				}
			}
		}
		return $info;
	}
}