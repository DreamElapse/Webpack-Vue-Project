<?php
/*
 * 文件上传类
 * @author Ocean
 * @date 2015-7-8
 */

namespace Common\Extend\Base;
use Think\Crypt;
class Cupload{
    static $imageType = array('goods','article','other');
    
    /*
     * 上传文件
     * @param $data $_FILES数组
     * @param $serverId 图片服务器id
     * @param $force 是否强制返回数组
     * @param $configs 配置  array('img_type'=>'','return_type'=>'')
     */
    static function uploadApi($data, $serverId = '', $configs = array(), $force=true){
        if(!isset($configs['imgtype'])){
            $configs['imgtype'] = 'other';
        }
        if(!in_array($configs['imgtype'],self::$imageType)){
            return false;
        }
        if(!isset($configs['returntype'])){
            $configs['returntype'] = 2;   //1：返回详细数组， 2：返回图片路径，保存名称数组
        }
        $imgServer = C('IMAGE_SERVER');
        if(empty($serverId)){
            $serverId = array_rand($imgServer);
        }
        $url = $imgServer[$serverId];
        $ret = self::upload($url, $data, $configs);

        return self::decodeJson($ret,$force);
    }

    //curl 上传文件
    private static function upload($url, $files, $configs){
        if(empty($files)) return false;

        $decryt = $data = array();
        $filename = array();
        foreach ($files as $k => $val){
            if(is_array($val)){
                if(!empty($val['name'])){
                    $filename[] = $val['name'];
                    $data[$k] = "@".realpath($val['tmp_name']).";type=".$val['type'].";filename=".$val['name'];
                }           
            }else{
                if(!empty($files['name'])){
                    $filename[] = $files['name'];
                    //上传单张图片
                    $data['upfile'] = "@".realpath($files['tmp_name']).";type=".$files['type'].";filename=".$files['name'];
                    break;
                }
            }
        }

        if(!empty($configs)){
            $data = array_merge($data, $configs);
        }

        //加签名
        $data['sign'] = md5(md5($data['imgtype'] . C('UPLOAD_KEY') . join('#$^', $filename) . C('UPLOAD_SECRET')));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $return_data = curl_exec($ch);
        curl_close($ch);
        return $return_data;
   }

   /**
    * url加密方法
    * @param $data  加密数据
    * @return 加密字符串
    *
    */
    static function encode($data){
       return base64_encode(Crypt::encrypt(json_encode($data), C('UPLOAD_SECRET')));
    }
   /**
    * url解密方法
    * @param $value 解密字符串
    * @return 解密数据
    *
    */
    static function decode($value){        
        $v = json_decode(Crypt::decrypt(base64_decode($value), C('UPLOAD_SECRET')));
        return is_scalar($v) ? $v : (array) $v;
    }

    /*
     * json返回解密的数据
     * 默认$force=true时，以数组格式返回，否则以对象返回
     * @author Ocean
     */
    static function decodeJson($value,$force=true){
        $decrypt = Crypt::decrypt(base64_decode($value), C('UPLOAD_SECRET'));
        if(is_null($decrypt))
            return $value;
        if($force){
           $v = json_decode($decrypt);
           return self::object_to_array($v);
        }else{
           $v = json_decode($decrypt);
           return $v;
        }
    }

    static function object_to_array($obj){
       $arr=array();
       $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
       foreach ($_arr as $key => $val){
           $val = (is_array($val) || is_object($val)) ? self::object_to_array($val) : $val;
           $arr[$key] = $val;
       }
       return $arr;
    }
}