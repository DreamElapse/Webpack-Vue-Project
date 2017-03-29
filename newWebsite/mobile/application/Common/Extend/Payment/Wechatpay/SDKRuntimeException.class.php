<?php
namespace Common\Extend\Payment\Wechatpay;

class Exception extends \Exception {
	public function errorMessage(){
		return $this->getMessage();
	}
}
class SDKRuntimeException extends \Exception {
	public function errorMessage(){
		return $this->getMessage();
	}
}
?>