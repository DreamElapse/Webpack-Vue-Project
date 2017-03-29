<?php
/**
 * ====================================
 * 优惠券类库
 * ====================================
 * Author: 9004396
 * Date: 2017-02-06 15:00
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: Integral.class.php
 * ====================================
 */
namespace Common\Extend;
use Common\Extend\Time;

class Bonus{
    public function __construct() {
		
    }
	
	/*
	*	生成优惠券序列号
	*	@Author 9009123 (Lemonice)
	*	@return int
	*/
	public function getBonusNumber(){
		$asdasd = D('UserBonusCenter');
		$number = $asdasd->getField('MAX(bonus_sn) as max_sn');		
		$number = $number ? floor($number / 10000) : 100000;		
		return $number;
	}

    /*
	*	生成优惠券 - 线下发放、手动领取
	*	@Author 9009123 (Lemonice)
	*	@param  int $type_id 优惠券类型ID
	*	@param  int $number 生成的优惠券数量
	*	@return int
	*/
    public function offLineGrant($type_id = 0, $number = 0){
		if($type_id <= 0 || $number <= 0){
			return 0;
		}
		$num = $this->getBonusNumber();  //生成优惠券序列号
		$data = array();
		for ($i = 0, $j = 0; $i < $number; $i++) {
			$bonus_sn = ($num + $i) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
			$data[] = array(
				'bonus_type_id'=>$type_id,
				'bonus_sn'=>$bonus_sn,
			);
			$j++;
		}
		$result = D('UserBonusCenter')->addAll($data);
		if($result === false){
			return 0;
		}
        return $number;
    }
	
	/*
	*	按用户发放优惠券
	*	@Author 9009123 (Lemonice)
	*	@param  int $type_id 优惠券类型ID
	*	@param  array $user_id 需要发放优惠券的用户ID
	*	@return int
	*/
    public function UserGrant($type_id = 0, $user_id = array()){
		if($type_id <= 0 || empty($user_id)){
			return 0;
		}
		$type_name = D('BonusTypeCenter')->where("type_id = '$type_id'")->getField('type_name');
		if(empty($type_name)){
			return 0;
		}
		$number = 0;
		$data = array();
		$UserInfoModel = D('UserInfo');
		$UserBonusCenter = D('UserBonusCenter');
		foreach ($user_id as $key => $uid) {
			$num = $this->getBonusNumber();  //生成优惠券序列号
			$bonus_sn = ($num + 1) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
			if(strpos($type_name, "生日优惠劵") !== false) {
				$birthday = $UserInfoModel->where("user_id = '$uid'")->getField('birthday');
				
				$this_year = Time::localDate("Y");
				$birth_month = Time::localDate("m", $birthday);
				$birth_day = Time::localDate("d", $birthday);
				$end_time = Time::localMktime(23,59,59,$birth_month,$birth_day,$this_year+1);
				$start_time = Time::gmTime();
				
				$data = array(
					'bonus_id'=>'',
					'bonus_type_id'=>$type_id,
					'bonus_sn'=>$bonus_sn,
					'user_id'=>$uid,
					'used_time'=>0,
					'order_id'=>0,
					'start_time'=>$start_time,
					'end_time'=>$end_time,
					'add_time'=>Time::gmTime(),
				);
			}else{
				$data = array(
					'bonus_id'=>'',
					'bonus_type_id'=>$type_id,
					'bonus_sn'=>$bonus_sn,
					'user_id'=>$uid,
					'used_time'=>0,
					'order_id'=>0,
					'start_time'=>0,
					'add_time'=>Time::gmTime(),
				);               
			} 
			$result = $UserBonusCenter->add($data);
			if($result !== false){
				$number += 1;
			}
		}
        return $number;
    }
	
	/*
	*	按用户发放优惠券
	*	@Author 9009123 (Lemonice)
	*	@param  int $id 订单表的记录ID
	*	@return int
	*/
    public function UserRankGrant($type_id = 0, $rank_id = 0, $validated_email = 0){
		if($type_id <= 0 || $rank_id <= 0){
			return 0;
		}
		$UsersCenterModel = D('UsersCenter');
		$where = array('A.rank'=>$rank_id);
		if($validated_email == 1) {
			$where['U.is_validated'] = 1;
		}
		$UsersCenterModel->field('U.user_id')->alias('U')->join('__USER_ACCOUNT__ AS A ON U.user_id=A.user_id','left');
		$user_list = $UsersCenterModel->where($where)->select();
		$user_id = array();
		$number = 0;
		if(!empty($user_list)){
			foreach($user_list as $key=>$value){
				$user_id[] = $value['user_id'];
			}
			$number = $this->UserGrant($type_id, $user_id);
		}
        return $number;
    }
}
