<?php
/**
 * ====================================
 * 用户中心
 * ====================================
 * Author: 9009221
 * Date: 2016-06-28
 * ====================================
 * File: UserController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Integral;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;
use Common\Extend\Send;

class UserController extends InitController {

    protected $_muser = null;

    public function _initialize() {
		parent::_initialize();
        if(is_null($this->_muser) && ACTION_NAME != 'getInformations'){
            $this->_muser = D('Users');
        }
    }

    /*
    *	登录
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function login() {		
        if (IS_AJAX) {
            $username = I('username', '', 'trim');
            $password = I('password');
            $code = I('code', '', 'trim');
            $password_error = session('password_error');
			
            if (!$username || !$password) {
                $this->error('用户名或密码不能为空');
            }

            if (!$this->_muser->checkMobile($username)) {
                $this->error('没有该用户');
            }

            if (isset($password_error) && $password_error > 2 && !$this->check_verify($code)) {
                $this->error('验证码错误');
            }

            $password = md5(md5($password));
            $result = $this->_muser->login($username, $password);
			if ($result != false) {
                $this->_muser->update_user_info();
                $this->_muser->login_log($username,C('site_id'));
				$user_id = session('user_id');
                // add by 9006765
                if($user_id > 0){
                    D('cart')->updateCartUserInfo($user_id);
                }
				//检查是否有临时添加的地址，有则自动归属到当前会员 add by 9009123
                if($user_id > 0){
					D('UserAddress')->setRealUserAddress($user_id);
                }
                $this->bindWechat($username); // Add by 90004396
				session('password_error', null);
                $this->success($result);
            } else {
                if (empty($password_error)) {
                    $password_error = 0;
                    session('password_error', 0);
                }
                $password_error++;
                session('password_error', $password_error);
                $this->error('密码错误',$password_error, -1);
            }
        } else {
            $this->display();
        }
    }
	
	/*
    *	退出登录
    *	@Author 9009123
    *	@return exit && JSON
    */
    public function logout() {
        session('user_id',null);
		session('userInfo',null);
		$this->success();
    }
	
	/*
    *	检查用户是否存在
    *	@Author 9009123
    *	@return exit && JSON
    */
    public function checkUserExists() {
		$mobile = I('mobile',null,'trim');
		if ($mobile == null || !is_phone($mobile)) {
			$this->error('手机格式不对');
		}
        if ($this->_muser->checkMobile($mobile) ) {
			$this->success(array('is_register'=>1));  //该手机号已被注册
		}
		$this->success(array('is_register'=>0));
    }

    /*
    *	注册
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function register() {
        if (IS_AJAX) {
            $username = I('username', '', 'trim');
            $password = I('password');
            $rePassword = I('re_password');
            $mobile = I('mobile');
            $code = strtolower(I('code', '', 'trim'));
            $email = I('email', '', 'trim');
            if (!is_phone($mobile)) {
                $this->error('手机格式不对');
            }
            if ($this->_muser->checkMobile($mobile) ) {
                $this->error('该手机号已被注册');
            }
            if ($password != $rePassword || empty($rePassword)) {
                $this->error('两次密码不一致');
            }
            if (!Send::checkMobileCode($code,0,$mobile)) {
                $this->error('验证码不正确');
            }
            $data['user_name'] = $username;
            $data['mobile'] = PhxCrypt::phxEncrypt($mobile);
            $data['password'] = md5(md5($password));
            $data['email'] = $email;
            $data['reg_time'] = Time::gmTime();
            $data['state']    = 0;
            
            $newUserId = $this->_muser->reg($data);
            if ($newUserId) {
                $this->bindWechat($username); // Add by 90004296
                $this->success('注册成功');
            } else {
                $this->error('注册失败，请重试');
            }
        } else {
            $this->display('');
        }
    }

    /**
     * 绑定微信用户
     * @author 90004296
     * @time   2017-3-2
     * @param $mobile
     * @return bool
     */
    private function bindWechat($mobile){
        $bindUserModel = D('BindUser');
        $mobile = PhxCrypt::phxEncrypt($mobile);
        if($bindUserModel->getBindMobile($this->openId) == false){
            if(empty($this->user_id)){
                $userInfo = $this->_muser->where(array('mobile' => $mobile))->find();
                $user_id = $userInfo['user_id'];
            }else{
                $user_id = $this->user_id;
            }
            $data['openid'] = $this->openId;
            $data['mobile'] = $mobile;
            $ret = $bindUserModel->updateUser($data);
            if($ret){
                //签到积分入账
                if($user_id > 0){
					//is_merge_integral=0,1 是否已经合并过签到积分
                    $bindUserInfo = $bindUserModel->field('points_left,is_merge_integral')->where("openid = '$this->openId'")->find();  //未绑定时候的签到积分
					$points = isset($bindUserInfo['points_left']) ? $bindUserInfo['points_left'] : 0;
					$is_merge_integral = isset($bindUserInfo['is_merge_integral']) ? $bindUserInfo['is_merge_integral'] : 0;
                    if($points > 0 && $is_merge_integral <= 0){
                        $IntegralObject = new Integral();
                        $result = $IntegralObject->variety(C('SITE_ID'), $points, '签到积分到账', 0, false, array('user_id'=>$user_id,'type' => 1));
                        if($result){
                            $bindUserModel->where("openid = '$this->openId'")->save(array('is_merge_integral'=>1));
                        }
                    }
                }
            }
            return $ret ? true : false;
        }else{
            return false;
        }
    }

    /*
    *	找回密码
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function find_password() {
        if (IS_AJAX) {
            $type = I('type', '', 'intval');
            
            if ($type == 1) {
				$find_num = session('find_num');
				if ($find_num > FIND_PASSWORD) $this->error('操作次数太多');
                if (empty($find_num)) {
                    $find_num = 0;
                    session('find_num', 0);
                }
                $find_num++;
                session('find_num', $find_num);
                //验证用户名
                $username = I('request.username', '', 'trim');
                if (!$this->_muser->checkMobile($username) && !$this->_muser->checkEmail($username)) {
					
                    $this->error('没有该用户');
                }
                $check = is_phone($username) ? 'mobile' : 'email';
                session('s_name', $username);
                $this->success(array('check'=>$check));
            } elseif ($type == 2) {
                $check = I('check', 'mobile');
				//$mobile = I('mobile','','trim');
				$mobile = I('username','','trim');
                $code = I('code');
                if ($check == 'mobile') {  //手机号码找回密码
					/*if ($mobile == null || !is_phone($mobile)) {
						$this->error('手机格式不对');
					}*/
					if (!$this->_muser->checkMobile($mobile)) {
						$this->error('手机号码未注册');
					}
                    $ret = Send::checkMobileCode($code,0,$mobile);
                } else {  //email找回密码
					if (!$this->_muser->checkEmail($mobile)) {
						$this->error('邮箱地址未注册');
					}
                    $email_code = session('email_code');
                    $ret = $email_code == $code ? true : false;
                }
                if ($ret) {
                    $this->success();
                } else {
                    $this->error('验证码不正确');
                }
            } elseif ($type == 3) {
                $password = I('password');
                $re_password = I('re_password');
                if ($password != $re_password) {
                    $this->error('密码不一致');
                }
                $data['password'] = md5(md5($password));
                $s_name = session('s_name');
				$where = is_phone($s_name) ? "mobile='".PhxCrypt::phxEncrypt($s_name)."'" : "email='".$s_name."'";
				
				//更改密码
				$this->_muser->update($data, $where);
                $this->success();
            }
        } else {
            $this->display();
        }
    }
	
	/*
    *	修改帐号的密码
    *	@Author 9009123
    *	@return exit && JSON
    */
	public function resetPassword(){
		$old_password = I('old_password');
		$password = I('password');
		$re_password = I('re_password');
		
		if(!$old_password){
			$this->error('请输入原密码');
		}
		
		$user_id = session('user_id');
		if(!$user_id){
			$this->error('请先登录');
		}
		
		if ($password != $re_password) {
			$this->error('密码不一致');
		}
		
		$old_password = md5(md5($old_password));
		$re_old_password = $this->_muser->where("user_id = '$user_id'")->getField('password');
		if(!$re_old_password){
			$this->error('请先登录');
		}
		if($old_password != $re_old_password){
			$this->error('您的原密码不正确');
		}
		
		$data['password'] = md5(md5($password));
		//更改密码
		$this->_muser->update($data, "user_id = '$user_id'");
		$this->success();
	}

    /*
    *	获取我的优惠券
    *	@Author 9009221
    *	@return exit && JSON
    */
    public function getBonus() {
        if (IS_AJAX) {
            $user_id = session('user_id');
            if (!$user_id) {
                $this->error('请先登录');
            }
            $page = I('page',1,'intval');
			$used = I('used',1,'intval');  //$used 是否可使用，1=可使用，0=不可使用、过期
            $bonus = $this->_muser->getUserBonusList($user_id, $page, 8, $used);
			
            if ($bonus) {
                $this->success($bonus);
            } else {
				$this->success(array());
            }
        } else {
            $this->display();
        }
    }
	
	/*
    *	获取站内信列表
    *	@Author 9009123
    *	@return exit && JSON
    */
    public function getInformations() {
		$page = max(I('request.page',1,'intval'),1);
		$pageSize = I('request.page_size',3,'intval');
		
		$is_promotion = I('request.is_promotion',NULL);
		
		$start = ($page - 1) * $pageSize;
		
		$root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : str_replace('/application/','',APP_PATH);  //根目录
		$list = include($root . '/application/Common/Conf/informations.php');
		$list = is_array($list)&&!empty($list) ? $list : array();
		
		$site_id = C('site_id');
		
		$list = isset($list[$site_id]) ? $list[$site_id] : array();  //根据不同站点显示不同的数据
		
		$list = $this->filterData($list,$is_promotion);  //过滤过期的站内信
		
		$data = array();
		if($pageSize > 0){
			if(!empty($list)){
				$i = 0;
				foreach($list as $key=>$value){
					if($i >= $start && $i < $start + $pageSize){
						$data[] = $value;  //分页用的
					}
					$i++;
				}
			}
		}else{
			$data = $list;
		}
		
        $this->success($data);
    }
	
	/*
    *	过滤站内信列表
    *	@Author 9009123
	*	@param $list array  站内信列表
	*	@param $is_promotion 是否促销，1 or 0
    *	@return array
    */
	private function filterData($list = array(), $is_promotion = NULL){
		if(empty($list)){
			return $list;
		}
		$return = array();
		foreach($list as $value){
			$add = true;
			if(!is_null($is_promotion) && $value['is_promotion'] != $is_promotion){
				$add = false;
			}
			$time = Time::gmTime();
			
			//时间限制
			if($value['start_time'] >= $time || $value['end_time'] <= $time){
				$add = false;
			}
			if($add == true){
				$return[] = $value;
			}
		}
		return $return;
	}
	
	/*
    *	修改头像
    *	@Author 9009123
    *	@return exit && JSON
    */
    public function changePhoto() {
        $user_id = session('user_id');
		if (!$user_id) {
			$this->error('请先登录');
		}
		$photo = I('request.photo','','trim');  //base64的图片文件
		
		if(strlen($photo) < 4){
			$this->error('上传头像出错了！');
		}
		$data = explode('base64,',$photo);
		if(isset($data[1]) && strlen($data[1]) > 4){
			$type = strtolower($data[0]);
			$photo_content = base64_decode($data[1]);
			
			//重新检查图片类型
			if(strstr($type, 'jpeg') != false || strstr($type, 'jpg') != false){
				$suffix = '.jpg';
			}elseif(strstr($type, 'gif') != false){
				$suffix = '.gif';
			}elseif(strstr($type, 'png') != false){
				$suffix = '.png';
			}elseif(strstr($type, 'bmp') != false){
				$suffix = '.bmp';
			}else{
				$this->error('只允许上传jpg、jpeg、gif、png、bmp格式的图片，请确认后重试!');
			}
			
			$root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : str_replace('/application/','',APP_PATH);  //根目录
			$path = '/upload/user/'.$user_id.'/';
			$url = $root . $path . md5(rand(100000,999999)) . $suffix;
			makeDir($root . $path);  //检查目录是否存在，不存在则创建
			
			@file_put_contents($url, $photo_content);
			
		}else{
			$this->error('上传头像出错了！');
		}
		
		$UserInfo = D('UserInfo');
		
		$old_photo_url = $UserInfo->where("user_id = '$user_id'")->getField('photo_url');
		$url = str_replace($root,'',$url);
		
		
		$UserInfo->create(array('photo_url'=>$url));
		$rs = $UserInfo->where("user_id = '$user_id'")->save();
		if($rs > 0 && $old_photo_url != '' && $old_photo_url != $url && file_exists($root.$old_photo_url)){
			@unlink($root.$old_photo_url);
		}
		if($this->user_id > 0){
			$user_info = session('userInfo');
			$user_info[$this->user_id]['photo_url'] = $url;
			session('userInfo', $user_info);
		}
		
		$this->success($url);
    }

    /*
    *	发送验证码
    *	@Author 9009221
    *	@return exit && JSON
    */
    public function sendSms() {
        //if (IS_AJAX) {
			$mobile = I('mobile');
            $code = Send::setMobileCode(300, $mobile);
            $msg    = '亲爱的用户，您好，验证码为'.$code.'，请继续完成验证。如非本人操作，请及时致电020-22005555反馈情况，避免给您带来损失。';
            $ip = get_client_ip();
            $user_id = 0;

            if(!empty($mobile) && !empty($code)){
                $result = Send::send_sms($mobile,$user_id,$ip,$msg,'code');
				//$result['error'] = 'M000000';
				//setcookie('yzcode',$code,time()+86400,'/');
                if($result['error'] == 'M000000'){
                    $this->success();
                }elseif($result['error'] == 'M000006'){
                    $this->error('发送失败');
                }else{
                    $this->error($result['message']);
                }
            }
       //}
    }

    /*
    *	发送邮件
    *	@Author 9009221
    *	@return exit && JSON
    */
    public function sendEmail(){
        if(IS_AJAX){
            $email_code = rand(000000,999999);
            $email = I('email') ? I('email') : session('s_name');
            $str_tmp = '<p>尊敬的'.$email.',您好：<br/><br/>您在瓷肌会员中心点击了“忘记密码”按钮，系统自动为您发了这封邮件。您修改密码的验证码为'.$email_code.'<br/>如有任何疑问，请致电 020-22005555 </p><br/>';
            if(Send::send_mail($email,'找回密码',$str_tmp)){
                session('email_code', $email_code);
                $this->success();
            }else{
                $this->error('邮件发送失败');
            }
        }

    }

    /*
    *	验证码
    *	@Author 9009221
    *	@return exit && JSON
    */
    public function verify() {
        $options = array(
            'fontSize' => 12,
            'length' => 4,
            'imageW' => 92,
            'imageH' => 32,
        );
        $Verify = new \Think\Verify($options);
        $Verify->entry();
    }

    /*
    *	检验验证码
    *	@Author 9009221
    *	@return bool
    */
    public function check_verify($code, $id = '') {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }



    public function userSkin(){
        $photo = I('request.file','','trim');  //base64的图片文件
        if(strlen($photo) < 4){
            $this->error('上传图像出错了！');
        }
        $data = explode('base64,',$photo);
        if(isset($data[1]) && strlen($data[1]) > 4){
            $type = strtolower($data[0]);
            $photo_content = base64_decode($data[1]);

            //重新检查图片类型
            if(strstr($type, 'jpeg') != false || strstr($type, 'jpg') != false){
                $suffix = '.jpg';
            }elseif(strstr($type, 'gif') != false){
                $suffix = '.gif';
            }elseif(strstr($type, 'png') != false){
                $suffix = '.png';
            }elseif(strstr($type, 'bmp') != false){
                $suffix = '.bmp';
            }else{
                $this->error('只允许上传jpg、jpeg、gif、png、bmp格式的图片，请确认后重试!');
            }

            $root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : str_replace('/application/','',APP_PATH);  //根目录
            $path = '/upload/user/user_skin/';
            $url = $root . $path . md5(rand(100000,999999)) . $suffix;
            makeDir($root . $path);  //检查目录是否存在，不存在则创建

            @file_put_contents($url, $photo_content);

        }else{
            $this->error('上传头像出错了！');
        }
        $tel = I('request.phoneNum','','trim');
        if(empty($tel)){
            $this->error('手机不能为空');
        }
        /* 手机号加密 */
        $tel = PhxCrypt::phxEncrypt($tel);
        $now = time();

        $user_skin_model = D('user_skin');
        $day_times = $user_skin_model->where("tel = '".$tel."' and add_time > ($now - 3600*24) and add_time < $now")->count();
        if($day_times >= 10){
            $this->error('24小时内只能上传10张');
        }
        $url = str_replace($root,'',$url);
        $user_skin_model->create(array('tel'=>$tel,'pic'=>$url,'add_time'=>$now));

        if($user_skin_model->add()){
            $this->success('提交成功');
        }else{
            $this->error('信息保存失败，请稍后再试');
        }

    }


    public function getCollectGoods(){
//        $this->user_id = 238082;
        if(empty($this->user_id)){
            $this->error('请先登录');
        }
        $page = I('request.page',1,'intval');
        $limit = I('request.limit',10,'intval');
        $site_id = C('site_id');
        $collectGoods = D('collectGoods')->where(array('user_id' => $this->user_id,'site_id' => $site_id))->page($page,$limit)->select();
		if(!empty($collectGoods)){
			foreach($collectGoods as $key=>$value){
				$goods_info = D('Goods')->field('is_delete,is_on_sale,market_price,shop_price,is_package')->where("goods_id = '$value[goods_id]'")->find();
				$value['shop_price'] = 0;
				$value['is_on_sale'] = 0;
				$value['is_delete'] = 1;
				$value['is_package'] = 0;
				
				if(!empty($goods_info)){
					$value = array_merge($value,$goods_info);
					if($value['is_package'] == 1){
						$ext_info = D('GoodsActivity')->where("goods_id = '$value[goods_id]'")->getField('ext_info');
						$ext_info = $ext_info ? unserialize($ext_info) : array('package_price'=>$value['shop_price']);
						$value['shop_price'] = $ext_info['package_price'];
					}
				}
				$collectGoods[$key] = $value;
			}
		}
		
        $this->success($collectGoods);
    }

}