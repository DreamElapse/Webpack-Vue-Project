<?php
/**
 * ====================================
 * 优惠活动 类
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-11 13:48
 * ====================================
 * File: Favourable.class.php
 * ====================================
 */
namespace Common\Extend\Order;
use Common\Extend\Time;

class Favourable{
	private $sessionId = NULL;                  //session ID
	private $user_id = 0;                       //当前登录的用户ID
	private $favourable_difference = 0;         //辅助计算不满足优惠活动金额下限的差额
	
	public $choose_rec_id = NULL;               //选择的购物车ID，这里的ID只有普通商品ID，不包括优惠活动
	public $bonus_money = 0;                    //本次下单使用的红包金额
	
	public function __construct(){
		$this->sessionId = session_id();  //获取当前session ID
		$this->user_id = D('Home/OrderInfo')->getUser('user_id');
    }
	
    public function Favourable($data){
        $this->__construct();
    }
    
	/*
	*	检查优惠活动
	*	@Author 9009123 (Lemonice)
	*	@param  array  $cart_list  购物车列表，二维数组
	*	@return array
	*/
	public function check($cart_list = array(), $rec_id = ''){
		$result = array(  //返回的结果
			'status'=>1,  //1=成功，0=失败
			'msg'=>'Success',
			'data'=>array()
		);
		$total_discount = 0;
		$gift_num = 0;
		$gift_prices = array();
		$gift_keys = array();
		
		if($rec_id != '' && is_null($this->choose_rec_id)){
			$this->choose_rec_id = $rec_id;  //储存下购物车选择的ID
		}
		
		$check_favourable = $this->checkHandle();  //检查活动、优惠规则
		
		if (isset($check_favourable['error']) && $check_favourable['error'] == 1 && !empty($check_favourable['data'])) {
			$result['status'] = 0;
			$result['msg'] = '购物车中存在商品：['.implode(']、[', $check_favourable['data']).'] 不满足优惠活动条件，无法完成购买，请联系客服！';
			return $result;
		}
		
		$full_minus_act_ids = $this->getActIdByType(FAT_FULL_MINUS);   //获取所有满立减活动id
		
		$GoodsModel = D('Home/Goods');
		/* 格式化价格 */
		foreach ($cart_list as $key => $value){
			if($value['extension_code'] == 'exchange_goods'){
				//读取商品积分
				$exchange_integral = D('Home/ExchangeGoods')->where("goods_id='" .$value['goods_id']. "'")->getField('exchange_integral');
				$value['goods_price'] = $this->valueOfIntegral($exchange_integral);
				$value['amount'] = $value['goods_price'] * $value['goods_number'];
				$cart_list[$key]['goods_price'] =  $this->valueOfIntegral($exchange_integral)  ;
			}
			//获取套装的图片
			if($value['extension_code'] == 'package_buy'){
				$GoodsModel->field('g.goods_thumb,g.goods_img,g.original_img');
				$imgs = $GoodsModel->alias(' AS g')->join("__GOODS_ACTIVITY__ AS a ON a.goods_id = g.goods_id", 'left')->where("a.act_id = '$value[goods_id]'")->find();
				
				$cart_list[$key]['goods_thumb'] = isset($imgs['goods_thumb']) ? $imgs['goods_thumb'] : '';
				$cart_list[$key]['goods_img']   = isset($imgs['goods_img']) ? $imgs['goods_img'] : '';
				$cart_list[$key]['original_img'] = isset($imgs['original_img']) ? $imgs['original_img'] : '';
			}
	
			if($value['is_gift'] >0){
				$gift_num++;
				$gift_keys[] = $key;
				$gift_prices[$key] = $value['goods_price'];
				if(in_array($value['is_gift'],$full_minus_act_ids)){
					$cart_list[$key]['is_full_minus'] = $value['is_gift'];  //满赠商品标识
				}
			}
	
			//商品折扣
			$cart_list[$key]['discount'] = 0;
			$cart_list[$key]['amount'] = $value['amount'] - $cart_list[$key]['discount'];
			
			$cart_list[$key]['formated_discount'] = priceFormat( $cart_list[$key]['discount'] , false);
			
			$cart_list[$key]['formated_market_price'] = priceFormat($value['market_price'], false);
			$cart_list[$key]['formated_goods_price']  = priceFormat($value['goods_price'], false);
			
			$cart_list[$key]['formated_amount']     = priceFormat($value['amount'] - $cart_list[$key]['discount'], false);
			
		}
		$result['data'] = $cart_list;
		return $result;
	}
	
	/*
	*	完成提交订单时再次检验是否存在不满足优惠活动的购物行为
	*	@Author 9009123 (Lemonice)
	*	@return boolean
	*/
	public function checkHandle(){
		$cart_gift = $this->getCartGift();  //`session_id`='" . $this->sessionId . "' AND `is_gift`!=0 AND `parent_id`=0
		if (empty($cart_gift)) return array(); //这里没有优惠活动商品返回成功
		
		$reslut = array('error'=>0, 'message'=>'success', 'data' => array());
		if(!empty($cart_gift)){
			foreach ($cart_gift as $val) {
				$favourable = $this->info($val['is_gift']);  //取得优惠活动信息
			
				$type = $val['extension_code'] == 'package_buy' ? 'gift_package' : 'gift';
				$check_result = $this->checkInfo($favourable, $type, $val['goods_id'], 0, true);
				
				if ($check_result < 0) {
					$reslut['error'] = 1;
					$reslut['message'] = 'fail';
					$reslut['data'][] = $val['goods_name'];
				}
				
			}
		}
		return $reslut;
	}
	
	/*
	*	取得优惠活动信息
	*	@Author 9009123 (Lemonice)
	*	@param   int     $act_id     活动id
	*	@return boolean
	*/
	public function info($act_id) {
		$row = D('Home/FavourableActivity')->where("act_id = '$act_id'")->find();
		if (!empty($row)){
			$row['start_times'] = $row['start_time'];
			$row['end_times']   = $row['end_time'];
			$row['start_time'] = Time::localDate('Y-m-d H:i:s', $row['start_time']);
			$row['end_time'] = Time::localDate('Y-m-d H:i:s', $row['end_time']);
			$row['formated_min_amount'] = priceFormat($row['min_amount']);  //格式化金额
			$row['formated_max_amount'] = priceFormat($row['max_amount']);  //格式化金额
			$row['gift'] = unserialize($row['gift']);
			$row['gift_package'] = $row['gift_package']==''? array(): unserialize($row['gift_package']);
			if ($row['act_type'] == FAT_GOODS){
				$row['act_type_ext'] = round($row['act_type_ext']);
			}
			$row['conflict_act'] = unserialize( $row['conflict_act'] );
		}
		return $row;
	}
	
	/*
	*	获取满立减信息
	*	@Author 9009123 (Lemonice)
	*	@param array $favourable 满立减优惠活动信息
	*	@param float $price 满立减商品的总额
	*	@return array
	*/
	public function getFullMinusInfo($favourable, $price){
		//格式化优惠活动中的数量与折扣对应关系
		$act_type_ext = explode(',', $favourable['act_type_ext']);
		$ext_num = array();
		if (!empty($act_type_ext)) {
			foreach ($act_type_ext as $val) {
				$nd = explode('|', $val);
				if (count($nd) != 2 || $nd[0] <= 0 || $nd[1] < 0) continue;
				$ext_num[$nd[0]] = $nd[1];
			}
		}
	
		ksort($ext_num);
		$limit = array('disconut_price'=>0,'desc','subtotal'=>0);
		foreach ($ext_num as $k => $v) {
			if ($k <= $price){
				$limit['discount_price'] = $v;
				$limit['desc'] = "指定商品买满￥{$k}立减￥{$v},已优惠￥{$v}";
				$limit['short_desc'] = "满{$k}减{$v}";
				$limit['subtotal'] = $price - $v;
			}
		}
		
		return $limit;
	}
	
	/*
	*	计算积分的价值（能抵多少钱）
	*	@Author 9009123 (Lemonice)
	*	@param   int     $integral   积分
	*	@return float   积分价值
	*/
	public function valueOfIntegral($integral){
		$scale = floatval(D('Home/ShopConfig')->config('integral_scale'));
		return $scale > 0 ? round(($integral / 100) * $scale, 2) : 0;
	}
	
	/*
	*	根据优惠活动类型获取优惠活动id
	*	@Author 9009123 (Lemonice)
	*	@param type int 优惠活动类型
	*	@return array  act_id 数组
	*/
	public function getActIdByType($type){
		$type_list = D('Home/FavourableActivity')->field('act_id')->where("act_type = '$type'")->select();
		$type = array();
		if(!empty($type_list)){
			foreach($type_list as $value){
				$type[] = $value['act_id'];
			}
		}
		return $type;
	}
	
	/*
	*	获得订单需要支付的支付费用
	*	@Author 9009123 (Lemonice)
	*	@param   integer $payment_id
	*	@param   float   $order_amount
	*	@param   mix     $cod_fee
	*	@return float
	*/
	public function payFee($payment_id, $order_amount, $cod_fee = null){
		$pay_fee = 0;
		$payment = D('Home/Payment')->getPayment($payment_id);
		if(!$payment){
			return $pay_fee;
		}
		$rate    = ($payment['is_cod'] && !is_null($cod_fee)) ? $cod_fee : $payment['pay_fee'];
	
		if (strpos($rate, '%') !== false){
			/* 支付费用是一个比例 */
			$val     = floatval($rate) / 100;
			$pay_fee = $val > 0 ? $order_amount * $val /(1- $val) : 0;
		}else{
			$pay_fee = floatval($rate);
		}
	
		return round($pay_fee, 2);
	}
	
	/*
	*	获取购物车的赠品、换购
	*	@Author 9009123 (Lemonice)
	*	@return exit & Json
	*/
	public function getCartGift($parent_id = -1){
		/*$gift_list = array(  //测试的
			0=>array(
				'goods_id'=>151,
				'goods_price'=>110,
				'goods_number'=>1,
				'extension_code'=>'package_buy',
				'is_gift'=>3,
				'parent_id'=>0,
			),
			1=>array(
				'goods_id'=>150,
				'goods_price'=>110,
				'goods_number'=>1,
				'extension_code'=>'',
				'is_gift'=>3,
				'parent_id'=>0,
			),
			2=>array(
				'goods_id'=>64,
				'goods_price'=>110,
				'goods_number'=>1,
				'extension_code'=>'',
				'is_gift'=>3,
				'parent_id'=>0,
			),
			
			
			[rec_id] => t_1
            [user_id] => 100
            [session_id] => 7hlig4ftk137gfani7jpf2d622
            [is_gift] => 420
            [parent_id] => 0
            [goods_id] => 1053
            [goods_price] => 268
            [goods_number] => 3
            [extension_code] => 
            [send_num] => 0
            [goods_sn] => 3201A390
            [goods_name] => 酵素光感肌密礼盒(Chnskin V1.0)
            [market_price] => 0
		);*/
		
		$gift_list = D('Home/Cart')->cartData(array('user_id'=>$this->user_id), true);
		$list = array();
		if(!empty($gift_list)){
			$GoodModel = D('Home/Goods');
			foreach($gift_list as $value){
				if($value['is_gift'] > 0){  //是活动商品
					$goods = $GoodModel->field('goods_thumb,goods_img,original_img')->where("goods_id = '$value[goods_id]'")->find();
					if(!empty($goods)){
						$value['goods_thumb'] = $goods['goods_thumb'];
						$value['goods_img'] = $goods['goods_img'];
						$value['original_img'] = $goods['original_img'];
					}
				}
				if($value['extension_code'] != 'package_goods'){
					$list[] = $value;
				}
			}
		}
		return $list;
	}
	
	/*
	*	添加优惠活动商品的参数合法性检验
	*	@Author 9009123 (Lemonice)
	*	@param array $favourable 优惠活动信息
	*	@param string $type 请求类型
	*	@param int $goods_id 商品id
	*	@param int $num 商品增加数量 减去商品的时候用负值eg:$num = -1
	*	@param boolean $after 如果$after=true表示限制金额包含了优惠的金额否则不包含，
	*	主要用于最后订单提交的合法检验即check_favourable_again方法,添加商品到购物车时$after=false
	*	@return int 如果检验不通过则返回＜0的错误编码，
	*	如果检验通过则返回1(FAT_BUY_DISCOUNT类型时返回折扣率)
	*/
	public function checkInfo($favourable, $type, $goods_id, $num = 1, $after = FALSE){
		if (empty($favourable) || empty($type) || empty($goods_id)) {
			return -1; //参数错误
		}
		
		$all_act_type = array(FAT_GOODS,FAT_BUY_ADD,FAT_BUY_PRICE,FAT_BUY_NUM,FAT_BUY_DISCOUNT,
			FAT_BUY_NUM_DERATE,FAT_BUY_ONLINE_PAYMENT,FAT_GIFT_BONUS,FAT_FULL_GIFT_BONUS,FAT_SNATCH,FAT_PICKS,FAT_LIMITED_BUY,FAT_FULL_MINUS);
		
		if (!in_array($favourable['act_type'], $all_act_type)) {
			return -2; //优惠活动类型有误
		}
	
		//判断活动是否开始
		$new_time = Time::gmTime();
		if ($new_time < $favourable['start_times']) {
			return -3; //活动未开始
		}
	
		//判断活动是否结束
		if ($new_time > $favourable['end_times']) {
			return -4; //活动已截止
		}
	
		//判断是否有权限参与该活动
		$uid = $this->user_id > 0 ? '1' : '0';
		if (strpos($favourable['user_rank'], $uid) === false) {
			return -5; //无权参与该活动
		}
		
		//判断是否有不能同时进行的优惠活动冲突
		if ($this->checkConflictAct($favourable['conflict_act'])) {
			return -6; //购物车中存在优惠活动冲突
		}
	
		//判断是否满足了优惠范围
		if (!$this->checkActRange($favourable)) {
			return -7; //不满足优惠活动范围
		}
	
		//检查是否有购买含有指定字符的正价商品
		if (!$this->checkContainString($favourable['contain_str'])) {
			return -8; //没有购买含有指定字符的正价商品
		}
		
		//检查提交的单品或者套装是否是优惠活动中的商品，或者是否满足金额上下限
		if (in_array($type, array('gift','gift_package'))) {
			$gift_goods = $favourable[$type];
			$pmin = $pmax = '';
			foreach ($gift_goods as $val) {
				if ($val['id'] == $goods_id) {
					$pmin = floatval($val['pmin']);
					$pmax = floatval($val['pmax']);
					break;
				}
			}
		
			if ($pmin === '') {
				return -11; //提交的商品id不是优惠活动中的商品
			}
			if ($pmin > 0 || $pmax > 0) {
				$restrict_amount = $this->getRestrictAmount($favourable['act_id'], $after);
	
				if ($pmin > 0 && $restrict_amount < $pmin) {
					$this->favourable_difference = $pmin - $restrict_amount;
					return -12; //提交的商品id不满足优惠活动中该商品的金额下限
				}
				if ($pmax > 0 && $restrict_amount > $pmax) {
					return -13; //提交的商品id不满足优惠活动中该商品的金额上限
				}
			}
		
		}
		
		//检查是否满足金额上下限限制
		$min_amount = floatval($favourable['min_amount']);
		$max_amount = floatval($favourable['max_amount']);
		if ($min_amount > 0 || $max_amount > 0) {
			$restrict_amount = $this->getRestrictAmount($favourable['act_id'], $after);  //获取除了不计入优惠活动金额限制的商品价值
			if ($min_amount > 0 && $restrict_amount < $min_amount) {
				$this->favourable_difference = $min_amount - $restrict_amount;
				return -9; //购买金额未达优惠活动中限定金额下限
			}
			
			if ($max_amount > 0 && $restrict_amount > $max_amount) {
				return -10; //购买金额超过优惠活动中限定金额上限
			}
		}
		
		//优惠活动范围赠品是单品的情况下判断提交的id是否是单品
		if ($type == 'gift_range') {
			if ($favourable['gift_range'] == 0 
				|| ($favourable['gift_range'] == 1 && !$this->isSingleGoods($goods_id))) {
				return -14; //提交的商品id优惠活动中赠品的范围
			}
		}
	
		if($favourable['stock_limited'] == 1){  //如果优惠活动限制了库存
			$goods_stock = $this->getGoodsStock($type, $goods_id);
			$cart_goods_num = $this->getCartGoodsNumber($type,$goods_id,$favourable['act_id']);  //购物传车中产品的数量
			if($goods_stock === false || $cart_goods_num === false){
				return -1;
			}elseif($goods_stock === 0){
				return -22; //产品已抢空
			}elseif($goods_stock < $cart_goods_num + $num){
				return -23;  //产品库存不足
			}
		}
		
		//根据不同的优惠品选购限制方式分别判断
		switch ($favourable['act_type']) {
			case FAT_GIFT_BONUS :
			case FAT_FULL_GIFT_BONUS :  
			case FAT_GOODS : // 默认赠送方式
				$buyed_num = $this->getActGoodsNum($favourable['act_id']);  //获取最多可以购买的数量
				$limit_num = $favourable['act_type_ext'];
				if ($limit_num > 0 && $buyed_num + $num > $limit_num) {
					return -15; //购买优惠品数量超过活动的限定数
				}
				
				break;
			case FAT_SNATCH :
				$buyed_num = $this->getActGoodsNum($favourable['act_id']);
				$limit_num = $favourable['act_type_ext'];
				if ($limit_num > 0 && $buyed_num + $num > $limit_num) {
					return -20; //购买优惠品数量超过活动的限定数(限时抢购)
				}
				break;
			case FAT_PICKS :  
				$buyed_num = $this->getActGoodsNum($favourable['act_id']);
				$limit_num = $favourable['act_type_ext'];
				if ($limit_num > 0 && $buyed_num + $num > $limit_num) {
					return -21; //购买优惠品数量超过活动的限定数(精选特卖)
				}
				break;
			case FAT_BUY_ONLINE_PAYMENT : // 在线支付送优惠品
				$buyed_num = $this->getActGoodsNum($favourable['act_id']);
				$limit_num = $favourable['act_type_ext'];
				if ($limit_num > 0 && $buyed_num + $num > $limit_num) {
					return -151; //购买优惠品数量超过活动的限定数
				}
				break;
			case FAT_LIMITED_BUY://限量抢购
				$goods_stock = $this->getGoodsStock($type, $goods_id);
				$cart_goods_num = $this->getCartGoodsNumber($type,$goods_id,$favourable['act_id']);  //购物传车中产品的数量
				if($goods_stock === false || $cart_goods_num === false){
					return -1;
				}elseif($goods_stock === 0){
					return -22; //产品已抢空
				}elseif($goods_stock < $cart_goods_num + $num){
					return -23;  //产品库存不足
				}
				$buyed_num = $this->getActGoodsNum($favourable['act_id']);
				$limit_num = $favourable['act_type_ext'];
				if ($limit_num > 0 && $buyed_num + $num > $limit_num) {
					return -15; //购买优惠品数量超过活动的限定数
				}
				break;
			case FAT_BUY_ADD : // 递增方式（对应商品进行自增：买一送一）
				break;
			case FAT_FULL_MINUS ://满立减
				break;
			case FAT_BUY_PRICE : // 享受等价选购（受订购商品金额限制）
				$restrict_amount = $this->getRestrictAmount($favourable['act_id'], $after);
				$limit_amount = $this->getActTypeExtLimit($favourable, $restrict_amount);
				if ($limit_amount == '') {
					return -16; //不满足最低订购条件
				}
				$act_amount = $this->getActGoodsAmount($favourable['act_id']);
				$goods_price = $this->getActGoodsPrice($favourable, $type, $goods_id);
				if ($act_amount + $goods_price * $num > $limit_amount) {
					return -17; //选择优惠品价值超过优惠活动订购条件
				}
				break;
			case FAT_BUY_NUM : // 享受限量选购（受订购商品金额限制）
				$restrict_amount = $this->getRestrictAmount($favourable['act_id'], $after);
				$limit_num = $this->getActTypeExtLimit($favourable, $restrict_amount);
				if ($limit_num == '') {
					return -18; //不满足最低订购条件
				}
				$act_goods_num = $this->getActGoodsNum($favourable['act_id']);
				if ($act_goods_num + $num > $limit_num) {
					return -19; //选择优惠品数量超过优惠活动订购条件
				}
				break;
			case FAT_BUY_DISCOUNT : // 享受折扣选购（受订购数量影响）
				$act_goods_num = $this->getActGoodsNum($favourable['act_id']);
				$limit_num = $this->getActTypeExtLimit($favourable, $act_goods_num + $num);
				return $limit_num == '' ? 1 : round($limit_num/10,2); //返回相应折扣
				break;
			case FAT_BUY_NUM_DERATE : // 享受计件折扣或减免（受订购数量影响）
				$act_goods_num = $this->getActGoodsNum($favourable['act_id']);
				$limit_num = $this->getActTypeExtLimit($favourable, $act_goods_num + $num);
				return $limit_num == '' ? 1 : round($limit_num/10,2); //返回相应折扣
				break;
			default :
				return -2; //优惠活动类型有误
		}
		
		return 1;
	}
	
	/*
	*	获取提交的商品id在优惠活动中的价格
	*	@Author 9009123 (Lemonice)
	*	@param array $favourable 优惠活动信息
	*	@param string $type 类型
	*	@param int $goods_id 商品id
	*	@return boolean|Ambigous <number, boolean>
	*/
	function getActGoodsPrice($favourable, $type, $goods_id){
		if (!in_array($type, array('gift','gift_package','gift_range'))) {
			return false;
		}
		$goods_id = intval($goods_id);
		$price = 0;
		if ($type == 'gift_range') {
			if (strpos($favourable['gift_range_price'], '%')) {
				$shop_price = D('Home/Goods')->where("goods_id = '$goods_id' AND is_on_sale = 1 AND is_delete = 0")->getField('shop_price');
				$discount = floatval(trim(str_replace('%', '', $shop_price)));
				$price = floor($shop_price * $discount / 100);
			}else{
				$price = $favourable['gift_range_price'];
			}
		}else{
			$gift_goods = $favourable[$type];
			foreach ($gift_goods as $val) {
				if ($val['id'] == $goods_id) {
					$price = $val['price'];
					break;
				}
			}
		}
	
		return $price;
	}
	
	/*
	*	获取对应优惠活动id内购物车的商品总额
	*	@Author 9009123 (Lemonice)
	*	@param int $act_id 活动id
	*	@return number
	*/
	public function getActGoodsAmount($act_id){
		$goods_money = 0;  //金额
		//获取所有换购和赠品
		$gift_list = $this->getCartGift();
		if(!empty($gift_list)){
			foreach($gift_list as $gift){
				if($gift['is_gift'] == $act_id && $gift['extension_code'] != 'package_goods' && $gift['parent_id'] == 0){
					$goods_money += $gift['goods_price'] * $gift['goods_number'];  //把所有符合条件的赠品都累加数量
				}
			}
		}
		return empty($goods_money) ? 0 : $goods_money;
	}
	
	/*
	*	获取优惠活动选购类型的对应数值
	*	@Author 9009123 (Lemonice)
	*	@param array $favourable 优惠活动信息
	*	@param number $num
	*	@return Ambigous <string, number>
	*/
	public function getActTypeExtLimit($favourable, $num){
		//格式化优惠活动中的数量与折扣对应关系
		$act_type_ext = explode(',', $favourable['act_type_ext']);
		$ext_num = array();
		if (!empty($act_type_ext)) {
			foreach ($act_type_ext as $val) {
				$nd = explode('|', $val);
				if (count($nd) != 2 || $nd[0] <= 0 || $nd[1] < 0) continue;
				$ext_num[$nd[0]] = $nd[1];
			}
		}
	
		ksort($ext_num);
		$limit = '';
		foreach ($ext_num as $k => $v) {
			if ($k <= $num) $limit = $v;
		}
		
		return $limit;
	}
	
	/*
	*	获取对应优惠活动id内购物车的商品数
	*	@Author 9009123 (Lemonice)
	*	@param int $act_id 活动id
	*	@return number
	*/
	public function getActGoodsNum($act_id){
		$act_id = intval($act_id);
		$goods_number = 0;  //数量
		//获取所有换购和赠品
		$gift_list = $this->getCartGift();
		if(!empty($gift_list)){
			foreach($gift_list as $gift){
				if($gift['is_gift'] == $act_id && $gift['extension_code'] != 'package_goods' && $gift['parent_id'] == 0){
					$goods_number += $gift['goods_number'];  //把所有符合条件的赠品都累加数量
				}
			}
		}
		
		return empty($goods_number) ? 0 : $goods_number;
	}
	
	/*
	*	获取购物车中指定优惠活动的产品数量
	*	@Author 9009123 (Lemonice)
	*	@param string $type 商品类型 'gift','gift_package'
	*	@param int $id   商品id，套装id
	*	@param $act_id 活动id
	*	@return int 商品数量
	*/
	public function getCartGoodsNumber($type,$id,$act_id){
		if(empty($type) || !in_array($type,array('gift','gift_package')) || empty($id) || empty($act_id)){
			return false;
		}
		$goods_number = 0;  //数量
		//获取所有换购和赠品
		$gift_list = $this->getCartGift();
		if(!empty($gift_list)){
			foreach($gift_list as $gift){
				if($gift['is_gift'] == $act_id && $gift['goods_id'] == $id && $gift['parent_id'] == 0){
					if(($type == 'gift' && $gift['extension_code'] == '') || ($type != 'gift' && $gift['extension_code'] == 'package_buy')){
						$goods_number += $gift['goods_number'];  //把所有符合条件的赠品都累加数量
					}
				}
			}
		}
		return intval($goods_number);
	}
	
	/*
	*	获取商品库存
	*	@Author 9009123 (Lemonice)
	*	@param string $type 商品类型 'gift','gift_package'
	*	@param int $id   商品id，套装id
	*	@return mix
	*/
	public function getGoodsStock($type,$id){
		if(empty($type) || !in_array($type,array('gift','gift_package')) || empty($id)){
			return false;
		}
		$GoodsModel = D('Home/Goods');
		if($type == 'gift'){
			$goods_number = $GoodsModel->where("goods_id = '$id'")->getField('goods_number');
		}else{
			$goods_number = $GoodsModel->alias(' AS g')->join("__GOODS_ACTIVITY__ AS ga ON ga.goods_id = g.goods_id", 'left')->where("ga.act_id  = '$id'")->getField('g.goods_number');
		}
		return intval($goods_number);
	}
	
	/*
	*	判断一个商品id是否是单品
	*	@Author 9009123 (Lemonice)
	*	@param int $goods_id 商品id
	*	@return boolean
	*/
	public function isSingleGoods($goods_id){
		$goods_id = intval($goods_id);
		$goods_id = D('Home/Goods')->where("goods_id = '$goods_id' and is_package = 0")->getField('goods_id');
		return ($goods_id ? true : false);
	}
	
	/*
	*	获取除了不计入优惠活动金额限制的商品价值
	*	@Author 9009123 (Lemonice)
	*	@return number
	*/
	public function getRestrictAmount($act_id, $after = FALSE){
		//先计算出不计入优惠活动金额限制活动id
		$new_time = Time::gmTime();
		$FavourableActivity = D('Home/FavourableActivity');
		$data = $FavourableActivity->field('act_id')->where("`start_time`<='".$new_time."' AND `end_time`>='".$new_time."' AND `is_join_amount` = 0")->select();
		
		$no_count = array();
		if(!empty($data)){
			foreach($data as $value){
				$no_count[] = $value['act_id'];  //把ID放进数组
			}
		}
		
		$where = array();  //查询条件
		
		$act_id = intval($act_id);
		if (!empty($act_id) && !in_array($act_id, $no_count)) {
			$no_count[] = $act_id;  //把活动ID加入进去
		}
		//购物车有选择才加入
		if(!is_null($this->choose_rec_id) && !empty($this->choose_rec_id)){
			$where[] = "rec_id IN(".$this->choose_rec_id.")";
		}
		
		if (!empty($no_count)) {
			$where[] = "is_gift NOT IN(".implode(',', $no_count).")";
		}
		if($this->user_id > 0){
			$where[] = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
		}else{
			$where[] = "session_id = '".$this->sessionId."'";
		}
		
		$where[] = "extension_code != 'package_goods'";
		
		//再计算除了不计入优惠活动金额限制的活动所购买的商品的价值
		$amount = D('Home/Cart')->where(implode(' AND ', $where))->field('SUM(`goods_price` * `goods_number`) as sum')->find();
		$amount = isset($amount['sum']) ? intval($amount['sum']) : 0;
		
		//获取活动商品，并且把能计入价格的都计算
		$gift = D('Home/Cart')->tempGift();
		
		if(!empty($gift)){
			foreach($gift as $value){
				if(isset($value['goods_price']) && $value['goods_price'] > 0 && isset($value['is_gift']) && $value['is_gift'] > 0){
					$is_join_amount = $FavourableActivity->where("act_id = '$value[is_gift]'")->getField('is_join_amount');
					if($is_join_amount == 1){  //可以计入活动计算的价格
						$amount += $value['goods_price'] * $value['goods_number'];
					}
				}
			}
		}
		
		// if ($after && $amount > 0) {
		// 	if ($this->user_id > 0) {  //当前有登录
		// 		$amount = $amount - $this->memberPreferential($this->user_id);
		// 	}
			
		// 	if ($this->bonus_money > 0) {
		// 		$amount = $amount - $this->bonus_money;
		// 	}
		// }
		
		return $amount > 0 ? $amount : 0;
	}
	
	/*
	*	根据订单判断是否可以享受某优惠活动
	*	@Author 9009123 (Lemonice)
	*	@param   array   $favourable     优惠活动信息
	*	@param   int     $order_id       订单ID
	*	@return bool
	*/
	public function available($favourable , $chekc_act_type = true){
		$time = Time::gmTime();
		$result = D('Home/FavourableActivity')->field('act_id')->where("start_time < '$time' AND end_time > '$time' AND is_join_amount = 1")->select();
		$fav_id = array();
		if(!empty($result)){
			foreach($result as $key=>$value){
				$fav_id[] = $value['act_id'];
			}
		}
		$fav_id[] = 0;
		
		// 优惠范围内的商品总额	
		$amount = $this->orderFavourableAmount($favourable, $fav_id);
		if ($amount === false){
			return false;
		}
		//判断是否已参与自己限制的优惠活动
		if ($this->orderOtherFavLimit($favourable, $fav_id) === false){
			return false;
		}
		
		//判断优惠范围
		if ($this->orderActRangeLimit($favourable,$fav_id) === false){
			return false;
		}
		
		if ($chekc_act_type == false){
			return $amount;//不作以下判断,返回用于计算活动条件的金额
		}
		
		//判断优惠品选购限制
		if ($this->orderActTypeLimit($favourable, $amount) === false){
			return false;
		}
		
		return true;
	}
	
	/*
	*	获取活动商品的购买数量
	*	@Author 9009123 (Lemonice)
	*	@param   int   $act_id     活动ID
	*	@return string
	*/
	public function getGiftNumber($act_id){
		$gift_list = $this->getCartGift();
		$number = 0;
		if(!empty($gift_list)){
			foreach($gift_list as $gift){
				if($gift['extension_code'] != 'package_goods' && $gift['is_gift'] == $favourable['act_id']){
					$number += $gift['goods_number'];
				}
			}
		}
		return $number;
	}
	
	/*
	*	取得优惠品选购限制
	*	@Author 9009123 (Lemonice)
	*	@param   array   $favourable     优惠活动
	*	@return string
	*/
	public function orderActTypeLimit($favourable , $amount = 0,$add_fav_goods = array()){
		$CartModel = D('Home/Cart');
		//全部商品
		if ($favourable['act_type'] == FAT_GOODS || $favourable['act_type'] == FAT_GIFT_BONUS || $favourable['act_type'] == FAT_FULL_GIFT_BONUS){
			if ($favourable['act_type_ext'] == 0){
				return true;
			}
			$num = $this->getGiftNumber();
			//添加新的优惠时处理
			if ($add_fav_goods){
				$num = $num + count($add_fav_goods['gift']) + count($add_fav_goods['gift_package']);
				$num = $num - 1;//如果相等减一满足下面的条件
			}
			if ($num < $favourable['act_type_ext']){
				return true;
			}
			return false;
		}
		//递增方式（对应商品进行自增：买一送一）
		if ($favourable['act_type'] == FAT_BUY_ADD){
			return false;
		}
		//享受单品等价选购（受订购商品金额限制）
		if ($favourable['act_type'] == FAT_BUY_PRICE){
			if ($favourable['act_type_ext'] == '') return false;
			$act_type_ext = explode(',',$favourable['act_type_ext']);
			$CartModel->alias(' AS c')->join("__GOODS__ AS g ON c.goods_id = g.goods_id", 'left');
			if($this->user_id > 0){
				$session_where = "(c.session_id = '".$this->sessionId."' OR c.user_id = '".$this->user_id."')";
			}else{
				$session_where = "c.session_id = '".$this->sessionId."'";
			}
			$price = $CartModel->where($session_where . " AND c.extension_code <> 'package_goods' AND c.is_gift = '".$favourable['act_id']."' AND c.goods_id=g.goods_id")->field('SUM(g.shop_price * c.goods_number) as sum')->find();
			$price = $price['sum'];
			//添加新的优惠时处理
			if ($add_fav_goods['gift_package']){
				return false;//不支持套装
			}
			if ($add_fav_goods['gift']){
				$new_price = D('Home/Goods')->where("goods_id IN(".implode(',',$add_fav_goods['gift']).")")->field('SUM(shop_price) as sum')->find();
				$price += $new_price['sum'];
				$price = $price - 1;//如果相等减一满足下面的条件
			}
			foreach ($act_type_ext as $val){
				$val =  explode('|',$val);
				if ($amount < $val[0]) continue;//如果购买金额小于指定换购区间，跳过
				if ($price < $val[1]) return true;//如果换购的商品原价总额未超过换购限制，可享受
			}
			return false;
		}
		//享受限量选购（受订购商品金额限制）
		if ($favourable['act_type'] == FAT_BUY_NUM){
			if ($favourable['act_type_ext'] == '') return false;
			$act_type_ext = explode(',',$favourable['act_type_ext']);
			$num = $this->getGiftNumber();
			//添加新的优惠时处理
			if ($add_fav_goods){
				$num = $num + count($add_fav_goods['gift']) + count($add_fav_goods['gift_package']);
				$num = $num - 1;//如果相等减一满足下面的条件
			}
			foreach ($act_type_ext as $val){
				$val =  explode('|',$val);
				if ($amount < $val[0]){
					continue;//如果购买金额小于指定换购区间，跳过
				}
				if ($num < $val[1]){
					return true;//如果换购的商品数量未超过换购限制，可享受
				}
			}
			return false;
		}
	
	}
	
	/*
	*	判断优惠范围
	*	@Author 9009123 (Lemonice)
	*	@param   array   $favourable     优惠活动
	*	@return float
	*/
	public function orderActRangeLimit($favourable, $fav_id=array()){
		//全部商品
		if ($favourable['act_range'] == FAR_ALL){
			return true;
		}
		$CartModel = D('Home/Cart');
		//获取所有换购和赠品，赠品价格=0，换购的商品价格>0，需要计算入总金额
		$gift_list = $this->getCartGift();
		
		//全部套装
		if ($favourable['act_range'] == FAR_ALL_PACKAGE){
			//检查赠品和活动是否符合
			if(!empty($gift_list)){
				foreach($gift_list as $gift){
					if($gift['extension_code'] == 'package_buy' && in_array($gift['is_gift'],$fav_id)){
						return true;  //可享受
					}
				}
			}
			if(!in_array(0,$fav_id)){
				return false;
			}
			//检查普通商品是否符合
			if($this->user_id > 0){
				$session_where = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
			}else{
				$session_where = "session_id = '".$this->sessionId."'";
			}
			$count = $CartModel->where($session_where . " AND extension_code = 'package_buy' AND is_gift IN(".implode(',',$fav_id).")")->count();
			if ($count > 0){
				return true;  //可享受
			}
			return false;
		}
		//按分类选择
		if ($favourable['act_range'] == FAR_CATEGORY){
			if (empty($favourable['act_range_ext'])){
				return false;   //没有定义分类，全屏蔽
			}
			//检查赠品和活动是否符合
			if(!empty($gift_list)){
				foreach($gift_list as $gift){
					if($gift['extension_code'] != 'package_buy' && in_array($gift['is_gift'],$fav_id)){
						$cat_id = D('Home/Goods')->where("goods_id = '$gift[goods_id]'")->getField('cat_id');
						if($cat_id > 0 && in_array($cat_id,$favourable['act_range_ext'])){
							return true;  //可享受
						}
					}
				}
			}
			if(!in_array(0,$fav_id)){
				return false;
			}
			//检查普通商品是否符合
			if($this->user_id > 0){
				$session_where = "(c.session_id = '".$this->sessionId."' OR c.user_id = '".$this->user_id."')";
			}else{
				$session_where = "c.session_id = '".$this->sessionId."'";
			}
			$CartModel->alias(' AS c')->join("__GOODS__ AS g ON c.goods_id = g.goods_id", 'left');
			$count = $CartModel->where($session_where . " AND c.extension_code != 'package_buy' AND c.is_gift IN(".implode(',',$fav_id).") AND g.cat_id IN (".$favourable['act_range_ext'].")")->count();
			if ($count > 0){
				return true;  //可享受
			}
			return false;
		}
		//按商品选择
		if ($favourable['act_range'] == FAR_GOODS){
			if (empty($favourable['act_range_ext'])){
				return false;//没有定义商品，全屏蔽
			}
			//检查赠品和活动是否符合
			if(!empty($gift_list)){
				foreach($gift_list as $gift){
					if($gift['extension_code'] != 'package_buy' && in_array($gift['goods_id'],$favourable['act_range_ext'])){
						return true;  //可享受
					}
				}
			}
			if($this->user_id > 0){
				$session_where = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
			}else{
				$session_where = "session_id = '".$this->sessionId."'";
			}
			$count = $CartModel->where($session_where . " AND extension_code != 'package_buy' AND goods_id IN (".$favourable['act_range_ext'].")")->count();
			if ($count > 0){
				return true;//可享受
			}
			return false;
		}
		//按套装选择
		if ($favourable['act_range'] == FAR_PACKAGE){
			if (empty($favourable['act_range_ext'])){
				return false;//没有定义套装，全屏蔽
			}
			
			//检查赠品和活动是否符合
			if(!empty($gift_list)){
				foreach($gift_list as $gift){
					if($gift['extension_code'] == 'package_buy' && in_array($gift['goods_id'],$favourable['act_range_ext'])){
						return true;  //可享受
					}
				}
			}
			if($this->user_id > 0){
				$session_where = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
			}else{
				$session_where = "session_id = '".$this->sessionId."'";
			}
			$count = $CartModel->where($session_where . " AND extension_code = 'package_buy' AND goods_id IN (".$favourable['act_range_ext'].")")->count();
			if ($count > 0){
				return true;//可享受
			}
			return false;
		}
		return false;
	}
	
	/*
	*	判断是否已参与自己限制的优惠活动
	*	@Author 9009123 (Lemonice)
	*	@param   array   $favourable     优惠活动
	*	@return float
	*/
	public function orderOtherFavLimit($favourable , $fav_id=array()){
		//无限直接返回真值
		if (empty($favourable['conflict_act'])){
			return true;
		}
		foreach ($favourable['conflict_act'] as $clist){
			$act_id[] = $clist['act_id'];
		}
		
		$count = 0;
		//获取所有换购和赠品，赠品价格=0，换购的商品价格>0，需要计算入总金额
		$gift_list = $this->getCartGift();
		if(!empty($gift_list)){
			foreach($gift_list as $gift){
				if(in_array($gift['is_gift'],$act_id)){
					$count += 1;
					break;
				}
			}
		}
		if ($count < 1){
			return true;//可享受
		}
		return false;
	}
	
	/*
	*	取得订单中某优惠活动范围内的总金额
	*	@Author 9009123 (Lemonice)
	*	@param   array   $favourable     优惠活动
	*	@return float
	*/
	public function orderFavourableAmount($favourable ,$fav_id=array()){
		/* 优惠范围内的商品总额 */
		if(in_array(0,$fav_id)){  //普通商品
			if($this->user_id > 0){
				$session_where = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
			}else{
				$session_where = "session_id = '".$this->sessionId."'";
			}
			$amount = D('Home/Cart')->where($session_where . " AND extension_code != 'package_goods' AND is_gift IN(".implode(',',$fav_id).")")->field('SUM(goods_price * goods_number) as sum')->find();
			$amount = intval($amount['sum']);
		}else{
			$amount = 0;
		}
		
		//获取所有换购和赠品，赠品价格=0，换购的商品价格>0，需要计算入总金额
		$gift_list = $this->getCartGift();
		if(!empty($gift_list)){
			foreach($gift_list as $gift){
				if($gift['extension_code'] != 'package_goods' && in_array($gift['is_gift'],$fav_id)){
					if(!isset($gift['send_num']) || $gift['goods_number'] > $gift['send_num']){
						$amount += $gift['goods_price'] * ($gift['goods_number'] - $gift['send_num']);
					}
				}
			}
		}
		$amount = (!$amount||empty($amount))?0:$amount;
		if ($amount >= $favourable['min_amount'] && ($amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0)){
			return $amount;//价格符合
		}
		return false;
	}
	
	/*
	*	正价商品会员优惠金额
	*	@Author 9009123 (Lemonice)
	*	@param int $user_id 会员id
	*	@return float 优惠的金额
	*/
	public function memberPreferential($user_id = 0, $is_return_discount = false){
		$user_id = $user_id > 0 ? $user_id : $this->user_id;
		if ($user_id <= 0){
			return 0; 
		}
		//会员商品9.5折
		$discount = 100;
		$params = array();
		$params['userid'] = $user_id;	
		
		$rank = D('Home/UserAccount')->where("user_id = '".$this->user_id."'")->getField('rank');  //获取会员等级
		
		$discount = D('Home/UserRank')->where("rank_id = '".$rank."'")->getField('discount');  //获取折扣
		if(!$discount){  //找不到折扣，可能是这个会员数据出问题了，找一个最低等级的做默认
			$discount = D('Home/UserRank')->order('min_points asc')->getField('discount');
			if(!$discount){
				$discount = 100;
			}
		}
		$preferential = 1 - round($discount / 100, 2);	
		
		//是否直接返回折扣
		if($is_return_discount == true){
			return $preferential;
		}
		
		//获取购物车所有正价商品（普通商品）
		if($this->user_id > 0){
			$session_where = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
		}else{
			$session_where = "session_id = '".$this->sessionId."'";
		}
		$amount = D('Home/Cart')->where($session_where . " AND `is_gift`=0")->field('SUM(`goods_price`*`goods_number`) as sum')->find();
		$amount = !$amount['sum'] ? 0 : $amount['sum'];
		//获取所有换购和赠品，赠品价格=0，换购的商品价格>0，需要计算入总金额
		/*$gift_list = $this->getCartGift();
		if(!empty($gift_list)){
			foreach($gift_list as $gift){
				if($gift['extension_code'] != 'package_goods'){
					$amount += $gift['goods_price'] * $gift['goods_number'];
				}
			}
		}*/
		return floor($amount * $preferential);  //floor: 舍去小数点
	}
	
	/*
	*	检查是否购买含有指定字符的正价商品
	*	@Author 9009123 (Lemonice)
	*	@param string $contain_str 后台设定的必须含有的指定字符
	*	@return boolean
	*/
	public function checkContainString($contain_str){
		if (empty($contain_str)){
			return true;
		}
		//按逗号切割成数组
		$contain = is_string($contain_str)&&$contain_str!='' ? explode(',', trim(str_replace(' ', ',', $contain_str))) : array();
		if (empty($contain)){
			return true;
		}
		//获取购物车的普通商品
		if($this->user_id > 0){
			$session_where = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
		}else{
			$session_where = "session_id = '".$this->sessionId."'";
		}
		$res = D('Home/Cart')->field('goods_name')->where($session_where . " AND `is_gift`=0 AND `parent_id`=0")->select();
		if(!empty($res)){
			foreach ($res as $v) {
				foreach ($contain as $val) {
					//匹配，只要匹配到一个，就说明该活动命中
					if (strpos($v['goods_name'], $val) !== false) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	/*
	*	检查是否有优惠活动冲突
	*	@Author 9009123 (Lemonice)
	*	@param array $conflict_act 后台设置的不能同时进行的优惠活动id组成的数组
	*	@return boolean 有冲突放回true,没有冲突放回false
	*/
	public function checkConflictAct($conflict_act){
		$return = false;
		if (empty($conflict_act) || !is_array($conflict_act)){
			return $return;
		}
		$act_id = array();
		foreach ($conflict_act as $v){
			$act_id[] = intval($v['act_id']);
		}
		$act_id = array_unique($act_id);
		//查看此优惠信息是否存在购物车
		$cart_gift = $this->getCartGift();  //获取购物车的赠品
		if(!empty($cart_gift)){
			foreach($cart_gift as $gift){
				if($gift['is_gift'] > 0 && in_array($gift['is_gift'], $act_id)){
					$return = true;
					break;
				}
			}
		}
		return $return;
	}
	
	/*
	*	检查是否满足优惠活动范围
	*	@Author 9009123 (Lemonice)
	*	@param array $favourable 优惠活动信息
	*	@return boolean
	*/
	public function checkActRange($favourable){
		//全部商品
		if ($favourable['act_range'] == FAR_ALL){
			return true;
		}
		$CartModel = D('Home/Cart');
		//全部套装
		if ($favourable['act_range'] == FAR_ALL_PACKAGE){
			if($this->user_id > 0){
				$session_where = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
			}else{
				$session_where = "session_id = '".$this->sessionId."'";
			}
			$count = $CartModel->where($session_where . " AND `extension_code`='package_buy' AND `is_gift` = 0")->count();
			return $count > 0 ? true : false;
		}
		if (empty($favourable['act_range_ext'])){ //没有定义分类，全屏蔽
			 return false;
		}
		//按分类选择
		if ($favourable['act_range'] == FAR_CATEGORY){
			if($this->user_id > 0){
				$session_where = "(c.session_id = '".$this->sessionId."' OR c.user_id = '".$this->user_id."')";
			}else{
				$session_where = "c.session_id = '".$this->sessionId."'";
			}
			$CartModel->where($session_where . " AND c.`parent_id` = 0 AND c.`is_gift` = 0 AND g.`cat_id` IN (".$favourable['act_range_ext'].")");
			$count = $CartModel->alias(' AS c')->join("__GOODS__ AS g ON c.goods_id = g.goods_id", 'left')->order('c.is_gift,c.rec_id')->count();
			return $count > 0 ? true : false;
		}
		//按商品选择
		if ($favourable['act_range'] == FAR_GOODS){
			if($this->user_id > 0){
				$session_where = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
			}else{
				$session_where = "session_id = '".$this->sessionId."'";
			}
			$count = $CartModel->where($session_where . " AND `extension_code` != 'package_buy' AND `is_gift` = 0 AND `goods_id` IN (".$favourable['act_range_ext'].")")->count();
			return $count > 0 ? true : false;
		}
		//按套装选择
		if ($favourable['act_range'] == FAR_PACKAGE){
			if($this->user_id > 0){
				$session_where = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
			}else{
				$session_where = "session_id = '".$this->sessionId."'";
			}
			$count = $CartModel->where($session_where . " AND `extension_code` = 'package_buy' AND `goods_id` IN (".$favourable['act_range_ext'].")")->count();
			//AND `is_gift` = 0
			return $count > 0 ? true : false;
		}
			
		return false;
	}
}
?>