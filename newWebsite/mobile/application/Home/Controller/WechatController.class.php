<?php
/**
 * ====================================
 * 微信
 * ====================================
 * Author: 9009221
 * Date: 2016-07-25
 * ====================================
 * File: WechatController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Wechat;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;
use Common\Extend\Logistics;
use Common\Extend\Curl;
use Think\Cache\Driver\Memcache;


class WechatController extends InitController {
    protected $user = NULL;
    protected $wechat = NULL;
	private $Memcache = NULL;
	
	/* 订单状态 */
	private $order_status = array(
		'0' => '未确认',
		'1' => '已确认',
		'2' => '已取消',
		'3' => '无效',
		'4' => '退货',
		'5' => '异常',
		'6' => '丢失',
		'99' => '假删除标记',
	);
	/* 物流状态 */
	private $shipping_status = array(
		'0' => '未发货',
		'1' => '已发货',
		'2' => '已收货',
		'3' => '配货中',
		'4' => '已打单',
		'5' => '配货审核中',
		'6' => '配货审核退回',
		'7' => '已打捡货单',
		'8' => '已打包',
		'9' => '压单',
		'20' => '仓库返回异常',
		'30' => '退货已签收',
	);
	
	//定义客服回复菜单
	private $menu_array = array(
		array(
			'number'=>1,  //回复序号
			'title'=>'查询订单与快递信息',  //显示的名称标题
			'function'=>'sendMemberLogistics',  //处理的方法
		),
		array(
			'number'=>2,  //回复序号
			'title'=>'查询会员等级',  //显示的名称标题
			'function'=>'sendMemberLevel',  //处理的方法
		),
		array(
			'number'=>3,  //回复序号
			'title'=>'查询会员积分',  //显示的名称标题
			'function'=>'sendMemberIntegral',  //处理的方法
		),
	);	
	
    public function __construct() {
        parent::__construct();
        Wechat::$app_id = C('appid');
        Wechat::$app_secret = C('appsecret');
		
        $this->user = D('Users');
        $this->wechat = D('BindUser');

		$this->Memcache = new Memcache();
    }

    public function index() {
        //测试Lemonice
        /*$data = array(
            'MsgType'=>'voice',
            'FromUserName'=>'oFJj4s0X8rLPta-HwWvSoXWo5H3s',
            'Recognition'=>'测试的快递是不是',
        );
        Wechat::$userOpenId = $data['FromUserName'];*/
        //$this->fwcheck($data);
        //die;
        $data = Wechat::postData();
		if($data['FromUserName'] == 'oFJj4s0X8rLPta-HwWvSoXWo5H3s'){
			file_put_contents(APP_PATH . '../runtime/Logs/wechat.txt');
		}
        if(empty($data)){
            $echoStr = $_GET["echostr"];
            if(Wechat::checkSignature()){
                echo $echoStr;
                exit;
            }
        }

        if ($data && is_array($data)) {
			Wechat::$userOpenId = $data['FromUserName'];	//发送方账号openid
			switch($data['MsgType']){
				//接收事件推送
				case 'event':
					switch($data['Event']){
						case 'subscribe':		//关注
							$this->user_activity_log($data['FromUserName'],USER_ACT_SUBSCRIBE);		//记录最后活动时间
							//关注欢迎消息推送
							$content = $this->subscribe($data);
							Wechat::serviceText($content);
							//上传二维码名片，并推送图片
							$data_media = Wechat::mediaUpload("./public/images/wechat/tel_card.jpg","image");
							$media_id = $data_media['media_id'];			//生产环境
							Wechat::serviceImage($media_id);
							break;
						case 'unsubscribe':		//取消关注
							$this->user_activity_log($data['FromUserName'],USER_ACT_UNSUBSCRIBE);		//记录最后活动时间
							$this->unSubscribe($data);
							break;
						case 'CLICK':			//点击菜单事件
							$this->user_activity_log($data['FromUserName'],USER_ACT_MENU);		//记录最后活动时间
							//推送图文模版
							$wechatNewsTpl = include(APP_PATH . 'Common/Conf/wechatNewsTpl.php');
							if (isset($wechatNewsTpl[$data['EventKey']]) && !empty($wechatNewsTpl[$data['EventKey']])){
								if(isset($wechatNewsTpl[$data['EventKey']]['picurl'])){
									$domain_source = C('DOMAIN_SOURCE');
									$wechatNewsTpl[$data['EventKey']]['picurl'] = $domain_source['img_domain'].$wechatNewsTpl[$data['EventKey']]['picurl'];
								}
								echo Wechat::newsTpl($wechatNewsTpl[$data['EventKey']]);
								exit;
							}
							switch($data['EventKey']){
								/*case 'removebind':
									//取消绑定
									if ($this->removebind($data)) {
										Wechat::serviceText('已经取消手机绑定');
									} else {
										Wechat::serviceText('取消手机绑定失败，请联系客服');
									}
									break;*/
								case 'orderinquiry':
									$this->logisticsHelp($data);
									break;
								case 'queryLogistics':
									$this->logisticsHelp($data);
									break;
								case 'fwcheck':
									$this->fwcheckHelp($data);
									break;
								case 'customerService':					//多客服系统，底部菜单加入
									$this->customerService($data);			//微信多客服接入
									break;
								case 'signin':
									$signinMsg = $this->wechat->SignIn($data);
									if(empty($signinMsg)){
										Wechat::serviceText('签到人数过多，签到失败，如再次签到失败请联系客服');
									}else{
										Wechat::serviceText($signinMsg);
									}
							}
					}
					break;
				//接收被动回复消息
				case 'text':		//回复文本
					$this->user_activity_log($data['FromUserName'],USER_ACT_REPLY);		//记录最后活动时间
					$content = trim($data['Content']);
					if ($this->isMobile($content)) {
						$this->showMenu($data);  //显示菜单到微信，如果没有菜单则认为是查询物流信息
						$this->sendLogistics($data);  //发送物流信息
					} elseif ($this->isFwcode($content)) {
						$this->fwcheck($data);
					} elseif (preg_match('/^快递单号[：:]+([a-zA-Z0-9]+)$/', $content, $match)) {
						$invoice_no = $match[1];
						$this->sendLogistics($data, $invoice_no);
					}
					//输入手机号码、显示菜单、并且回复序号后-->查询对应菜单中功能的数据
					elseif(preg_match('/^([0-9]{1,2})$/', $content)){
						$this->execMenuFunction($data);
					}
					//签到
					elseif(strpos('12'.$content,'签到')){
						$signinMsg = $this->wechat->SignIn($data);
                        if(empty($signinMsg)){
                            Wechat::serviceText('签到人数过多，签到失败，如再次签到失败请联系客服');
                        }else{
						    Wechat::serviceText($signinMsg);
                        }
					}
					else{  //其他所有未识别的，都返回提示
						$this->customerService($data);		//微信多客服接入
						//echo WechatClass::textTpl('您好，您回复的内容暂时无法识别，请回复手机号即可查询订单、快递物流、会员等信息，或者回复"客服"进行客服人工咨询。');
//						echo Wechat::textTpl('您好，您回复的内容暂时无法识别，请回复手机号即可查询订单、快递物流、会员等信息。');
//						exit;
					}
					break;
				case 'image':		//回复图片
				case 'voice':		//回复语言
					$this->taskVoice($data);
					$this->user_activity_log($data['FromUserName'],USER_ACT_REPLY);		//记录最后活动时间
					break;
				case 'video':		//回复视频
				case 'shortvideo':	//回复小视频
				case 'location':	//回复位置
				case 'link':		//回复链接
					$this->user_activity_log($data['FromUserName'],USER_ACT_REPLY);		//记录最后活动时间
					break;
			}

        }
    }

	/*
	*	提示如何查询防伪码
	*	@Author 9009123 (Lemonice)
	*	@param $data 微信对话的接收参数数组
	*	@return true or false
	*/
	private function fwcheckHelp($data = array()){
		Wechat::serviceText("1.回复标签款式+标签码查询真伪（如A:44559898566554）
2.点击<a href='http://mp.weixin.qq.com/s/TFFSj--1ChOcqfG-mBqjdA'>产品使用</a>，查看护肤品使用方法。
3.点击<a href='http://mp.weixin.qq.com/s/DRKrXJkzZPqGfkJY7uP_RA'>产品使用</a>，查看美肤仪器使用方法。");
		return true;
	}

	/*
	*	提示如何查询物流信息
	*	@Author 9009123 (Lemonice)
	*	@param $data 微信对话的接收参数数组
	*	@return true or false
	*/
	private function logisticsHelp($data = array()){
		Wechat::serviceText('发送您手机号码到公众号，即可查询您的订单物流信息。');
		return true;
	}

	/*
	*	处理微信对话中的语言
	*	@Author 9009123 (Lemonice)
	*	@param $data 微信对话的接收参数数组
	*	@return true or false
	*/
    public function taskVoice($data = array()) {
		if(empty($data) || empty($data['FromUserName']) || empty($data['Recognition'])){
			return false;
		}
		$content = $data['Recognition'];  //微信自动解析好的语音文字
		
		if(strstr($content, '物流') !== false 
			|| strstr($content, '快递') !== false 
			|| strstr($content, '发货') !== false 
			|| strstr($content, '收货') !== false
		){
			$this->logisticsHelp($data);  //提示查询物流
		}else if(strstr($content, '客服') !== false 
			|| strstr($content, '服务') !== false
		){
			$this->customerService($data);			//微信多客服接入
		}else if(strstr($content, '防伪') !== false 
			|| strstr($content, '伪造') !== false 
			|| strstr($content, '真假') !== false
		){
			$this->fwcheckHelp($data);  //提示查询防伪码
		}
		
        return false;
    }

	/*
    *	进入多客服系统
    *	@Author 9009123 (Lemonice)
    *	@param $data 
    *	@return exit
    */
	private function customerService($data){
		$openid = $data['FromUserName'];
		$mobile = $this->wechat->where("openid = '$openid'")->getField('mobile');
		
		if(!$mobile){
			$content = "您好，请先绑定您的手机号码\n<a href='".siteUrl()."#/check-code'>（手机认证）</a>";
			$result = Wechat::serviceText($content);
			exit;
		}
		$list = Wechat::getAllService();  //获取所有客服资料
		$service_is_online = false;  //是否有客服在线
		if(isset($list['kf_online_list']) && is_array($list['kf_online_list']) && !empty($list['kf_online_list'])){
			foreach($list['kf_online_list'] as $value){
				if(isset($value['status']) && $value['status'] == 1){  //在线
					$service_is_online = true;
					break;
				}
			}
		}
		if($service_is_online == true){  //有客服在线
			echo Wechat::customerServiceTpl();
			exit;
		}else{  //没有客服在线
			Wechat::serviceText('您好，感谢您的咨询，现在人工客服不在线。有问题请拨打瓷肌售后热线：02022005555。测试阶段：人工服务时间14：00-18：30。带来不便，请多体谅。');
		}
	}
	
	//查询手机号码对应账号的订单和快递信息 - Add By 9009123 (Lemonice)
	private function sendMemberLogistics($data,$menu = array()){
		$data['Content'] = $data['mobile'];
		$this->sendLogistics($data);
	}
	//查询手机号码对应的会员帐号的会员等级 - Add By 9009123 (Lemonice)
	private function sendMemberLevel($data,$menu = array()){
		$mobile = PhxCrypt::phxEncrypt($data['mobile']);
		$user_id = D('Users')->where("(`mobile`='$mobile' OR `user_num`='$mobile') AND `state`!=9")->getField('user_id');
		
		if($user_id > 0){
			$rank = D('UserAccount')->where("user_id = '$user_id'")->getField('rank');
			if($rank > 0){
				$rank_name = D('UserRank')->where("rank_id = '$rank'")->getField('rank_name');
				$rank_name = $rank_name ? $rank_name : '未知级别';
			}else{
				$rank_name = D('UserRank')->order("min_points asc")->getField('rank_name');
			}
			$text = '你好，'.$data['mobile'].'先生/女士，你现在是韩国瓷肌'.$rank_name.'。';
		}else{
			$text = '亲，您还不是会员，现在注册成为韩国瓷肌会员吧，尽享更多会员福利。';
		}
		echo Wechat::textTpl($text);
		exit;
	}
	//查询手机号码对应的会员帐号的会员积分 - Add By 9009123 (Lemonice)
	private function sendMemberIntegral($data,$menu = array()){
		$mobile = PhxCrypt::phxEncrypt($data['mobile']);
		$user_id = D('Users')->where("(`mobile`='$mobile' OR `user_num`='$mobile') AND `state`!=9")->getField('user_id');
		
		if($user_id > 0){
			$result = D('IntegralCenter')->getPointsLeft($user_id);  //查询用户可用积分, 会计算被冻结的积分在内
			$user_points = $result['user_points'];
			$text = '你好，'.$data['mobile'].'先生/女士，你现在有效积分是'.$user_points.'。';
		}else{
			$text = '亲，您还不是会员，现在注册成为韩国瓷肌会员吧，尽享更多会员福利。';
		}
		echo Wechat::textTpl($text);
		exit;
	}
	//回复菜单到微信 - Add By 9009123 (Lemonice)
	private function showMenu($data = array()){
		$menu_array = $this->menu_array;
		if(isset($menu_array) && !empty($menu_array)){
			$text = '';
			foreach($menu_array as $menu){
				if(isset($menu['number']) && isset($menu['title']) && isset($menu['function']) && method_exists($this, $menu['function'])){
					$text .= $menu['number'] . '. ' . $menu['title'] . "\n";
				}
			}
			
			if($text != ''){
				$text = trim($text)."\n\n".'请回复上面序号继续操作。';
				//把手机号码记录
				$this->Memcache->set(md5('wechat_menu_'.$data['userOpenId']), trim($data['Content']), 600);  //把手机号码存起来
				echo Wechat::textTpl($text);  //输入手机号码，回复个菜单回去
				exit;
			}
		}
	}
	//调用菜单中定义的方法名称 - Add By 9009123 (Lemonice)
	private function execMenuFunction($data = array()){
		$number = intval($data['Content']);
		$menu_array = $this->menu_array;
		
		//查询
		$menu = array();
		$number_array = array();
		if($number > 0 && !empty($menu_array)){
			foreach($menu_array as $value){
				if(method_exists($this, $value['function'])){
					if($value['number'] == $number){
						$menu = $value;
					}
					$number_array[] = $value['number'];
				}
			}
		}
		if(!empty($menu)){
			//查询手机号码
			$mobile = $this->Memcache->get(md5('wechat_menu_'.$data['userOpenId']));  //获取手机号码
			if($mobile == ''){
				echo Wechat::textTpl('请先回复您的手机号码！');  //输入手机号码，回复个菜单回去
				exit;
			}
			$data['mobile'] = $mobile;
			$function = $menu['function'];
			$this->$function($data, $menu);  //调用方法
			echo '';  //输出空到微信，避免重发
			exit;
		}
		echo Wechat::textTpl('亲，您回复的序号暂时无法识别，当前可输入的序号为：'.implode('、',$number_array).'，您也可以联系下客服进行处理！');
		exit;
	}

    /*
    *	物流推送
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function sendLogistics($info = array(), $invoice_no='') {
		Wechat::$userOpenId = $info['FromUserName'];
		$info['invoice_no'] = $invoice_no ? $invoice_no : 0;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => siteUrl().'Wechat/sendWxLogistics.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_TIMEOUT => 4,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query($info, '', '&'),
			CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded;charset=UTF-8"),
		));
		$res = @curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		echo '';  //回复空文本，避免微信服务器重发
    }
	
	/**
	 *	物流微信推送
	 *	@params invoice_no 物流单号
	 *	@params Content 手机号
	 */
	public function sendWxLogistics(){
		$invoice_no = I('request.invoice_no', '');
		$info['Content'] = I('request.Content', '');
		$info['FromUserName'] = I('request.FromUserName', '');
		$this->sendWechatLogistics($info, $invoice_no);
	}
	
	/**
	 *	物流微信推送 - 会被外部调用
	 *	@params info 微信详情
	 *	@params invoice_no 快递单号码
	 */
	public function sendWechatLogistics($info, $invoice_no=''){
		Wechat::$userOpenId = $info['FromUserName'];
		if ($invoice_no) {
            $params['outid'] = $invoice_no;
        } else {
            $mobile = $info['Content'];
            $mobile = PhxCrypt::phxEncrypt($mobile);
            $params['mobile'] = $mobile;
        }
        $orders = $this->user->getOrder($params, true);
        $data = array();
        if ($orders) {
			$Logistics = new Logistics();
            foreach ($orders as $k => $v) {
				$logistics_platform = $v['shipping_name'];
				$functio_name = '';
                switch($v['shipping_name']){
					case 'ems快递':
					case 'EMS特快专递':
						$functio_name = 'ems';
					break;
					case '京东瓷肌快递':
						$functio_name = 'jingDong';
					break;
					case '思迈':
						$functio_name = 'sm';
					break;
					case '顺丰速运':
						$functio_name = 'sf';
					break;
					case '韵达快运':
						$functio_name = 'yunDa';
					break;
					case '申通快递':
						$functio_name = 'shengTong';
					break;
				}
				
				//发送订单信息
				$order_content = "订单编号：".$v['order_sn']."\n";
				//2016-10-18 16:00之后的订单总价都用  money_paid+order_amount
				if($v['update_time'] >= Time::localStrtotime('2016-10-18 16:00:00') || $v['integral_money'] > 0){
					$money = $v['order_amount'] + $v['money_paid'];
				}else{
					$money = $v['goods_amount'] - $v['bonus'] - $v['integral_money'] + $v['shipping_fee'] - $v['discount'] - $v['payment_discount'];
				}
				$order_content .= "订单金额：".$money."元\n";
				//显示订单和物流状态
				if(isset($v['order_status']) && isset($this->order_status[$v['order_status']])){
					$order_content .= "当前状态：".$this->order_status[$v['order_status']];
					if(isset($v['shipping_status']) && isset($this->shipping_status[$v['shipping_status']])){
						$order_content .= '、'.$this->shipping_status[$v['shipping_status']];
					}
					$order_content .= "\n";
				}
				//订单商品详情
				/*if(isset($v['goods_list']) && !empty($v['goods_list'])){
					$order_content .= "\n";
					$order_content .= "商品详情：\n";
					foreach($v['goods_list'] as $val){
						//$order_content .= $val['goods_name']."\n[".$val['goods_price'].'元 x '.$val['goods_number']."]\n";
						$order_content .= $val['goods_name']." x ".$val['goods_number']."\n";
					}
				}*/
				$order_content = trim($order_content);
				$ret = Wechat::serviceText($order_content);
				
				
				//发送物流信息
				$Logistics->setConfig('order_sn',$v['order_sn']);
				$Logistics->setConfig('invoice_no',$v['invoice_no']);
				$Logistics->setConfig('shipping_name',$v['shipping_name']);
				$result = $functio_name!='' ? $Logistics->$functio_name() : false;  //请求发送
				if($result == false){  //如果都获取不到，试着去图灵找
					if(in_array($shipping_name, array('EMS特快专递', 'ems快递'))){
						$Logistics->setConfig('shipping_name','EMS快递');
					}
					$result = $Logistics->tuRing();
				}
				$content = $Logistics->getResponse();  //获取返回值

				//拆分超长物流信息
				$len_int = ceil(strlen($content) / 1950);
				if($len_int > 1){
					$content_arr = explode("\n\n", $content);
					$content_arr_ceil =  ceil(count($content_arr) / $len_int);
					$content_arr_2 = array_chunk($content_arr, $content_arr_ceil);
					foreach($content_arr_2 as $key=>$val){
						$ret = Wechat::serviceText(trim(implode("\n\n", $val)));
					}
				}else{
					$ret = Wechat::serviceText($content);
				}
				
				//查询记录
				$log['mobile'] = empty($mobile) ? empty($info['Content'])?'': $info['Content'] : $mobile;
				$log['order_id'] = $v['order_sn'];
				$log['invoice_no'] = $v['invoice_no'];
				$log['add_time'] = Time::gmTime();
				$log['msg'] = $content;
				$log['logistics_platform'] = $logistics_platform;
                $userModel = M('WxLog',null,'USER_CENTER');
                $log_exist = $userModel->where(array('invoice_no'=>$log['invoice_no']))->find();
                if(empty($log_exist)){
                    $userModel->data($log)->add();
                }else{
                    $userModel->where(array('invoice_no'=>$log['invoice_no']))->save($log);
                }
				$data['order_sn'] = $v['order_sn'];
				$data['invoice_no'] = $v['invoice_no'];
            }
        } else {
            $content = "订单跟踪通知\n十分抱歉！翻遍整个系统也找不到您的订单信息，请您再次核对下您的收货手机号码是否正确";
            $ret = Wechat::serviceText($content);
        }
        $data['open_id'] = $info['FromUserName']?$info['FromUserName']:'';
        $data['content'] = $content ? $content : '';
        $data['type'] = '公众号查询推送';
        $data['errcode'] = intval($ret['errcode']);
        $data['errmsg'] = empty($ret['errmsg']) ? '' : $ret['errmsg'];
        $data['addtime'] = Time::gmTime();
		M('WxMsg')->data($data)->add();
	}

    /*
    *	防伪推送
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function fwcheck($info) {
		if(!class_exists('nusoap_base')){
			import('Common/Extend/Nusoap');
		}
        $codearr = explode(':', $info['Content']);
        $code = $codearr[1];
        Wechat::$userOpenId = $info['FromUserName'];
        //WechatClass::$userOpenId = 'oFJj4s0r20ii06W7NqL-yLl_rBm0';
        if ($codearr[0] == 'C') {
            $postData = array(
				'fwcode' => $code,
				'referer' => 'http://www.zxfw315.com/Gm/Default.asp?FWcode='.$code,
				'ip' => get_client_ip()
            );
            $ret = Curl::post('http://www.cn315fw.com/fwapi/query/result', $postData);
            $content = preg_match('/<div id=\"result\" .*?>.*?<\/div>/ism', $ret, $matches);
            $content = trim(strip_tags($matches[0]));
        } else {
//	$client = new soapclient1('http://ws.p-pass.com/T3315WebSrvSetup/T3315WebSrv.asmx?WSDL', true);
//	对于电信线路，如上述地址访问不了，则可访问
			$client = new \soapclient1('http://wsu.t3315.com/T3315WebSrvSetup/T3315WebSrv.asmx?WSDL', true);
            //	$client = new \soapclient1('http://220.202.11.226/T3315WebSrvSetup/T3315WebSrv.asmx?WSDL', true);
            $err = $client->getError();
            if ($err) {
                $content = '防伪验证查询连接失败，请稍后再试';
            } else {
                $cid = '4366,4367';			//企业编号 必须与入网企业编码一致
                $queryPwd = '0000';		//企业查询密码
                $parm = array('QryChannel' => '10000', 'FwCode' => $code, 'CompanyId' => $cid, 'QueryPwd' => $queryPwd, 'VerifyCode' => '', 'TermIp' => get_client_ip(), 'AddrName' => '');
                $result = $client->call('FW', array('parameters' => $parm));
				$content = '您输入的防伪码：'.$code."\n";
                if ($client->fault) {
                    $content = $result;
                } else {
                    $err = $client->getError();
                    if ($err) {
                        $content = '防伪验证查询连接失败，请稍后再试';
                    } else {
                        $pos = strpos($result, '|');
                        if ($pos === false) {
                            $content = $result;
                        } else {
                            $returnary = split('[|]',$result);
                            $content .= '查询结果： '.str_replace($code,'',$returnary[17]);
                        }
                    }
                }
            }
        }
        $ret = Wechat::serviceText($content);
        $content = $err ? $err : $content;
        $data['open_id'] = $info['FromUserName'];
        $data['content'] = $content;
        $data['type'] = '防伪查询推送';
        $data['errcode'] = $ret['errcode'];
        $data['errmsg'] = $ret['errmsg'];
        $data['addtime'] = Time::gmTime();
        D('BindUser')->addWxMsg($data);
    }

    /*
    *	用户关注
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function subscribe($info) {
        $data['openid'] = $info['FromUserName'];
        Wechat::$userOpenId = $data['openid'];
        $userInfo = Wechat::getUserInfo();

        if (empty($userInfo)) return false;
        $field = array('nickname','sex','language','city','province','country','headimgurl','remark','subscribe','subscribe_time');
        foreach ($userInfo as $key => $val) {
            if (!in_array($key, $field)) continue;
            $data[$key] = $val;
        }
		$nickname = empty($userInfo['nickname'])?'':$userInfo['nickname'].',';		//微信昵称

        $msg = "，您好，你有任何肌肤问题都可以随时联系我，我是您贴身的护肤顾问小瓷，请保存我们的官方热线电话020-22005555，识别下面发送的二维码可直接添加通讯录哦";
        if ($this->wechat->isSubcribe($data['openid'])) {
			$data['subscribe_time'] = $userInfo['subscribe_time'];		//最近关注时间（可能是第二次以上关注）
			$this->wechat->updateUser($data);
			$content = $userInfo['nickname'].$msg;
        }else{
			$data['add_time'] = $userInfo['subscribe_time'];
			$data['subscribe_time'] = $userInfo['subscribe_time'];
			$this->wechat->add($data);
			$content = $nickname.$msg;
		}
		return $content;

    }

    /*
    *	取消关注
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function unSubscribe($info) {
        $data = array('subscribe'=>0,'cancel_time'=>time(),'openid'=>$info['FromUserName']);
        $this->wechat->updateUser($data);
		//删除标签的绑定
		D('WechatTagBind')->where("openid = '$info[FromUserName]'")->delete();
    }

    /*
    *	取消绑定
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    private function removebind($info) {
        $data = array('mobile'=>'', 'openid'=>$info['FromUserName']);
        return $this->wechat->updateUser($data);
    }

    public function isMobile($mobile) {
        return isMobile($mobile);
    }

    public function isFwcode($code) {
        $type = array('A','B','C');
        $arr = explode(':', $code);
        if (in_array($arr[0], $type) && preg_match('/\w/', $arr[1])) {
            return true;
        } else {
            return false;
        }
    }

}
