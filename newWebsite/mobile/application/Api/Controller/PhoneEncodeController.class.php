<?php
/**
 * ====================================
 * 手机号码加解密
 * ====================================
 * Author: 9009123
 * Date: 2016-09-13 16:16
 * ====================================
 * File: PhoneEncodeController.class.php
 * ====================================
 */
namespace Api\Controller;
use Common\Controller\ApiController;
use Common\Extend\PhxCrypt;

class PhoneEncodeController extends ApiController{
	//密钥权限
	protected $_permission = array(
		//加密联系号码
		'phxEncrypt'=>array(
			'java'
		),
		//解密联系号码
		'phxDecrypt'=>array(
			'java'
		),
	);
	
	/*
	*	加密联系号码
	*	@Author 9009123 (Lemonice)
	*	@return exit & json
	*/
	public function phxEncrypt(){
		$phone = I('request.phone');  //联系号码
		if($phone == ''){
			$this->error('E10020');
		}
		$encode = PhxCrypt::phxEncrypt($phone);
		$this->success($encode);
	}
	
	/*
	*	解密联系号码
	*	@Author 9009123 (Lemonice)
	*	@return exit & json
	*/
	public function phxDecrypt(){
		$phone = I('request.phone');  //联系号码，加密后的字符串
		if($phone == ''){
			$this->error('E10020');
		}
		$decode = PhxCrypt::phxDecrypt($phone);
		$this->success($decode);
	}
}
