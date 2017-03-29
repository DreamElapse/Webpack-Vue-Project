<?php
/**
 * ====================================
 * 前端公共类
 * ====================================
 * Author: 9004396
 * Date: 2016-06-21 9:33
 * ====================================
 * File: InitController.class.php
 * ====================================
 */
namespace Common\Controller;
use Home\Model\UsersModel;
use Common\Extend\Base\Config;
use Think\Controller;



class InitController extends Controller{
	protected $status = null;  //请求状态
	protected $msg = null;  //请求信息
	protected $user_id = 0;
    protected $openId;


    public function __construct(){
        parent::__construct();
        $this->openId = $openId = session('sopenid');

        if(empty($this->user_id)){
            $this->user_id = session('user_id');
        }
        //已登陆则不需要自动登陆
        if(empty($this->user_id)){
            $userModel = new UsersModel();
            $userModel->wechatLogin($openId);
        }
    }
    
    public function _initialize(){
        Config::init();
        $db_config = C('DB_CONFIG');
        if(!empty($db_config) && is_array($db_config)){ //根据域名切换对应数据库
            foreach ($db_config as $item) {
                if(in_array($_SERVER['HTTP_HOST'], $item['HOST'])){
					foreach ($item as $key=>$value){
						switch ($key){
							case "CONFIG":
								C($item['CONFIG']);//重置数据配置
								break;
							case "HOST":
								break;
							default:		//设置其他参数
								C($key,$value);
						}
					}
                }
            }
        }
		$host = C('domain');
		$domain = array();
		foreach ($host as $item){
			$domain = array_merge($domain, $item);
		}
		if(!in_array($_SERVER['HTTP_HOST'],$domain)){
			$this->error('请求失败，网关错误');
		}

		
        $source_domain = array();
        $resource = C('resource');
        $directory = 'res/';
        $directory .= C('site_id') == 14 ? 'q/' : '3g/';
        $source_domain['img_domain'] = $resource['IMG_URL'].$directory;
        $source_domain['static_domain'] = $resource['CSS_URL']; //JS和css暂时使用一个链接
		C('domain_source', $source_domain);
    }
	
	/*
	*	成功返回结果
	*	@Author 9009123 (Lemonice)
	*	@param  anything $data  返回的数据
	*	@return exit && JSON
	*/
	protected function success($data = '', $status = 1,$exit=1){
		$return = array(
			'status' => $status,
			'msg' => 'Success',
			'data' => $data
		);
		if(!is_null($this->status)){
			$return['status'] = $this->status;
		}
		if(!is_null($this->msg)){
			$return['msg'] = $this->msg;
		}
        if($exit){
            $this->ajaxReturn($return);
        }else{
            return $return;
        }

	}
	
	/*
	*	错误返回结果
	*	@Author 9009123 (Lemonice)
	*	@param  anything $data  错误信息
	*	@return exit && JSON
	*/
	protected function error($msg = '', $data = '', $status = 0,$exit=1){
		$return = array(
			'status' => $status,
			'msg' => $msg,
			'data' => $data
		);
		if(!is_null($this->status)){
			$return['status'] = $this->status;
		}
		if(!is_null($this->msg)){
			$return['msg'] = $this->msg;
		}
        if($exit){
            $this->ajaxReturn($return);
        }else{
            return $return;
        }

	}

	/**
	 * 记录会员在公众号的最后活动时间
	 * @param $openid
	 * @param $activity_type
	 * @return bool|mixed
	 */
	protected function user_activity_log($openid,$activity_type){
		$data['last_activity_time'] = time();
		$data['activity_type'] = $activity_type;

		$user_activity = new \Cpanel\Model\UserActivityModel();
		$row = $user_activity->where("openid = '$openid'")->find();
		if($row){
			$ret = $user_activity->where("openid = '$openid'")->save($data);
		}else{
			$data['openid'] = $openid;
			$ret = $user_activity->add($data);
		}

		return $ret;
	}
}