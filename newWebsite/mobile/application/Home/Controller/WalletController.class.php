<?php
/**
 * ====================================
 * 电子钱包 控制器
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-10-27 10:14
 * ====================================
 * File: WalletController.class.php
 * ====================================
 */

namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Wallet;

class WalletController extends InitController {
	private $not_login_msg = '您还未登录，请先登录';  //当前没登录的提示信息
	
	//不用登录的方法名称
	private $not_login = array(
		//'save','info','defaults'
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
	*	获取电子钱包的余额与账单列表
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
    public function getInfo(){
		$Wallet = new Wallet();
		$user_id = 10000001001;
		$result = $Wallet->getBillInfo($user_id);
		$error = $Wallet->getError();
		if(!is_null($error)){  //出错了
			echo $error;
			exit;
		}
		
		$array = isset($result['journalList']) ? $result['journalList'] : array();
		$list = array();
		//处理分页和时间段等条件
		if(!empty($array)){
			$tmp_list = array(0=>array(),1=>array());
			//算出最近3个月收支的最晚时间日期
			$three_month_ago = strtotime("-3 month");
			//$three_month_ago = strtotime("2016-09-14");
			
			foreach($array as $key=>$value){
				$dealDate_time = strtotime($value['dealDate']);
				if($dealDate_time >= $three_month_ago){
					$tmp_list[0][] = $value;
				}else{
					$tmp_list[1][] = $value;
				}
			}
			$tmp_list[0] = arraySort($tmp_list[0],'dealDate');  //按日期排序 - 倒序
			$tmp_list[1] = arraySort($tmp_list[1],'dealDate');  //按日期排序 - 倒序
			$list = $tmp_list;
		}
		
		
		$bill_info = array(
			'balance'=>(isset($result['balance']) ? $result['balance'] : 0),
			'list'=>$list,
		);
		
    	$this->success($bill_info);
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
}