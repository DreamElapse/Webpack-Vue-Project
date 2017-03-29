<?php
/**
 * ====================================
 * 微信大转盘活动控制器
 * ====================================
 * Author: 
 * Date: 
 * ====================================
 * File: RotateController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Wechat;
use Common\Extend\PhxCrypt;
// use Common\Extend\WechatJsSdk;
//use Common\Extend\Send;
use Common\Extend\Time;
use Common\Extend\Order\Order;

class RotateController extends InitController {
	private $rotateModel;
	private $open_id;
    protected $user_id;
    private $langGrade = array(
        0  => '谢谢参与',
        1  => '一等奖',
        2  => '二等奖',
        3  => '三等奖',
        4  => '四等奖',
        5  => '五等奖',
        6  => '六等奖',
        7  => '七等奖',
        8  => '八等奖',
        9  => '九等奖',
        10 => '十等奖',
    );

    public function __construct() {
        parent::__construct();
        Wechat::$app_id = APPID;
        Wechat::$app_secret = APPSECRET;
        $this->rotateModel = D('Rotate');
        $this->open_id = session('sopenid') ? session('sopenid') : '';
        $this->user_id = session('user_id') ? session('user_id') : 0;
    }

    // 活动主页
    public function index(){
		$rotate_info = $this->rotateModel->getRotateInfo('id,act_name,des_info,act_title,lottery_num');
		if(!$rotate_info){
			$this->error('活动未开始');
		}
		$prize_cfg = $this->rotateModel->getPrizeInfo($rotate_info['id'], 0, 'id,grade,prize_name,goods_id');
		if(!$prize_cfg){
			$this->error('活动未开始');
		}
		$rotate_info['prize_cfg'] = $prize_cfg;
		$rotate_info['des_info'] = htmlspecialchars_decode(htmlspecialchars_decode($rotate_info['des_info']));

        //剩余可抽奖次数
        $num = $this->rotateModel->logTimes($rotate_info['id'], $this->open_id);
        $rotate_info['num'] = $rotate_info['lottery_num'] - $num;
        unset($rotate_info['lottery_num']);
        $this->success(array(
            'data'=>$rotate_info,
            'isCheckWechat'=>(!$this->open_id ? 0 : 1),
        ));
    }

    /**
     * 获取抽奖记录
     * @param $id int 转盘活动ID
     * @param $limit int 获取记录的数量，默认 10 条
     */
    public function getLog(){
        $rotate_id = I('request.rotate_id', 0, 'intval');
        $limit = I('request.limit', 10, 'intval');
        $logs = $this->rotateModel->rotateLog($rotate_id, '', true, 'add_time,prize_name,nick_name,goods_id', $limit);
        foreach($logs as $key=>$val){
            $nick_name_len = mb_strlen($val['nick_name'], 'utf-8');
            if($nick_name_len >= 2){
                $logs[$key]['nick_name'] = mb_substr($val['nick_name'],0, 1, 'utf-8').'**'.mb_substr($val['nick_name'],-1, 1, 'utf-8');
            }else{
                $logs[$key]['nick_name'] = $val['nick_name'].'**';
            }
            $grade = $this->rotateModel->prizeGrade($rotate_id, $val['goods_id'], 'grade');
            if($grade){
                $logs[$key]['prize_name'] = $this->langGrade[$grade['grade']].'：'.$val['prize_name'];
            }
        }

        $this->ajaxReturn($logs);
    }

    /**
     * 抽奖
     * @param $rotate_id int 转盘活动ID
     * return 奖品等级
     */
    public function rotating(){

        $logModel = M('wx_rotates_log', null, 'USER_CENTER');
        $source_url = I('request.source_url', '', 'trim');
        $rotate_id = I('request.rotate_id', 0, 'intval');

        //活动跟奖品必须同时存在
		$info = $this->rotateModel->getRotateInfo('id,act_name,lottery_num,act_title,base_num');
		if(!$info){
			$this->error('活动未开始！');
		}
        $lottery_num = $info['lottery_num']; //每天可抽奖的次数
		$prizes = $this->rotateModel->getPrizeInfo($info['id']);
        if(!$prizes){
            $this->error('活动未开始！');
        }

        //判断用户抽奖次数是否用完
        $day_num = $this->rotateModel->logTimes($rotate_id, $this->open_id);
        if($day_num >= $lottery_num){
            $this->error('今天次数已用完，明天继续吧！');
        }
		
		//计算剩余的概率
		$chance_sum = 0;
		$arr = array();
		foreach($prizes as $k=>$v){
			$a = ($v['chance'] / 100);
			list($int1, $floor1) = explode('.', $a);
			$arr[] = mb_strlen($floor1);
			$chance_sum += ($v['chance'] / 100);
		}
		$chance_sum = (1 - $chance_sum);
		list($int, $floor) = explode('.', $chance_sum);
		$arr[] = mb_strlen($floor);
		$max = max($arr);
		$chance_sum = $chance_sum * pow(10, $max);
		foreach($prizes as &$val){
			$val['chance_int'] = ($val['chance'] / 100) * pow(10, $max);
		}

		//初始奖项
        $data = array(
            'rotate_id' => $rotate_id,
            'grade' => 0,
            'prize_name' => '谢谢参与',
            'goods_id' => 0,
            'id' => 0,
            'chance_int' => $chance_sum + $info['base_num'],
        );
		array_push($prizes, $data);

        //中奖的用户直接返回未绑定奖品的奖项
        $is_winner = $this->rotateModel->isWinner($rotate_id,$this->open_id);
        if(!$is_winner){

            $chance_int = 0; //临时存储奖品个数为0，或者奖项中完并且概率为0的奖品的概率总和
            $new_prizes = array(); //存放可用于抽奖的奖品
            foreach($prizes as $k=>&$v){
				if($v['goods_id'] > 0){
					$win_num = $logModel->where(array('rotate_id'=>$rotate_id, 'goods_id'=>$v['goods_id']))->count();
					if($win_num >= $v['prize_num']){
						// 奖品已经被抽完的，将它的概率累加到未绑定商品的奖项上
						$chance_int += $v['chance_int'];
					}else{
						// 未抽完的，将奖品新进到临时奖品数组中
						$new_prizes[] = $v;
					}
				}else{
					$new_prizes[] = $v;
				}

            }
			
			/* 将奖品抽完和未绑定奖品的概率都放到未绑定奖品的奖项上 */
			foreach($new_prizes as &$val){
				if($val['grade'] === 0){
					$val['chance_int'] += $chance_int;
				}
			}

            /* 根据经典算法，抽取奖品，得到奖品等级 */
            $arr_rand = array();
            foreach($new_prizes as &$n_val){
                if($n_val['chance_int'] > 0){
                    $arr_rand[$n_val['grade']] = $n_val['chance_int'];
                }
            }

            if(!empty($arr_rand)){
                $prizes_grade = $this->getRand($arr_rand);  //奖品等级
                $prizes_grade = (int)$prizes_grade;
                unset($val);
                foreach($new_prizes as $val){
                    if($prizes_grade == $val['grade']){
                        $return['rotate_id']  = $val['rotate_id'];
                        $return['grade']	  = $val['grade'];
                        $return['prize_name'] = $val['prize_name'];
                        $return['goods_id']   = $val['goods_id'];
                        $return['prize_id']   = $val['id'];
                        break;
                    }
                }
            }else{
                $return = $data;
            }
		}else{
			$return['rotate_id']  = $data['rotate_id'];
			$return['grade']	  = $data['grade'];
			$return['prize_name'] = $data['prize_name'];
			$return['goods_id']   = $data['goods_id'];
			$return['prize_id']   = $data['id'];
		}
		
        $nick_name = D('BindUser')->getUserNickName($this->openId);
        $logModel->add(
            array(
                'add_time'	 => time(),
                'source_url' => $source_url,
                'prize_name' => $return['prize_name'],
                'goods_id'	 => $return['goods_id'],
                'open_id'	 => $this->open_id,
                'nick_name'  => empty($nick_name) ? '' : $nick_name,
                'content'	 => '',
                'rotate_id'	 => $return['rotate_id'],
                'act_name'	 => $info['act_name'],
            )
        );
		if(isset($return['chance_int'])){
			unset($return['chance_int']);
		}
//		 echo '<pre>';
//		 print_r($return);exit;
        //剩余可抽奖次数
        $num = $this->rotateModel->logTimes($info['id'], $this->open_id);
        $return['num'] = $info['lottery_num'] - $num;
        $return['grade_name'] = $return['grade'] > 0 ? $this->langGrade[$return['grade']] : '';
        $this->success(array('prize'=>$return));
    }

    /**
     * 中奖用户填写收货地址
     */
    public function saveAddress(){

        $address_id = I('request.address_id', 0, 'intval');
        $consignee  = I('request.consignee', '', 'trim');
        $province   = I('request.province', 0, 'intval');
        $city       = I('request.city', 0, 'intval');
        $district   = I('request.district', 0, 'intval');
        $town       = I('request.town', 0, 'intval');
        $address    = I('request.address', '', 'trim');
        $mobile     = I('request.mobile', '', 'trim');
        $attribute  = I('request.attribute', '', 'trim');

        $msg = '';
        if(!$consignee){
            $msg = '请输入收货人';
        }
        if(mb_strlen($consignee, 'utf8')<2 && !preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $consignee)){
            $msg = '收货人必须最少2位中文';
        }
        if($address_id){  //编辑
            if($mobile && !preg_match('/^((\+86)|(86))?(1[3|4|5|7|8]{1}\d{9})$/', $mobile)){
                $msg = '请输入正确的手机号码';
            }elseif(!$mobile){
                unset($mobile);  //等于空表示不修改手机号码
            }
        }else{  //添加
            if(!preg_match('/^((\+86)|(86))?(1[3|4|5|7|8]{1}\d{9})$/', $mobile)){
                $msg = '请输入正确的手机号码';
            }
        }
        if(!$province){
            $msg = '请选择所在省份';
        }
        if(!$city){
            $msg = '请选择所在城市';
        }
        if(!$district){
            $msg = '请选择所在区域';
        }
        if(mb_strlen($address, 'utf8')<5 && !preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $address)){
            $msg = '收货人的详细地址必须大于5位中文';
        }
        if($msg){
            $this->error($msg);
        }

//        echo PhxCrypt::phxDecrypt('±acad2e06c0b57824e6eb8388ab485046');exit;//13711458538
        $phx_mobile = '';
        if(!empty($mobile)){
            $phx_mobile = PhxCrypt::phxEncrypt($mobile);
        }
        $bindUserModel = D('BindUser');
        $usersModel = D('Users');
        $addressModel = M('user_address', null, 'USER_CENTER');

        $address_data['consignee']  = $consignee;
        $address_data['province']   = $province;
        $address_data['city']       = $city;
        $address_data['district']   = $district;
        $address_data['town']       = $town;
        $address_data['mobile']     = $phx_mobile;
        $address_data['address']    = $attribute.'|+_+|'.$address;
        if($this->user_id){
            $address_data['user_id'] = $this->user_id;

            if($address_id){
                $new_address_id = $address_id;
                if(!empty($mobile)){
                    $address_data['update_time'] = Time::gmTime();
                    $new_address_id = $addressModel->where(array('address_id'=>$address_id))->save($address_data);
                }
            }else{
                session('new_consignee', $address_data);
                $address_data['add_time'] = Time::gmTime();
                $addressModel->add($address_data);
                $new_address_id = $addressModel->getLastInsID();
            }

        }else{
            //用户未登陆，判断手机号是否是会员，不是的自动注册，是的将此地址归入该账号内
            $user_info = $usersModel->where(array('mobile'=>$phx_mobile))
                ->field('user_id,mobile,user_num,user_name')
                ->find();
            if(!empty($user_info)){
                //地址归入
                $address_data['user_id'] = $user_info['user_id'];
                $address_data['add_time'] = Time::gmTime();
                $addressModel->data($address_data)->add();
                $new_address_id = $addressModel->getLastInsID();
            }else{
                //不存在，自动注册会员
                $real_ip = get_client_ip();
                $password = substr($mobile,-6);  //获取手机号码后六位做为密码
                $data = array(
                    'mobile'=>$phx_mobile,
                    'sms_mobile'=>$mobile,
                    'ip'=>$real_ip,
                    'email'=>'',
                    'source'=>$_SERVER['HTTP_HOST'],
                    'sex'=>0,
                    'password'=>$password,
                );
                //验证存在或初始注册，存在，则返回user_id,不存在，则返回初始注册的user_id和随机密码
                $reg_res = $usersModel->addNewMember($data);
                $user_id = isset($reg_res['user_id']) ? $reg_res['user_id'] : (isset($reg_res['new_user_id']) ? $reg_res['new_user_id'] : 0);  //会员ID
                if($user_id){
                    $address_data['user_id'] = $user_id;
                    $address_data['add_time'] = Time::gmTime();
                    $addressModel->add($address_data);
                    $new_address_id = $addressModel->getLastInsID();
                }
            }
        }

        //查看是否微信绑定
        $is_bind = $bindUserModel->where(array('openid'=>$this->open_id))->count();
//        $this->open_id = 'omBGQtzDYNQmu-s9hVvg6U_Dn7TQ';
        if(!$is_bind){
            $bindUserModel->add(array('openid'=>$this->open_id, 'mobile'=>$phx_mobile));
        }
        $this->success(
            array(
                'address_id' => $new_address_id,
                'consignee' => $address_data['consignee'],
                'province' => $address_data['province'],
                'city' => $address_data['city'],
                'district' => $address_data['district'],
                'town' => $address_data['town'],
                'mobile' => $mobile,
                'address' => $address_data['address'],
            )
        );
    }

    /**
     * 中奖奖品购物车显示接口
     *
     * @param id 活动id
     * @param prize_id 奖品id
     */
    public function goodsInfo(){
        $rotate_id = I('request.rotate_id', 0, 'intval');
        $prize_id = I('request.prize_id', 0, 'intval');
        $address_id = I('request.address_id', 0, 'intval');
        if($rotate_id && $prize_id){
            $goodsModel = M('goods', null, 'USER_CENTER');

            /* 判断活动是否存在 */
            $rotate_info = $this->rotateModel->rotateInfo($rotate_id, 'id,act_name,act_title,lottery_num,shipping_free,postage');
            if(!$rotate_info){
                $this->error('活动未开始');
            }
			
            /* 判断奖品是否存在 */
            $prizes_info_goods = $this->rotateModel->getPrizeInfo($rotate_info['id'], $prize_id, 'goods_id');
            if(!$prizes_info_goods){
                $this->error('奖品无效');
            }

            /* 商品是否存在，并读取该商品的图片 */
            $fields = 'goods_id,cat_id,goods_name,goods_sn,market_price,shop_price,goods_type';
            $goods_info = $goodsModel->where(array('goods_id'=>$prizes_info_goods['goods_id'], 'is_on_sale'=>1))
                ->field($fields)
                ->find();
            $goods_info['shop_price'] = 0;
            if(!$goods_info){
                $this->error('商品不存在！');
            }

            $imgs = M('goods_gallery', null, 'USER_CENTER')->where(array('goods_id'=>$goods_info['goods_id']))->field('img_url')->select();
            if($imgs){
                foreach($imgs as &$val){
                    $val['img_url'] = C('domain_source.img_domain').$val['img_url'];
                }
            }
            if($rotate_info['shipping_free']){
                $orderObject = new Order();
                //检查收货地址是否有设置
                $consignee = $orderObject->getRealUserAddress($address_id);
                //计算邮费
                $rotate_info['shipping_free'] = $orderObject->getShippingFee($consignee, 0, $rotate_info['postage'], false);
            }else{
                $rotate_info['shipping_free'] = $rotate_info['postage'];
            }

            $goods_info['img_url'] = $imgs;
            $goods_info['shipping_free'] = $rotate_info['shipping_free'];
            $goods_info['goods_number'] = 1;
            $goods_info['amount'] = $goods_info['shop_price'];

//            $goods_info['market_price'] = sprintf("%d",$goods_info['market_price']);
//            $goods_info['shop_price'] = sprintf("%d",$goods_info['shop_price']);
            $this->success($goods_info);
        }
    }

    /**
     * 奖品生成订单
     * @param $param
     */
	public function buildOrder(){
        $goodsModel = M('goods', null, 'USER_CENTER');
        $orderInfoModel = M('order_info');

        $site_id = C('SITE_ID');
        $real_ip = get_client_ip();

        $prize_id = I('request.prize_id', 0, 'intval'); //转盘奖品id
        $rotate_id = I('request.rotate_id',0,'intval'); //转盘活动id
        $address_id = I('request.address_id',0,'intval'); //地址ID
        $payment_id = I('request.payment_id',0,'intval');  //1是货到付款（不支持）
        $remark = I('request.remark','','trim');

        $is_wechat = isCheckWechat();
        if($is_wechat == true && $payment_id == 4){  //微信不支持支付宝
            $this->error('请选择支付方式');
        }
		
		//检查同IP是不是频繁下单
		$limit_time  = Time::gmtime() - 1800;
		$count = D('OrderInfo')->where(array('add_time'=>array('gt',$limit_time), 'ip_address'=>$real_ip, 'postscript'=>array('like','%中奖订单%')))->count();
		
		if ($count){
			$this->error('不能重复提交，如有疑问请联系在线客服！');
		}

        if(!($rotate_id && $prize_id)){
            $this->error('此商品不存在');
        }

        //转盘信息
        $rotate_info = $this->rotateModel->rotateInfo($rotate_id,'id,act_name,act_title,lottery_num,shipping_free,postage', false);
        if(!$rotate_info){
            $this->error('活动未开始！');
        }

        /* 获取奖品绑定的商品id */
        $prize_info = $this->rotateModel->getPrizeInfo($rotate_info['id'], $prize_id, 'id,goods_id,rotate_id');
        if(!$prize_info){
            $this->error('活动未开始！');
        }

        /* 通过奖品绑定的商品id获取商品的信息 */
        $goods_info = $goodsModel
            ->where(array('goods_id'=>$prize_info['goods_id'], 'is_on_sale'=>1))
            ->field('goods_id,cat_id,goods_name,goods_sn,market_price,shop_price,goods_type')
            ->find();
        if(!$goods_info){
            $this->error('活动未开始！');
        }

        $OrderObject = new Order();

        //检查收货地址是否有设置
        $consignee = $OrderObject->getRealUserAddress($address_id);

        /* 检查收货人信息是否完整 */
        if (!$consignee){
            $this->error('请填写收货地址');
        }

        //计算邮费，是否加收（偏远地区15元）
        if($rotate_info['shipping_free']){
            $data['shipping_fee'] = $OrderObject->getShippingFee($consignee, 0, $rotate_info['postage'], false);
        }else{
            $data['shipping_fee'] = $rotate_info['postage'];
        }
        $data['shop_price'] = 0;
        $order = array(
            'order_amount'    => $data['shipping_fee'] + $data['shop_price'],  //应付金额
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
            'integral'        => 0,  //使用的积分
            'integral_money'  => 0,  //使用积分抵消的金额
            'bonus'           => 0,
            'need_inv'        => 0,
            'inv_type'        => '',
            'inv_payee'       => '',
            'inv_content'     => '',
            'postscript'      => '*#lottery draw*#'.htmlspecialchars($remark),  //订单备注
            'how_oos'         => '',
            'need_insure'     => 0,  //保险
            'user_id'         => $this->user_id,  //用户
            'add_time'        => Time::gmtime(),  //下单时间
            'order_status'    => OS_UNCONFIRMED,  //订单状态
            'shipping_status' => SS_UNSHIPPED,  //物流状态
            'pay_status'      => PS_UNPAYED,  //支付状态
            'agency_id'       => 0,  //收货地址所在的办事处ID
            'ip_address' 	  => $real_ip,  //客户端IP地址
            'goods_amount'    => $data['shop_price'],  //商品总金额
            'discount'        => 0,  //加上会员折扣
            'tax'             => 0,  //税收
            'parent_id'       => 0,
            'kefu'            => '手机商城下单',  //订单自动分配
            'divide_region'   => '广州地区手机商城下单',
            'user_id'         => $this->user_id,  //会员ID
            'bonus_id'        => 0,  //红包ID
            'order_sn'        => $OrderObject->getOrderSn(),  //生成订单号
            'site_id'         => $site_id,
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
//            $order['mobile'] = PhxCrypt::phxEncrypt($order['mobile']);
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
        $order['order_id'] = $orderInfoModel->data($order)->add();

        //插入到会员中心的订单表
        $insert_id = D('OrderInfoCenter')->insert($order);

        if($order['order_id']){
            $order_goods_data = array(
                'order_id'=>$order['order_id'],
                'order_sn'=>$order['order_sn'],
                'goods_id'=>$goods_info['goods_id'],
                'goods_name'=>($goods_info['goods_name'] ? $goods_info['goods_name'] : ''),
                'goods_sn'=>($goods_info['goods_sn'] ? $goods_info['goods_sn'] : ''),
                'goods_number'=>1,
                'market_price'=>($goods_info['market_price'] ? $goods_info['market_price'] : 0),
                'goods_price'=>($goods_info['shop_price'] ? $goods_info['shop_price'] : 0),
                'goods_attr'=>'',
                'is_real'=>1,
                'extension_code'=>'',
                'parent_id'=>0,
                'is_gift'=>0,
                'site_id'=>$order['site_id'],
            );

            $package_goods_data = array();
            if($goods_info['goods_type'] == 2){
                // 套装
                $order_goods_data['extension_code'] = 'package_buy';
                $goodsActivityModel = M('goods_activity', null, 'USER_CENTER');
                $packageGoodsModel = M('package_goods', null, 'USER_CENTER');

                $goods_activity = $goodsActivityModel->where(array('goods_id'=>$prize_info['goods_id']))->field('act_id')->find();
                $package_goods_id = $packageGoodsModel->where(array('package_id'=>$goods_activity['act_id']))->getField('goods_id', true);
                $package_goods = $goodsModel->where(array('goods_id'=>array('in', $package_goods_id)))->select();

                if(!empty($package_goods)){
                    foreach($package_goods as $key=>$val){
                        $package_goods_data[] = array(
                            'order_id'=>$order['order_id'],
                            'order_sn'=>$order['order_sn'],
                            'goods_id'=>$val['goods_id'],
                            'goods_name'=>($val['goods_name'] ? $val['goods_name'] : ''),
                            'goods_sn'=>($val['goods_sn'] ? $val['goods_sn'] : ''),
                            'goods_number'=>1,
                            'market_price'=>($val['market_price'] ? $val['market_price'] : 0),
                            'goods_price'=>($val['shop_price'] ? $val['shop_price'] : 0),
                            'goods_attr'=>'',
                            'is_real'=>1,
                            'extension_code'=>'package_goods',
                            'parent_id'=>$goods_info['goods_id'],
                            'is_gift'=>0,
                            'site_id'=>$order['site_id'],
                        );
                    }
                }
            }
            array_unshift($package_goods_data, $order_goods_data);
            $orderGoodsModel = D('OrderGoods');
            $orderGoodsCenterModel = D('OrderGoodsCenter');
            $result = $orderGoodsModel->addAll($package_goods_data);//插入商品数据到当前站点数据库
            $res = $orderGoodsCenterModel->addAll($package_goods_data);  //插入商品数据到ucenter数据库
        }else{
            $this->error('提交订单失败');
        }

        /* 取得支付信息，生成支付代码 */
        if ($order['order_amount'] > 0){
            $pay = $OrderObject->getPaymentClass($code);  //实例化网银类
            $payment = D('Payment')->getPayment(strtolower($code));
            $payment = $OrderObject->unserialize_config($payment['pay_config']);
            $order["content"] = $pay->getCode($order,$payment);

            if(!$order["content"]){
                $this->error('您当前的站点不支持使用该支付方式，请重新选择！');
            }

            $pay_info_data = array(
                'site_id'=>$order['site_id'],
                'pay_id'=>$order['pay_id'],
                'name'=>($order["consignee"] ? $order["consignee"] : ''),
                'order_sn'=>$order["order_sn"],
                'order_amount'=>$order["order_amount"],
                'source'=>2,
                'add_time'=>Time::gmtime(),
            );
            $res = D('PayInfo')->insert($pay_info_data);
        }
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



    // ========================================= private methods ==========================================

    /**
     * 经典的概率算法
     * @param $arr 预先设置的数组
     * @param $base_num 基数
     * @return int|string
     */
    private function getRand($arr){
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($arr);
        //概率数组循环
        foreach ($arr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($arr);
        return $result;
    }


    /*
	*	处理支付信息
	*	@Author
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
	*	获取订单的来源地址
	*	@Author
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
            if(strstr($order['ip_info_text'],'?')){
                $order['ip_info_text'] = $order['ip_info_text'] . '&openid='.$this->open_id;
            }else{
                $order['ip_info_text'] = $order['ip_info_text'] . '?openid='.$this->open_id;
            }
        }

        return $order;
    }
}