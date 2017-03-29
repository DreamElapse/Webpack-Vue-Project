<?php
//============================================
// 用户满意度调查相关接口
//============================================
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Curl;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;
use Common\Extend\Wechat;

class UserSurveyController extends InitController
{
	private $api_url = 'http://api.chinaskin.cn/Comment/';
    private $curl_key = 'CHSURL#*GO888';

    public function index()
    {}


	/**
	 * 品牌调查问题接口
	 *
	 */
	public function question(){
		
		$surveyModel = M('survey', 'py_', 'CPANEL');
		$now_time = time();
		$where['locked'] = 0;
		$where['start_time'] = array('elt', $now_time);
		$where['end_time'] = array('gt', $now_time);
		$field = 'sid,name,content';
		$questions = $surveyModel->where($where)->field($field)->find();
		$questions['content'] = unserialize($questions['content']);
        $questions['isCheckWechat'] = (!isCheckWechat() ? 0 : 1);
		$this->success($questions);
	}

    /**
     * 满意度调查
     * 1、服务评价
     * 2、品牌调查
     */
    public function survey(){
        $params_data = I('request.params');
        $params_data = base64_decode($params_data);
        $params_data = explode('|', $params_data);

        $name       = !empty($params_data[0]) ? $params_data[0] : '公司';; //评价对象名称（可以为客服名称或者产品名称、公司等）
        $channel    = !empty($params_data[1]) ? $params_data[1] : '0301';; //评价渠道:01定单评价,0201呼叫中心呼出,0202呼叫中心呼入,0301微信,0302个人QQ,0303企点QQ,0304营销QQ,0305公众号
        $evalDime   = !empty($params_data[2]) ? $params_data[2] : '0101' ; //评价维度:0101品牌,0102质量,0103价格,0201售前,0202售中,0203售后
        $company    = !empty($params_data[3]) ? $params_data[3] : ''; //所属公司
        $dept       = !empty($params_data[4]) ? $params_data[4] : ''; //所属部门
        $evalPerson = !empty($params_data[5]) ? $params_data[5] : ''; //评价人

        $evalTarget = ($evalDime === '0101') ? '01' : '02'; //评价目标:01产品,02服务,固定值
        $evalType   = ($evalDime === '0101') ? '05' : '03';     //评价方式：01定单评价，02电话打分,03客服推评价链接,05市调
        $msgContent = I('request.msg', '', 'trim');	//评价留言
        $comments   = I('request.comments'); //评论内容，及评分
        $code       = I('request.code', '', 'trim');		//评价对象编码（可以为客服编码或者产品编码等）
        $order_sn   = I('request.orderSn', '', 'trim');    //所属订单
        $from_site  = I('request.fromSite', '', 'trim'); //来源站

        /**
         * 品牌满意度调查的自动获取评价人昵称
         * 先读取缓存，再去读取关注，最后调用微信接口
         */
        if($evalDime === '0101'){
            $nick_name = S(md5($this->openId));
            if(empty($nick_name)){
                $nick_name = D('BindUser')->getUserNickName($this->openId);
            }
            $evalPerson = $nick_name;
        }
//        $evalPerson = '';
		if(!($evalDime && $name && $channel)){
            F('survey_log'.date('Y-m-d'), var_export(array($this->openId=>array($evalDime,$name,$channel,$evalPerson), 'time'=>date('Y-m-d H:i:s')), TRUE)."\n".F('survey_log'.date('Y-m-d')), $path=DATA_PATH);

            $this->error('param error');
        }

		/* 服务评价时没有传评价内容，以评价维度匹配评价内容 */
		$comment = array();
		if(empty($comments)){
			$this->error('请为本次服务评分');
		}
		if(empty($comments[0]['scope'])){
			$this->error('请为本次服务评分');
		}

        //两个钟头内只能提交一次
        $now_time = Time::gmTime();
        $pre_time = $now_time - 7200;//两小时之前
        /*if($evalPerson){
            $model = M('user_comment', null, 'USER_CENTER');
            $where['evalTarget'] = $evalTarget;
            $where['evalType'] = $evalType;
            $where['evalPerson'] = $evalPerson;
            $where['name'] = $name;
            $where['channel'] = $channel;
            $where['evalDime'] = $evalDime;
            $where['_string'] = "evalDate>=$pre_time and evalDate<$now_time";
            $has = $model->where($where)->count();
        }else{
            $eval_person = cookie('eval_person');
            $has = true;
            if(empty($eval_person)){
                $has = false;
                cookie('eval_person', 1, 7200);
            }
        }*/
        $s_k = md5($evalTarget.$evalType.$name.$channel.$evalDime.get_client_ip());
        if(S($s_k)){
            $has = true;
        }else{
            $has = false;
            S($s_k, 1, 7200);
        }

        if($has){
            $this->error('不可重复评价');
        }
		foreach($comments as $key=>$val){
			if(empty($val['content']) && !empty($val['scope'])){
				//服务评价，根据评价维度匹配评价内容
				switch($evalDime){
					case '0201'://售前
                        $content = '售前服务';
						break;
					case '0202'://售中
						$content = '下单便利性';
						break;
					case '0203'://售后
						$content = '售后服务';
						break;
					case '0103'://价格
						$content = '价格服务';
						break;
					case '0102'://质量
						$content = '质量服务';
						break;
					case '0101'://品牌
						$content = '广告内容';
						break;
					default:
					$this->error('缺失评价维度');
				}
				$val['content'] = $content;
			}
			
			$comment[$key] = array(
                'evalTarget'    => $evalTarget,
                'evalDime'      => $evalDime,
                'name'          => $name,
                'channel'       => $channel,
                'evalType'      => $evalType,
                'content'       => $val['content'],
                'scope'         => $val['scope'],
                'evalPerson'    => $evalPerson,
                'msgContent'    => $msgContent,
                'code'          => $code,
                'company'       => $company,
                'dept'          =>$dept,
                'rid'           =>$order_sn,
                'from_site'     =>$from_site,
            );
			
		}

        $params['comment'] = $comment;
        
        Curl::$key = $this->curl_key;
        $result = Curl::getApiResponse($this->api_url.'pageComment', $params);

        if($result['error']!='A00000'){
            $this->error($result['message']);
        }
        $this->success($result['data']);
    }
}
