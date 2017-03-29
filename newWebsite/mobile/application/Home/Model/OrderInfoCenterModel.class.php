<?php
/**
 * ====================================
 * 会员中心 里面的订单详情模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-06 17:28
 * ====================================
 * File: OrderInfoCenterModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CustomizeModel;
use Common\Extend\Time;

class OrderInfoCenterModel extends CustomizeModel{
	protected $_config = 'USER_CENTER';
    protected $_table = 'OrderInfo';
	
	
	/*
	*	获取订单总金额
	*	@Author Lemonice
	*	@param  string $order_sn 订单号码
	*	@return int
	*/
	public function getOrderAmount($order_sn){
		$order_amount = 0;
        $data = $this->field('order_amount,goods_amount,bonus,integral_money,shipping_fee,postscript,discount,money_paid,payment_discount,update_time')->where("order_sn = '$order_sn'")->find();
		
		if(!empty($data)){
			if($data['update_time'] >= Time::localStrtotime('2016-10-18 16:00:00') || $data['integral_money'] > 0){  //$data['integral_money'] > 0是积分兑换
				$order_amount = $data['order_amount'] + $data['money_paid'];
			}else{
				$order_amount = $data['goods_amount'] - $data['bonus'] - $data['integral_money'] + $data['shipping_fee'] - $data['discount'] - $data['payment_discount'];
			}
		}
		return $order_amount;
	}
	
	/*
	*	处理订单的详情信息
	*	@Author Lemonice
	*	@param  array $order 订单详情
	*	@param  bool  $get_goods  是否获取对应商品
	*	@param  bool  $count_bonus  是否统计对应的红包数量、个数
	*	@param  bool  $get_integral 是否获取积分的字段
	*	@return array
	*/
	public function orderFormat($order, $get_goods = false, $count_bonus = false, $get_integral = false){
		if(empty($order)){
			return $order;
		}
		
		if(isset($order['pay_id'])){
			$order['pay_type'] = '';
			if($order['pay_type'] == '' && $order['pay_id'] == 1){  //货到付款
				$order['pay_type'] = '货到付款';
			}
			if($order['pay_type'] == '' && $order['pay_id'] != 1){  //在线付款
				$order['pay_type'] = '在线支付';
			}
		}
        $order_status =  $this->getStatus($order);  //订单状态
		$order['order_name'] = $order_status['order_name'];
        $order['handle']     = $order_status['handle'];


		//下单时间
		if(isset($order['add_time'])){
			$order['add_date'] = $order['add_time'] > 0 ? Time::localDate('Y-m-d H:i:s', $order['add_time']) : '';
		}
		//支付时间
		if(isset($order['pay_time'])){
			$order['pay_date'] = $order['pay_time'] > 0 ? Time::localDate('Y-m-d H:i:s', $order['pay_time']) : '';
		}
		//物流状态
		if(isset($order['shipping_status'])){
			$order['shipping_name'] = $this->shippingStatus($order['shipping_status']);
		}
		//付款状态
		if(isset($order['pay_status'])){
			if($order['pay_id'] == 1){  //货到付款
				$order['pay_status_name'] = '货到付款';
			}else{
				$order['pay_status_name'] = $this->payStatus($order['pay_status']);
			}
		}
		//是否查询订单的商品列表
		if($get_goods == true && isset($order['order_id'])){
			$order['goods_list'] = $this->getGoodsList($order['order_id'], (isset($order['site_id']) ? $order['site_id'] : 0));  //获取商品
		}
		//统计红包数量
		if($count_bonus == true && isset($order['order_id'])){
			$order['bonus_count'] = $this->getBonusCount($order['order_id'], (isset($order['site_id']) ? $order['site_id'] : 0));  //获取红包数量
		}
		
		//计算订单真实金额
		if(isset($order['order_sn'])){
			$order['order_amount'] = $this->getOrderAmount($order['order_sn']);
		}
		
		//处理积分
		if(isset($order['integral'])){
			$order['integral'] = intval($order['integral']);
			$order['integral'] = $order['integral'] > 0 ? '+'.$order['integral'] : $order['integral'];
		}
		
		//处理中奖订单
		if(isset($order['postscript'])){
			$flag = '*#lottery draw*#';  //中奖订单标记
			if(strstr($order['postscript'], $flag) !== false){
				$order['winning'] = 1;  //是中奖订单
			}else{
				$order['winning'] = 0;  //不是中奖订单
			}
			$order['postscript'] = str_replace($flag,'',$order['postscript']);
		}
		
		if(isset($order['pay_name']) && strstr($order['pay_name'],'</')){
			preg_match('/>(.+)<\//',$order['pay_name'],$array);
			$order['pay_name'] = isset($array[1])&&$array[1]!='' ? $array[1] : $order['pay_name'];
		}
		
		if(isset($order['site_id'])){
			unset($order['site_id']);
		}
		return $order;
	}
	
	/*
	*	获取物流状态
	*	@Author Lemonice
	*	@param  int $shipping_status 物流状态
	*	@param  string $default 默认值
	*	@return array
	*/
	public function shippingStatus($shipping_status = 0, $default = '未发货'){
		$name = $default;
		switch($shipping_status){
			case SS_UNSHIPPED:
				$name = '未发货';
			break;
			case SS_SHIPPED:
				$name = '已发货';
			break;
			case SS_RECEIVED:
				$name = '已收货';
			break;
			case SS_PREPARING:  //配货中
			case SS_WAITCHECK:  //配货审核中
			case SS_CHECKBACK:  //配货审核退回
			case SS_DEPOSTUNUSUAL:  //仓库返回异常
			case SS_OVERSTOCK:  //压单
				$name = '配货中';
			break;
			case SS_EXPRESSED:
				$name = '打单';
			break;
			case SS_PRINTPREPARING:
				$name = '已打单';  //已打捡货单
			break;
			case SS_BALE:
				$name = '已打包';
			break;
			case SS_REIURNED:
				$name = '退货已签收';  //退货已签收
			break;
		}
		return $name;
	}
	
	/*
	*	获取物流状态
	*	@Author Lemonice
	*	@param  int $pay_status 付款状态
	*	@return array
	*/
	public function payStatus($pay_status = 0){
		$name = '未付款';
		switch($pay_status){
			case PS_UNPAYED:
				$name = '未付款';
			break;
			case PS_PAYING:
				$name = '付款中';
			break;
			case PS_PAYED:
				$name = '已付款';
			break;
			case PS_PAY_PARI:
				$name = '已支付部分货款';
			break;
			case PS_FPAYED:
				$name = '已付款';  //前台已付款
			break;
			case PS_REFUNDING:
				$name = '退款中';
			break;
			case PS_REFUND:
				$name = '已退款';
			break;
			case PS_UNREFUND:
				$name = '退款失败';
			break;
		}
		return $name;
	}
	
	/*
	*	获得订单商品
	*	@Author Lemonice
	*	@param  int $order_id 订单ID
	*	@param  int $site_id  站点ID
	*	@return array
	*/
    public function getBonusCount($orderId, $site_id = 0){
		$site_id = $site_id > 0 ? $site_id : C('SITE_ID');  //站点ID
		$count = D('UserBonusCenter')->where("order_id='$orderId' AND site_id = $site_id")->count();
        return $count;
    }
	
	/*
	*	获得订单商品
	*	@Author Lemonice
	*	@param  int $orderId 订单ID
	*	@param  int $site_id  站点ID
	*	@return array
	*/
    public function getGoodsList($orderId, $site_id = 0){
		$site_id = $site_id > 0 ? $site_id : C('SITE_ID');  //站点ID
		$goodsList = D('OrderGoodsCenter')->field('goods_id,goods_sn,extension_code, goods_name, goods_number, goods_price, market_price')->where("order_id='$orderId' AND parent_id=0 AND site_id = $site_id")->group('goods_id')->select();
		
		if(!empty($goodsList)){  //package_buy
			$GoodsModel = D('Goods');
			foreach($goodsList as $key=>$value){
				$value['type'] = $value['extension_code']=='package_buy' ? 1 : 0;  //0=单品，1=套餐
				unset($value['extension_code']);
				
				//获取商品图片
				if($value['type'] == 1){  //套装，数据不一样
					$goods_img =  $GoodsModel->field('goods_thumb, goods_img, original_img')->alias('g')->join('LEFT JOIN __GOODS_ACTIVITY__ AS ga  ON g.goods_id=ga.goods_id')->where("act_id = '$value[goods_id]'")->find();
				}else{
					$goods_img = $GoodsModel->field('goods_thumb, goods_img, original_img')->where("goods_id = '$value[goods_id]'")->find();
				}
				
				if(!empty($goods_img)){
					$value = array_merge($value, $goods_img);
					$value['goods_thumb'] = C('domain_source.img_domain').$value['goods_thumb'];
					$value['goods_img'] = C('domain_source.img_domain').$value['goods_img'];
					$value['original_img'] = C('domain_source.img_domain').$value['original_img'];
				}else{
					$value['goods_thumb'] = '';
					$value['goods_img'] = '';
					$value['original_img'] = '';
				}
				
				//修改套装的goods_id为商品表ID
				if($value['type'] == 1){
					$goods_id = D('GoodsActivity')->getPackageGoodsId($value['goods_id']);
					if($goods_id > 0){
						$value['goods_id'] = $goods_id;
					}
				}
				
				$goodsList[$key] = $value;
				
			}
		}
		
        return $goodsList;
    }
	
	/*
	*	获取订单状态
	*	@Author Lemonice
	*	@param  array  $order  订单详情
	*	@return string
	*/
    private function getStatus($order){
        $ret = array('order_name' => '','handle' => 0);
        if (($order['pay_id'] == 1 && $order['shipping_status'] == SS_UNSHIPPED) || $order['order_status'] == OS_CONFIRMED) {  //pay_id=1货到付款
            $ret['order_name'] = '已确认';
        }else if($order['order_status'] == OS_UNCONFIRMED){
			$ret['order_name'] = '未确认';
		}
		if($order['pay_id'] > 1){  //在线支付
			if ($order['pay_status'] != PS_UNPAYED) {
				$ret['order_name'] = $this->payStatus($order['pay_status']);
			}else{
				$ret['order_name'] = '未付款';
				$ret['handle'] = 1;  //显示"重新支付"按钮
			}
			
			//如果没有支付完成的单子，查询已支付的金额
			if($order['pay_status'] != PS_PAYED && $order['pay_status'] < PS_FPAYED){
				//查询已支付的金额，是否显示"重新支付"按钮
				$pay_money = D('PayMultipleLog')->getOrderPayMoney($order['order_sn']);
				if($pay_money < $order['order_amount']){
					$ret['handle'] = 1;  //显示"重新支付"按钮
				}else{
					$ret['order_name'] = '已付款未确认';
					$ret['handle'] = 0;  //不显示"重新支付"按钮
				}
			}
		}
        
		if($order['shipping_status'] != SS_UNSHIPPED){
			$ret['order_name'] = $this->shippingStatus($order['shipping_status']);
			
			if ($order['shipping_status'] == SS_RECEIVED) {  //已签收
				$is_comment = $this->is_comment($order['order_sn'],C('SITE_ID'));
				if(!$is_comment){
					$ret['handle'] = 2;  //表示评价
				}else{
					$ret['handle'] = 3;  //表示已评价
				}
			}
		}
		
		if ($order['order_status'] == OS_RETURNED) {
            $ret['order_name'] = '已退货';
        }else if ($order['order_status'] == OS_CANCELED || $order['order_status'] == OS_INVALID) {
            $ret['order_name'] = '已取消';
			$ret['handle'] = 0;
        }
        return $ret;
    }

    /**
     * 订单是否已评论
     * @param $order_sn
     * @param $site_id
     * @return mixed
     */
	private function is_comment($order_sn,$site_id){
         $r = D('UserCommentCenter')->field('id')->where(array('rid'=>$order_sn,'site_id'=>$site_id))->select();
         if(empty($r)){
             return false;
         }else{
             return true;
         }
    }

	/*
	*	分页处理
	*	@Author Lemonice
	*	@param  string $field 查询的字段
	*	@param  string $where 查询条件
	*	@param  string $order 排序的字段
	*	@param  int $page 当前页，第几页
	*	@param  int $pageSize 每页显示多少条
	*	@param  bool $get_integral 是否获取使用的积分
	*	@return array
	*/
    public function getPage($field = '*', $where = '',$order = '', $page = 1, $pageSize = 0, $get_integral = false) {
		$total = 0;
		$pageTotal = 1;
		
		//是否启用分页
		if($pageSize > 0){
			$total = $this->where($where)->count();  //统计总记录数
			$this->page($page.','.$pageSize);
			$pageTotal = ceil($total / $pageSize);  //计算总页数
		}else{
			$page = 1;
		}
		if($order != ''){
			$this->order($order);
		}
		$list = $this->field($field)->where($where)->select();
		$total = $total > 0 ? $total : count($list);
		if(!empty($list)){
			foreach($list as $key=>$order){
				$list[$key] = $this->orderFormat($order,true,false,$get_integral);
			}
		}
        return array('page' => $page, 'pageSize' => $pageSize, 'total' => (int)$total, 'pageTotal' => $pageTotal, 'list' => $list);
    }
	
	/*
	*	获取订单信息
	*	@Author 9009123 (Lemonice)
	*	@param  $params array  字段参数
	*	@return int [Affected Number]
	*/
    public function getLogisticsOrder($params) {
		$where = array();
        if ($params['order_sn']) {
            $where[] = "order_sn = '{$params['order_sn']}'";
        }
		if($params['mobile']) {
            $where[] = "mobile = '{$params['mobile']}'";
        }
		if($params['invoice_no']) {
            $where[] = "invoice_no = '{$params['invoice_no']}'";
        }
		if(empty($where)){
			return array();
		}
        $ret = $this->where("(" . implode(' OR ',$where) . ") AND order_status = 1 AND shipping_status != 2")->select();
        return $ret;
    }
	
	/*
	*	编辑
	*	@Author 9009123 (Lemonice)
	*	@param  $data array  详情
	*	@return int [Affected Number]
	*/
	public function update($data = array()){
		$this->create($data);
		$result = $this->save();
		return $result==false ? 0 : $result;
	}
	
	/*
	*	插入
	*	@Author 9009123 (Lemonice)
	*	@param  $data array  详情
	*	@return int [insert_id]
	*/
	public function insert($data = array()){
		$this->create($data);
		$insert_id = $this->add();
		return $insert_id;
	}
}