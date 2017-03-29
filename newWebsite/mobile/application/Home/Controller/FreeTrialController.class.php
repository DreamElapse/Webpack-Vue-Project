<?php
/**
 *	免费试用控制器
 *
 *
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Time;
use Common\Extend\PhxCrypt;
class FreeTrialController extends InitController
{
	
	/**
	 *	获取免费试用活动
	 *
	 */
    public function index(){
		$fid = I('request.fid', 0, 'intval');
		if(!$fid){
			$this->error('活动不存在！');
		}
		$where['activity_id'] = $fid;
		$site_id = I('site_id', 0, 'intval');
		if($site_id){
			$where['site_id'] = $site_id;
		}
		
		$freeActivityModel = M('Activity',null,'USER_CENTER');
		$field = 'activity_id, site_id, activity_name, period_name, activity_desc, goods_id, goods_sn, goods_name, act_price, start_time, end_time, virtual_buy, max_number, status';
		$free_info = $freeActivityModel->field($field)->where($where)->find();
		
		$now_time = time();
		if(empty($free_info)){
			$this->error('活动不存在');
		}
		if($now_time < $free_info['start_time']){
			$this->error('活动尚未开始');
		}
		if($now_time > $free_info['end_time']){
			$this->error('活动已经结束');
		}
		switch($free_info['status']){
			case 6:
				$this->error('活动已经关闭');
			default: 
		}
		
		//获取商品信息
		$goods_info = M('Goods', null, 'USER_CENTER')->where(array('goods_id'=>$free_info['goods_id']))->find();
		$img_url = M('GoodsGallery', null, 'USER_CENTER')
				->where(array('goods_id'=>$free_info['goods_id']))
				->order('img_id ASC')
				->field('img_url')
				->find();
		
		$free_info['price'] = $goods_info['shop_price'];
		$free_info['goods_img'] = 'http://my.chinaskin.cn/public/upload/goods/'.$img_url['img_url'];
		$free_info['goods_desc'] = $goods_info['goods_desc'];
		
		//获取已经申请的数
		$trial_count = M('TryOut',null,'USER_CENTER')->where(array('status'=>1, 'activity_id'=>$fid))->count();
		$free_info['join_number'] = $trial_count + $free_info['virtual_buy'];	//总申请数量
		$free_info['leave_number'] = $free_info['max_number'] - $free_info['join_number'];	//剩余数量
		$free_info['leave_time'] = $free_info['end_time'] - Time::gmTime();	//剩余数量
		
		//获取商品属性
		// $attrs = M('GoodsAttr', null, 'USER_CENTER')->alias('ga')
			// ->join(" LEFT JOIN __ATTRIBUTE__ AS a ON a.attr_id = ga.attr_id ")
			// ->field('ga.goods_attr_id, ga.attr_value, a.attr_name')
			// ->where("ga.goods_id = ".$free_info['goods_id'])
			// ->select();
		// $free_info['attr'] = $attrs;
		
		$free_info['start_time'] = Time::localDate('m月d日', $free_info['start_time']);
		$free_info['end_time'] = Time::localDate('m月d日', $free_info['end_time']);
		
		$this->success($free_info);
    }
	
	/**
	 *	试用申请提交
	 *
	 */
	public function trialPost(){
		
		$data['activity_id'] =  I('request.fid', 0, 'intval');	//试用活动id
    	$data['name'] = strip_tags(trim(I('request.name')));	//姓名
    	$data['sex'] = I('request.sex', 0, 'intval');			//性别
    	$data['age'] = I('request.age');			//年龄
    	$data['phone'] = trim(I('request.phone'));				//手机号码
    	$data['address'] = strip_tags(trim(I('request.address')));	//收货地址
    	$data['skin_disease'] = I('request.skin_disease');			//需改善的肌肤问题
    	$data['apply_reason'] = strip_tags(trim(I('request.apply_reason')));	//申请理由
        $data['site_id'] = C('SITE_ID');	//站点id
		$user_id = session('user_id');
        $data['user_id'] = empty($user_id) ? 0 : session('user_id');
		
		if(!$data['activity_id']){
			$this->error('参数错误');
		}
		if(empty($data['name'])){
			$this->error('请填写姓名');
		}
		if(!$data['sex']){
			$this->error('请填写性别');
		}
		if(!$data['age']){
			$this->error('请选择年龄');
		}
		if(!is_phone($data['phone'])) {
			$this->error('请填写正确的手机号');
		}
		if(empty($data['address'])){
			$this->error('请填写收货地址');
		}
		if(empty($data['skin_disease'])){
			$this->error('请选择皮肤问题');
		}
		if(empty($data['apply_reason'])){
			$this->error('请填写申请理由');
		}
		$phone = PhxCrypt::phxEncrypt($data['phone']);
		$data['phone'] = $phone;
		
		//判断3月内是否有参与试用申请
		$tryOutModel = M('TryOut',null,'USER_CENTER');
		$has = $tryOutModel->where("add_time > UNIX_TIMESTAMP(DATE_SUB(CURDATE(),INTERVAL 3 MONTH)) AND activity_id = " . $data['activity_id'] . " AND phone = '$phone'")->count();
		if($has){
			$this->error('对不起，您已提交过申请！');
		}
		$data['status']  = 1;
        $data['add_time'] = Time::gmTime();
		$res = $tryOutModel->data($data)->add();
		if($res){
			$this->success();
		}else{
			$this->error('申请失败');
		}
	}
	
	/**
	 *	获取免费试用的id
	 *
	 */
	public function getTrial(){
		$freeActivityModel = M('Activity',null,'USER_CENTER');
		$time = Time::gmTime();
		$where['activity_type'] = 3;
		$where['start_time'] = array('elt', $time);
		$where['end_time'] = array('egt', $time);;
		$activity = $freeActivityModel->where($where)->field('activity_id')->find();
		if(empty($activity)){
			$this->error('暂无试用产品');
		}
		$this->success($activity);
		
	}
	
	
}
