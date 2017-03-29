<?php
/**
 * ====================================
 * 微信公众平台 - 消息推送模版解析
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-03-10 11:20
 * ====================================
 * File: WechatSendMessageTemplate.class.php
 * ====================================
 */
namespace Common\Extend;
use Common\Extend\Wechat;
use Common\Extend\PhxCrypt;

class WechatSendMessageTemplate extends Wechat
{
	/*
	*	构造函数，传APPID等数据给父类
	*	@Author 9009123 (Lemonice)
	*	@return Object
	*/
	public function __construct($app_id = '', $app_secret = '') {
		Wechat::$app_id = $app_id != '' ? $app_id : C('APPID');
		Wechat::$app_secret = $app_secret != '' ? $app_secret : C('APPSECRET');
    }
	
    /**
    *	解析内容模版
	*	@param string $msg_body
	*	@param string $openid 微信openid
    *	@return string
    */
    public function resolveTpl($msg_body = '', $openid = ''){
       	if($msg_body == '' || $openid == ''){
			return $msg_body;
		}
		$BinUserModel = D('Cpanel/User');
		$user = $BinUserModel->field('mobile,nickname as username,sex,city,province')->where("openid = '$openid'")->find();
        if(empty($user)){
			return $msg_body;
		}
		$sex_array = array(1=>'男',2=>'女');
		$user['sex'] = isset($sex_array[$user['sex']]) ? $sex_array[$user['sex']] : '未知';
		$user['mobile'] = $user['mobile']!='' ? PhxCrypt::phxDecrypt($user['mobile']) : '';
		$variable_data = $user;  //允许解析的变量
		
		//解析变量 - {$username}
		preg_match_all('/\{\$[\w-]+\}/i', $msg_body, $variable_list);
		$variable_list = isset($variable_list[0]) ? $variable_list[0] : array();
		if(!empty($variable_list)){
			foreach($variable_list as $variable){
				$key = substr($variable,2,-1);
				$value = '';
				if(isset($variable_data[$key])){  //检查变量存在，可解析
					$value = $variable_data[$key];
				}
				$msg_body = str_replace($variable,$value,$msg_body);  //解析对应的变量
			}
		}
		
		//解析标签 - 解析高级变量{$name=value,$name2=$value2,$......}
		preg_match_all('/\{\$(.+?)\}/i', $msg_body, $tag_a_list);
		$tag_a_list = isset($tag_a_list[0]) ? $tag_a_list[0] : array();
		if(!empty($tag_a_list)){
			foreach($tag_a_list as $key=>$value){
				$variable_data = $this->resolveTplVariable($value);  //解析回来值
				
				//检查A标签
				if(isset($variable_data['href']) && isset($variable_data['text'])){
					$tag_a = "<a href='".$variable_data['href']."'>".$variable_data['text']."</a>";
					$msg_body = str_replace($value,$tag_a,$msg_body);  //解析对应的变量
				}
			}
		}
		return $msg_body;
    }
	
	/**
    *	解析内容模版 - 解析高级变量{$name=value,$name2=$value2,$......}
	*	@param string $msg_body
	*	@param string $openid 微信openid
    *	@return string
    */
	public function resolveTplVariable($variable = ''){
		$data = array();
		if($variable == ''){
			return $data;
		}
		$variable = substr($variable,2,-1);
		$array = explode(',$',$variable);
		if(!empty($array)){
			foreach($array as $key=>$value){
				$arr = explode('=',$value,2);
				$name = isset($arr[0]) ? trim($arr[0]) : '';
				$val = isset($arr[1]) ? trim($arr[1]) : '';
				if(!empty($name)){
					$data[$name] = $val;
				}
			}
		}
		return $data;
	}
}