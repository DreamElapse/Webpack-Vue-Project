<?php
/**
 * ====================================
 * 微信 会员标签库
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-01-19 15:05
 * ====================================
 * File: WechatUserTag.class.php
 * ====================================
 */
namespace Common\Extend;
use Common\Extend\Wechat;

class WechatUserTag extends Wechat{
	//错误码对照表
	public $error_code = array(
		'-1'=>'统繁忙',
		'45157'=>'标签名非法，请注意不能和其他标签重名',
		'45158'=>'标签名长度超过15个字符',
		'45056'=>'创建的标签数过多，请注意不能超过100个',
		'45058'=>'不能修改0/1/2这三个系统默认保留的标签',
		'45057'=>'该标签下粉丝数超过10w，不允许直接删除',
		'40003'=>'传入非法的openid',
		'45159'=>'非法的tag_id',
		'40032'=>'每次传入的openid列表个数不能超过50个',
		'45059'=>'有粉丝身上的标签数已经超过限制',
		'49003'=>'传入的openid不属于此公众号',
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
	*	新增加标签
	*	@Author 9009123 (Lemonice)
	*	@param $name 标签名称，15字符以内
	*	@return array
	*/
    public function add($name = '') {
		if(empty($name)){
			return false;
		}
		$data = array(
			'tag'=>array(
				'name'=>urlencode($name)
			),
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/tags/create?access_token='. self::getAccessToken('WechatUserTag_add'), urldecode(json_encode($data)));
        return $this->error($ret);
    }
	
	/*
	*	编辑标签
	*	@Author 9009123 (Lemonice)
	*	@param $id 标签ID，微信那边的ID
	*	@param $name 标签名称，15字符以内
	*	@return array
	*/
    public function edit($id = 0, $name = '') {
		if($id <= 0 || empty($name)){
			return false;
		}
		$data = array(
			'tag'=>array(
				'id'=>$id,
				'name'=>urlencode($name)
			),
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/tags/update?access_token='. self::getAccessToken('WechatUserTag_edit'), urldecode(json_encode($data)));
        return $this->error($ret);
    }
	
	/*
	*	删除标签 - 当某个标签下的粉丝超过10w时，后台不可直接删除标签
	*	@Author 9009123 (Lemonice)
	*	@param $id 标签ID，微信那边的ID
	*	@return array
	*/
    public function delete($id = 0) {
		if($id <= 0){
			return false;
		}
		$data = array(
			'tag'=>array(
				'id'=>$id,
			),
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/tags/delete?access_token='. self::getAccessToken('WechatUserTag_delete'), json_encode($data));
        return $this->error($ret);
    }
	
	/*
	*	获取标签下粉丝列表
	*	@Author 9009123 (Lemonice)
	*	@param $id 标签ID，微信那边的ID
	*	@param $next_openid 从哪个openid开始获取
	*	@return array
	*/
    public function getTagUserList($id = 0,$next_openid = '') {
		if($id <= 0){
			return false;
		}
		$data = array(
			'tagid'=>$id,
			'next_openid'=>$next_openid,
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/user/tag/get?access_token='. self::getAccessToken('WechatUserTag_getTagUserList'), json_encode($data));
        return $this->error($ret);
    }
	
	/*
	*	批量为标签添加用户 - 每个用户最多拥有3个标签
	*	@Author 9009123 (Lemonice)
	*	@param $id 标签ID，微信那边的ID
	*	@param $openid_list 添加的用户openid列表
	*	@return array
	*/
    public function addTagUser($id = 0,$openid_list = array()) {
		if($id <= 0 || empty($openid_list)){
			return false;
		}
		$data = array(
			'tagid'=>$id,
			'openid_list'=>$openid_list,
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token='. self::getAccessToken('WechatUserTag_addTagUser'), json_encode($data));
        return $this->error($ret);
    }
	
	/*
	*	批量为用户取消标签
	*	@Author 9009123 (Lemonice)
	*	@param $id 标签ID，微信那边的ID
	*	@param $openid_list 取消的用户openid列表
	*	@return array
	*/
    public function deleteTagUser($id = 0,$openid_list = array()) {
		if($id <= 0 || empty($openid_list)){
			return false;
		}
		$data = array(
			'tagid'=>$id,
			'openid_list'=>$openid_list,
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging?access_token='. self::getAccessToken('WechatUserTag_deleteTagUser'), json_encode($data));
        return $this->error($ret);
    }
	
	/*
	*	获取用户身上的标签列表
	*	@Author 9009123 (Lemonice)
	*	@param $openid 用户的openid
	*	@return array
	*/
    public function getUserTag($openid = '') {
		if(empty($openid)){
			return false;
		}
		$data = array(
			'openid'=>$openid,
		);
        $ret = wCurl::post('https://api.weixin.qq.com/cgi-bin/tags/getidlist?access_token='. self::getAccessToken('WechatUserTag_getUserTag'), json_encode($data));
        return $this->error($ret);
    }
	
	/*
	*	获取所有标签
	*	@Author 9009123 (Lemonice)
	*	@return array
	*/
    public function getTagList() {
        $ret = wCurl::get('https://api.weixin.qq.com/cgi-bin/tags/get?access_token=' . self::getAccessToken('WechatUserTag_getTagList'));
        return $this->error($ret);
    }
	
	/*
	*	匹配错误码
	*	@Author 9009123 (Lemonice)
	*	@param $result 微信返回的结果
	*	@return array
	*/
	private function error($result = ''){
		if($result == ''){
			return array(
				'errcode'=>'-99999',
				'errmsg'=>'微信未返回',
				'error'=>'微信未返回',
			);
		}
		$result = json_decode($result, true);
		$result['error'] = '';
		if($result['errcode'] > 0 && isset($this->error_code[$result['errcode']])){
			$result['error'] = $this->error_code[$result['errcode']];
		}
		return $result;
	}
}
