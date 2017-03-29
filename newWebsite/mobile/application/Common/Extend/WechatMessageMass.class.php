<?php
/**
 * ====================================
 * 微信分组群发库
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-01-17 11:29
 * ====================================
 * File: WechatMessageMass.class.php
 * ====================================
 */
namespace Common\Extend;
use Common\Extend\Wechat;

class WechatMessageMass extends Wechat{
	public $msgType = array(
		'news'   =>'图文素材',   //图文
		'text'   =>'纯文本信息',     //文本
		'voice'  =>'语音素材',    //语音
		'image'  =>'图片素材',    //图片
		//'video'  =>'视频素材',  //视频
	);
	
	/*
	*	构造函数，传APPID等数据给父类
	*	@Author 9009123 (Lemonice)
	*	@return Object
	*/
	public function __construct($app_id = '', $app_secret = '') {
		Wechat::$app_id = $app_id != '' ? $app_id : C('APPID');
		Wechat::$app_secret = $app_secret != '' ? $app_secret : C('APPSECRET');
    }
	
	/*
	*	根据标签进行群发
	*	@Author 9009123 (Lemonice)
	*	@param $info 群发内容详情
	*	@return array
	*/
    public function massToTag($info = array()) {
		if(empty($info)){
			return false;
		}
		
		$msgType = $this->addTypePre($info['type']);  //部分类型需要加前缀
		$send_ignore_reprint = isset($info['send_ignore_reprint']) ? intval($info['send_ignore_reprint']) : 1;
		$is_to_all = isset($info['is_to_all']) ? (intval($info['is_to_all']) > 0 ? 1 : 0) : 0;
		$data = array(
			'filter'=>array(
				'is_to_all'=>($is_to_all ? true : false),  //是否发给所有用户，有次数限制，1=所有，0=不是所有
			),
			$msgType=>$this->getMsgTypeData($info),  //获取群发的信息组成可发送的结构数组
			'title'=>$info['title'],
			'description'=>$info['description'],
			'msgtype'=>$msgType,  //消息类型
    		'send_ignore_reprint'=>$send_ignore_reprint,  //如果此消息被微信评定为转载别人的文章，是否继续发送，1=继续，0=暂停
		);
		if($is_to_all == 0){
			$data['filter']['tag_id'] = $info['tag_id'];  //标签ID
		}
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='. self::getAccessToken('WechatMessageMass_massToTag'), $this->decodeUnicode(json_encode($data)));
        return json_decode($ret, true);
    }
	
	/*
	*	删除群发任务
	*	@Author 9009123 (Lemonice)
	*	@param $msg_id 群发任务的ID
	*	@return array
	*/
    public function deleteMass($msg_id = '') {
		if(empty($msg_id)){
			return false;
		}
		$data = array(
			'msg_id'=>$msg_id
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/message/mass/delete?access_token='. self::getAccessToken('WechatMessageMass_deleteMass'), json_encode($data));
        return json_decode($ret, true);
    }
	
	/*
	*	获取群发的信息组成可发送的结构数组
	*	@Author 9009123 (Lemonice)
	*	@param $info 群发内容详情
	*	@return array
	*/
	private function getMsgTypeData($info = array()){
		$data = array();
		if($info['type'] == 'text'){  //文本模式
			$data = array(
				'content'=>$info['content'],  //文本信息
			);
		}else if($info['type'] == 'video'){  //视频模式
			$media_id = $this->getVideoMediaId($info['media_id'], $info['title'], $info['description']);  //获取视频真实的素材ID
			$data = array(
				'media_id'=>$media_id,  //真实的视频ID
			);
		}else{  //其他模式
			$data = array(
				'media_id'=>$info['media_id'],  //其他图片素材ID、图文素材ID、音频素材ID等等
			);
		}
		return $data;
	}
	
	/*
	*	对群发类型加前缀
	*	@Author 9009123 (Lemonice)
	*	@param $msgType 群发类型
	*	@return string
	*/
	private function addTypePre($msgType = ''){
		//这两个模式是有mp前缀的
		if($msgType == 'video' || $msgType == 'news'){
			$msgType = 'mp'.$msgType;
		}
		return $msgType;
	}
	
	/*
	*	获取视频素材的群发素材ID
	*	@Author 9009123 (Lemonice)
	*	@param $media_id 视频素材基础ID
	*	@param $title 视频标题
	*	@param $description 视频标题描述
	*	@return string
	*/
	private function getVideoMediaId($media_id, $title = '', $description = ''){
		$data = array(
			'media_id'=> $media_id,
			'title'=> 'TEST',
			'description'=> 'Test_description'
		);
		$ret = wCurl::post('https://file.api.weixin.qq.com/cgi-bin/media/uploadvideo?access_token='. self::getAccessToken('WechatMessageMass_getVideoMediaId'), json_encode($data));
		$result = json_decode($ret, true);
        return (isset($result['media_id']) ? $result['media_id'] : '');
	}
}
