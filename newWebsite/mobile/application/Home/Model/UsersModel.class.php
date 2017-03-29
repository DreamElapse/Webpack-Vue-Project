<?php
/**
 * ====================================
 * 会员中心模型
 * ====================================
 * Author: 9004396
 * Date: 2016-06-25 10:29
 * ====================================
 * File: usersModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\UserCenterModel;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;
use Common\Extend\Send;

class UsersModel extends UserCenterModel {

    /**
     * 微信自动登陆
     * @param $openid
     * @return array|bool
     */
    public function wechatLogin($openid){
        if(empty($openid)){
            return false;
        }
        $WechatModel = new WechatModel();
        $mobile = $WechatModel->where(array('openid' => $openid))->getField('mobile');
        $password = $this->where(array('mobile' => $mobile))->getField('password');
        return $this->login($mobile,$password);
    }


    /**
     * 用户登录
     * @author 9009221
     * @param $username
     * @param $password
     * @return array or false
     */
    public function login($username, $password) {
        if (!empty($username) && !empty($password)) {
            if (is_phone($username)) {
                $username = PhxCrypt::phxEncrypt($username);
            }
            $where = array();
            $where['state'] = array('in',array(0,1));
            $where['mobile'] = $username;
            $where['password'] = $password;
            $this->where($where);
            $this->field('user_id,user_name,email,mobile,user_num');
            $row = $this->find();
			
            if ($row) {
                foreach ($row as $key => $item){
                    switch ($key){
                        case 'mobile':
                            session($key,PhxCrypt::phxDecrypt($item));
                            break;
                        default:
                            session($key,$item);
                    }
                }
				//设置登录状态
				$this->setUserInfo($row);
				
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
	
	/*
	*	获取登录状态、用户详情
	*	@Author 9009123 (Lemonice)
	*	@param  int $user_id 用户ID
	*	@return exit
	*/
	public function getUserLoginInfo($user_id = 0){
		$user_info = session('userInfo');
		
		$row = isset($user_info[$user_id]) ? $user_info[$user_id] : array();
		if(!empty($row)){
			//获取当前总积分和等级
			$total_points = D('UserAccount')->where("user_id = '$row[user_id]'")->getField('total_points');
			$total_points = $total_points ? $total_points : 0;
			
			$rank = array();  //该用户当前等级
			//获取下一级锁需要的积分数量
			$rank_list = D('UserRank')->field('rank_name,min_points,max_points')->order('min_points desc')->select();
			if(!empty($rank_list)){
				$i = count($rank_list);  //等级递增
				foreach($rank_list as $key=>$value){
					if($total_points >= intval($value['min_points'])){
						$rank = $value;
						$rank['level'] = $i;  //该用户当前等级
						$rank['percent'] = sprintf("%.2f", (($total_points - $value['min_points']) / ($value['max_points'] - $value['min_points']) * 100));
						$rank['percent'] = $rank['percent'] > 100 ? 100 : ($rank['percent'] < 0 ? 0 : $rank['percent']);
						$rank['total_points'] = $total_points;
						break;
					}
					$i--;
				}
			}   
			
			$rank['level'] = isset($rank['level']) ? intval($rank['level']) : 1;
			$row = array_merge($row,$rank);
			
			$row['total_points'] = $total_points;  //当前用户总积分
			$result = D('IntegralCenter')->getPointsLeft($row['user_id']);  //查询用户可用积分, 会计算被冻结的积分在内
			
			$row['points_left'] = $result['user_points'];
			
			$userInfo[$user_id] = $row;
			//session('userInfo',$userInfo);  //不重新写session，减少IO压力
		}
		return $row;
	}
	
	/*
	*	设置登录状态、用户详情
	*	@Author 9009123 (Lemonice)
	*	@param  array|int $row  用户详情（array），或者用户ID（int）
	*	@return exit
	*/
	public function setUserInfo($row = 0){
		if(!is_array($row)){
			$where = array(
				'user_id'=>$row,  //会员ID
			);
			$this->where($where);
			$this->field('user_id,user_name,email,mobile,user_num');
			$row = $this->find();
		}
		
		//该用户当前等级
		$rank = array(
			'rank_name'=>'未知等级',  //级别名称
			'min_points'=>0,         //当前级别的最小分数值
			'max_points'=>0,         //当前级别的最大分数值
			'total_points'=>0,       //当前用户总积分
			'points_left'=>0,        //当前用户可用积分
			'level'=>1,              //等级，从1开始的级别
			'percent'=>0,            //进度百分比
		);
		
		//获取当前总积分和等级
		$total_points = D('UserAccount')->where("user_id = '$row[user_id]'")->getField('total_points');
		$total_points = $total_points ? $total_points : 0;
		
		$rank = array();  //该用户当前等级
		//获取下一级锁需要的积分数量
		$rank_list = D('UserRank')->field('rank_name,min_points,max_points')->order('min_points desc')->select();
		if(!empty($rank_list)){
			$i = count($rank_list);  //等级递增
			foreach($rank_list as $key=>$value){
				if($total_points >= intval($value['min_points'])){
					$rank = $value;
					$rank['level'] = $i;  //该用户当前等级
					$rank['percent'] = sprintf("%.2f", (($total_points - $value['min_points']) / ($value['max_points'] - $value['min_points']) * 100));
					$rank['percent'] = $rank['percent'] > 100 ? 100 : ($rank['percent'] < 0 ? 0 : $rank['percent']);
					$rank['total_points'] = $total_points;
					break;
				}
				$i--;
			}
		}   
		
		//获取头像文件地址
		$user_info = D('UserInfo')->field('name,photo_url')->where("user_id = '$row[user_id]'")->find();
		$row['name'] = isset($user_info['name']) ? $user_info['name'] : '';
		$row['photo_url'] = isset($user_info['photo_url']) ? $user_info['photo_url'] : '';
		
		$rank['level'] = isset($rank['level']) ? intval($rank['level']) : 1;
		$row = array_merge($row,$rank);
		
		$row['total_points'] = $total_points;  //当前用户总积分
		$result = D('IntegralCenter')->getPointsLeft($row['user_id']);  //查询用户可用积分, 会计算被冻结的积分在内
		
		$row['points_left'] = $result['user_points'];
		
		if(isset($row['user_num'])){
			unset($row['user_num']);
		}
		
		if(isset($row['mobile']) && !empty($row['mobile'])){
			$row['mobile'] = PhxCrypt::phxDecrypt($row['mobile']);
		}
		$userInfo[$row['user_id']] = $row;
		session('userInfo',$userInfo);
		
		return true;
	}
	
	/*
	*	新增会员并且发送短信
	*	@Author 9009123 (Lemonice)
	*	@param  string $data  用户相关的信息
	*	@return exit
	*/
    public function addNewMember($data){
    	$user_id = $this->where("mobile = '$data[mobile]'")->getField('user_id');	//是否已存在

    	if($user_id > 0){
    		return array('user_id'=>$user_id);  //用户存在
    	}
    	$password = $data['password'];
    	$data['password'] = md5(md5($data['password']));
		$data['state'] = 1;  //自动注册状态
		$data['auto_reg_time'] = Time::gmTime();  //注册时间
		$user_id = $this->reg($data);  //新注册帐号
		if($user_id){
			$msg = "您已经绑定手机号码！登陆瓷肌会员中心激活账号即可查看订单物流，享受积分、生日礼包等福利，账号为当前手机号，密码：".$password;
			Send::send_sms($data['sms_mobile'],$user_id,$data['ip'],$msg);  //发短信，不管结果是否成功
		}
		return array('new_user_id'=>$user_id);  //新用户，返回字段不同，区别
    }

    /**
     * 用户注册
     * @author 9009221
     * @param $data
     * @return bool
     */
    public function reg($data) {
        if ($data) {
            $ret = $this->add($data);
            if ($ret) {
                session('user_id',$ret);
                return $ret;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 更新用户信息
     * @author 9009221
     * @param $data
     * @return bool
     */
    public function update($data, $where) {
        if (!empty($where)) {
            $user = $this->table('users');
            $ret = $user->where($where)->save($data);
            if ($ret) {
                return $ret;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * 更新用户的登录信息
     * @author 9009221
     * @return bool
     */
    public function update_user_info() {
        if (empty($_SESSION['user_id'])) {
            return false;
        }
        $user = $this->table('users');
        $row = $user->field('last_time,last_ip')->where("user_id={$_SESSION['user_id']}")->find();
        if ($row) {
            $_SESSION['last_time'] = $row['last_time'];
            $_SESSION['last_ip'] = $row['last_ip'];
        }

        /* 更新登录时间及登录IP  */
        $data['last_ip'] = get_client_ip();
        $data['last_time'] = time();
        $where['user_id'] = session('user_id');
        $user->where($where)->save($data);
    }

    /**
     * 检测手机号码是否注册
     * @author 9009221
     * @param $username
     * @return bool
     */
    public function checkMobile($username) {
        if (!empty($username)) {
			$mobile = '';
            if (preg_match('/^\d{11}$/', $username)) {
                $mobile = PhxCrypt::phxEncrypt($username);
            }else{
				return false;
			}
            $res = $this->where(array('mobile'=>$mobile))->find();
            if($res){
                return true;
            }
        }
        return false;
    }

    /**
     * 检测邮箱是否注册
     * @author 9009221
     * @param $email
     * @return bool
     */
    public function checkEmail($email) {
        if (isset($email) && !empty($email)) {
            $user = $this->table('users');
            $res = $user->where("email='{$email}'")->find();
            if ($res) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检测会员NO
     * @param $user_num
     * @return bool
     */
    public function checkUser_num($user_num) {
        if ($user_num) {
            $user = $this->table('users');
            $res = $user->where("user_num LIKE '%{$user_num}%'")->find();
            if ($res) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取用户优惠券
     * @param $user_id
     * @param $page
     * @param $size
	 * @param $used 是否可使用，1=可使用，0=不可使用、过期
     * @return array
     */
    public function getUserBonusList($user_id, $page=1, $size=8, $used = 1) {
        $now_time = Time::gmTime();
        $offset = ($page-1)*$size;
		$site_id = C('SITE_ID');
		if($used == 1){  //可使用的
			$where = "user_id='$user_id' and $now_time <= if(ub.end_time>0,ub.end_time,bt.use_end_date) and used_time = 0 and (FIND_IN_SET('$site_id',use_site) or use_site = 0)";
		}else{  //过期的
			$where = "user_id='$user_id' and $now_time > if(ub.end_time>0,ub.end_time,bt.use_end_date) and used_time = 0 and (FIND_IN_SET('$site_id',use_site) or use_site = 0)";
		}
        
        $userBonusList = $this->table('user_bonus as ub')
                ->join('bonus_type as bt on ub.bonus_type_id=bt.type_id')
                ->field('count(ub.bonus_id) as number,bt.type_money, bt.min_goods_amount, bt.type_name,bt.use_start_date, bt.use_end_date, ub.bonus_sn, ub.start_time, ub.end_time, ub.used_time')
                ->where($where)->limit($offset, $size)->group('bonus_type_id')->select();
		
        foreach ($userBonusList as $k => $ub) {
            if (strpos($ub['type_name'],"注册现金劵")!==false) {
                $userBonusList[$k]['valid_period']="无限制";
            } else {
                $start_time = !empty($ub['start_time']) ? Time::localDate("Y-m-d",$ub['start_time']) : Time::localDate("Y-m-d",$ub['use_start_date']);
                $end_time = !empty($ub['end_time']) ? Time::localDate("Y-m-d",$ub['end_time']) : Time::localDate("Y-m-d",$ub['use_end_date']);
                $userBonusList[$k]['valid_period'] = $start_time." - ".$end_time;
            }
            $userBonusList[$k]['min_goods_info'] = ($ub['min_goods_amount']>0) ? "消费满".sprintf("%d",$ub['min_goods_amount'])."元可用" : '';
        }
        return $userBonusList;
    }

    /**
     * 获取用户信息
     * @param $user_id
     * @return bool
     */
    public function getUserInfo($user_id) {
        if (isset($user_id)) {
            $user = $this->table('users');
            $res = $user->where("user_id='{$user_id}'")->find();
            if ($res) {
                $res['mobile'] = PhxCrypt::phxDecrypt($res['mobile']);
                return $res;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 获取订单信息
     * @param $params
	 * @param $get_goods  是否获取对应订单的商品
     * @return array
     */
    public function getOrder($params, $get_goods = false) {
        $order = $this->table('order_info');
        if ($params['outid']) {
            $where = "invoice_no = '{$params['outid']}' AND shipping_status != 2";
        } elseif($params['mobile']) {
            $where = "mobile = '{$params['mobile']}' AND order_status = 1 AND shipping_status != 2";
        }
        $data = $order->where($where)->select();
		if($get_goods == true && !empty($data)){
			foreach($data as $key=>$value){
				$value['goods_list'] = $this->getOrderGoods('',$value['order_id']);
				$data[$key] = $value;
			}
		}
        return $data;
    }
	
	/**
     * 根据订单号码，获取对应的商品列表
     * @param $order_sn
     * @return mixed
     */
	private function getOrderGoods($order_sn = '',$order_id = ''){
		if($order_sn == '' && empty($order_id)){
			return array();
		}
		$order = $this->table('order_goods');
		if(!empty($order_sn)){
			$where['order_sn'] = $order_sn;
		}
		if(!empty($order_id)){
			$where['order_id'] = $order_id;
		}
		$where['extension_code'] = array('neq', 'package_goods');
		$data = $order->field('goods_id,goods_name,goods_number,goods_price,extension_code')
				->where($where)
				->group('goods_id')
				->select();
		return $data;
	}

    public function addWxLog($data) {
        $this->table('wx_log')->add($data);
    }

    /**
     * 登录日志
     * @param $username
     * @param int $site_id
     * @param string $source
     * @param int $status
     * @return bool
     */
    public function login_log($username,$site_id = 0,$source = '', $status = 0) {
        if(empty($username)) return false;

        if (is_phone($username)) {
            $username = PhxCrypt::phxEncrypt($username);
        }
        $user = $this->table('users');
        $row = $user->where("(email = '{$username}' or mobile = '{$username}' or user_num = '{$username}')")->find();
        $log = $this->table('login_log');
        $data = array(
                'user_id'   => $row['user_id'],
                'site_id'   => $site_id,
                'source'    => $source,
                'status'    => $status,
                'ip_address'=> get_client_ip(),
                'log_time'  => time(),
        );
        if($log->add($data)) {
            return true;
        }else {
            return false;
        }
    }
}