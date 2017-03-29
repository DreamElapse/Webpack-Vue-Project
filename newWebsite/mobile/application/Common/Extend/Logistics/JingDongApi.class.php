<?php
/**
 * ====================================
 * 京东物流Api 基于开放者开发平台.
 * ====================================
 * Author: 
 * Date: 
 * ====================================
 * File: Jingdongapi.class.php
 * ====================================
 */
namespace Common\Extend\Logistics;

/**
 * 
 */
class JingDongApi {
    private $jos_url = 'http://gw.api.360buy.com/routerjson';
    private $access_token = 'e0c18b77-2370-46a5-addb-729f4075d96e';
    private $app_secret = '4b84d05c548a4315a09caeebf0d3d469';
    private $app_key = 'C1B00CF6624A6B1E1583157B18CC4064';
    private $jos_sys_param = array();
     
    public function __construct() {
        date_default_timezone_set('Asia/Shanghai');
        $this->jos_sys_param['access_token'] = $this->access_token;
        $this->jos_sys_param['app_key'] = $this->app_key;
        $this->jos_sys_param['timestamp'] = date('Y-m-d H:i:s');
        $this->jos_sys_param['v'] = '2.0';
    }
     
    public function josRequest($method, $user_param) {
        $this->jos_sys_param['method'] = $method;
        $this->jos_sys_param['360buy_param_json'] = json_encode($user_param);
        $this->jos_sys_param['sign'] = $this->generateSign($this->jos_sys_param);
         
        try {
            $num = 1;
            while ($num <= 3 && ($reponse = $this->josPost($this->jos_url, $this->jos_sys_param)) == FALSE) {$num++;}
        } catch (Exception $e) {
             
        }
         
        return $reponse;
    }
     
    /**
     * 签名
     * @param  $params 业务参数
     * @return void
     */
    private function generateSign($params) {
        if (isset($params['sign'])) {
            unset($params['sign']);
        }
        //所有请求参数按照字母先后顺序排序
        ksort($params);
        //定义字符串开始 结尾所包括的字符串
        $stringToBeSigned = $this->app_secret;
        //把所有参数名和参数值串在一起
        foreach ($params as $k => $v) {
            $stringToBeSigned .= "$k$v";
        }
        unset($k, $v);
        //把venderKey夹在字符串的两端
        $stringToBeSigned .= $this->app_secret;
        //使用MD5进行加密，再转化成大写
        return strtoupper(md5($stringToBeSigned));
    }
     
    private function josPost($url, array $post = array(), array $options = array()) {
        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => http_build_query($post)
        );
        //print_r(http_build_query($post));
        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        $result = array();
        
        if( !$result = curl_exec($ch)) {
            throw new Exception(curl_error($ch), 101);
        }
        
        curl_close($ch);
        return $result;
    }   
}