<?php
/**
 * ====================================
 * 在线支付 控制器
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-06-29 15:31
 * ====================================
 * File: OnlinePaymentController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Time;
use Common\Extend\Order\Order;
use Common\Extend\Order\Bonus;
use Common\Extend\PhxCrypt;
use Common\Extend\Order\Member;
use Common\Extend\Order\Integral;
use Common\Extend\Send;

class OnlinePaymentController extends InitController{
	private $not_login_msg = '您还未登录，请先登录';  //当前没登录的提示信息
	
	private $sessionId = NULL;                  //session ID
	
	private $dbModel = NULL;  //储存地址数据表对象
	//private $user_id = 0;  //储存用户ID
	
	
	private $OrderObject = NULL;  //储存Order类的实例化
	
	public function __construct(){
		parent::__construct();
		$this->dbModel = D('Cart');
		$this->user_id = $this->getUserId();  //获取用户ID
		
		$this->OrderObject = new Order();
		$this->sessionId = session_id();  //获取当前session ID
		
		if(isset($_REQUEST['address_id']) && intval($_REQUEST['address_id']) < 0){
			$this->setRequest('address_id',0);
		}
	}
	
	/*
	*	设置某个参数的I函数可获取
	*	@Author 9009123 (Lemonice)
	*	@param  string  $name  字段名称、参数名称
	*	@param  string  $value  值
	*	@return nothing
	*/
	private function setRequest($name = '', $value = ''){
		$_REQUEST[$name] = $value;
		$_POST[$name] = $value;
		$_GET[$name] = $value;
	}
	
	/*
	*	获取订单应付金额 - 文章页面支付
	*	@Author 9009123 (Lemonice)
	*	@return [Go To aggregate]
	*/
	public function quickAggregate(){
		$user_id = $this->user_id;
		//设置地址
		$address['consignee'] = I('request.consignee','','trim');  //姓名
		$address['mobile'] = I('request.mobile','','trim');  //手机号码
		$address['province'] = I('request.province',0,'intval');  //省份
		$address['city'] = I('request.city',0,'intval');  //城市
		$address['district'] = I('request.district',0,'intval');  //区域
		$address['town'] = I('request.town',0,'intval');  //街道
		$address['address'] = I('request.address','','trim');  //详细地址
		$address['attribute'] = I('request.attribute','','trim');  //属性
		
		if($address['consignee'] != '' && 
			$address['mobile'] != ''
			&& $address['province'] > 0
			&& $address['city'] > 0
			&& $address['district'] > 0
			&& $address['address'] != ''
			&& $address['attribute'] != ''
		){
			$result = D('UserAddress')->saveAddress($address, $user_id);  //保存地址
			if(!is_array($result)){
				$this->error($result);
			}
			$this->setRequest('address_id',(isset($result['address_id']) ? intval($result['address_id']) : 0));  //设置提交了的地址ID
		}else{  //没提交地址、没选择地址，注销保存的旧地址
			session('new_consignee',null);
			session('default_address_id',null);
		}
		
		//检查优惠券号码
		$bonus_sn = I('request.bonus_sn','','trim');
		if($bonus_sn != ''){
			$bonus_info = $this->checkBonus(true);
			if(!is_array($bonus_info)){
				$this->error($bonus_info);
			}
			$this->setRequest('bonus_type',$bonus_info['type_id']);  //设置提交了的优惠券类型
		}
		
		//new2.3g.chinaskin.cn/Home/OnlinePayment/quickAggregate.json?
		//bonus_sn=1407281547&payment_id=4&consignee=程赐明&mobile=13711458538&province=6&city=76&district=693&town=29033&address=测试的地址&attribute=公司
		
		$total = $this->aggregate(true,$address['province']);  //获取总金额
		
		$this->success($total);
	}
	
	/*
	*	提交订单 - 文章页面支付
	*	@Author 9009123 (Lemonice)
	*	@return [Go To createOrder]
	*/
	public function quickOrder(){
		//new2.3g.chinaskin.cn/Home/OnlinePayment/quickOrder.json?
		//token=21e733e12d53890ae0a849ec1bb4b36c&remark=订单备注
		$code = I('code','','trim');
		//$consignee = $this->OrderObject->getRealUserAddress();
		//$mobile = isset($consignee['mobile'])&&$consignee['mobile']!='' ? $consignee['mobile'] : '';
		//if (!Send::checkMobileCode($code,0,$mobile)) {
		if (!Send::checkMobileCode($code)) {
			$this->error('验证码不正确');
		}
		
		$this->createOrder();  //调用创建订单
	}
	
	
	/*
	*	获取购物车勾选的商品 -  - 确认订单页面
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function getGoodsList(){
		//获取购物车商品
		$cart_goods = $this->getCartGoods();
		$this->OrderObject->statistics($cart_goods);
		$list = array();
		if(!empty($cart_goods)){
			foreach($cart_goods as $value){
				$list[] = array(
					'rec_id'=>$value['rec_id'],
					'is_gift'=>$value['is_gift'],
					'goods_id'=>$value['goods_id'],
					'goods_price'=>$value['goods_price'],
					'goods_number'=>$value['goods_number'],
					'goods_sn'=>$value['goods_sn'],
					'goods_name'=>$value['goods_name'],
					'market_price'=>$value['market_price'],
					'amount'=>$value['amount'],
					'goods_thumb'=>C('domain_source.img_domain').$value['goods_thumb'],
					'goods_img'=>C('domain_source.img_domain').$value['goods_img'],
					'original_img'=>C('domain_source.img_domain').$value['original_img'],
					'discount'=>$value['discount'],
					'formated_discount'=>$value['formated_discount'],
					'formated_market_price'=>$value['formated_market_price'],
					'formated_goods_price'=>$value['formated_goods_price'],
					'formated_amount'=>$value['formated_amount'],
				);
			}
		}
		$this->success($list);
	}
	
	/*
	*	获取当前购物车可用与不可用的优惠券 - 确认订单页面
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function getBonusList(){		
		//$this->checkLogin();  //检查是否登录
		
		//$page = I('request.page',1,'intval');
        //$pageSize = I('request.pageSize',0,'intval');
		$page = 1;  //不分页了
        $pageSize = 0;  //不分页了
		
		//获取购物车商品
		$cart_goods = $this->getCartGoods();
		$this->OrderObject->statistics($cart_goods);
		
		$BonusObject = new Bonus();
		if($page <= 1){
			$list = $BonusObject->onlinePayment($cart_goods,$page,$pageSize);  //获取当前会员可用的购物车优惠券、以及不可用的优惠券
		}else{
			$list = array();  //不分页了，第二页开始为空
		}
		
		$result = array(
			'page'=>1,
			'pageSize'=>$pageSize,
			'total'=>count($list),
			'pageTotal'=>1,
			'page'=>1,
			'list'=>$list,
		);
		
		if(isset($result['list']) && !empty($result['list'])){
			$list = array();
			foreach($result['list'] as $bonus){
				$list[] = array(
					'type_id'=>$bonus['type_id'],
					'type_name'=>$bonus['type_name'],
					'type_money'=>$bonus['type_money'],
					'count'=>$bonus['count'],
					'is_payonline_discount'=>$bonus['is_payonline_discount'],
				);
			}
			$result['list'] = $list;
		}else{
			$result['page'] = 1;
			$result['pageSize'] = $pageSize;
			$result['total'] = 0;
			$result['pageTotal'] = 1;
			$result['page'] = 1;
			$result['page'] = 1;
			$result['list'] = array();
		}
		$this->success($result);
	}
	
	/*
	*	获取当前购物车可用与不可用的优惠券 - 确认订单页面
	*	@Author 9009123 (Lemonice)
	*	@param  true or false  $is_return  是否返回结果，否则终端输出
	*	@return exit & Json
	*/
	public function checkBonus($is_return = false){
		$bonus_sn = I('request.bonus_sn','','trim');
		if($bonus_sn == ''){
			if($is_return === false){
				$this->error('请输入优惠券编码！');
			}else{
				return '请输入优惠券编码！';
			}
		}
		
		//获取购物车商品
		$cart_goods = $this->getCartGoods();
		$this->OrderObject->statistics($cart_goods);
		
		$BonusObject = new Bonus();
		
		//获取购物车相关的统计信息
		$cart_statistics = session('cart_statistics');
		
		$total_price = $cart_statistics['total_price'];
		$is_gift = $cart_statistics['have_gift'];   //购物车商品中是否有赠品
		$is_package = $cart_statistics['have_package'];  //购物车商品中是否有套装
		
		$pay_id = session('payment_data.payment_id');
		$addition = array(
			'user_id'=>$this->user_id,
			'cart_amount'=>$total_price,
			'site_id'=>C('SITE_ID'),
			'is_package'=>$is_package,
			'check_bonus'=>$is_gift,   //是否包含其他优惠劵活动
			'is_member_discount'=>($this->user_id > 0 ? 1 : 0), //是否享受了会员折扣优惠
			'is_payonline_discount'=>(ONLINE_PAYMENT_DISCOUNT > 0 ? ($pay_id != 1 ? 1 : 0) : 0),  //是否享受在线优惠
			'cart_category_info' =>D('Category')->getCartCategory($cart_goods) //购物车中商品分类信息
		);
		$bonus = D('UserBonusCenter')->bonusInfo(0, $bonus_sn);
		
		if(!$bonus || empty($bonus)){
			if($is_return === false){
				$this->error('此优惠券不存在！');
			}else{
				return '此优惠券不存在！';
			}
		}
		
		$result = $BonusObject->checkBonus($bonus,$addition);  //校验优惠券
		
		if(is_array($result)){  //如果返回了数组，则是校验通过
			//查看是否实物券
			if(isset($result['coupon_type']) && $result['coupon_type'] == 2 && !empty($result['front_actid'])){
                if(is_array($result['front_actid']) && !empty($result['front_actid'])){  //如果是实物券，并且有绑定活动，则应该
                    $gift_bonus = $BonusObject->getGiftBonus($result['front_actid']);
					$CartModel = D('Cart');
                    $gift_act_id = array();  //实物券活动id
                    foreach($gift_bonus as $gb){
						$CartModel->addTempGift(array(  //添加商品到购物车
							'goods_id'=>$gb['id'],
							'goods_price'=>$gb['price'],
							'goods_number'=>$gb['num'],
							'extension_code'=>($gb['type'] == 'gift' ? '' : 'package_buy'),
							'is_gift'=>$gb['act_id']
						));
                        $gift_act_id[] = $gb['act_id'];
                    }
					//记录这些商品，如果有取消优惠券功能，则需要删除购物车对应的商品
					//session('gift_bonus',array_unique($gift_act_id));
                }
            }
			
			session('use_bonus_id.' . $bonus['type_id'], $result['bonus_id']);  //把优惠券ID记录到session
			
			//优惠券详情
			$data = array(
				'bonus_id'=>$result['bonus_id'],
				'type_id'=>$bonus['type_id'],
				'type_name'=>$bonus['type_name'],
				'type_money'=>$bonus['type_money'],
				'coupon_type'=>$bonus['coupon_type'],
				'is_payonline_discount'=>$bonus['is_payonline_discount'],
				'count'=>1,
			);
			
			if($is_return === false){
				$this->success($data);
			}else{
				return $data;
			}
		}else{
			if($is_return === false){
				$this->error($result);
			}else{
				return $result;
			}
		}
	}
	
	/*
	*	获取订单总额+运费等优惠信息，总计、合计 - 确认订单页面
	*	@Author 9009123 (Lemonice)
	*	@param  true or false  $is_return  是否返回结果
	*	@param  int $province 省份，计算邮费用的
	*	@return exit & Json
	*/
	public function aggregate($is_return = false,$province = 0){
		//获取购物车商品
		$cart_goods = $this->getCartGoods();
		
		$bonus_type = I('request.bonus_type',0,'intval');  //红包类型
		$payment_id = I('request.payment_id',0,'intval');  //选择了什么支付
		$address_id = I('request.address_id',0,'intval');  //地址ID，等于0的话就是新添加的地址，从session获取
		$integral = I('request.integral',0,'intval');  //积分
		
		/*$is_wechat = isCheckWechat();
		if($is_wechat == true && $payment_id == 4){  //微信不支持支付宝
			$this->error('请选择支付方式。');
		}*/
		
		//$bonus_id = session('use_bonus_id.' . $bonus_type);
		//$bonus_id = $bonus_id ? $bonus_id : 0;
		
		$total = $this->OrderObject->getOrderFree($cart_goods, $address_id, $province, $payment_id, $bonus_type, $integral);
		$total['token'] = $this->token();  //获取token，提交订单时候用的，避免重复点击
		
		//保存这些数据到session
		session('payment_data',array(
			'bonus_type'=>($bonus_type > 0 ? $bonus_type : 0),  //有优惠券才保存
			'payment_id'=>$payment_id,
			'address_id'=>$address_id,
			'integral'=>$integral,
		));
		
		if($is_return === false){
			$this->success($total);
		}else{
			return $total;
		}
	}
		
	
	/*
	*	提交订单 - 创建订单 -  - 确认订单页面
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function createOrder(){
		$remark = I('request.remark','','trim');  //订单备注
		
		//检查同IP是不是频繁下单
		$real_ip = get_client_ip();
		$limit_time  = Time::gmtime() - 600;
		$count = D('OrderInfo')->where("add_time > '".$limit_time."' AND ip_address='".$real_ip."'")->count();
		if ($count > 3){
			$this->error('你刚下完单了不能重复下单，如有疑问请联系在线客服！');
		}
		
		//获取支付信息
		$payment_data = session('payment_data');
		if(!$payment_data || !isset($payment_data['bonus_type']) || !isset($payment_data['payment_id']) || !isset($payment_data['address_id']) || !isset($payment_data['integral'])){
			$this->error('请您选择支付方式 和 收货地址！');
		}
		
		$address_id = $payment_data['address_id'] ? intval($payment_data['address_id']) : 0;
		$payment_id = $payment_data['payment_id'] ? intval($payment_data['payment_id']) : 0;  //1是货到付款
		$bonus_type = $payment_data['bonus_type'] ? intval($payment_data['bonus_type']) : 0;
		$integral = $payment_data['integral'] ? intval($payment_data['integral']) : 0;
		
		$is_wechat = isCheckWechat();
		if($is_wechat == true && $payment_id == 4){  //微信不支持支付宝
			$this->error('请选择支付方式。');
		}
		
		//检查收货地址是否有设置
		$consignee = $this->OrderObject->getRealUserAddress($address_id);
		/* 检查收货人信息是否完整 */
		if (!$this->OrderObject->checkConsigneeInfo($consignee)){
			$this->error('请填写收货地址');
		}
		
		//校验token，避免重复提交
		$token = I('request.token','','trim');
		if($token == ''){
			$this->error('页面已过期，请刷新后重试！');
		}
		$this->token($token);  //校验token，如果不正确，会直接提示错误
		
		
		//获取购物车商品
		$cart_goods = $this->getCartGoods();
		
		//$bonus_id = session('use_bonus_id.' . $bonus_type);
		//$bonus_id = $bonus_id ? $bonus_id : 0;
		$total = $this->OrderObject->getOrderFree($cart_goods, $address_id, 0, $payment_id, $bonus_type, $integral);
		
		$order = array(
			'order_amount'    => $total['amount'],  //应付金额
			'shipping_fee'    => $total['shipping_fee'],  //邮费，0=包邮
			'shipping_id'     => 15,  //intval($_POST['shipping']),指定配送方式,为EMS edit by lxm
			'shipping_type'   => 1,
			'pay_id'          => $payment_id,  //支付平台，1=货到付款
			'pay_fee'         => $total['pay_fee'],  //支付平台费用
			'payment_discount'=> $total['pay_fee_discount'],  //支付平台费用
			'pack_id'         => 0,  //包装
			'card_id'         => 0,  //卡片、贺卡
			'card_message'    => '',  //卡片文字
			'surplus'         => 0.00,  //余额
			'integral'        => $integral,  //使用的积分
			'integral_money'  => $total['integral_money'],  //使用积分抵消的金额
			'bonus'           => isset($total['bonus']) ? intval($total['bonus']) : 0,  //优惠券金额
			'need_inv'        => 0,
			'inv_type'        => '',
			'inv_payee'       => '',
			'inv_content'     => '',
			'postscript'      => htmlspecialchars($remark),  //订单备注
			'how_oos'         => '',
			'need_insure'     => 0,  //保险
			'user_id'         => $this->user_id,  //用户
			'add_time'        => Time::gmtime(),  //下单时间
			'order_status'    => OS_UNCONFIRMED,  //订单状态
			'shipping_status' => SS_UNSHIPPED,  //物流状态
			'pay_status'      => PS_UNPAYED,  //支付状态
			'agency_id'       => 0,  //收货地址所在的办事处ID
			'ip_address' 	  => $real_ip,  //客户端IP地址
			'goods_amount'    => $total['goods_price'],  //商品总金额
			'discount'        => $total['discount']+$total['member_discount'],  //加上会员折扣
			'tax'             => 0,  //税收
			'parent_id'       => 0,
			'divide_region'   => '广州地区手机商城下单',
			'kefu'            => '手机商城下单',
		);
		
		if($payment_id != 1){
			switch($order['pay_id']){
				case 4:  //支付宝
					$code = 'alipay';
					$order['pay_name'] = '支付宝';
				break;
                case 6:  //钱包支付
                    $code = 'chinaskinpay';
                    $order['pay_name'] = '钱包支付';
                    break;
				case 7:  //财付通
					$code = 'tenpay';
					$order['pay_name'] = '财付通';
				break;
				case 8:  //快钱
					$code = 'kuaiqian';
					$order['pay_name'] = '快钱支付';
				break;
				case 18:  //微信支付
//					if(C('SITE_ID') != 14){  //如果不是Q站，不给微信支付，目前只支持Q站
//						$this->error('当前站点不支持微信支付，请选择其他支付方式');
//					}
					$code = 'wechatpay';
					$order['pay_name'] = '微信支付';
				break;
				default:  //网银
					$code = 'tenpay';
					$order['pay_name'] = '网银支付';
					$order["bank_id"] = $order['pay_id'];  //网银识别
					$order['pay_id'] = 7;  //  > 10 是网银，强制使用财付通
				break;
			}
		}else{
			$order['pay_name'] = '货到付款';
		}
		
		$cookie_source_url = cookie('source_url');
		$source_url = I('source_url','','trim');
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
		
		//（订单归属问题）
		if($this->user_id == 0 && $order['pay_id']==1){   		//未登陆，并且是货到付款
			
			//判断是否是通过新增地址而添加的新用户，提示初始账号密码
			$new_consignee = session('new_consignee');
			
			if(!empty($new_consignee)){   //收获地址，初始注册
				
				$password = substr($new_consignee['mobile'],-6);  //获取手机号码后六位做为密码
				$data = array(
					'mobile'=>PhxCrypt::phxEncrypt($new_consignee['mobile']),
					'sms_mobile'=>$new_consignee['mobile'],
					'ip'=>$real_ip,
					'email'=>isset($new_consignee['email']) ? $new_consignee['email'] : '',
					'source'=>$_SERVER['HTTP_HOST'],
					'sex'=>0,
					'password'=>$password,
				);
				
				$MemberObject = new Member();
				//验证存在或初始注册，存在，则返回user_id,不存在，则返回初始注册的user_id和随机密码
				$reg_res = $MemberObject->addNewMember($data);
				
				$this->user_id = isset($reg_res['user_id']) ? $reg_res['user_id'] : (isset($reg_res['new_user_id']) ? $reg_res['new_user_id'] : 0);
				
				if($this->user_id > 0){
					//保存地址到会员中心（user_id：为刚初始注册或以新增地址中的手机号的user_id）
					$new_address_id = $MemberObject->saveAddress($this->user_id, $new_consignee);
					session('default_address_id', $new_address_id);
					
					//非初始注册,包涵初始注册密码
					$never_login = D('Users')->where("user_id = '".$this->user_id."'")->getField('state');  //为1，说明是初始注册但未登陆过
				}
			}
		}
		
		$order['user_id'] = $this->user_id;
		
		/* 收货人信息 */
		if(!empty($consignee)){
			foreach ($consignee as $key => $value){
				$order[$key] = addslashes($value);
			}
		}
		
		
		/* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
		if ($order['order_amount'] <= 0){
			$order['order_status'] = OS_CONFIRMED;
			$order['confirm_time'] = Time::gmtime();
			$order['pay_status']   = PS_PAYED;
			$order['pay_time']     = Time::gmtime();
			$order['order_amount'] = 0;
		}
		
		/*
		 *判断cookie，将广告来源（yiqifa）记录表中
		*/
		if(!empty($_COOKIE['yiqifa'])){
			$yiqifa = urldecode(stripslashes($_COOKIE['yiqifa']));
			$union = explode(":",$yiqifa);
			$union_info['from_url'] = $union[0];
			$union_info['channel'] = $union[1];
			$union_info['cid'] = $union[2];
			$union_info['wi'] = $union[3];
		}/*else{
			$yiqifa = array();
		}*/
		
		//$from_ad = 0;
		if(isset($union_info['from_url'])){
			$from_url = $union_info['from_url'];
			if(isset($copartner_array[$from_url])){
				$union_info['from_ad'] = $copartner_array[$from_url][0];
				$union_info['referer'] = $copartner_array[$from_url][2];
			}
		}
		
		$order['from_ad']          = session('from_ad');
		$order['from_ad']          = $order['from_ad'] ? $order['from_ad'] : '0';
		$order['referer']          = session('referer');
		$order['referer']          = $order['referer'] ? addslashes($order['referer']) : '';
		
		$sitename                  = cookie('sitename');
		$order['referer']          = $sitename ? trim($sitename) : $order['referer'];
		$order['from_ad']		   = $union_info['from_ad'] > 0 ? $union_info['from_ad'] : $order['from_ad'];
		$order['referer']		   = !empty($union_info['referer']) ? $union_info['referer'] : $order['referer'] ;
		
		
		//获取新订单号
		$order['order_sn'] = $this->OrderObject->getOrderSn(); 
		
		//获取优惠券ID
		$order['bonus_id'] = 0;
		$use_bonus_id = session('use_bonus_id');
		if($bonus_type > 0 && is_array($use_bonus_id) && isset($use_bonus_id[$bonus_type])){  //手动输入的优惠券
			$order['bonus_id'] = $use_bonus_id[$bonus_type];
		}else{
			if($bonus_type > 0){  //自身帐号有的优惠券，或者可自动获取的优惠券类型
				$day = Time::localGetdate();
				$today  = Time::localMktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
				//$where = "b.bonus_type_id = '$bonus_type' AND t.use_end_date >= '$today' AND t.send_type != 3 AND b.user_id = '".$this->user_id."' AND b.order_id = 0";
				$where = "b.bonus_type_id = '$bonus_type' AND t.use_end_date >= '$today' AND b.user_id = '".$this->user_id."' AND b.order_id = 0";
				
				$UserBonusCenter = D('UserBonusCenter');
				
				$UserBonusCenter->alias(' AS b')->join("__BONUS_TYPE__ AS t ON t.type_id = b.bonus_type_id", 'left');
				$order['bonus_id'] = $UserBonusCenter->where($where)->order('RAND()')->getField('b.bonus_id');
				$order['bonus_id'] = $order['bonus_id'] ? $order['bonus_id'] : 0;
			}
		}
		
		//$order['mobile'] = PhxCrypt::phxEncrypt($order['mobile']);
		$order['mobile'] = isset($order['encode_mobile']) ? $order['encode_mobile'] : PhxCrypt::phxEncrypt($order['mobile']);
		
		//插入到自身站点的订单表
		$order['order_id'] = D('OrderInfo')->insert($order);
		$order['site_id'] = C('SITE_ID');
		//插入到会员中心的订单表
		$insert_id = D('OrderInfoCenter')->insert($order);
		
		$ExchangeGoodsModel = D('ExchangeGoods');
		$IntegralObj = new Integral();
		$BonusObj = new Bonus();
		$CartModel = D('Cart');
		$GoodsModel = D('Goods');
		
		/* 插入订单商品 */
		foreach($cart_goods as $k=>$row){
			if($row['goods_name']){
				if($row['extension_code'] == 'exchange_goods'){  //积分兑换的
					$integral = $ExchangeGoodsModel->where("goods_id = '$row[goods_id]'")->getField('exchange_integral');
					$goods_price = $IntegralObj->valueOfIntegral($integral);
				}else{
					$goods_price = $row['goods_price'];
				}
				
				$data = array(
					'order_id'=>$order['order_id'],
					'order_sn'=>$order['order_sn'],
					'goods_id'=>$row['goods_id'],
					'goods_name'=>($row['goods_name'] ? $row['goods_name'] : ''),
					'goods_sn'=>($row['goods_sn'] ? $row['goods_sn'] : ''),
					'goods_number'=>($row['goods_number'] ? $row['goods_number'] : 1),
					'market_price'=>($row['market_price'] ? $row['market_price'] : 0),
					'goods_price'=>($goods_price ? $goods_price : 0),
					'goods_attr'=>($row['goods_attr'] ? $row['goods_attr'] : ''),
					'is_real'=>($row['is_real'] ? $row['is_real'] : 1),
					'extension_code'=>($row['extension_code'] ? $row['extension_code'] : ''),
					'parent_id'=>($row['parent_id'] ? $row['parent_id'] : 0),
					'is_gift'=>$row['is_gift'],
					'site_id'=>$order['site_id'],
				);
				$this->insertOrderGoods($data);  //插入商品数据到数据库
				
				//检查如果是套装，则回去套装商品加入到订单商品
				if($data['extension_code'] == 'package_buy'){  //是套装
					$package = $GoodsModel->getPackageInfo(0, 0, $data['goods_id']);
					if(isset($package['package_goods']) && !empty($package['package_goods'])){
						foreach($package['package_goods'] as $key=>$children){
							if($children['goods_name']){
								$package_goods = array(
									'order_id'=>$data['order_id'],
									'order_sn'=>$data['order_sn'],
									'goods_id'=>$children['goods_id'],
									'goods_name'=>$children['goods_name'],
									'goods_sn'=>$children['goods_sn'],
									'goods_number'=>$children['goods_number'],
									'market_price'=>$children['market_price'],
									'goods_price'=>$children['shop_price'],
									'goods_attr'=>'',
									'is_real'=>$children['is_real'],
									'extension_code'=>'package_goods',
									'parent_id'=>($children['package_id'] ? $children['package_id'] : 0),
									'is_gift'=>$row['is_gift'],
									'site_id'=>$data['site_id'],
								);
								$this->insertOrderGoods($package_goods);  //插入商品数据到数据库
							}
						}
					}
				}
				$goods_name_arr[] = $row['goods_name'];
			}
		}
		//货到付款方式时记录order_sn到session用于能有一次修改订单支付方式$_REQUEST['step'] == 'change_pay_mode'
		if ($order['pay_id'] == 1) {
			session('pay_mode_order_sn',$order['order_sn']);
		}
		
		/* 插入支付日志 */
		//$order['log_id'] = insert_pay_log($order['order_id'], $order['order_amount'], PAY_ORDER);
		/* 取得支付信息，生成支付代码 */
		if ($order['order_amount'] > 0){
			
			if($order['pay_id'] != 1){  //非货到付款
				$OrderObj = new Order();
				$pay = $OrderObj->getPaymentClass($code);  //实例化网银类
				$payment = D('Payment')->getPayment(strtolower($code));
				$payment = $OrderObj->unserialize_config($payment['pay_config']);
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
		}
		
		//使用优惠券		
		if ($order['bonus_id'] > 0 && $order['order_amount'] > 0){
			$result = $BonusObj->useBonus($order['bonus_id'], $order['order_id'], $order['site_id'], $this->user_id);  //使用优惠券
			if(is_string($result)){
				$this->error($result);
			}
		}
		
		/* 清空购物车 ,两种情况，登录了，保留，不登录，刷新之后清除*/
		if($this->user_id > 0){
			$where = "(session_id = '" . $this->sessionId . "' OR user_id = '".$this->user_id."')";
		}else{
			$where = "session_id = '" . $this->sessionId . "'";
		}
		$rec_id = $this->OrderObject->getSelectCartRecId();  //购物车普通商品的购物车ID
		$where = !empty($rec_id) ? $where . " and rec_id IN($rec_id)" : $where;
		//删除购物车普通商品
		$CartModel->where($where)->delete();
		//删除session记录
		$CartModel->delTempGift(null);
		
		//记录当前所下的订单，用于在线支付时读取订单信息
		session('pay_online_order_sn', $order['order_sn']);
	
		//new_consignee
		session('use_bonus_id',NULL);
		session('cart_statistics',NULL);
		session('gift_bonus',NULL);
		session('payment_data',NULL);
		
		session('client_ip',NULL);
		cookie('error',NULL);
		
		$data = array(
			'order_id'=>$order['order_id'],
			'order_sn'=>$order['order_sn'],
			'amount'=>$order['order_amount'],
			'discount'=>$order['discount'],
			'bonus'=>$order['bonus'],
			'shipping_fee'=>$order['shipping_fee'],
			'payment_id'=>(isset($order['bank_id']) ? $order['bank_id'] : $order['pay_id']),
			'payment_name'=>$order['pay_name'],
			'remark'=>$order['postscript'],
			'user_id'=>$order['user_id'],
			'add_time'=>$order['add_time'],
			'add_date'=>Time::localDate('Y-m-d H:i:s',$order['add_time']),
			'content'=>$order['content'],
		);
		
		$this->success($data);
	}
	
	/*
	*	插入订单商品到数据库
	*	@Author 9009123 (Lemonice)
	*	@param array $data  订单商品详情
	*	@return true or false
	*/
	private function insertOrderGoods($data = array()){
		if(is_array($data) && !empty($data)){
			D('OrderGoodsCenter')->insert($data);  //插入商品数据到ucenter数据库
			D('OrderGoods')->insert($data);  //插入商品数据到当前站点数据库
			return true;
		}
		return false;
	}
	
	/*
	*	生成token - 此方法是为了避免前端重复提交、多次点击
	*	@Author 9009123 (Lemonice)
	*	@param string $check_token  需要检查的token
	*	@return string
	*/
	private function token($check_token = ''){
		$token = session('token');
		$token_time = session('token_time');
		$exper_time = 300;  //有效时间，秒
		
		if($check_token != ''){
			if((!$check_token || $check_token == '') && (!$token || $token == '') ){
				if((microtime(true)-$token_time)>$exper_time){
					$this->error('页面已过期');
				}
				$this->error('正在处理您的订单，再次购买请稍后'.ceil(($exper_time-(microtime(true)-$token_time))/60).'分钟！');
			}
			if(!$token || $token == '' || $check_token == '' ||  ($token!=$check_token)){
				if((microtime(true)-$token_time)>$exper_time){
					$this->error('页面已过期，请刷新');
				}
				$this->error('若您提交过订单，再次购买请稍后'.ceil(($exper_time-(microtime(true)-$token_time))/60).'分钟！');
			}
			session('token',NULL);
			return true;
		}else{  //获取token
			if(!$token || !$token_time || (microtime(true)-$token_time) > $exper_time) {
				$token_time = microtime(true);
				$token = md5($token_time);
				session('token_time', $token_time);
				session('token', $token);
			}
			return $token;
		}
	}
	
	/*
	*	获取购物车商品
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
	private function getCartGoods(){
		$rec_id = $this->OrderObject->getSelectCartRecId();  //购物车普通商品的购物车ID
		//$rec_id[72] = 72;
		//$rec_id[73] = 73;
		//$rec_id[74] = 74;
		//session('cart_select', $rec_id);
		
		if($rec_id === false){  //如果返回了false表示没有普通商品、也没有优惠活动
			$this->error('您没勾选购物车商品！');
		}
		
		/* 购物车商品信息 */
		$cart_goods = $this->OrderObject->cartSelectGoods($rec_id); // 取得商品列表，计算合计
		
		if(is_array($cart_goods) && empty($cart_goods)){
			$this->error('您没勾选购物车商品！');
		}elseif(!is_array($cart_goods) && $cart_goods != ''){
			$this->error($cart_goods);
		}
		return $cart_goods;
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
	*	微信支付获取openid
	*	@Author 9009123 (Lemonice)
	*	@return exit & Jumb To Pay
	*/
	public function getOpenId(){
		$openid = session('sopenid');
        if(empty($openid)){
            import('Common/Extend/Payment/Wechatpay/WxPay');
            $jsApi = new \JsApi_pub();
            $jsApi->weChat_appId = APPID;
            $jsApi->weChat_appSecret = APPSECRET;
            $jsApi->api_call_url = 'http://q.chinaskin.cn'.$_SERVER['REQUEST_URI'];
            $code = I('get.code',NULL,'trim');
//            $host = urlencode($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            $host = '';
            if ($_SERVER['HTTP_HOST'] != 'q.chinaskin.cn') {  //判断当前域名是否授权域名，不是授权域名组装回调地址
                $host = urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
            if(is_null($code)){
                $url = $jsApi->createOauthUrlForCode('snsapi_base',$host);
                header("Location: $url");  //跳转过去，为了获取code
            }else{
                /*$source = base64_decode(I('get.source'));
                if(empty($source)){
                    $callback = '/';
                }else{
                    $callback = siteUrl().'#'.$source;
                }*/
                $jsApi->setCode($code);
                $openid = $jsApi->getOpenid();
                if(!empty($openid)){
                    session('sopenid',$openid);
                    //header("Location: $callback");  //跳转过去，为了获取code
                }else{
                    $url = $jsApi->createOauthUrlForCode('snsapi_userinfo',$host);
                    header("Location: $url");  //跳转过去，为了获取code
                }
            }
        }
		$wechatpay_data = session('wechatpay_data');
		if(!$wechatpay_data || !isset($wechatpay_data['order']['order_sn'])){
			echo '支付出错了，请返回重试，或联系客服帮助！';
			exit;
		}
		session('wechatpay_data', NULL);
		$OrderObj = new Order();
		$wechatpay = $OrderObj->getPaymentClass('wechatpay');  //实例化微信类
		echo $wechatpay->goToPay($wechatpay_data['order'],$wechatpay_data['payment']);  //开始支付
		exit;
	}
	
	/*
	*	检测是否可以使用微信支付
	*	@Author 9009123 (Lemonice)
	*	@return exit & JSON
	*/
	public function checkWechatPay(){
		$data = array(
			'result'=>0,
		);
		$result = isCheckWechat();
		if($result == false){  //不是微信打开网页
			$this->success($data);
		}
//		if(C('SITE_ID') != 14){  //如果不是Q站，不给微信支付，目前只支持Q站
//			$this->success($data);
//		}
		$data['result'] = 1;  //支持微信支付
		$this->success($data);
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