<?php
/**
 * ====================================
 * 优惠券相关操作 类
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-14 17:32
 * ====================================
 * File: Bonus.class.php
 * ====================================
 */
namespace Common\Extend\Order;
use Common\Extend\Time;
use Common\Extend\Order\Order;
use Common\Extend\Order\Favourable;

class Bonus{
	private $sessionId = NULL;                  //session ID
	private $user_id = 0;                       //当前登录的用户ID
	
	public function __construct(){
		$this->sessionId = session_id();  //获取当前session ID
		$this->user_id = D('Home/OrderInfo')->getUser('user_id');
		$this->user_id = $this->user_id ? $this->user_id : 0;
    }
	
    public function Bonus($data){
        $this->__construct();
    }
    
	/*
	*	取得当前购物车用户应该得到的红包总额
	*	@Author 9009123 (Lemonice)
	*	@return int
	*/
	public function getCartTotalBonus(){
		$day    = Time::localGetdate();
		$today  = Time::localMktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
		
		$CartModel = D('Home/Cart');
		$BonusTypeModel = D('Home/BonusType');
		
		/* 按商品发的红包 */
		if($this->user_id > 0){
			$session_where = "(c.session_id = '".$this->sessionId."' OR c.user_id = '".$this->user_id."')";
		}else{
			$session_where = "c.session_id = '".$this->sessionId."'";
		}
		$CartModel->alias(' AS c')->join("__GOODS__ AS g ON c.goods_id = g.goods_id", 'left')->join("__BONUS_TYPE__ AS t ON g.bonus_type_id = t.type_id", 'left');
		$CartModel->where($session_where . " AND c.is_gift = 0 AND t.send_type = '" . SEND_BY_GOODS . "' AND t.send_start_date <= '$today' AND t.send_end_date >= '$today'");
		$goods_total = $CartModel->field('SUM(c.goods_number * t.type_money) as sum')->find();
		$goods_total = floatval($goods_total['sum']);
		
	
		/* 取得购物车中非赠品总金额 */
		if($this->user_id > 0){
			$session_where = "(session_id = '".$this->sessionId."' OR user_id = '".$this->user_id."')";
		}else{
			$session_where = "session_id = '".$this->sessionId."'";
		}
		$amount = floatval($CartModel->where($session_where . " AND is_gift = 0")->getField("SUM(goods_price * goods_number)"));
		
		/* 按订单发的红包 */
		$order_total = floatval($BonusTypeModel->where("send_type = '" . SEND_BY_ORDER . "' AND send_start_date <= '$today' AND send_end_date >= '$today' AND min_amount > 0")->getField("FLOOR('$amount' / min_amount) * type_money"));
	
		return $goods_total + $order_total;
	}
	
	/*
	*	取得购物车内能使用的用户优惠券
	*	@Author 9009123 (Lemonice)
	*	@param  array  $cart_goods  购物车商品
	*	@param  int $page 当前分页
	*	@param  int $pageSize  每页显示多少条,0表示不分页
	*	@return array or bool 当前会员的红包列表，返回false表示不能使用优惠券
	*/
	public function onlinePayment($cart_goods, $page = 1, $pageSize = 0){
		$use_bonus = D('Home/ShopConfig')->config('use_bonus');  //获取是否开启了优惠券
		$valid_bonus = array();	//可使用的红包
		$invalid_bonus = array();	//不可使用的红包
		if ($use_bonus == '1'){
			//获取购物车相关的统计信息
			$cart_statistics = session('cart_statistics');
			
			if(!$cart_statistics){
				$Order = new Order();
				$Order->statistics($cart_goods);
				$cart_statistics = session('cart_statistics');
			}
			
			// 取得用户可用红包
			$bonus_data = array(
				'user_id'=>$this->user_id,
				'cart_amount'=>$cart_statistics['total_price'],
				'is_package'=>$cart_statistics['have_package'],
				'is_gift'=>$cart_statistics['have_gift'],
				'site_id'=>C('SITE_ID'),
				'page'=>$page,
				'pageSize'=>$pageSize
			);
			
			$bonus_list = $this->getUserBonusList($bonus_data, true);  //获取可用、不可用的红包
			return $bonus_list;
		}
		return false;
	}
    
	/*
	*	获取用户可用和不可用优惠劵
	*	@Author 9009123 (Lemonice)
	*	@param $data=array(
    *       'user_id'=>'XX', //用户id
    *       'cart_amount'=>123, //商品总价
    *       'is_package'=>123, //是否套装
    *       'site_id'=1 //站点id
    *   )
	*	@param bool $auto_bonus 是否获取自动发放的优惠券
	*	@return array or bool
	*/
    public function getUserBonusList($data = array(),$auto_bonus = false) {
        if(!isset($data['user_id']) || !isset($data['cart_amount']) || !isset($data['is_package']) || !isset($data['site_id'])){
            return false;
        }
		
        $user_id = intval($data['user_id']);
        $cart_amount = floatval($data['cart_amount']);
        $is_package = intval($data['is_package']);
		$is_gift = intval($data['is_gift']);
        $site_id = intval($data['site_id']);
		
        $BonusModel = D('Home/UserBonusCenter');	
		
		//设置分页相关的处理
		$page = isset($data['page']) ? $data['page'] : 1;
		$pageSize = isset($data['pageSize']) ? $data['pageSize'] : 0;
		
		
		$result = $BonusModel->getUserBonusPage($user_id, $page, $pageSize);  //获取当前用户的所有优惠券
		$bonus_list = isset($result['list']) ? $result['list'] : array();
		if($auto_bonus == true){
			$time = Time::gmtime();
			$site_id = C('SITE_ID');
			$type_list = D('Home/BonusTypeCenter')->where("send_type = 3 and reuse = 1 and use_start_date <= '$time' and use_end_date >= '$time' and (FIND_IN_SET('$site_id',use_site) or use_site = 0)")->select();
			$type_list = is_array($type_list) ? $type_list : array();
			$bonus_list = array_merge($type_list,$bonus_list);
		}
		
		if($bonus_list !== false){
			$type_count = $result['type_count'];  //类型统计
			$list = $bonus_list;  //列表
			$valid_bonus = array();
			
			if (!empty($list)){
				$now_time = Time::gmtime();
				$payment_id = session('payment_data.payment_id');
				$addition = array(
					'user_id' => $user_id,
					'cart_amount' => $cart_amount,  //购物车总金额
					'site_id' => $site_id,
					'is_package' => $is_package,
					'check_bonus' => $is_gift,  //是否包含其他优惠劵活动
					'is_member_discount' => ($user_id > 0 ? 1 : 0),  //是否登录，有登录就有会员折扣
					'is_payonline_discount' => (ONLINE_PAYMENT_DISCOUNT > 0 ? ($payment_id != 1 ? 0 : 1) : 0),  //是否享受在线支付待遇
					'cart_category_info' => $this->getCartCategory() //购物车中商品分类信息
				);
				
				foreach ($list as $k => $bonus){
					$result = $this->checkBonus($bonus,$addition,false);  //校验优惠券
					//判断是否为数组，如果是数组，则校验通过
					if(is_array($result)){				
						if(isset($type_count[$bonus['type_id']])){
							$bonus['count'] = intval($type_count[$bonus['type_id']]);  //统计多少张优惠券
						}else{
							$bonus['count'] = 1;
						}
						$bonus = $this->formatTypeMoney($bonus);  //优惠券类型名称
						$valid_bonus[] = $bonus;
					}
				}
			}
			$bonus_list = $valid_bonus;
        }
		
		return $bonus_list;
    }
	
	/*
	*	获取购物车中商品分类信息
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
	public function getCartCategory(){
		$OrderObject = new Order();
		$rec_id = $OrderObject->getSelectCartRecId();  //购物车普通商品的购物车ID
		if($rec_id === false){  //如果返回了false表示没有普通商品、也没有优惠活动
			$this->error('您没勾选购物车商品！');
		}
		
		/* 购物车商品信息 */
		$rows = $OrderObject->cartSelectGoods($rec_id); // 取得商品列表
		$result = D('Home/Category')->getCartCategory($rows);
		return $result;
	}
	
	/*
	*	判断优惠券是否可用于当前购物车
	*	@Author 9009123 (Lemonice)
	*	@param array  $bonus  红包详情
	*	@param array  $addition  购物车相关的东西
	*	@param bool   $count_error  是否统计错误次数
	*	@return array
	*/
    public function checkBonus($bonus = array(), $addition = array(), $count_error = true){
        $ret = array('bonus_sn'=>'','bonus_id'=>'','type_money'=>0);  //优惠券详情
		
		if(!is_array($bonus) || empty($bonus)){
			return $ret;
		}
		$this->_initErrors($count_error);  //初始化记录错误信息
		
		if(($bonus['reuse'] == 0 && $bonus['type_money'] >= 0 && empty($bonus['order_id']) && empty($bonus['site_id']))
				|| ($bonus['reuse'] == 1 && $bonus['type_money'] >= 0)){  //判断优惠券为可用优惠劵
			$now = Time::gmTime();
			if ($now > $bonus['use_end_date'] || $bonus['use_start_date'] > $now){  //验证是否过期
				return $this->_errorIncrease("抱歉，".$bonus['type_name'].'优惠劵使用期无效,使用时间为：'.Time::localDate('Y-m-d',$bonus['use_start_date']).' 至 '.Time::localDate('Y-m-d',$bonus['use_end_date']), $count_error);
			}elseif(!empty($bonus['start_time']) && !empty($bonus['end_time']) && ($now < $bonus['start_time'] || $now > $bonus['end_time'])){  //验证生日优惠券是否过期
				return $this->_errorIncrease("抱歉，".$bonus['type_name'].'优惠劵使用期无效,使用时间为：'.Time::localDate('Y-m-d',$bonus['start_time']).' 至 '.Time::localDate('Y-m-d',$bonus['end_time']), $count_error);
			}else{
				$ret = array('bonus_sn'=>$bonus['bonus_sn'],'bonus_id'=>intval($bonus['bonus_id']),'type_money'=>floatval($bonus['type_money']));
			}
		}else{
			return $this->_errorIncrease("抱歉，无效的优惠劵", $count_error);
		}           
		if($addition){  //附加验证  如最低消费额，站点id，是否套装，是否需要登录等
			
			if (isset($addition['cart_amount']) && floatval($addition['cart_amount']) == 0){  //验证购物车是否为空
				return $this->_errorIncrease("抱歉，你的购物车为空，不能使用此优惠劵", $count_error);
			}
			if(isset($addition['cart_amount']) && floatval($addition['cart_amount']) <= $bonus['type_money']){
				return $this->_errorIncrease("抱歉，购物金额少于优惠劵金额，不能使用此优惠劵", $count_error);
			}
			if(isset($addition['cart_amount']) && floatval($addition['cart_amount']) > 200 && $bonus['coupon_type'] == 1){  //验证免邮券
				return $this->_errorIncrease("抱歉，你的购物金额大于200元，系统已经自动帮你免邮，无须使用此优惠劵", $count_error);
			}
			if (isset($addition['cart_amount']) && $bonus['min_goods_amount'] > floatval($addition['cart_amount'])){  //验证购物金额
				return $this->_errorIncrease("抱歉，消费金额不足".$bonus['min_goods_amount']."元，不能使用此优惠劵", $count_error);
			}
			if(isset($addition['user_id']) && $bonus['reuse'] == 0 && $bonus['user_id'] != 0 && intval($addition['user_id']) != $bonus['user_id']){  //验证user_id
				return $this->_errorIncrease("抱歉，你不能使用此优惠劵", $count_error);
			}
			if(isset($addition['is_package']) && intval($addition['is_package']) != 1 && $bonus['is_package'] == 1){  //验证套装购买
				return $this->_errorIncrease("抱歉，只有购买套装才可以使用此优惠劵", $count_error);
			}
			
			if($bonus['coupon_range'] > 0 && !empty($bonus['coupon_range_info']) && !empty($addition['cart_category_info'])){  //验证优惠范围
				$range_arr = explode(",",$bonus['coupon_range_info']);
				if($bonus['coupon_range'] == 1){ //限定指定分类
					if(empty($addition['cart_category_info']['cat_ids']) || 
							(!empty($range_arr) && !array_intersect($range_arr,$addition['cart_category_info']['cat_ids']))){
						return $this->_errorIncrease("抱歉，只有购买指定分类下的商品才可以使用此优惠劵", $count_error);
					}
				}elseif($bonus['coupon_range'] == 2){  //限定指定套装
					if(empty($addition['cart_category_info']['package_ids']) || 
							(!empty($range_arr) && !array_intersect($range_arr,$addition['cart_category_info']['package_ids']))){
						return $this->_errorIncrease("抱歉，只有购买指定套装才可以使用此优惠劵", $count_error);
					}
				}elseif($bonus['coupon_range'] == 3){  //限定指定商品
					if(empty($addition['cart_category_info']['goods_ids']) || 
							(!empty($range_arr) && !array_intersect($range_arr,$addition['cart_category_info']['goods_ids']))){
						return $this->_errorIncrease("抱歉，只有购买指定单品才可以使用此优惠劵", $count_error);
					}
				}elseif($bonus['coupon_range'] == 4){
					if(empty($addition['cart_category_info']['act_ids']) || 
							(!empty($range_arr) && !array_intersect($range_arr,$addition['cart_category_info']['act_ids']))){
						return $this->_errorIncrease("抱歉，只有购买指定活动下的商品才可以使用此优惠劵", $count_error);
					}
				}elseif($bonus['coupon_range'] == 5){   //同时指定了套装id和单品id
					$gids = $addition['cart_category_info']['goods_ids'];  //单品id
					$pids = $addition['cart_category_info']['package_ids'];  //套装id
					foreach($pids as $k => $pid){
						$pids[$k] = 'p'.$pid;
					}
					$merge_ids = array_merge($gids,$pids);   //合并
					if(empty($merge_ids) || (!empty($range_arr) && !array_intersect($range_arr,$merge_ids))){
						return $this->_errorIncrease("抱歉，只有购买指定单品或者套装才可以使用此优惠劵", $count_error);
					}
				}
				if(in_array($bonus['coupon_range'],array(2,3,4,5)) && $bonus['amount_range_limit']){  //如果指定了优惠范围价格限制
					if(!$this->_judgeAmountRangeLimit($bonus['coupon_range'],$bonus['coupon_range_info'],$bonus['amount_range_limit'],$addition['cart_category_info']['subtotal'])){
						return $this->_errorIncrease("抱歉，优惠范围内的商品总价没有达到金额下限！", $count_error);
					}
				}
			}
			
			$permit_site = explode(",",$bonus['use_site']);
			if(isset($addition['site_id'])&&!empty($bonus['use_site'])&&!in_array($addition['site_id'],$permit_site)){   //验证站点id
				return $this->_errorIncrease("抱歉，当前站点不能使用此优惠劵", $count_error);
			}
			if(isset($addition['check_bonus']) && intval($addition['check_bonus']) > 0 && $bonus['is_other_gift'] != 1 && $bonus['coupon_range'] != 4){  //验证可否和其他优惠品同时使用
				return $this->_errorIncrease("抱歉，此优惠劵不能和其他优惠品同时使用", $count_error);  //此规则需要着重测试
			}
			if(isset($addition['is_member_discount'])&&intval($addition['is_member_discount'])==1&&$bonus['is_member_discount']!=1){  //验证可否和会员优惠同时使用
				return $this->_errorIncrease("抱歉，此优惠劵不能和会员优惠同时使用", $count_error);
			}
			if(isset($addition['is_payonline_discount'])&&intval($addition['is_payonline_discount'])==1&&$bonus['is_payonline_discount']!=1){  //验证是否能和在线支付同时使用
				return $this->_errorIncrease("抱歉，此优惠劵不能和在线支付优惠同时使用", $count_error);
			}                               
		}
		cookie('error',NULL);
		if($bonus['coupon_type'] == 1 && empty($bonus['type_info'])){  //免邮券
			$ret['free_postage'] = 1;
		}elseif($bonus['coupon_type'] == 2 && !empty($bonus['type_info'])){  //礼品实物券
			$act_id_arr = explode(',',$bonus['type_info']);
			$front_actid = array();
			foreach ($act_id_arr as $aid){
				$tmp = explode('|',$aid);
				$front_actid[] = $tmp[0];   //前台活动id
				$back_actid[] = $tmp[1];  //业务后台活动id
			}
			$ret['front_actid'] =  $front_actid;
			$ret['back_actid'] = $back_actid;
		}elseif($bonus['coupon_type'] == 3 && intval($bonus['type_info']) != 0){  //折扣券
			$ret['discount'] = floatval(intval($bonus['type_info'])/100);                   
		}
		$ret['coupon_type'] = intval($bonus['coupon_type']);
		return $ret;
    }
	
	/*
	*	获取实物劵
	*	@Author 9009123 (Lemonice)
	*	@param array $act_id  活动ID
	*	@return array
	*/
	public function getGiftBonus($act_id){
		if(!is_array($act_id) && empty($act_id)){
			return array();
		}
		$now = Time::gmTime();
		
		//会员等级
		$user_rank = 0;
		if($this->user_id > 0){
			$user_rank = D('Home/UserAccount')->where("user_id = '".$this->user_id."'")->getField('rank');  //获取会员等级
		}
		
		$FavourableActivityModel = D('Home/FavourableActivity');
		
		$FavourableActivityModel->where("FIND_IN_SET('".$user_rank."',user_rank) AND start_time <= '$now' AND end_time >= '$now' AND act_type = '".FAT_GIFT_BONUS."' AND level_type = '". LEVEL_COMMON ."' AND act_id IN(".implode(',',$act_id).")");
		$rows = $FavourableActivityModel->select();
		
		$gift_bonus = array();
		if($rows){
			$FavourableObj = new Favourable();
			$GoodsModel = D('Home/Goods');
			$GoodsActivityModel = D('Home/GoodsActivity');
			foreach ($rows as $k => $row){
				$row['conflict_act'] = unserialize( $row['conflict_act'] );
				if($FavourableObj->available($row)){
					$gift_arr = !empty($row['gift'])?unserialize($row['gift']):array();
					if($gift_arr){
						foreach ($gift_arr as $a => $g){
							$gift_goods = $GoodsModel->field('is_on_sale,is_delete')->where("goods_id = '".$g['id']."'")->find();
							if($gift_goods['is_on_sale']==1 && $gift_goods['delete']==0){
								$gift_arr[$a]['act_id']=$row['act_id'];
								$gift_arr[$a]['type']='gift';
								$gift_bonus[]=$gift_arr[$a];
							}
						}
					}
					$gift_package_arr = !empty($row['gift_package']) ? unserialize($row['gift_package']) : array();
					if($gift_package_arr){
						foreach ($gift_package_arr as $b => $gp){
							$gid = $GoodsActivityModel->where("act_id = '" .$gp["id"]. "'")->getField('goods_id');
							$package_goods = $GoodsModel->field('is_on_sale,is_delete')->where("goods_id = '".$gid."'")->find();
							if($package_goods['is_on_sale']==1 && $package_goods['is_delete']==0){
								$gift_package_arr[$b]['act_id'] = $row['act_id'];
								$gift_package_arr[$b]['type'] = 'gift_package';
								$gift_bonus[] = $gift_package_arr[$b];
							}
						}
					}
				}
			}
		}
		return $gift_bonus;
	}
	
	/*
	*	初始化错误次数（检查优惠券）
	*	@Author 9009123 (Lemonice)
	*	@param bool  $count_error  是否开启错误统计
	*	@return boolean
	*/
	protected function _initErrors($count_error = true){
		if($count_error == false){
			return $count_error;
		}
        $client_ip = get_client_ip();  //获取ip
		$error = cookie('error');
		$session_client_ip = session('client_ip');
        if(!$error || $client_ip != $session_client_ip){
			cookie('error','0', 3600);
        }
        session('client_ip', $client_ip);
    }
	
	/*
	*	判断错误是否多次
	*	@Author 9009123 (Lemonice)
	*	@param string $msg 原错误提示信息
	*	@param bool  $count_error  是否开启错误统计
	*	@return array
	*/
    protected function _errorIncrease($msg = '', $count_error = true){
		if($count_error == false){
			return $msg;
		}
		$error = intval(cookie('error')) + 1;
		cookie('error', $error, 3600);
		return $error > 10 ? "抱歉，错误次数过多，请稍后再试。" : $msg;
    }
	
	/*
	*	判断是否达到价格限制
	*	@Author 9009123 (Lemonice)
	*	@param integer $type 优惠范围类型
	*	@param string $limitRange 优惠范围字符串（以英文逗号隔开）
	*	@param integer $limitAmount 限制的总金额
	*	@param array $subtotal   各种优惠类型的金额小计数组
	*	@return array
	*/
    protected function _judgeAmountRangeLimit($type,$limitRange,$limitAmount,$subtotal){
        if($limitAmount == 0) return true;  //不限制直接返回
        $limitArr = !empty($limitRange) ? explode(',',$limitRange) : array();
        switch($type){
            case 2 :  //指定套装
                if(empty($subtotal['package'])){ 
                    return false;
                }else{
                    $limittotal = 0; //优惠范围内的商品总价
                    foreach($subtotal['package'] as $pk => $pval){
                        if(in_array($pk,$limitArr)){
                            $limittotal += $pval;
                        }
                    }
                    return $limittotal > $limitAmount;
                }
                break;
            case 3 :  //指定单品
                if(empty($subtotal['goods'])){ 
                    return false;
                }else{
                    $limittotal = 0; //优惠范围内的商品总价
                    foreach($subtotal['goods'] as $pk => $pval){
                        if(in_array($pk,$limitArr)){
                            $limittotal += $pval;
                        }
                    }
                    return $limittotal > $limitAmount;
                }
                break;
            case 4 :  //指定活动
                if(empty($subtotal['act'])){ 
                    return false;
                }else{
                    $limittotal = 0; //优惠范围内的商品总价
                    foreach($subtotal['act'] as $pk => $pval){
                        if(in_array($pk,$limitArr)){
                            $limittotal += $pval;
                        }
                    }
                    return $limittotal > $limitAmount;
                }
                break;
            case 5 :  //指定单品和套装
                if(empty($subtotal['package']) && empty($subtotal['goods'])){ 
                    return false;
                }else{
                    $limittotal = 0; //优惠范围内的商品总价
                    foreach($subtotal['package'] as $pk => $pval){
                        if(in_array('p'.$pk,$limitArr)){
                            $limittotal += $pval;
                        }
                    }
                    foreach($subtotal['goods'] as $gk => $gval){
                        if(in_array($gk,$limitArr)){
                            $limittotal += $gval;
                        }
                    }
                    return $limittotal > $limitAmount;
                }
                break;
            default : 
                break;
        }
        return false;
    }
	
	/*
	*	使用优惠劵
	*	@Author 9009123 (Lemonice)
	*	@param array $ub  优惠券详情
	*	@return array
	*/
    public function useBonus($bonus_id = 0, $order_id = 0, $site_id = '', $user_id = 0){
		if($bonus_id == 0 || $order_id == 0 || $site_id == ''){
			return false;
		}  
        $UserBonusCenterModel = D('Home/UserBonusCenter');
		$bonus = $UserBonusCenterModel->bonusInfo($bonus_id);
		if($bonus['reuse'] == 0 && $bonus['used_time'] != 0){  //判断是否可以重复使用
			return '您的优惠券不能重复使用';
		}
		$now = Time::gmTime();
		if($bonus['use_start_date'] > $now || $bonus['use_end_date'] < $now) {
			return '您的优惠券不在规定的使用范围内，请检查是否过期';
		}
		if(!empty($bonus['start_time']) && !empty($bonus['end_time']) && ($now < $bonus['start_time'] || $now > $bonus['end_time'])){   //判断注册有礼优惠劵及生日优惠劵的使用日期是否已过
			return '您的优惠券已经过期';
		}
		if(!empty($bonus['user_id']) && !empty($user_id) && ($bonus['user_id'] != $user_id)){ //验证user_id是否正确
			return '您还没拥有该优惠券';
		}
		$permit_site = !empty($bonus['use_site']) ? explode(",",$bonus['use_site']) : array();
		if(!empty($bonus['use_site']) && !in_array($site_id,$permit_site)){ //验证当前站点是否可以使用
			return '您的优惠券不适用于当前站点';
		}
		return $UserBonusCenterModel->useBonus(array(
			'bonus_id'=>$bonus_id,
			'order_id'=>$order_id,
			'site_id'=>$site_id,
			'user_id'=>$user_id,
			'reuse'=>$bonus['reuse'],
		));
    }
	
	/*
	*	优惠券类型名称
	*	@Author 9009123 (Lemonice)
	*	@param array $ub  优惠券详情
	*	@return array
	*/
	private function formatTypeMoney($ub = array()){
		if(empty($ub)){
			return $ub;
		}
		$ub['format_type_money'] = priceFormat($ub['type_money']);
		if($ub['coupon_type'] == 1){
			$ub['format_type_money'] = "免邮优惠";
		}elseif($ub['coupon_type'] == 2){
			$ub['format_type_money'] = "免费赠品优惠";
		}elseif($ub['coupon_type'] == 3){
			$ub['format_type_money'] = floatval($ub['type_info'] / 10)."折优惠";
		}
		return $ub;
	}
}
?>