<?php
/**
 * ====================================
 * 积分操作 类
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-14 17:32
 * ====================================
 * File: Integral.class.php
 * ====================================
 */
namespace Common\Extend\Order;
use Common\Extend\Time;
use Common\Extend\Order\Favourable;
use Common\Extend\Order\Bonus;

class Integral{
	private $sessionId = NULL;                  //session ID
	private $user_id = 0;                       //当前登录的用户ID
	
	public function __construct(){
		$this->sessionId = session_id();  //获取当前session ID
		$this->user_id = D('Home/OrderInfo')->getUser('user_id');
    }
	
    public function Integral(){
        $this->__construct();
    }
	
	/*
	*	取得购物车该赠送的积分数
	*	@Author 9009123 (Lemonice)
	*	@param array  $goods  购物车商品
	*	@return int 积分数
	*/
	public function getGiveIntegral($goods){
		$CartModel = D('Home/Cart');
		
		//获取数据表里面的购物车普通商品
		if($this->user_id > 0){
			$session_where = "(c.session_id = '".$this->sessionId."' OR c.user_id = '".$this->user_id."')";
		}else{
			$session_where = "c.session_id = '".$this->sessionId."'";
		}
		$CartModel->alias(' AS c')->join("__GOODS__ AS g ON c.goods_id = g.goods_id", 'left');
		$CartModel->where($session_where . " and c.goods_id > 0 and c.parent_id = 0 and c.is_gift = 0");
		$integral = $CartModel->field('SUM(c.goods_number * IF(g.give_integral > -1, g.give_integral, c.goods_price)) as sum')->find();
		$integral = $integral['sum'];

		//获取购物车选择的赠品、优惠活动
		$favourableObj = new Favourable();
		$gift = $favourableObj->getCartGift();
		$gift_amount = 0;
	    //过滤套餐下的子商品
		if(!empty($gift)){
			foreach($gift as $value){
				if($value['extension_code'] != 'package_goods' && $value['goods_price'] > 0 && (isset($value['send_num']) ? $value['goods_number']>$value['send_num'] : $value['goods_number']>0)){  //package_goods为子商品, send_num是赠送的数量
					$goods_number = isset($value['send_num']) ? $value['goods_number'] - $value['send_num'] : $value['goods_number'];
					$gift_amount += $value['goods_price'] * $goods_number;
				}
			}
		}
		
		//换购、买一送一等相关活动的金额也是需要算积分
		$integral += intval($gift_amount);
		
		return $integral;
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
	*	计算指定的金额需要多少积分
	*	@Author 9009123 (Lemonice)
	*	@param   integer $value  金额
	*	@return float
	*/
	public function integralOfValue($value){
		$scale = floatval(D('Home/ShopConfig')->config('integral_scale'));
	
		return $scale > 0 ? round($value / $scale * 100) : 0;
	}
}
?>