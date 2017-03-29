<?php
/**
 * ====================================
 * 积分 控制器
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-02-07 15:31
 * ====================================
 * File: IntegralController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Order\Order;
use Common\Extend\Time;
use Common\Extend\PhxCrypt;

class IntegralController extends InitController{
	private $not_login_msg = '您还未登录，请先登录';  //当前没登录的提示信息
	
	private $dbModel = NULL;  //储存地址数据表对象
	
	protected $user_id = 0;  //当前登录的ID
	
	private $not_login_action = array();  //不需要登录的方法
	
	public function __construct(){
		parent::__construct();
		$this->dbModel = D('IntegralCenter');
		if(isset($this->not_login_action) && !in_array(ACTION_NAME, $this->not_login_action)){
			$this->user_id = $this->checkLogin();  //检查登录，获取用户ID
		}
	}
	
	/*
	*	积分商城商品列表
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function goodsList(){
		$user_id = $this->user_id;
		$page = I('request.page',1,'intval');
		$pageSize = I('request.pageSize',8,'intval');
		
        $data = $this->dbModel->getPage($user_id, $page, $pageSize);
		$this->success($data);
	}
	
	/*
	*	积分商城商品详情
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function goodsInfo(){
		$user_id = $this->user_id;
		$exchange_id = I('request.exchange_id',0,'intval');
		if($exchange_id <= 0){
			$this->error('出错了');
		}
        $data = $this->dbModel->getInfo($exchange_id, $user_id);
		
		//偏远地区+15元
		if(isset($data['shipping_fee_remote']) && $data['shipping_fee_remote'] > 0){
			//检查收货地址是否有设置
			$consignee = D('UserAddress')->getDefaultAddress($user_id);
			$OrderObject = new Order();
			$data['shipping_fee'] = $OrderObject->getShippingFee($consignee, 0, $data['shipping_fee'], false);
		}
		
		$this->success($data);
	}
	
	/*
	*	积分日记列表
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function logList(){
        $user_id = $this->user_id;
		$page = I('request.page',1,'intval');
		$pageSize = I('request.pageSize',8,'intval');
		
		$where = array();
		$where[] = "user_id = '$user_id'";
		
        $UserPointLog = D('UserPointLog');  //会员中心的订单表
		$field = 'order_sn,state,points,remark,add_time,point_type';
		$order = 'add_time desc';
        $data = $UserPointLog->getPage($field,(!empty($where)?implode(' and ',$where):''), $order, $page, $pageSize);
        
		$this->success($data);
	}
	
	/*
	*	积分兑换列表 - 【我的兑换】
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function exchangeList(){
        $user_id = $this->user_id;
		$page = I('request.page',1,'intval');
		$pageSize = I('request.pageSize',8,'intval');
		
		$site_id = C('SITE_ID');
		
		$where = array();
		$where[] = "user_id = '$user_id'";
		$where[] = "site_id = '$site_id'";
		
        $UserPointExchangeModel = D('UserPointExchangeCenter');
		$field = 'order_id,order_sn,data,addtime,goods_number';
		$order = 'addtime desc';
        $data = $UserPointExchangeModel->getPage($field,(!empty($where)?implode(' and ',$where):''), $order, $page, $pageSize);
        
		$this->success($data);
	}
	
	/*
	*	积分兑换 - 购买下单
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function createOrder(){
		$user_id = $this->user_id;
		$exchange_id = I('request.exchange_id',0,'intval');
		$address_id = I('request.address_id',0,'intval');
		$payment_id = I('request.payment_id',0,'intval');  //1是货到付款（不支持）
		$remark = I('request.remark',0,'intval');

		if($exchange_id <= 0){
			$this->error('此商品不存在');
		}
		if($payment_id <= 1){
			$this->error('选择的支付方式不存在');
		}
		
		$is_wechat = isCheckWechat();
		if($is_wechat == true && $payment_id == 4){  //微信不支持支付宝
			$this->error('请选择支付方式。');
		}
		
		$OrderInfoModel = D('OrderInfo');
		$UserPointExchangeModel = D('UserPointExchangeCenter');
		$site_id = C('SITE_ID');
		$real_ip = get_client_ip();
		
		//检查同IP是不是频繁下单
		$this->checkFrequently();
		
		$data = $this->dbModel->getInfo($exchange_id, $user_id, true);
		if(empty($data)){
			$this->error('此商品不存在或者积分不足于兑换，或者已经下线！');
		}
		
		//检查购买数量
		if($data['per_number'] > 0){			
			$this->checkNumber($data);
		}
		
		$data['extension_code'] = $data['goods_type'] == 'package_goods' ? 'package_buy' : '';
		
		$OrderObject = new Order();
		
		//检查收货地址是否有设置
		$consignee = $OrderObject->getRealUserAddress($address_id);
		
		/* 检查收货人信息是否完整 */
		if (!$consignee){
			$this->error('请填写收货地址');
		}
		
		//计算邮费，是否满200包邮（偏远地区15元），不满200基础邮费20（偏远地区35）
		if(isset($data['shipping_fee_remote']) && $data['shipping_fee_remote'] > 0){
			$data['shipping_fee'] = $OrderObject->getShippingFee($consignee, $data['price'], $data['shipping_fee'], false);
		}
		
		$order = array(
			'order_amount'    => $data['shipping_fee'] + $data['price'],  //应付金额
			'shipping_fee'    => $data['shipping_fee'],  //邮费，0=包邮
			'shipping_id'     => 15,  //intval($_POST['shipping']),指定配送方式,为EMS edit by lxm
			'shipping_type'   => 1,
			'pay_id'          => $payment_id,  //支付平台，1=货到付款（不支持）
			'pay_fee'         => 0,  //支付平台费用
			'payment_discount'=> 0,  //支付平台费用
			'pack_id'         => 0,  //包装
			'card_id'         => 0,  //卡片、贺卡
			'card_message'    => '',  //卡片文字
			'surplus'         => 0.00,  //余额
			'integral'        => $data['point'],  //使用的积分
			'integral_money'  => $data['point'],  //积分与抵消的金额一致，固定一比一的比例
			//'integral_money'  => $data['shop_price'] - $data['price'],  //使用积分抵消的金额
			'bonus'           => 0,
			'need_inv'        => 0,
			'inv_type'        => '',
			'inv_payee'       => '',
			'inv_content'     => '',
			'postscript'      => htmlspecialchars($remark),  //订单备注
			'how_oos'         => '',
			'need_insure'     => 0,  //保险
			'user_id'         => $user_id,  //用户
			'add_time'        => Time::gmtime(),  //下单时间
			'order_status'    => OS_UNCONFIRMED,  //订单状态
			'shipping_status' => SS_UNSHIPPED,  //物流状态
			'pay_status'      => PS_UNPAYED,  //支付状态
			'agency_id'       => 0,  //收货地址所在的办事处ID
			'ip_address' 	  => $real_ip,  //客户端IP地址
			'goods_amount'    => $data['price'],  //商品总金额
			'discount'        => 0,  //加上会员折扣
			'tax'             => 0,  //税收
			'parent_id'       => 0,
			'divide_region'   => '广州地区手机商城下单',
			'kefu'            => '手机商城下单',
			'user_id'         => $this->user_id,  //会员ID
			'bonus_id'        => 0,  //红包ID
			'order_sn'        => $OrderObject->getOrderSn(),  //生成订单号
			'site_id'         => $site_id,
			
			'extension_code'  => GAT_INTEGRAL_BUY,
			'extension_id'    => $$exchange_id,
		);
		
		//处理pay_id/pay_name等支付详情
		$order = $this->getPayInfo($order);
		$code = $order['code'];
		
		//获取订单的来源地址
		$order = $this->getSourceUrl($order);
		
		/* 收货人信息 */
		if(!empty($consignee)){
			foreach ($consignee as $key => $value){
				$order[$key] = addslashes($value);
			}
			//$order['mobile'] = PhxCrypt::phxEncrypt($order['mobile']);
			$order['mobile'] = isset($order['encode_mobile']) ? $order['encode_mobile'] : PhxCrypt::phxEncrypt($order['mobile']);
		}
		
		/* 如果不用支付任何金额，修改订单状态为已确认、已付款 */
		if ($order['order_amount'] <= 0){
			$order['order_status'] = OS_CONFIRMED;
			$order['confirm_time'] = Time::gmtime();
			$order['pay_status']   = PS_PAYED;
			$order['pay_time']     = Time::gmtime();
			$order['order_amount'] = 0;
		}
		
		//插入到自身站点的订单表
		$order['order_id'] = $OrderInfoModel->insert($order);
		//插入到会员中心的订单表
		$insert_id = D('OrderInfoCenter')->insert($order);
		
		$order_goods_data = array(
			'order_id'=>$order['order_id'],
			'order_sn'=>$order['order_sn'],
			'goods_id'=>$data['goods_id'],
			'goods_name'=>($data['goods_name'] ? $data['goods_name'] : ''),
			'goods_sn'=>($data['goods_sn'] ? $data['goods_sn'] : ''),
			'goods_number'=>1,  //($data['goods_number'] ? $data['goods_number'] : 1)
			'market_price'=>($data['market_price'] ? $data['market_price'] : 0),
			'goods_price'=>($data['shop_price'] ? $data['shop_price'] : 0),
			'goods_attr'=>($data['goods_attr'] ? $data['goods_attr'] : ''),
			'is_real'=>($data['is_real'] ? $data['is_real'] : 1),
			'extension_code'=>($data['extension_code'] ? $data['extension_code'] : ''),
			'parent_id'=>($data['parent_id'] ? $data['parent_id'] : 0),
			'is_gift'=>0,
			'site_id'=>$order['site_id'],
		);
		$OrderGoodsModel = D('OrderGoods');
		$OrderGoodsCenterModel = D('OrderGoodsCenter');
		$OrderGoodsModel->insert($order_goods_data);  //插入商品数据到当前站点数据库
		$OrderGoodsCenterModel->insert($order_goods_data);  //插入商品数据到ucenter数据库
		
		//检查如果是套装，则回去套装商品加入到订单商品
		if($order_goods_data['extension_code'] == 'package_buy'){  //是套装
			if(isset($data['package_goods']) && !empty($data['package_goods'])){
				foreach($data['package_goods'] as $key=>$children){
					if($children['goods_name']){
						$package_goods = array(
							'order_id'=>$order_goods_data['order_id'],
							'order_sn'=>$order_goods_data['order_sn'],
							'goods_id'=>$children['goods_id'],
							'goods_name'=>$children['goods_name'],
							'goods_sn'=>$children['goods_sn'],
							'goods_number'=>$children['goods_number'],
							'market_price'=>$children['market_price'],
							'goods_price'=>$children['shop_price'],
							//'goods_attr'=>'',
							//'is_real'=>$children['is_real'],
							'extension_code'=>'package_goods',
							'parent_id'=>($data['package_id'] ? $data['package_id'] : 0),
							//'is_gift'=>$row['is_gift'],
							'site_id'=>$order_goods_data['site_id'],
						);
						$OrderGoodsModel->insert($package_goods);  //插入商品数据到当前站点数据库
						$OrderGoodsCenterModel->insert($package_goods);  //插入商品数据到ucenter数据库
					}
				}
			}
		}
		
		//记录到兑换记录表
		$UserPointExchangeModel->create(array(
			'site_id'=>$site_id,
			'user_id'=>$user_id,
			'goods_id'=>$data['goods_id'],
			'goods_name'=>$data['goods_name'],
			'goods_number'=>1,
			'points'=>$data['point'],
			'price'=>$data['price'],
			'order_id'=>$order['order_id'],
			'order_sn'=>$order['order_sn'],
			'client_ip'=>$real_ip,
			'data'=>serialize($data),
			'addtime'=>Time::gmtime(),
		));
		$UserPointExchangeModel->add();
		//库存减1
		$this->dbModel->where("exchange_id = '$exchange_id'")->setDec('max_number');
		//冻结积分
		$UserPointFreezeModel = D('UserPointFreeze');
		$UserPointFreezeModel->create(array(
			'site_id'=>$site_id,
			'user_id'=>$user_id,
			'order_sn'=>$order['order_sn'],
			'mobile'=>$order['mobile'],
			'type'=>1,  //类型：0：订单，1.换购
			'integral'=>'-'.$order['integral'],
			'create_time'=>Time::gmtime(),
		));
		$result = $UserPointFreezeModel->add();
		if(!$result){
			$OrderInfoModel->where("order_id = '$order[order_id]'")->delete();
			$OrderGoodsCenterModel->where("order_id = '$order[order_id]'")->delete();
			D('PayInfo')->where("order_sn = '$order[order_sn]'")->delete();
			$OrderGoodsModel->where("order_id = '$order[order_id]'")->delete();
			$$OrderGoodsCenterModel->where("order_id = '$order[order_id]'")->delete();
			$this->error('兑换失败，请重试或者联系客服。');
		}else{
			D('Users')->setUserInfo($user_id);  //刷新登录的积分缓存
		}
		
		
		/* 取得支付信息，生成支付代码 */
		//$order['order_amount'] = 0.01;  //测试  Lemonice
		if ($order['order_amount'] > 0){
			$pay = $OrderObject->getPaymentClass($code);  //实例化网银类
			$payment = D('Payment')->getPayment(strtolower($code));
			$payment = $OrderObject->unserialize_config($payment['pay_config']);
			$order["content"] = $pay->getCode($order,$payment);
			
			if(!$order["content"]){
				$this->error('您当前的站点不支持使用该支付方式，请重新选择！');
			}
			
			//将支付信息写入支付对账数据库
			D('PayInfo')->insert(array(
				'site_id'=>$order['site_id'],
				'pay_id'=>$order['pay_id'],
				'name'=>($order["consignee"] ? $order["consignee"] : ''),
				'order_sn'=>$order["order_sn"],
				'order_amount'=>$order["order_amount"],
				'source'=>2,
				'add_time'=>Time::gmtime(),
			));
		}
		
		//记录当前所下的订单，用于在线支付时读取订单信息
		session('pay_online_order_sn', $order['order_sn']);
		
		$data = array(
			'order_sn'=>$order['order_sn'],
			'amount'=>$order['order_amount'],
			'shipping_fee'=>$order['shipping_fee'],
			'payment_id'=>(isset($order['bank_id']) ? $order['bank_id'] : $order['pay_id']),
			'payment_name'=>$order['pay_name'],
			'remark'=>$order['postscript'],
			'content'=>$order['content'],
		);
		
		$this->success($data);
	}
	
	/*
	*	处理支付信息
	*	@Author 9009123 (Lemonice)
	*	@param array $order 订单详情
	*	@return array
	*/
	private function getPayInfo($order = array()){
		switch($order['pay_id']){
			case 4:  //支付宝
				$order['code'] = 'alipay';
				$order['pay_name'] = '支付宝';
			break;
			case 6:  //钱包支付
				$order['code'] = 'chinaskinpay';
				$order['pay_name'] = '钱包支付';
				break;
			case 7:  //财付通
				$order['code'] = 'tenpay';
				$order['pay_name'] = '财付通';
			break;
			case 8:  //快钱
				$order['code'] = 'kuaiqian';
				$order['pay_name'] = '快钱支付';
			break;
			case 18:  //微信支付
//					if(C('SITE_ID') != 14){  //如果不是Q站，不给微信支付，目前只支持Q站
//						$this->error('当前站点不支持微信支付，请选择其他支付方式');
//					}
				$order['code'] = 'wechatpay';
				$order['pay_name'] = '微信支付';
			break;
			default:  //网银
				$order['code'] = 'tenpay';
				$order['pay_name'] = '网银支付';
				$order["bank_id"] = $order['pay_id'];  //网银识别
				$order['pay_id'] = 7;  //  > 10 是网银，强制使用财付通
			break;
		}
		return $order;
	}
	
	/*
	*	检查是否频繁下单
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
	private function checkFrequently(){
		$real_ip = get_client_ip();
		$limit_time  = Time::gmtime() - 600;
		$count = D('OrderInfo')->where("add_time > '".$limit_time."' AND ip_address='".$real_ip."'")->count();
		if ($count > 3){
			$this->error('你刚下完单了不能重复下单，如有疑问请联系在线客服！');
		}
	}
	
	/*
	*	检查某个商品当前用户兑换次数
	*	@Author 9009123 (Lemonice)
	*	@param array $data 商品详情
	*	@return array
	*/
	private function checkNumber($data = array()){
		$user_id = $this->user_id;
		$site_id = C('SITE_ID');
		$count = 0;
		$OrderInfoModel = D('OrderInfo');
		$UserPointExchangeModel = D('UserPointExchangeCenter');
		$order_id_array = $UserPointExchangeModel->field('order_id')->where("site_id = '$site_id' AND user_id = '$user_id' AND goods_id = '$data[goods_id]'")->select();
		
		if(!empty($order_id_array)){
			$order_ids = array();
			foreach($order_id_array as $oid){
				$order_ids[] = $oid['order_id'];
			}
			//查看对应的订单是否【未确认】【已确认】
			$count = $OrderInfoModel->where("order_id IN(".implode(',',$order_ids).") AND (order_status = '".OS_UNCONFIRMED."' OR order_status = '".OS_CONFIRMED."')")->count();
		}
		
		if($count >= $data['per_number']){
			$this->error('每人限量兑换最高'.$data['per_number'].'个，您当前已经达到限制，请未支付的订单尽快支付。');
		}
		
		return $count;
	}
	
	/*
	*	获取订单的来源地址
	*	@Author 9009123 (Lemonice)
	*	@param array $order 订单详情
	*	@return array
	*/
	private function getSourceUrl($order = array()){
		$source_url = I('source_url','','trim');
		$cookie_source_url = cookie('source_url');
		$order['ip_info_text'] = $cookie_source_url!='' ? $cookie_source_url : ($source_url!='' ? $source_url : '');
        if(!empty($order['ip_info_text'])){
            $weixin = array();
            preg_match('/campaign=(\w*)_kefugw/',$order['ip_info_text'],$weixin);
            if(!empty($weixin)){
                $order['weixin'] = $weixin[1];
            }
        }
		//判断是否微信打开的，如果是则增加openid到来源地址
		$is_wechat = isCheckWechat();
		if($is_wechat == true){  //微信打开网页
			$openid = session('sopenid');
			if(strstr($order['ip_info_text'],'?')){
				$order['ip_info_text'] = $order['ip_info_text'] . '&openid='.$openid;
			}else{
				$order['ip_info_text'] = $order['ip_info_text'] . '?openid='.$openid;
			}
		}
		
		return $order;
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
}