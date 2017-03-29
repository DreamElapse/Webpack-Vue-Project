<?php
/**
 * ====================================
 * 订单相关操作 类
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-14 17:32
 * ====================================
 * File: Order.class.php
 * ====================================
 */
namespace Common\Extend\Order;
use Common\Extend\Time;
use Common\Extend\Order\Favourable;
use Common\Extend\Order\Bonus;
use Common\Extend\Order\Integral;

class Order{
	private $sessionId = NULL;                  //session ID
	private $user_id = 0;                       //当前登录的用户ID
	
	public function __construct(){
		$this->sessionId = session_id();  //获取当前session ID
		$this->user_id = D('Home/OrderInfo')->getUser('user_id');
    }
	
    public function Order($data){
        $this->__construct();
    }
    
	/*
	*	取得购物车的所有商品，包括赠品、优惠活动
	*	@Author 9009123 (Lemonice)
	*	@param   int     $rec_id   勾选的购物车ID
	*	@return array  购物车商品数组
	*/
	public function cartSelectGoods($rec_id = ''){
		$CartModel = D('Home/Cart');
		$CartModel->field("c.rec_id, c.user_id, c.goods_id, c.goods_name, c.goods_sn, c.goods_number, c.market_price, c.goods_price, c.goods_attr, c.is_real, c.parent_id, c.is_gift, c.extension_code, c.goods_price * c.goods_number AS amount, g.goods_thumb, g.goods_img, g.original_img");
		
		if($this->user_id > 0){  //有登录
			$where = "(c.session_id = '" . $this->sessionId . "' OR c.user_id = '" . $this->user_id . "')";
		}else{    //没登录
			$where = "c.session_id = '" . $this->sessionId . "'";
		}
		if($rec_id != ''){
			$where .= " and c.rec_id IN($rec_id)";
		}
		$CartModel->where("$where AND c.extension_code != 'package_goods'");
		$CartModel->alias(' AS c')->join("__GOODS__ AS g ON c.goods_id = g.goods_id", 'left');
		$cart_list = $CartModel->order('c.is_gift,c.rec_id')->select();
		
		$OrderObject = new Order();
		
		//实例化【优惠活动】类
		$favourableObj = new Favourable();
		$favourableObj->choose_rec_id = $rec_id;  //当前勾选的购物车ID
		$favourableObj->bonus_money = 0;  //红包金额, 当前页面好没得输入红包，因此这里没有红包金额
		
		//获取购物车选择的赠品、优惠活动
		$gift = $favourableObj->getCartGift();
		$gift_list = array();
	    //过滤套餐下的子商品
		if(!empty($gift)){
			foreach($gift as $value){
				if($value['extension_code'] != 'package_goods'){  //package_goods为子商品
					$value['amount'] = $value['goods_price'] * $value['goods_number'];
					$gift_list[] = $value;
				}
			}
		}
		//把赠品、活动 与 普通商品合并一起
		if(!empty($gift_list)){
			$cart_list = array_merge($cart_list, $gift_list);
		}
		
		//开始检查
		$result = $favourableObj->check($cart_list);
		
		//判断校验是否成功  status=（1是成功，0是失败）
		if($result['status'] != 1){
			return $result['msg'];  //提示错误
		}
		
		$cart_list = isset($result['data'])&&!empty($result['data']) ? $result['data'] : $cart_list;  //重新赋值列表，这里的列表只有普通商品
		
		//统计商品总价和相关信息，保存到session，方便其他接口使用
		if(!empty($cart_goods)){
			$OrderObject->statistics($cart_goods);
		}
		
		if(!empty($cart_list)){
			foreach($cart_list as $key=>$value){
				if(isset($value['extension_code']) && $value['extension_code'] == 'package_buy'){  //是套装
					$cart_list[$key]['goods_id'] = M('goods_activity')->where("act_id = '$value[goods_id]'")->getField('goods_id');
				}
			}
		}
		return $cart_list;
	}
	
	/*
	*	统计商品的总金额、是否存在套餐、是否存在优惠活动等信息，
	*	保存到session，供其他接口使用，不用重复统计
	*	@Author 9009123 (Lemonice)
	*	@param  array   $cart_goods  购物车所有商品列表
	*	@return bool [true or false]
	*/
	public function statistics($cart_goods = array()){
		if(!empty($cart_goods)){
			$total_price = 0;  //所有商品的总价格goods_price*goods_number
			$have_gift = 0;  //是否有优惠活动存在
			$have_package = 0;  //是否存在套餐
			foreach($cart_goods as $value){
				if(isset($value['is_gift']) && $value['is_gift'] > 0){  //是活动
					$have_gift = 1;
				}
				if(isset($value['extension_code']) && $value['extension_code'] == 'package_buy'){  //是套装
					$have_package = 1;
				}
				//加总价格
				//查看是否为买一送一
				if($value['extension_code'] != 'package_goods' && $value['is_gift'] > 0 && $value['goods_price'] > 0 && (isset($value['send_num']) ? $value['goods_number']>$value['send_num'] : $value['goods_number']>0)){  //package_goods为子商品, send_num是赠送的数量
					$goods_number = isset($value['send_num']) ? $value['goods_number'] - $value['send_num'] : $value['goods_number'];
					$total_price  += $value['goods_price'] * $goods_number;
				}else{
					$total_price  += $value['goods_price'] * $value['goods_number'];
				}
			}
			//保存到session
			session('cart_statistics',array(
				'total_price'=>$total_price,
				'have_gift'=>$have_gift,
				'have_package'=>$have_package,
			));
			return true;
		}
		return false;
	}
	
	/*
	*	获取购物车中选择的普通商品的购物车ID
	*	@Author 9009123 (Lemonice)
	*	@return string [ID]
	*/
	public function getSelectCartRecId(){
		$rec_id = array();  //购物车普通商品的购物车ID
		$cart_select = session('cart_select');  //获取购物车选择的所有商品，一维数组，格式如下注释的
		
		/*
		Array
		(
			[t_0] => t_0  //t_开头的优惠活动，是字符串，详情存在session里面
			[t_1] => t_1
			[t_2] => t_2
			[158] => 158  //纯数字的是普通商品，是数值，详情数据存在Cart数据表里面，该值是rec_id
			[498] => 498
		)
		*/
		if(!empty($cart_select)){
			foreach($cart_select as $id){
				if(is_numeric($id)){
					$rec_id[] = $id;
				}
			}
		}else{
			return false;  //购物车没有任何商品，也没有优惠活动
		}
		
		return (!empty($rec_id) ? implode(',', $rec_id) : '');
	}
	
	/*
	*	根据地区、购物平台等 统计购物车总金额、优惠等
	*	@Author 9009123 (Lemonice)
	*	@param  array  $cart_goods  购物车商品
	*	@param  int  $address_id  地址ID，如果是新添加的没登录的可以传0
	*	@param  int  $province  省份ID，如果有地址ID可不传或者传0
	*	@param  int  $payment_id  支付平台ID
	*	@param  int  $bonus_type  优惠券类型ID
	*	@param  int    $integral  使用多少积分
	*	@return array
	*/
	public function getOrderFree($cart_goods = array(), $address_id = 0, $province = 0, $payment_id = 0, $bonus_type = 0, $integral = 0){
		//获取填写的收货地址
		$consignee = $this->getRealUserAddress($address_id);
		
		$order = array(
			'integral'=>($integral ? $integral : 0),  //使用积分
			'bonus_type'=>($bonus_type ? $bonus_type : 0),  //使用优惠券
			'pay_id'=>($payment_id ? $payment_id : 0),    //支付ID   payment_id
		);
		if((!isset($consignee['province']) || $consignee['province'] <= 0) && $province > 0){
			$consignee['province'] = $province;
		}
		//总价
		$total = $this->unionOrderFee($order, $cart_goods, $consignee);//总费用
		
		return $total;
	}
	
	/*
	*	统计购物车总金额、优惠等
	*	@Author 9009123 (Lemonice)
	*	@param  array $order = array(
			'integral'=>($integral ? $integral : 0),  //使用积分
			'bonus_type'=>($bonus_type ? $bonus_type : 0),  //使用优惠券的类型ID
			'pay_id'=>($payment_id ? $payment_id : 0),    //支付ID   payment_id
		);
	*	@param  array  $goods  购物车商品
	*	@param  array  $consignee  地址数组、地区数组 
	*	@return array
	*/
	public function unionOrderFee($order, $goods, $consignee){
		
		$total  = array(
			//'real_goods_count' => 0,
			//'gift_amount'      => 0,
			'goods_price'      => 0,
			'market_price'     => 0,
			'discount'         => 0,
			'shipping_fee'     => 0,
			'bonus'            => 0,
			'pay_fee'          => 0,  //在线支付的手续费等费用
			'pay_fee_discount' => 0,  //在线支付优惠金额
			'member_discount'  => 0
		);
		$weight = 0;
		$favourableObj = new Favourable();
		$BonusObj = new Bonus();
		$IntegralObj = new Integral();
		
		/*会员商品9.5折*/
		$preferential = 0;
		if ($this->user_id > 0) {
			$preferential = $favourableObj->memberPreferential($this->user_id,true);  //获取会员折扣
		}
		
		/* 商品总价 */
		$full_minus_act_ids = $favourableObj->getActIdByType(FAT_FULL_MINUS);   //获取所有满立减活动id
		$full_minus_subtotal = array();   //满立减活动产品金额小计
		foreach ($goods as $val){
			/* 统计实体商品的个数 */
			if(isset($val['extension_code']) && $val['extension_code'] =='package_goods' ){
				continue;
			}
			/*if ($val['is_real']){
				$total['real_goods_count'] += $val['goods_number'];
			}*/
			if($val['is_gift'] > 0 && in_array($val['is_gift'],$full_minus_act_ids)){
				$full_minus_subtotal[$val['is_gift']][] = $val['goods_price'] * $val['goods_number'];
			}
						
			//查看是否为买一送一
			if($val['is_gift'] > 0 && $val['goods_price'] > 0 && (isset($val['send_num']) ? $val['goods_number']>$val['send_num'] : $val['goods_number']>0)){  //package_goods为子商品, send_num是赠送的数量
				$goods_number = isset($val['send_num']) ? $val['goods_number'] - $val['send_num'] : $val['goods_number'];
				$total['goods_price']  += $val['goods_price'] * $goods_number;
 			}else{
				$total['goods_price']  += $val['goods_price'] * $val['goods_number'];
			}
			
			$total['market_price'] += $val['market_price'] * $val['goods_number'];
			$total['member_discount'] += intval($val['is_gift']) == 0 ? $val['goods_price'] * $val['goods_number'] * $preferential : 0;
			//$total['member_discount'] += $val['goods_price'] * $val['goods_number'] * $preferential;
		}
		
		//去掉会员折扣金额的小数点
		$total['member_discount'] = floor($total['member_discount']);
		
		if(!empty($full_minus_subtotal)){  //如果存在满立减活动产品
			$total['fav_minus_desc'] = $total['fav_minus_short_desc'] = $total['fav_minus_subtotal'] = array();
			foreach($full_minus_subtotal as $key => $fms){
				$fm_subtotal = array_sum($fms);
				$fav_info = $favourableObj->info($key);
				$minus_discount = $favourableObj->getFullMinusInfo($fav_info, $fm_subtotal);
				if(isset($minus_discount['discount_price']) && intval($minus_discount['discount_price']) > 0){
					$total['goods_price'] -= $minus_discount['discount_price'];
					$total['fav_minus_desc'][$key] = $minus_discount['desc'];
					$total['fav_minus_short_desc'][$key] = $minus_discount['short_desc'];
					$total['fav_minus_subtotal'][$key] = $minus_discount['subtotal'];
				}
			}
		}
	
		$total['member_discount'] = round($total['member_discount']); //会员优惠四舍五入
		
		//$total['saving']    = $total['market_price'] - $total['goods_price'];
		//$total['save_rate'] = $total['market_price'] ? round($total['saving'] * 100 / $total['market_price']) . '%' : 0;
	
		$total['goods_price_formated']  = priceFormat($total['goods_price'], false);
		$total['market_price_formated'] = priceFormat($total['market_price'], false);
		//$total['saving_formated']       = priceFormat($total['saving'], false);
		
		/* 活动折扣 */
		//$total['discount'] = $discount['discount'];
		$total['discount'] = 0;
		if ($total['discount'] > $total['goods_price']){
			$total['discount'] = $total['goods_price'];
		}
		
		$total['discount_formated'] = priceFormat($total['discount'], false);
		
		/* 优惠券 */
		if (isset($order['bonus_type']) && intval($order['bonus_type']) > 0){
			if(ONLINE_PAYMENT_DISCOUNT > 0 && !empty($order['pay_id']) && $order['pay_id'] != 1){
				$is_payonline_discount = 1;  //是否享受了在线支付优惠
			}else{
				$is_payonline_discount = 0;
			}
			
			$total_price = $total['goods_price'];
			$cart_statistics = session('cart_statistics');
			$check_bonus = $cart_statistics['have_gift'];
			$is_package = $cart_statistics['have_package'];
			$addition = array(
				'user_id'=>$this->user_id,
				'cart_amount'=>$total_price,
				'site_id'=>C('SITE_ID'),
				'is_package'=>$is_package,
				'check_bonus'=>$check_bonus,  //是否包含其他优惠劵活动
				'is_member_discount'=>($this->user_id > 0 ? 1 : 0),
				'is_payonline_discount'=>$is_payonline_discount,
				'cart_category_info' =>$BonusObj->getCartCategory() //购物车中商品分类信息
			);
			
			$bonus = D('Home/BonusTypeCenter')->where("type_id = '$order[bonus_type]' and (FIND_IN_SET('".C('SITE_ID')."',use_site) or use_site = 0)")->find();
			//$bonus = D('Home/UserBonusCenter')->bonusInfo($order['bonus_id']);
			
			$BonusObject = new Bonus();
			
			//如果有记录优惠券ID就获取优惠券详情
			$use_bonus_id = intval(session('use_bonus_id.'.$order['bonus_type']));
			if($use_bonus_id > 0){
				$bonus_info = D('Home/UserBonusCenter')->bonusInfo($use_bonus_id);
				$bonus = !empty($bonus_info) ? array_merge($bonus,$bonus_info) : $bonus;
			}
			
			$bonus_result = $BonusObject->checkBonus($bonus,$addition);  //校验优惠券
			
			if(is_array($bonus_result)){  //如果返回了数组，则是校验通过
				$total['bonus'] = $bonus_result['type_money'];
			}else{
				$total['bonus'] = 0;
				$order['bonus_id'] = 0;
			}
		}
		$total['bonus_formated'] = priceFormat($total['bonus'], false);
		
	
		/* 配送费用 */
		$shipping_cod_fee = NULL;
		
	
		/*调用接口判断是否免邮*/
		$is_free = false;
		$order_amount = $total['goods_price'] - $total['member_discount'] - $total['bonus'];
		
		$total['shipping_fee'] = $this->getShippingFee($consignee,$order_amount);  //获取邮费
		
		
		if(isset($bonus_result['free_postage']) && $bonus_result['free_postage'] == 1){
			$total['shipping_fee'] = 0; //使用免邮券，邮费为0
		}
		//判断是否包含包邮商品
		$pay_type = $order['pay_id']; //支付方式
		foreach ($goods as $key=>$val){
			if(($val['shipping_free'] == 1 && $pay_type != 1) || ($val['shipping_free'] == 2 && $pay_type == 1) || ($val['shipping_free'] == '1,2')){	 
				$total['shipping_fee'] = 0;
				break;
			}
		}
				
		$total['shipping_fee_formated']    = priceFormat($total['shipping_fee'], false);
	
		// 购物车中的商品能享受红包支付的总额
		$bonus_amount = 0;
		// 红包和积分最多能支付的金额为商品总额
		$max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;
	
		/* 计算订单总额 */
		$total['amount'] = $total['goods_price'] - $total['discount'] + $total['shipping_fee'];

		// 减去红包金额
		$use_bonus        = min($total['bonus'], $max_amount); // 实际减去的红包金额
		
		$total['bonus']   = $use_bonus;
		$total['bonus_formated'] = priceFormat($total['bonus'], false);

		$total['amount'] -= $use_bonus; // 还需要支付的订单金额
		$max_amount      -= $use_bonus; // 积分最多还能支付的金额
		
		/* 积分 */
		$order['integral'] = $order['integral'] > 0 ? $order['integral'] : 0;
		if ($total['amount'] > 0 && $max_amount > 0 && $order['integral'] > 0){
			$integral_money = $IntegralObj->valueOfIntegral($order['integral']);
	
			// 使用积分支付
			$use_integral             = min($total['amount'], $max_amount, $integral_money); // 实际使用积分支付的金额
			$total['amount']        -= $use_integral;
			$total['integral_money'] = $use_integral;
			$order['integral']       = $IntegralObj->integralOfValue($use_integral);
		}else{
			$total['integral_money'] = 0;
			$order['integral']       = 0;
		}
		$total['integral'] = $order['integral'];
		$total['integral_money_formated'] = priceFormat($total['integral_money'], false);
		
		/* 支付费用 */
		$total['pay_fee_discount'] = 0;  //在线支付优惠多少钱
		if (isset($order['pay_id'])){
			$total['pay_fee']      = $favourableObj->payFee($order['pay_id'], $total['amount'], $shipping_cod_fee);  //检查是否需要加在线支付费用
			
			/* 如果是在线支付则减免xx元 */
			if (ONLINE_PAYMENT_DISCOUNT > 0 && $order['pay_id'] != 1){  //不是货到付款，减xx元
				$total['discount'] += ONLINE_PAYMENT_DISCOUNT_AMOUNT;  //在线支付的xx元不算入折扣
				$total['discount_formated'] = priceFormat($total['discount'], false);
				
				//暂时去掉在线支付优惠xx元的
				$total['amount']   -= ONLINE_PAYMENT_DISCOUNT_AMOUNT;
				$total['pay_fee_discount'] = ONLINE_PAYMENT_DISCOUNT_AMOUNT;
			}else{
				$total['pay_fee_discount'] = 0;
			}
		}
		
		$total['pay_fee_discount_formated'] = priceFormat($total['pay_fee_discount'], false);
		$total['pay_fee_formated'] = priceFormat($total['pay_fee'], false);
	
		$total['amount'] += $total['pay_fee']; // 订单总额累加上支付费用
		$total['amount'] -= $total['member_discount']; //订单总额减去会员优惠
		$total['amount_formated']  = priceFormat($total['amount'], false);
		$total['member_discount_formated'] = priceFormat($total['member_discount'], false);
	
		/* 取得可以得到的积分和红包 */
		if ($order['extension_code'] == 'group_buy'){  //团购
			$total['will_get_integral'] = $group_buy['gift_integral'];
		}elseif ($order['extension_code'] == 'exchange_goods'){  //积分兑换
			$total['will_get_integral'] = 0;
		}else{
			//$total['will_get_integral'] = $IntegralObj->getGiveIntegral($goods);  //普通商品获取积分
			// + $total['shipping_fee'] + $total['pay_fee']
			$total['will_get_integral'] = $total['goods_price'] - $total['discount'] - $total['member_discount'] - $total['integral_money'] - $total['bonus'];
		}
		//登录会员生日两倍积分
		if($this->user_id > 0 && $total['will_get_integral'] > 0){
			$birthday = D('Home/UserInfo')->where("user_id = '".$this->user_id."'")->getField('birthday');
			if(date('m-d',$birthday) == date('m-d')){  //是否今天生日
				$total['will_get_integral'] = $total['will_get_integral'] * 2;  //两倍积分
			}
		}
		$total['will_get_integral'] = intval($total['will_get_integral']);
		
		$total['will_get_bonus']        = $order['extension_code'] == 'exchange_goods' ? 0 : priceFormat($BonusObj->getCartTotalBonus(), false);
		//$total['formated_goods_price']  = priceFormat($total['goods_price'], false);
		//$total['formated_market_price'] = priceFormat($total['market_price'], false);
		//$total['formated_saving']       = priceFormat($total['saving'], false);
		$total['amount_formated'] = str_replace('元', '', $total['amount_formated']);
		
		//如果是积分的
		if ($order['extension_code'] == 'exchange_goods'){
			$CartModel = D('Home/Cart');
			$CartModel->alias(' AS c')->join("__EXCHANGE_GOODS__ AS eg ON c.goods_id = eg.goods_id", 'left');
			$CartModel->where("c.session_id= '" . $this->sessionId . "' AND c.rec_type = '" . CART_EXCHANGE_GOODS . "' AND c.is_gift = 0 AND c.goods_id > 0");
			$CartModel->group('eg.goods_id');
			$exchange_integral = $CartModel->field('SUM(eg.exchange_integral) as sum')->find();
			$total['exchange_integral'] = $exchange_integral['sum'];
		}
	
		return $total;
	}
	
	/*
	*	获取邮费
	*	@Author 9009123 (Lemonice)
	*	@param  array  $consignee  地址 (最少包含省份ID)
	*	@param  float  $order_amount  商品总价格
	*	@param  float  $shipping_fee  邮费
	*	@param  bool   $count_amount  是否根据价格减免基础邮费
	*	@return array
	*/
	public function getShippingFee($consignee,$order_amount, $shipping_fee = -1, $count_amount = true){
		$shipping_fee = $shipping_fee >= 0 ? $shipping_fee : C('shipping_fee');  //默认基础邮费
		$remote_address = C('remote_address');
		
		if($count_amount == true && $order_amount >= 200){
			$shipping_fee = 0;
		}
		if(isset($remote_address[$consignee['province']])){
			$shipping_fee += $remote_address[$consignee['province']];
		}
		
		return $shipping_fee;
	}
	
	/*
	*	获取用户真实地址（包括新增的地址）
	*	@Author 9009123 (Lemonice)
	*	@param  int  $address_id  地址ID
	*	@return array
	*/
	public function getRealUserAddress($address_id = 0){
		$new_consignee = session('new_consignee');
		if($address_id == 0 && !empty($new_consignee)){ //如果有新增地址，则取新增的地址
			return $new_consignee;
		}else{
			$address_id = $address_id > 0 ? $address_id : intval(session('default_address_id'));
			$UserAddress = D('Home/UserAddress');
			$info = $UserAddress->field('consignee,province,city,district,town,address,mobile')->where("address_id = '$address_id'")->find();
			if(isset($info['mobile'])){
				$info['encode_mobile'] = $info['mobile'];
			}
			$info = $UserAddress->phxDecode($info);
			return $info;
		}
	}
	
	/*
	*	检查收货人信息是否完整
	*	@Author 9009123 (Lemonice)
	*	@param   array   $consignee  收货人信息
	*	@return bool    true 完整 false 不完整
	*/
	public function checkConsigneeInfo($consignee){
		$res =(!empty($consignee['consignee']) &&
			!empty($consignee['province']) &&
			!empty($consignee['address']) &&
			!empty($consignee['mobile'])) || (!empty($consignee['consignee']) &&
			!empty($consignee['province']) &&
			!empty($consignee['address']));
		
		if ($res){
			$RegionModel = D('Home/Region');
			if (empty($consignee['province'])){
				/* 没有设置省份，检查当前国家下面有没有省份 */
				$pro = $RegionModel->where("parent_id = '0'")->getField('region_id');
				$res = empty($pro);
			}elseif (empty($consignee['city'])){
				/* 没有设置城市，检查当前省下面有没有城市 */
				$city = $RegionModel->where("parent_id = '$consignee[province]'")->getField('region_id');
				$res = empty($city);
			}elseif (empty($consignee['district'])){
				$dist = $RegionModel->where("parent_id = '$consignee[city]'")->getField('region_id');
				$res = empty($dist);
			}
		}

		return $res;
	}
	
	/*
	*	判断是否是偏远地区  --  范围：吉林、辽宁、内蒙、宁夏、青海、西藏、新疆
	*	@Author 9009123 (Lemonice)
	*	@param array $consignee
	*	@return int
	*/
	public function isRemotePlace($consignee){
		$remote_address = C('remote_address');
		if(isset($consignee['province']) && isset($remote_address[$consignee['province']])){
			return 1;  //偏远地区
		}
		return 0;
	}
	
	/*
	*	得到新订单号
	*	@Author 9009123 (Lemonice)
	*	@return string
	*/
	public function getOrderSn(){
		/* 选择一个随机的方案 */
		mt_srand((double) microtime() * 1000000);
		//$sn = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
		$sn = date('ymd').'1'.rand(1000000,9999999);
		
		$result = D('Home/OrderInfoCenter')->where("order_sn = '$sn'")->getField('order_id');
		if($result > 0){
			$sn = $this->getOrderSn();
		}
		return $sn;
	}
	
	/*
	*	实例化对应的网银类
	*	@Author 9009123 (Lemonice)
	*	@param  string $pay_code  支付代码
	*	@return Object
	*/
	public function getPaymentClass($pay_code){
		$pay_code = ucfirst($pay_code);
		$name = '\Common\Extend\Payment\\' . $pay_code;  //对应网银类
		return new $name();
	}
	
	/*
	*	处理序列化的支付、配送的配置参数,  返回一个以name为索引的数组
	*	@Author 9009123 (Lemonice)
	*	@param   string       $cfg
	*	@return exit & Json
	*/
	public function unserialize_config($cfg){
		if (is_string($cfg) && ($arr = unserialize($cfg)) !== false){
			$config = array();
			foreach ($arr as $key => $val){
				$config[$val['name']] = $val['value'];
			}
			return $config;
		}else{
			return false;
		}
	}
}
?>