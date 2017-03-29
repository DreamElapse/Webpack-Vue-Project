<?php
/**
 * ====================================
 * 微信 素材管理库
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-01-17 14:29
 * ====================================
 * File: WechatMessageMass.class.php
 * ====================================
 */
namespace Common\Extend;
use Common\Extend\Wechat;

class WechatMedia extends Wechat{
	public $type = array(
		'image'=>'图片',
		//'video'=>'视频',
		'voice'=>'语音',
		'news'=>'图文',
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
	*	新增加永久图文素材
	*	@Author 9009123 (Lemonice)
	*	@param $list 图文列表，包含下面article字段，二维数组，同时添加多个
	*	@return array
	*/
    public function addImageText($list = array()) {
		if(empty($list)){
			return false;
		}
		$data = array();
		foreach($list as $key=>$value){
			$data['articles'][$key] = array(
				'title'=>$value['title'],  //标题
				'author'=>$value['author'],  //作者
				'thumb_media_id'=>$value['thumb_media_id'],  // 图文消息的封面图片素材id（必须是永久mediaID） 
				'show_cover_pic'=>$value['show_cover_pic'],  // 是否显示封面，0为false，即不显示，1为true，即显示 
				'digest'=>$value['digest'],  //图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空 
				'content'=>$value['content'],  // 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS 
				'content_source_url'=>$value['content_source_url'], // 图文消息的原文地址，即点击"阅读原文"后的URL 
			);
		}
		unset($list);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/material/add_news?access_token='. self::getAccessToken('WechatMedia_addImageText'), $this->decodeUnicode(json_encode($data)));
        return json_decode($ret, true);
    }
	
	/*
	*	更新永久图文素材
	*	@Author 9009123 (Lemonice)
	*	@param $media_id 图文素材ID
	*	@param $info 图文信息
	*	@param $index  第几个图文，此字段多图文时候有意义
	*	@return array
	*/
    public function editImageText($media_id = '', $info = array(), $index = 0) {
		if(empty($info)){
			return false;
		}
		$data = array(
			'media_id'=>$media_id,
			'index'=>$index,
			'articles'=>array(
				'title'=>$info['title'],  //标题
				'thumb_media_id'=>$info['thumb_media_id'],  // 图文消息的封面图片素材id（必须是永久mediaID） 
				'show_cover_pic'=>$info['show_cover_pic'],  // 是否显示封面，0为false，即不显示，1为true，即显示 
				'author'=>$info['author'],  //作者
				'digest'=>$info['digest'],  //图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空 
				'content'=>$info['content'],  // 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS 
				'content_source_url'=>$info['content_source_url'], // 图文消息的原文地址，即点击"阅读原文"后的URL 
			),
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/material/update_news?access_token='. self::getAccessToken('WechatMedia_editImageText'), $this->decodeUnicode(json_encode($data)));
        return json_decode($ret, true);
    }
	
	/*
	*	新增加图文素材内容里面的图片 - 不会有5000个的限制
	*	@Author 9009123 (Lemonice)
	*	@param $file_path  文件路径
	*	@return array
	*/
    public function uploadImageTextFile($file_path = '') {
		if($file_path == ''){
			return false;
		}
		$data = array(
			'media'=>'@'.$file_path,
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token='. self::getAccessToken('WechatMedia_uploadImageTextFile'), $data);
        return json_decode($ret, true);
    }
	
	/*
	*	删除永久素材
	*	@Author 9009123 (Lemonice)
	*	@param $media_id  素材ID
	*	@param $type 素材类型
	*	@return array
	*/
    public function delete($media_id = '', $type = '') {
		if($media_id == '' || $type == ''){
			return false;
		}
		$data = array(
			'media_id'=>$media_id,
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/material/del_material?access_token='. self::getAccessToken('WechatMedia_delete') .'&type=' . $type, json_encode($data));
        return json_decode($ret, true);
    }
		
	/*
	*	新增加永久素材 - 文件
	*	@Author 9009123 (Lemonice)
	*	@param $type 素材类型
	*	@param $file_path  文件路径
	*	@param $title  标题
	*	@param $introduction  描述
	*	@return array
	*/
    public function uploadFile($type, $file_path = '', $title = '', $introduction = '') {
		if(!isset($this->type[$type])){
			return false;
		}
		if($file_path == ''){
			return false;
		}
		$data = array(
			'type'=>$type,
			'media'=>'@'.$file_path,
			'description'=>json_encode(array(
				'title'=>$title,
				'introduction'=>$introduction
			)),
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='. self::getAccessToken('WechatMedia_uploadFile') .'&type=' . $type, $data);
        return json_decode($ret, true);
    }
	
	/*
	*	获取永久素材列表
	*	@Author 9009123 (Lemonice)
	*	@param $type 素材的类型
	*	@param $page  当前第几页
	*	@param $page_size  每页显示多少条
	*	@return array
	*/
    public function getList($type, $page = 1, $page_size = 20) {
		if(!isset($this->type[$type])){
			return false;
		}
        $offset = ($page - 1) * $page_size;
		$data = array(
			'type'=>$type,
			'offset'=>$offset,
			'count'=>$page_size,
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . self::getAccessToken('WechatMedia_getList'), json_encode($data));
        return json_decode($ret, true);
    }
}
