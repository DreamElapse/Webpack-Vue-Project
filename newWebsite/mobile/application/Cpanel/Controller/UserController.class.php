<?php
/**
 * ====================================
 * 微信关注会员
 * ====================================
 * Author: 9004396
 * Date: 2017-01-11 13:49
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: UserController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Extend\WechatUserTag;
use Common\Controller\CpanelController;

class UserController extends CpanelController{
    protected $tableName = 'User';
	
	//private $app_id = 'wxf24d369d597cb66b';
	//private $app_secret = '79ea49f66d0bf091f73120aa501bcae7';
	
	
	/*
	*	用户绑定标签
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function addTagUser(){
		$tag_id = I('post.tag_id',0,'intval');
		$openids = I('post.openid','','trim');
		
		if($tag_id <= 0){
			$this->ajaxReturn(array(
				'status'=>0,
				'info'=>'请勾选会员',
			));
		}
		if($openids == ''){
			$this->ajaxReturn(array(
				'status'=>0,
				'info'=>'请选择标签',
			));
		}
		$openid_list = explode(',',$openids);
		$WechatUserTag = new WechatUserTag();
		$result = $WechatUserTag->addTagUser($tag_id, $openid_list);
		
		if($result['errcode'] == 0){  //打标签成功
			$WechatTagBind = D('WechatTagBind');
			$data_insert = array();
			foreach($openid_list as $openid){
				$id = $WechatTagBind->where("openid = '$openid' AND tag_id = '$tag_id'")->getField('id');
				if(!$id){  //没绑定过，插入数据绑定
					$data_insert[] = array(
						'openid'=>$openid,
						'tag_id'=>$tag_id,
					);
				}
			}
			if(!empty($data_insert)){
				$WechatTagBind->addAll($data_insert);
			}
			$this->ajaxReturn(array(
				'status'=>1,
				'info'=>'绑定标签成功',
			));
		}else{
			$this->ajaxReturn(array(
				'status'=>0,
				'info'=>'['.$result['errcode'].']微信返回错误：'.($result['error']!='' ? $result['error'] : $result['errmsg']),
			));
		}
    }
	
	/*
	*	用户解除绑定标签
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function deleteTagUser(){
		$tag_id = I('post.tag_id',0,'intval');
		$openids = I('post.openid','','trim');
		
		if($tag_id <= 0){
			$this->ajaxReturn(array(
				'status'=>0,
				'info'=>'请勾选会员',
			));
		}
		if($openids == ''){
			$this->ajaxReturn(array(
				'status'=>0,
				'info'=>'请选择标签',
			));
		}
		$openid_list = explode(',',$openids);
		$WechatUserTag = new WechatUserTag($this->app_id, $this->app_secret);
		$result = $WechatUserTag->deleteTagUser($tag_id, $openid_list);
		
		if($result['errcode'] == 0){  //打标签成功
			$WechatTagBind = D('WechatTagBind');
			$WechatTagBind->where("openid IN('".implode("','",$openid_list)."') AND tag_id = '$tag_id'")->delete();
			$this->ajaxReturn(array(
				'status'=>1,
				'info'=>'解除绑定成功',
			));
		}else{
			$this->ajaxReturn(array(
				'status'=>0,
				'info'=>'['.$result['errcode'].']微信返回错误：'.($result['error']!='' ? $result['error'] : $result['errmsg']),
			));
		}
    }
}