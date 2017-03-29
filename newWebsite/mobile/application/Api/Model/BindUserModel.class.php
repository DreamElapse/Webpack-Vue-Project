<?php
/**
 * ====================================
 * 口碑中心/品牌动态 文章模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-10-31 15:32
 * ====================================
 * File: BindUserModel.class.php
 * ====================================
 */
namespace Api\Model;
use Common\Model\CommonModel;
use Common\Extend\PhxCrypt;

class BindUserModel extends CommonModel {
	/*
	*	获取视频
	*	@Author Lemonice
	*	@param  int $cat_id  分类ID
	*	@return array
	*/
    public function getOpenId($mobile = ''){
		//如果是明文的手机号码，则需要加密
		if(is_phone($mobile)){
			$mobile = PhxCrypt::phxEncrypt($mobile);
		}
		$openid = $this->where("mobile = '$mobile'")->getField('openid');
		return $openid;
    }
}