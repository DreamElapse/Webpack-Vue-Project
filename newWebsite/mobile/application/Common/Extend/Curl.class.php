<?php
/**
 * 重写Curl类
 *
 * @package    Curl
 * @author weitao
 */
namespace Common\Extend;
use Common\Extend\Cookie_Crypt;

class Curl{
    static $headers = array('Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg','Connection: Keep-Alive','Content-type: application/x-www-form-urlencoded;charset=UTF-8');
    static $user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)';
    static $compression = 'gzip';
    static $cookie_file;
    static $proxy = '';
   // static $key   = 'SMSAPI#*GO2821119';
    static $key = 'CHSURL#*GO888';

    /**
     * 模拟GET的方法
     * @param $url
     * @return mixed
     */
    static function get($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::$headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent);
        curl_setopt($ch, CURLOPT_ENCODING, self::$compression);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if(self::$proxy) curl_setopt($ch, CURLOPT_PROXY, self::$proxy);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 模拟POST的方法
     * @param $url
     * @param $data
     * @return mixed
     *
     */
    static function post($url,$data){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::$headers);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent);
        curl_setopt($ch, CURLOPT_ENCODING, self::$compression);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if(self::$proxy) curl_setopt($ch,CURLOPT_PROXY, self::$proxy);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }
    
    /**
     * 远程请求方法
     * @author Diven(702814242@qq.com)
     * @date 2014-07-12 15:47pm
     */
    static function request($url, $params, $cookie = '', $method = 'POST')
    {
    	$queryString = http_build_query($params);    	
    	$cookieString = self::getCookieString($cookie);
    	 
    	$ch = curl_init();
    	
    	if (strtoupper($method) == 'GET') {
    		$curl = strpos($url, '?') > 0 ? $url.'&'.$queryString : $url.'?'.$queryString;    		
    		curl_setopt($ch, CURLOPT_URL, $curl);
    	}else{
    		curl_setopt($ch, CURLOPT_URL, $url);
    		curl_setopt($ch, CURLOPT_POST, 1);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
    	}
    	
    	curl_setopt($ch, CURLOPT_HEADER, false);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);  	
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    	
    	if (!empty($cookieString))
    	{
    		curl_setopt($ch, CURLOPT_COOKIE, $cookieString);
    	}
    	 
    	$res = curl_exec($ch);
    	curl_close($ch);
    	return $res;
    }
    
    static private function getCookieString($params)
    {
    	if (is_string($params)){
    		return $params;
    	}   		
    	 
    	$str = '';
    	foreach ($params as $k => $v)
    	{
    		$str .= $k.'='.$v.';' ;
    	}    	
    	return rtrim($str,';');
    }
    
    /**
     * url加密方法
     * @author xiaodong
     * @param $data  加密数据
     * @return 加密字符串
     *
     */
     static function encode($data){
        return base64_encode(Cookie_Crypt::encrypt(json_encode($data), self::$key));
     }
    /**
     * url解密方法
     * @author xiaodong
     * @param $value 解密字符串
     * @return 解密数据
     *
     */
     static function decode($value){
         $v = json_decode(Cookie_Crypt::decrypt(base64_decode($value), self::$key));
         return is_scalar($v) ? $v : (array) $v;
     }
     
     /*
      * Api统一请求出口,用verify统一传递加密字符串 ，在api端获取verify参数然后再解密
      * @author Diven
      */
     static function requestApi($url, $data, $cookie = '', $type = 'POST')
     {
     	$param = array('verify'=>self::encode($data));
     	return Curl::request($url,$param,$cookie,$type);
     }
     
     static function getApiResponse($url, $data, $cookie = '', $type = 'POST', $force=true)
     {
         $ret=self::requestApi($url, $data, $cookie, $type);
         return self::decodeJson($ret,$force);
     }
     
     /*
      * json返回解密的数据
      * 默认$force=true时，以数组格式返回，否则以对象返回
      * @author Ocean
      */
     static function decodeJson($value,$force=true)
     {
         $decrypt=Cookie_Crypt::decrypt(base64_decode($value), self::$key);
         if(is_null($decrypt))
             return $value;
         if($force)
         {
            $v = json_decode($decrypt);
            return self::object_to_array($v);
         }else{
            $v = json_decode($decrypt);
            return $v;
         }
     }
     /*
      * 递归将json对象转化为数组
      * @author Ocean
      */
     static function object_to_array($obj)
     {
        $arr=array();
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val)
        {
            $val = (is_array($val) || is_object($val)) ? self::object_to_array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
     }
}

