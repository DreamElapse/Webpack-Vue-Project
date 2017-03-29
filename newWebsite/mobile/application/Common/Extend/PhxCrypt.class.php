<?php
/**
 * ====================================
 * 加解密类库（解密功能系统暂时取消）
 * ====================================
 * Author: 9004396
 * Date: 2016-06-27 14:36
 * ====================================
 * File: PhxCrypt.class.php
 * ====================================
 */
namespace Common\Extend;

class PhxCrypt
{
    protected static $_phxMod = "ecb";// 电码本模式, 不需要iv,此项不能修改
    protected static $_phx_key = array("phxAlg"=>"twofish","phxKey"=>"YaoCao201010OrderInfo","cut_length"=>5);

    public function __construct()
    {
    }
    protected static function _hex2bin($data)
    {
        $len = strlen($data);
        return pack("H".$len, $data);
    }

    //加密函数
    public static function phxEncrypt($plaintext, $iscut=false,$no_encrypt=false)
    {
        if (function_exists('phxencrypt') && $iscut == false){
            return phxencrypt($plaintext);//使用新的加密方式
        }
        $plaintext = trim($plaintext);
        if(empty($plaintext)) return false;
        //判断是否已加密连续达23位字符以上内容并且不是纯数字，不进行加密
        $reg = "|^[0-9a-zA-Z]{23,}$|i";
        if (strpos('A'.$plaintext,'±') == 1)	return $plaintext;//如开头带此符号的不执行加密
        $str = '';
        if ($iscut)
        {
            $cut_str = self::backCutStr($plaintext,self::$_phx_key["cut_length"]);//截取后面指定长度内容值
            $str = str_replace($cut_str,'',$plaintext).'±';
            $plaintext = $cut_str;
        }
        $td = @mcrypt_module_open(self::$_phx_key["phxAlg"], '', self::$_phxMod, '');
        $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, self::$_phx_key["phxKey"], $iv);
        $cipher = @mcrypt_generic($td, $plaintext);
        @mcrypt_generic_deinit($td);
        @mcrypt_module_close($td);
        $hexCipher = bin2hex($cipher);

        return '±'.$str.$hexCipher;
    }
    //判断字符串是否已加密，如加密执行解密操作is_substr判断是否前台加密
    public static function phxDecrypt($str,$is_substr = false)
    {
        if (self::isEncrypted($str))
        {
            if (function_exists('phxdecrypt'))
            {
                return phxdecrypt($str);//解密后输出
            }
        }


        $not_str = '';//不需要解密的内容
        $str_array = explode('±',$str);

        $decrypt_str = @$str_array[1];//需要解密的内容
        if (isset($str_array[2]))//此项不为空则为只加密内容部分,需重新定义
        {
            $not_str = $str_array[1];
            $decrypt_str = $str_array[2];
        }

        if (self::isEncrypted($decrypt_str))
        {
            if ($is_substr)
            {
                $decrypt_str = substr($decrypt_str,0,-2);
            }
            return $not_str . self::_phxDecrypt($decrypt_str);//解密后输出
        }
        return $str;
    }

    //解密函数
    protected static function _phxDecrypt($hexCipher)
    {
        if (empty($hexCipher)) return $hexCipher;
        $cipher=self::_hex2bin($hexCipher);
        $plaintext=@mcrypt_decrypt(self::$_phx_key["phxAlg"],self::$_phx_key["phxKey"],$cipher,self::$_phxMod);
        return trim($plaintext);
    }

    /*
    从后面起截取汉字函数，只支持Utf-8
    backCutStr(字符串, 截取长度);
    开始长度默认为 0
    */
    protected static function backCutStr($string,$sublen=5)
    {
        $sublen = intval($sublen);
        $string= str_replace('&nbsp;','',$string);
        $string = preg_replace( "@<(.*?)>@is", "", $string );
        $string= str_replace('  ','',$string);
        $pa="/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
        preg_match_all($pa,$string,$t_string);
        return join('',array_slice($t_string[0], $sublen * -1));
    }
    //判断是否加密
    public static function isEncrypted($str)
    {
        if ( is_numeric($str) && strlen($str) < 32) return false;
        $reg = "|^[0-9a-fA-F]{8,}$|i";
        $ret = preg_match($reg, $str);

        return $ret;
    }



}