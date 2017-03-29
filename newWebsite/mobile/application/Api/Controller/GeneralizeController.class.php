<?php
/**
 * ====================================
 * 爬虫推广测试接口类
 * ====================================
 * Author:
 * Date:
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: GeneralizeController.php
 * ====================================
 */
namespace Api\Controller;
use Common\Controller\ApiController;
use Common\Extend\PhxCrypt;

class GeneralizeController extends ApiController {

    protected $params;
    protected $model;
    protected $reptile_key = '@#!*%chinaskin-baifuni%*!#@'; //sign_key 签名key
    protected $rpt_qq_type = array('marketing'=>2, 'self'=>1); //qq类型，2为营销QQ，1为个人QQ
	private $api_tms = 'http://res.cjlady.com/public/data/';	//文件接口路径

    public function __construct()
    {
        parent::__construct();
        $this->model = M('ReptileLog', 'thinkphx_', 'APPS');
//        $this->params = I('post.');
        $this->params = I('request.');
    }
    public function index(){
        $str = '<h1 style="text-align: center; padding-top: 20%">Generalize API Interface</h1>';
        die($str);
    }

    /**
     * 记录访问并返回页面状态
     */
    public function logs(){
        $request_time = microtime(true);    //日志接口请求时间

        //TODO：检查的类型，1=只校验是否为爬虫，其他值为全部校验
        $check_type = isset($this->params['check_type']) ? intval($this->params['check_type']) : 0;

        $ip = $this->params['ip'];
        $url_num = $this->params['num'];
        $site_id = $this->params['site'];
        $campaign = $this->params['campaign'] ? $this->params['campaign'] : 0;;  //渠道，获取QQ用的
        $where['ip'] = $ip;
        $where['url_num'] = $url_num;
        $where['site_id'] = $site_id;
        $logs = $this->getLog($where);
        $return = array();
        /**
         * TODO:
         * 表中无记录，则为首次访问，记录并返回审核页面状态，$rpt_view = 1
         * 否则，若 $logs['view_num'] > 0 则表明引链被爬虫或其他访问过，返回 $rpt_view = 2 表是显示审核页面
         * 否则，返回 $rpt_view = 3 ，表是显示用户页面
         */
        $data['view_time'] = $this->params['view_time'];
        $data['view_num'] = $this->params['view_num'];  //次数，第一次是0
        $data['content'] = $this->params['content'];  //——SERVER  --json
        $data['view_page'] = $this->params['view_page'];  //链接
        $data['view_other'] = parse_url($data['view_page'], PHP_URL_HOST);
        $insert_data = array_merge($where, $data);
        if(!$logs){
            //审核页
            $this->model->add($insert_data);
            $rpt_view = 1;
        }else if($logs['view_num'] > 0){
            $rpt_view = 2;
        }else{
            $rpt_view = 3;
        }

        //TODO:加载配置
        $url_config = load_config(CONF_PATH.'generalize_url.php');
        $page_config = $url_config[$site_id][$url_num];

        $record = FALSE;
        if($page_config['on_off'] != 1 && $record){
            //TODO:屏蔽公司及本地
            if(!in_array($ip, array('14.23.61.75', '127.0.0.1'))){
                if(!($site_id==14 && $url_num==4)){
                    M('ReptileLogSh', 'thinkphx_', 'APPS')->add($insert_data);
                }
            }
        }

        ////TODO：检查的类型，1=只校验是否为爬虫，其他值为全部校验
        if($check_type == 1){
            $return['rpt_view'] = $rpt_view;  //1=默认，2=审核页，3=用户页面
            $return['timeout'] = PAGE_TIME_OUT;  //QQ号码
            $this->success($return);
        }

        //TODO:IP段是否在屏蔽的黑名单中或者http头信息中包含过滤文字
        if(!$page_config['on_off'] || !$this->checkStr($data['content'], 'crawlers') || $this->ipBlacklist($ip)){
            $rpt_view = 2;
        }

        //TODO:获取各状态页面的QQ
        $marking_qq = $page_config['qq']['marketing'];
        $self_qq = $page_config['qq']['self'];
        if($rpt_view == 1 || $rpt_view == 2){
            //TODO:审核页面显示营销QQ，如果有带渠道，则按照渠道去获取QQ，否则随机给予一个
            if($campaign){
                $rpt_qq = $marking_qq[$campaign];
            }
            if(!$rpt_qq){
                $rpt_qq = $marking_qq;
            }
            $rpt_qq_type = $this->rpt_qq_type['marketing'];
        }else{
            //TODO:用户页面QQ，以私人QQ为主，若未设置私人QQ，则使用营销QQ
            $self_qq_arr = array();
            if(!empty($self_qq)){
                //TODO:私人QQ为数组时，合并并随机取一个
                foreach($self_qq as $k=>$v){
                    if(is_array($v)){
                        $self_qq_arr = array_merge($self_qq_arr, $v);
                    }else{
                        $self_qq_arr[] = $v;
                    }
                }

                $rpt_qq = $self_qq_arr[array_rand($self_qq_arr)];
                $rpt_qq_type = $this->rpt_qq_type['self'];
            }else{
                //TODO:判断是否带渠道，有则按照渠道获取营销QQ，否则随机给一个
                if($campaign){
                    $rpt_qq = $marking_qq[$campaign];
                }
                if(!$rpt_qq){
                    $rpt_qq = $marking_qq;
                }
                $rpt_qq_type = $this->rpt_qq_type['marketing'];
            }
        }

        if(is_array($rpt_qq)){
            $rpt_qq = $rpt_qq[array_rand($rpt_qq)];
        }
        $return['rpt_view'] = $rpt_view;  //1=默认，2=审核页，3=用户页面
        $return['rpt_qq'] = $rpt_qq;  //QQ号码
        $return['timeout'] = $page_config['timeout'];  //QQ号码
        $return['rpt_qq_type'] = $rpt_qq_type;   //QQ类型，1=私人QQ，2=运销QQ
        $return['is_rpt'] = 1;
        $return['rpt_url_num'] = $url_num;  //编号
        $return['rpt_view_hide'] = '';  //meiyong

        //TODO:请求日志
        $data_request = array();
        $data_request['ip'] = $ip;
        $data_request['url_num'] = $url_num;
        $data_request['site_id'] = $site_id;
        $data_request['url'] = $this->params['view_page'];
        $data_request['request_time'] = $request_time;
        $data_request['view_num'] = array('exp', 'view_num+1');
        $data_request['content'] = $this->params['content'];
        $data_request['return_type'] = $rpt_view;
        $data_request['qq'] = $rpt_qq;
        $data_request['qq_type'] = $rpt_qq_type;
        $data_request['log_time'] = time();
        $this->requestLog($data_request);

        $this->success($return);
    }
	
    /**
     * 生成签名
     */
    public function getSign(){
        $ip = $this->params['ip'];
        $url_num = $this->params['num'];
        $site_id = $this->params['site'];
        $where['ip'] = $ip;
        $where['url_num'] = $url_num;
        $where['site_id'] = $site_id;
        $logs = $this->getLog($where);
        if($logs['view_num'] > 0){
            $this->error('0');
        }
        $id = $logs['id'];
        unset($logs['id']);
        $sign = $this->makeSign($logs);

        //TODO:更新日志
        $logs1 = $logs;
        $logs1['sign'] = $sign;
        $save['sign_time'] = microtime(true);
        $save['sign_param'] = json_encode($where);
        $save['sign_return'] = json_encode($logs1);
        M('RequestLog', 'thinkphx_', 'APPS')->where($where)->save($save);

        $this->success(array('sign'=>$sign, 'param'=>$logs));
    }

    /**
     * TODO:获取私人QQ接口
     * param $site_id int 站点id 14 q站， 87-3g站
     * param $url_num int 链接编号
     * param $id int TMS 推广链接 -> 爬虫页面列表 id
     */
    public function getQQ(){
		$site_id = I('request.site_id', 0, 'intval');
        $url_num = I('request.url_num', 0, 'intval');
        $id = I('request.id', 0, 'intval');	//TMS 推广链接 -> 爬虫页面列表 id
		$limit_ip = I('request.limit_ip', 0, 'intval');	//是否每个IP地址只返回同一个QQ号码, 1=开启限制一个IP返回同一个QQ，0=不开启
		
		if($id <= 0){
			//兼容旧的推广方法获取的QQ
			$config = load_config(CONF_PATH.'generalize_url.php');
            $id = isset($config[$site_id][$url_num]['tms_url_id']) ? $config[$site_id][$url_num]['tms_url_id'] : 0;
		}
		
		if($id > 0){
			//从tms获取最新的QQ
			$this->getCfg($id, $limit_ip);
		}else{
			$this->returnJsonp(0,'param error!','null');
		}

        /* if($site_id && $url_num){
            $qq = '';
            $qq_arr = array();
            $config = load_config(CONF_PATH.'generalize_url.php');
            $qq_arr = $config[$site_id][$url_num]['qq']['self'];
            $qqs = array();
            if(!empty($qq_arr)){
                foreach($qq_arr as $k=>$v){
                    if(is_array($v)){
                        $qqs = array_merge($qqs, $v);
                    }else{
                        $qqs[] = $v;
                    }
                }
            }
            if($qqs){
                $qq = $qqs[array_rand($qqs)];
            }
            $return = array(
                'status' => 1,
                'msg' => '',
                'data' => $qq
            );
            echo 'window.jsonp='.json_encode($return);
            exit;
        }else{
            $return = array(
                'status' => 0,
                'msg' => 'param error!',
                'data' => ''
            );
            echo 'window.jsonp='.json_encode($return);
			exit;
        } */
    }
	
	/**
	 * TODO:获取TMS那边的配置
	 * @param int $id TMS后台的链接ID
	 * @param int $limit_ip 是否限制每个IP只返回同一个QQ，1=是，0=否
	 */
	private function getCfg($id, $limit_ip = 0){
		//如果开启了限制IP，则判断是否存在QQ号码
		if($limit_ip > 0){
			$GeneralizeQqIpLimitModel = M('QqIpLimit', 'generalize_', 'USER_CENTER');
			$ip_address = get_client_ip();
			$qq = $GeneralizeQqIpLimitModel->where("link_id = '$id' and ip = '$ip_address'")->getField('qq');
			if($qq != ''){
				$this->returnJsonp(1,'',$qq);
			}
		}
		
		
		// $url = "http://www.tms.com/public/data/generalize_page/{$id}.data";
		$url = $this->api_tms."generalize_page/{$id}.data";
		/*$dir = RUNTIME_PATH.'Data/generalize_page/';
		$now_time = time();
		
		if(!file_exists($dir)){
			@mkdir($dir, 0777, true);
		}
		$file = $dir.$id.'.data';
		if(!file_exists($file)){
			$config = file_get_contents($url);
			file_put_contents($file, $config);
		}else{
			//文件修改时间超过5分钟，则重新获取更新
			$time = filemtime($file);
			echo $time;die;
			if(($now_time - $time) >= 300){
				$config = file_get_contents($url);
				file_put_contents($file, $config);
			}
			$config = file_get_contents($file);
		}*/
		$config = S('generalize_page_data_'.$id);
		if(!$config){
			$config = file_get_contents($url);
			S('generalize_page_data_'.$id,$config,array('expire'=>300));
		}
		
		if(!$config){
			$this->ajaxReturn('null');
		}
		
		$config = json_decode(str_replace('jsonp=', '', $config), true);
		// echo '<pre>';
		// print_r($config);exit;
		//目前只返回私人QQ，私人QQ无渠道区分
		$private_qq_list = array();
		if(isset($config['private_qq_list']['qqGroup'])){
			//无QQ开启时间
			foreach($config['private_qq_list']['qqGroup'] as $key=>$val){
				$private_qq_list = array_merge($private_qq_list, $val['qqList']);
			}
		}else{
			//QQ设置的开启时间，取时间最新的一组
			$qq_time = array();
			foreach($config['private_qq_list'] as $key=>$val){
				$qq_time[$key] = strtotime($val['time']);
			}
			arsort($qq_time);
			$qq_key = null;
			foreach($qq_time as $time_key=>$time){
				if($now_time>=$time_key){
					$qq_key = $time_key;
					break;
				}
			}
			if(!is_null($qq_key)){
				foreach($config['private_qq_list'][$qq_key]['qqGroup'] as $val){
					$private_qq_list = array_merge($private_qq_list, $val['qqList']);
				}
			}else{
				$this->ajaxReturn('null');
			}
		}
		
		$qq = $private_qq_list[array_rand($private_qq_list)];
		
		//记录到限制表
		if($limit_ip > 0){
			$GeneralizeQqIpLimitModel->create(array(
				'link_id'=>$id,
				'ip'=>$ip_address,
				'qq'=>$qq,
				'qq_type'=>1,  //QQ类型，1=私人QQ，2=企点QQ，3=企业/营销QQ
				'create_time'=>time(),
			));
			$GeneralizeQqIpLimitModel->add();
		}
		// echo '<pre>';
		// print_r($private_qq_list);
		$this->returnJsonp(1,'',$qq);
	}

    /**
     * 验证签名
     */
    /*public function checkSign(){
        $id = $this->params['id'];
        $sign = $this->params['sign'];
        $where['id'] = $id;
        $logs = $this->getLog($where);
        unset($logs['id']);
        $make_sign = $this->makeSign($logs);
        if($sign != $make_sign){
            $this->error();
        }
        $this->success();
    }*/

    //生成签名
    protected function makeSign($params){
        $params['reptile_key'] = $this->reptile_key;
        ksort($params);
        return md5(http_build_query($params));
    }

    /**
     * 获取记录
     * @param $where
     * @return mixed
     */
    protected function getLog($where){
        $field = 'id,ip,view_time,view_num,view_page,view_other,url_num,site_id';
        $logs = $this->model->field($field)->where($where)->find();
        return $logs;
    }

    /**
     * 添加记录
     *
     */
    public function addLog(){
        $data['ip'] = $this->params['ip'];
        $data['view_time'] = $this->params['time'];
        $data['view_num'] = $this->params['ip'];
        $data['content'] = $this->params['content'];
        $data['view_page'] = $this->params['page'];
        $data['view_other'] = $this->params['domain'];
        $data['url_num'] = $this->params['num'];
        $data['site_id'] = $this->params['site'];
        $res = $this->model->add($data);
        $this->success();
    }

    /**
     * 根据ip前三段判断是否黑名单
     * @param $ip
     * @return bool
     */
    private function ipBlacklist($ip){
        $ip_list = array(
            '61.135.172',
            '182.131.19',
            '182.140.153',
            '59.37.97',
            '183.3.234',
            '14.17.43',
//            '14.23.61',
        );
        $ip_arr = explode('.', $ip);
        array_pop($ip_arr);
        $ip_str = implode('.', $ip_arr);
        return in_array($ip_str, $ip_list);
    }

    //TODO:校验ip是否是在黑名单
    public function checkIp(){
        return $this->ipBlacklist($this->params['ip']);
    }

    /**
     * 判断字符是否在字符串中出现
     * @param $str  字符串
     * @param $check    查找的字符
     * @return bool
     */
    private function checkStr($str, $check){
        return stripos($str, $check) === FALSE;
    }


    //===================== 日志相关操作 ===================================================
    public function requestLog($data=array()){

        if($this->shieldIp($data['ip'], $data['site_id'], $data['url_num'])){
            $model = M('RequestLog', 'thinkphx_', 'APPS');
            $where['ip'] = $data['ip'];
            $where['site_id'] = $data['site_id'];
            $where['url_num'] = $data['url_num'];

            $log = $model->where($where)->find();
            if($log){
                //TODO:更新记录
                $up_data['view_num'] = $data['view_num'];
                if(!empty($log['qq2']) && !empty($log['request_time2'])){
                    $model->where($where)->save($up_data);
                }else{
                    //TODO:第二次接口请求数据为空，执行更新操作
                    $up_data['request_time2'] = $data['request_time'];
                    $up_data['return_type2'] = $data['return_type'];
                    $up_data['qq2'] = $data['qq'];
                    $up_data['qq2_type'] = $data['qq_type'];
                    $model->where($where)->save($up_data);
                }
            }else{
                //TODO:插入记录
                $model->add($data);
            }
        }
    }
    //TODO:更改日志签名状态
    public function singStatus(){
        if($this->shieldIp($this->params['ip'], $this->params['site'], $this->params['num'])){
            $where['ip'] = $this->params['ip'];
            $where['url_num'] = $this->params['num'];
            $where['site_id'] = $this->params['site'];
            M('RequestLog', 'thinkphx_', 'APPS')->where($where)->save(array('sign_status'=>$this->params['sign_status']));
        }
    }
    //TODO:更新QQ点击日志
    public function upClickqq(){
        if($this->shieldIp($this->params['ip'], $this->params['site_id'], $this->params['url_num'])){
            $where['ip'] = $this->params['ip'];
            $where['url_num'] = $this->params['url_num'];
            $where['site_id'] = $this->params['site_id'];

            $model = M('RequestLog', 'thinkphx_', 'APPS');
            $log = $model->where($where)->find();
            if(empty($log['click_qq']) && empty($log['click_time'])){
                $data['click_url'] = $this->params['click_url'];
                $data['click_time'] = $this->params['click_time'];
                $data['click_qq'] = $this->params['qq'];
                $data['click_qq_type'] = $this->params['qq_type'];
                $model->where($where)->save($data);
            }
        }
    }
    //TODO:更新一个流程的结束时间
    public function upOvertime(){
        if($this->shieldIp($this->params['ip'], $this->params['site_id'], $this->params['url_num'])){
            $where['ip'] = $this->params['ip'];
            $where['url_num'] = $this->params['url_num'];
            $where['site_id'] = $this->params['site_id'];
            $model = M('RequestLog', 'thinkphx_', 'APPS');
            $log = $model->where($where)->find();
            if(empty($log['over_time'])){
                $model->where($where)->setField(array('over_time'=>$this->params['over_time'], 'updated'=>1));
            }
        }
    }
    //日志屏蔽的ip
    private function shieldIp($ip, $site_id, $url_num){
        $ip_arr = array(
//            '127.0.0.1',
//            '14.23.61.75',
        );
        if(!in_array($ip, $ip_arr) && (($site_id == 14 && $url_num == 2) || ($site_id == 14 && $url_num == 3))){
            return true;
        }
        return false;
    }
	
	/**
     * 返回JSONP的数组信息
     * @param int $status  状态
     * @param string $msg  信息
	 * @param string $data 数据
     * @return exit
     */
	private function returnJsonp($status = 1,$msg = '',$data = ''){
		$return = array(
			'status' => $status,
			'msg' => $msg,
			'data' => $data
		);
		echo 'window.jsonp='.json_encode($return);
		exit;
	}
	
	//================================= 新版爬虫控制 =============================================
	//新爬虫限制条件
	public function checkNew(){
		$ip = I('request.ip');
		$site_id = I('request.site', 0, 'intval');
		$thems = I('request.thems');
		$server = I('request.server');
		$return['status'] = 0;
		
		/* 测试专用star */
		/* if(!(strpos($server['REQUEST_URI'], '?') === FALSE)){
			$str = substr($server['REQUEST_URI'], strpos($server['REQUEST_URI'], '?')+1);
			$uri = explode('&amp;', urldecode($str));
			foreach($uri as $k=>$v){
				list($key, $value) = explode('=', $v);
				$query[$key] = $value;
			}
			if(!empty($query['referer'])){
				$server['HTTP_REFERER'] = trim($query['referer']);
			}
			if(!empty($query['ip'])){
				$ip = trim($query['ip']);
			}
			if(!empty($query['useragent'])){
				$server['HTTP_USER_AGENT'] = trim($query['useragent']);
			}
			if(isset($query['on']) && $query['on']==1){
				$is_on = 1;
			}
			if(isset($query['on']) && $query['on']==0){
				$is_on = 0;
			}
		} */
		/* 测试专用end */
		if($this->checkNewIp($ip)){
			$return['status'] = 1;
		}
		if(!(strstr($server['REQUEST_URI'], 'This_Is_A_Example_Trace') === false)){
			$return['status'] = 1;
		}
		if(!(strstr($server['HTTP_USER_AGENT'], 'Windows') === false)){
			$return['status'] = 1;
		}
		if(
			!(strstr($server['HTTP_REFERER'], 'review.e.qq.com') === false) || 
			!(strstr($server['HTTP_REFERER'], 'tsa.oa.com') === false) || 
			!(strstr($server['HTTP_REFERER'], '10.130.67.91') === false) || 
			!(strstr($server['HTTP_REFERER'], 'review') === false) 
		){
			$return['status'] = 1;
		}
		if(is_null($is_on)){
			$is_on = $this->checkOn($site_id, $thems);
		}
		if($is_on == 0){
			$return['status'] = 1;
		}

		$this->ajaxReturn($return);
	}
	//监测开关是否开启,1-开启，0-关闭
	private function checkOn($site_id, $thems){
		//站点-ID：3g-87, q-14,
		$urls[87]['wxgdtmb']	= 1;	//http://3g.chinaskin.cn/star/gdtmb 
		$urls[87]['wxfpmanqd']	= 1;	//http://3g.chinaskin.cn/star/manqd
		$urls[87]['mb_a']	= 1;	//http://3g.chinaskin.cn/direct/mb_a
		
		$urls[14]['wxfpgdtmb']	= 1;	//http://q.chinaskin.cn/star/gdtmb
		$urls[14]['wxfpmanqd']	= 1;	//http://q.chinaskin.cn/star/manqd
        $urls[14]['gdt_mb']		= 1;    //http://q.chinaskin.cn/star/gdt_mb
		$urls[14]['6406_04']	= 1;	//http://q.chinaskin.cn/star/6406_04
		$urls[14]['6406_06']	= 1;	//http://q.chinaskin.cn/star/6406_06
		$urls[14]['man_qd']		= 1;	//http://q.chinaskin.cn/star/man_qd
        //$urls[14]['wm_qd']		= 1;	//http://q.chinaskin.cn/star/wm_qd
        $urls[14]['qb_c']		= 1;	//http://q.chinaskin.cn/direct-qb_c.html
		$urls[14]['testqd'] = 1;	//http://q.chinaskin.cn/star/testqd
        $urls[14]['testmb'] = 1;	//http://q.chinaskin.cn/star/testmb
        $urls[14]['qd_man']  =1;   //http://q.chinaskin.cn/direct-qd_man.html
        $urls[14]['qd_man_b'] =1;   //http://q.chinaskin.cn/direct-qd_man_b.html
        $urls[14]['6406_11']  =1;   //http://q.chinaskin.cn/star/6406_11
        $urls[14]['wxfpgdt_mb']  =1; //q.chinaskin.cn/wh/wxfpgdt_mb
        $urls[14]['wxfpman_qd']  =1; //q.chinaskin.cn/wh/wxfpman_qd
        $urls[14]['mb_qd']  =1; //q.chinaskin.cn/wh/mb_qd
        $urls[14]['gdtmb2']  =1; //q.chinaskin.cn/star/gdtmb2
        $urls[14]['man_qd_cm']  =1; //q.chinaskin.cn/star/man_qd_cm
        $urls[14]['gdt_mb_wxtest']  =1; //q.chinaskin.cn/star/gdt_mb_wxtest
		return $urls[$site_id][$thems];
	}
	//ip黑名单
	private function checkNewIp($ip){
		//ip 段限制
		$ip_segment = array(
			'101.226.125',
			'101.226.33',
			'101.226.51',
			'101.226.61',
			'101.226.64',
			'101.226.65',
			'101.226.66',
			'101.226.68',
			'101.226.69',
			'101.226.79',
			'101.226.89',
			'101.226.99',
			'112.65.193',
			'117.185.27',
			'140.207.185',
			'180.153.201',
			'180.153.205',
			'180.153.206',
			'180.153.211',
			'180.153.214',
			'180.153.81',
			'180.153.163',
			'180.163.2',
			'183.3.234',
			'183.57.53',
			'59.37.97',
			'61.135.172',
			'61.151.226',
			'61.151.217',
			'61.151.218',
			'182.140.153',
			'182.140.175',
			'119.147.146',
			'119.147.2',
			'125.39.189',
			'125.39.192',
			'59.41.157',
			'59.41.65',
			// '127.0.0',
			// '14.23.61',
		);
		//具体 ip 屏蔽
		$ip_arr = array(
			'183.60.228.44',
			'202.108.43.172',
			'219.137.74.126',
			'58.248.68.116',
			'58.62.24.18',
			'58.63.132.117',
			'183.43.36.105',
			'182.131.19.97',
			'112.90.137.179',
			'113.108.13.36',
			'115.152.250.65',
			'14.17.43.10',
			'14.215.42.250',
			'140.207.124.105',
			// '127.0.0.1',
			// '14.23.61.75',
		);
		
		//具体 ip 屏蔽优先，在屏蔽 ip 段
		if(in_array($ip, $ip_arr)){
			return true;
		}else{
			$ip_exp = explode('.', $ip);
			array_pop($ip_exp);
			$new_ip = implode('.', $ip_exp);
			
			return in_array($new_ip, $ip_segment);
		}
		// return in_array($ip, $ip_arr);
	}
	
	//================================== 新版爬虫控制 ==========================================================
}