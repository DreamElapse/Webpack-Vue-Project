<?php
/**
 * ====================================
 * 优惠券
 * ====================================
 * Author: 9009123
 * Date: 2017-03-18 11:17
 * ====================================
 * File: BonusController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;
use Common\Extend\Time;
use Common\Extend\Bonus;
use Common\Extend\PhxCrypt;

class BonusController extends CpanelController {
    protected $tableName = 'BonusTypeCenter';
	
	protected $allowAction = array('searchUser');

    public function __construct() {
        parent::__construct();
    }
	
	/**
     * 查列表
     */
    public function index() {
        if(IS_AJAX) {
            $params = I('request.');
            $this->dbModel->filter($params);
			$data = $this->dbModel->grid($params);
			$data = $this->dbModel->format($data);
            $this->ajaxReturn($data);
            exit;
        }
		$send_by = L('send_by');
		unset($send_by[SEND_BY_GOODS],$send_by[SEND_BY_ORDER]);
		$this->assign('send_by', $send_by);
		$this->assign('coupon_type', L('coupon_type'));
		$this->assign('coupon_range', L('coupon_range'));
        $this->display($this->template);
    }
	
	/*
	*	搜索用户帐号列表
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function searchUser(){
		$keyword_type = I('post.keyword_type', 0, 'intval');
		$keyword = I('post.keyword', '', 'trim');
		if($keyword_type <= 0){
			$this->error('请选择搜索的会员信息');
		}
		if(empty($keyword)){
			$this->error('请输入搜索的关键字');
		}
		$keyword_field_array = array(
			1=>'user_id',  //用户id
			2=>'email',  //邮箱
			3=>'mobile',  //手机号码
			4=>'user_num',  //会员号
			5=>'user_name',  //会员呢称
		);
		$list = D('UsersCenter')->searchList(isset($keyword_field_array[$keyword_type]) ? $keyword_field_array[$keyword_type] : 'user_id', $keyword, 'user_id,user_name,mobile');
		
		if(!empty($list)){
			foreach($list as $key=>$value){
				$value['user_name'] = trim($value['user_name']);
				$value['user_name'] = !empty($value['user_name']) ? $value['user_name'] : PhxCrypt::phxDecrypt($value['mobile']);
				unset($value['mobile']);
				$list[$key] = $value;
			}
		}
		$this->success($list);
	}
	
	/*
	*	优惠券导出
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function export(){
		$type_id = I('get.type_id', 0, 'intval');
		if($type_id <= 0){
			$this->error('请选中需要操作的优惠券类型');
		}
		
		$info = $this->dbModel->field('type_id,type_name,send_type,reuse')->where(array('type_id'=>$type_id))->find();
		if(empty($info)){
			$this->error('优惠券类型不存在');
		}
		
		if ($type_info['send_type'] == SEND_BY_PRINT || $type_info['send_type'] == SEND_BY_HAND) {
			$send_by = L('send_by');
			$this->error('只有['.$send_by[SEND_BY_PRINT].']和['.$send_by[SEND_BY_HAND].']才能导出报表！');
		}
		
		
		$params = I('request.');
		$UserBonusCenter = D('UserBonusCenter');
		$UserBonusCenter->filter($params);
		$data = $UserBonusCenter->select();
		$data = $UserBonusCenter->format(array('rows'=>$data));
		$rows = isset($data['rows']) ? $data['rows'] : array();
		
		if(!empty($rows)){
			$excel = "优惠券ID,";
			$excel .= "优惠券名称,";
			$excel .= "优惠券号码,";
			$excel .= "订单ID,";
			$excel .= "使用会员,";
			$excel .= "使用时间";
			if($info['reuse'] == 1){
				$excel .= ",使用数量,";
				$excel .= "领取数量";
			}
			$excel .= "\n";
			foreach($rows as $value){
				$excel .= ($value['bonus_id']!='' ? str_replace(',','|',$value['bonus_id']) : '').",";
				$excel .= ($info['type_name']!='' ? str_replace(',','|',$info['type_name']) : '').",";
				$excel .= ($value['bonus_sn']!='' ? str_replace(',','|',$value['bonus_sn']) : '').",";
				$excel .= ($value['order_id']!='' ? str_replace(',','|',$value['order_id']) : '').",";
				$excel .= ($value['user_name']!='' ? str_replace(',','|',$value['user_name']) : '').",";
				$excel .= ($value['used_time']!='' ? str_replace(',','|',$value['used_time']) : '');
				
				if($info['reuse'] == 1){
					$excel .= ",".$value['use_amount'].",";
					$excel .= $value['use_amount'];
				}
				$excel .= "\n";
			}
			$excel = iconv("utf-8", "gb2312", $excel);
			$filename = 'Bonus_List_'.date('YmdHis').'.csv';//添加文件名和后缀名
			
			//header("Content-type: text/csv");
			//header("Content-Disposition: attachment;filename=".$filename);
			//header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
			//header('Expires: 0');
			//header('Pragma: public');
			
			header("Content-type:text/csv"); 
			header("Content-Disposition:attachment;filename=".$filename);
			echo $excel;
			exit;
		}
		
		echo '<script>alert("抱歉，当前选择的优惠券暂时无数据，不能导出！");window.history.go(-1);</script>';
		exit;
	}
	
	/*
	*	发放优惠券 - 显示页面
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function grant(){
		$type_id = I('get.type_id', 0, 'intval');
		if($type_id <= 0){
			$this->error('请选中需要操作的优惠券类型');
		}
		
		$info = $this->dbModel->field('type_id,send_type')->where(array('type_id'=>$type_id))->find();
		if(empty($info)){
			$this->error('优惠券类型不存在');
		}
		$template = 'grant';
		if ($info['send_type'] == SEND_BY_USER) {  //按用户发放
			$this->assign('rank_list',     D('UserRank')->getRankList());
			$template = 'grantUser';
		}else {
			$this->assign('type_list',    $this->dbModel->getBonusTypeList($info['send_type']));
		}
		$this->assign('info', $info);
		$this->display($template);
	}
	
	/*
	*	发放优惠券 - 发放
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function doGrant(){
		$type_id = I('post.type_id', 0, 'intval');
		$number = I('post.number', 0, 'intval');
		$validated_email = I('post.validated_email', 0, 'intval');
		$handle_type = I('post.handle_type', '', 'trim');
		$rank_id = I('post.rank_id', 0, 'intval');
		$user_id = I('post.user_id', '', 'trim');
		
		if($type_id <= 0){
			$this->error('请选中需要操作的优惠券类型');
		}
		
		if($handle_type == 'rank'){
			if($rank_id <= 0){
				$this->error('请选择需要发放的用户等级');
			}
		}else if($handle_type == 'user'){
			if($user_id == ''){
				$this->error('请选择需要发放的用户');
			}
		}else{
			if($number <= 0){
				$this->error('请输入生成的数量');
			}
		}
		
		$info = $this->dbModel->info($type_id);
		if(empty($info)){
			$this->error('优惠券类型不存在');
		}
		@set_time_limit(0);
		
		$Bonus = new Bonus();
		$result = 0;
		
		$send_by = L('send_by');
		D('Admin')->addLog('发放优惠券[type_id='.$type_id.','.(isset($send_by[$info['send_type']]) ? $send_by[$info['send_type']] : '未知发放方式').']', login('user_id'));
		
		if ($info['send_type'] == SEND_BY_USER) {  //按用户发放
			$result = 0;
			if($handle_type == 'rank'){
				$result = $Bonus->UserRankGrant($type_id, $rank_id, $validated_email);
			}else if($handle_type == 'user'){
				$result = $Bonus->UserGrant($type_id, explode(',',$user_id));
			}
		}else {
			$result = $Bonus->offLineGrant($type_id, $number);
		}
		$this->success('共成功发放 '.$result.' 张优惠券');
	}
	
	/*
	*	查看优惠券
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function bonusList(){
		$type_id = I('get.type_id', 0, 'intval');
		if($type_id <= 0){
			$this->error('请选中需要操作的优惠券类型');
		}
		
		if(IS_AJAX) {
            $params = I('request.');
			$UserBonusCenter = D('UserBonusCenter');
			$UserBonusCenter->filter($params);
			$data = $UserBonusCenter->grid($params);
			
			if(method_exists($UserBonusCenter, 'format')) {
				$data = $UserBonusCenter->format($data);
			}
            $this->ajaxReturn($data);
            exit;
        }
		
		$show_bonus_sn = 0;
		$is_reuse = 0;
		if($type_id > 0){
			$type_info = D('BonusTypeCenter')->field('type_name,send_type,reuse')->where("type_id = '$type_id'")->find();
			if ($type_info['send_type'] == SEND_BY_PRINT || $type_info['send_type'] == SEND_BY_HAND) {
				$show_bonus_sn = 1;
			}
			$is_reuse = $type_info['reuse'];
		}
		$this->assign('show_bonus_sn', $show_bonus_sn);
		$this->assign('is_reuse', $type_info['reuse']);
		
        $this->display();
	}
	
	/*
	*	添加、编辑
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function form(){
		$type_id = I('request.type_id', 0, 'intval');
		if($type_id > 0){
			$info = $this->dbModel->info($type_id);
			if(empty($info)){
				$this->error('优惠券类型不存在');
			}
		}else{
			$start_time = time();
			$end_time = strtotime("+1 month");
			$info = array(
				'type_id'=>0,
				'send_start_date'=>$start_time,
				'send_end_date'=>$end_time,
				'use_start_date'=>$start_time,
				'use_end_date'=>$end_time,
				'type_money'=>0,
				'send_type'=>SEND_BY_USER,
				'is_package'=>0,
				'amount_range_limit'=>0,
				'use_site'=>0,
				'reuse'=>0,
			);
		}
		
		
		$send_by = L('send_by');
		unset($send_by[SEND_BY_GOODS],$send_by[SEND_BY_ORDER]);
		$this->assign('send_by', $send_by);
		$this->assign('coupon_type', L('coupon_type'));
		$this->assign('coupon_range', L('coupon_range'));
		
		$this->assign('info', $info);
		$this->display();
	}
	
	/*
	*	添加、编辑表单提交校验
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function _before_save(){
		$params = I('post.');
		$params['coupon_type'] = intval($params['coupon_type']);
		$params['send_type'] = intval($params['send_type']);
		
		if(!isset($params['send_type']) || $params['send_type'] < SEND_BY_USER){
			$this->error('请选择一个发放类型');
		}
		if(empty($params['type_name'])){
			$this->error('请输入优惠券名称');
		}
			
		if($params['coupon_type'] == COUPON_TYPE_COMMON && empty($params['type_money'])){  //普通优惠券
			$this->error('请输入优惠券金额');
		}else{
			$params['type_money'] = floatval($params['type_money']);
		}
		
		if($params['coupon_type'] == COUPON_TYPE_ENTITY && empty($params['type_info'])){  //实物劵
			$this->error('请输入活动ID');
		}else if($params['coupon_type'] == COUPON_TYPE_DISCOUNT && empty($params['type_info'])){  //折扣劵
			$this->error('请输入折扣');
		}
		
		if(isset($params['send_start_date']) && $params['send_start_date'] != ''){
			$params['send_start_date'] = strtotime($params['send_start_date']);
		}else if(!isset($params['send_start_date']) && isset($params['send_start_date_bak'])){
			$params['send_start_date'] = strtotime($params['send_start_date_bak']);
		}
		if(isset($params['send_end_date']) && $params['send_end_date'] != ''){
			$params['send_end_date'] = strtotime($params['send_end_date'].' 23:59:59');
		}else if(!isset($params['send_end_date']) && isset($params['send_end_date_bak'])){
			$params['send_end_date'] = strtotime($params['send_end_date_bak'].' 23:59:59');
		}
		$params['use_start_date'] = !empty($params['use_start_date']) ? strtotime($params['use_start_date']) : 0;
		$params['use_end_date'] = !empty($params['use_start_date']) ? strtotime($params['use_end_date'].' 23:59:59') : 0;
		
		$params['coupon_range'] = intval($params['coupon_range']);
		if($params['coupon_range'] == COUPON_RANGE_CLASS && empty($params['coupon_range_info'])){  //指定分类
			$this->error('请输入分类id');
		}else if($params['coupon_range'] == COUPON_RANGE_PACKAGE && empty($params['coupon_range_info'])){  //指定套装
			$this->error('请输入套装id');
		}else if($params['coupon_range'] == COUPON_RANGE_GOODS && empty($params['coupon_range_info'])){  //指定单品
			$this->error('请输入单品id');
		}else if($params['coupon_range'] == COUPON_RANGE_ACT && empty($params['coupon_range_info'])){  //指定活动
			$this->error('请输入活动id');
		}else if($params['coupon_range'] == COUPON_RANGE_GOODS_PACKAGE && empty($params['coupon_range_info'])){  //指定单品和套装
			$this->error('请输入套装和单品id');
		}
		
		$params['type_info'] = htmlspecialchars(stripslashes($params['type_info']));
		$params['coupon_range_info'] = htmlspecialchars(stripslashes($params['coupon_range_info']));
		$params['member_discount'] = !empty($params['member_discount']) ? intval($params['member_discount']) : 0;
		$params['payonline_discount'] = !empty($params['payonline_discount'])?intval($params['payonline_discount']) : 0;
		$params['other_gift'] = !empty($params['other_gift']) ? intval($params['other_gift']) : 0;
		$params['amount_range_limit'] = !empty($params['amount_range_limit']) ? floatval($params['amount_range_limit']) : 0;
		
		if(!isset($params['type_id']) || $params['type_id'] <= 0){
			$params['add_time'] = Time::gmtime();
		}
		
		return $params;
	}
	
	/*
	*	保存后的操作
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function _after_save(){
		$params = I('post.');
		if(empty($params['type_id'])) {
            $message = '添加优惠券[' . $params['type_name'] . ']';
        }else {
            $message = '编辑优惠券[ID:' . $params['type_id'] . ', type_name=' . $params['type_name'] . ']';
        }
		D('Admin')->addLog($message, login('user_id'));
	}
}

