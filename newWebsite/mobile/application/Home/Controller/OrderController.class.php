<?php
/**
 * ====================================
 * 订单 控制器
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-06-29 15:31
 * ====================================
 * File: OrderController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;


class OrderController extends InitController{
	private $not_login_msg = '您还未登录，请先登录';  //当前没登录的提示信息
	
	private $dbModel = NULL;  //储存地址数据表对象
	
	//private $user_id = 0;  //当前登录的ID
	
	private $not_login_action = array();  //不需要登录的方法
	
	public function __construct(){
		parent::__construct();
		$this->dbModel = D('OrderInfo');
//		session('user_id', 6540);  //测试的
//		C('SITE_ID',3);  //测试的
		if(isset($this->not_login_action) && !in_array(ACTION_NAME, $this->not_login_action)){
			$this->user_id = $this->checkLogin();  //检查登录，获取用户ID
		}
	}
	
	/*
	*	订单列表 - 自己的订单列表 - 会员中心
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function lists(){
        $user_id = $this->getUserId();
        $status = I('request.status',0,'intval');  //0=全部，1=未支付，2=货到付款，3=待发货,4=已发货,5=已完成,6=在线支付
		$page = I('request.page',1,'intval');
		$pageSize = I('request.pageSize',0,'intval');
		$site_id = C('SITE_ID');  //当前站点ID
		
		$where = array();
		if($status > 0){  //不是获取全部
			switch($status){
				case 1:  //未支付
					$where[] = "pay_id > 1 and (pay_status = " . PS_UNPAYED." OR pay_status = ".PS_PAYING.")";
				break;
				case 2:  //货到付款
					$where[] = "pay_id = 1";
				break;
                case 3: //待发货
                    $where[] = 'shipping_status = '.SS_PREPARING.' and order_status = '.OS_CONFIRMED;
                break;
                case 4: //已发货
                    $where[] = 'shipping_status = '.SS_SHIPPED.' and order_status = '.OS_CONFIRMED;
                break;
                case 5: //已完成
                    $where[] = 'shipping_status = '.SS_RECEIVED . ' and order_status = '.OS_CONFIRMED;
                break;
				case 6:  //在线支付
					$where[] = "pay_id > 1 and pay_status = " . PS_PAYED;
				default:  //全部
					
				break;
			}
			
		}
		$where[] = "site_id = '$site_id'";
		$where[] = "user_id = '$user_id'";
		
		//$where[] = "(pay_status != '".PS_UNPAYED."' OR pay_id = 1)";  //不显示未付款
		
        $OrderInfoCenter = D('OrderInfoCenter');  //会员中心的订单表
		$field = 'order_id,order_sn,order_status,shipping_status,postscript,integral_money as integral,pay_status,pay_id,pay_name,money_paid,order_amount';
		$order = 'add_time desc';
        $data = $OrderInfoCenter->getPage($field,(!empty($where)?implode(' and ',$where):''), $order, $page, $pageSize, true);
        
		$this->success($data);
	}
	
	/*
	*	订单列表 - 自己的订单列表 - 会员中心
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function info(){
        $user_id = $this->getUserId();
        $order_id = I('request.order_id',0,'intval');
		$order_sn = I('request.order_sn','','trim');
		$site_id = C('SITE_ID');  //当前站点ID
		
		$where = array();
		if($order_id > 0){
			$where[] = "order_id = " . $order_id;
		}
		if($order_sn > 0){
			$where[] = "order_sn = " . $order_sn;
		}
		if(empty($where)){
			$this->error('订单不存在！');
		}
		$where[] = "site_id = '$site_id'";
		$where[] = "user_id = '$user_id'";
		
        $OrderInfoCenter = D('OrderInfoCenter');  //会员中心的订单表
		$field = 'order_id,site_id,order_sn,order_status,shipping_status,postscript,pay_status,integral_money as integral,pay_id,pay_name,add_time,pay_time,goods_amount,bonus,shipping_fee,discount,money_paid,order_amount';
		$order = 'add_time desc';
        $data = $OrderInfoCenter->field($field)->where(implode(' and ',$where))->find();
		$data = $OrderInfoCenter->orderFormat($data, true, true, true);
        
		$this->success($data);
	}
	
	/*
	*	检查当前是否登录
	*	@Author 9009123 (Lemonice)
	*	@return int [user_id]
	*/
	private function checkLogin(){
		$user_id = $this->getUserId();  //用户ID
		if($user_id <= 0){
			$this->error($this->not_login_msg);  //没登录
		}
		return $user_id;
	}
	
	/*
	*	获取当前登录用户ID
	*	@Author 9009123 (Lemonice)
	*	@return int [user_id]
	*/
	private function getUserId(){
		$user_id = $this->dbModel->getUser('user_id');  //用户ID
		$user_id = $user_id ? $user_id : 0;
		return $user_id;
	}
	
	/*
	*	暂时不使用本控制器默认方法，预留
	*	@Author 9009123 (Lemonice)
	*	@return exit & 404[not found]
	*/
	public function index(){
		send_http_status(404);
	}
}