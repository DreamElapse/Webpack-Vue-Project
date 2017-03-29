<?php

/**
 * Crypt
 *
 * @category Chscore
 * @package Chscore_Crypt
 * @author xiaodong
 */
namespace Common\Extend;

class Cookie_Crypt
{
    
    const KEY = 'Chs*#DODOGOGO';

    /**
     * 加密
     *
     * @param $text string
     *            to encrypt
     * @param $key string
     *            a cryptographically random string
     * @param $algo int
     *            the encryption algorithm
     * @param $mode int
     *            the block cipher mode
     * @return string
     */
    public static function encrypt ($text, $key = self::KEY, $algo = MCRYPT_RIJNDAEL_256, 
            $mode = MCRYPT_MODE_CBC)
    {
        // Create IV for encryption
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($algo, $mode), MCRYPT_RAND);
        
        // Encrypt text and append IV so it can be decrypted later
        $text = mcrypt_encrypt($algo, hash('sha256', $key, TRUE), $text, $mode, $iv) . $iv;
        
        // Prefix text with HMAC so that IV cannot be changed
        return hash('sha256', $key . $text) . $text;
    }

    /**
     * 解密
     *
     * @param $text string
     *            to encrypt
     * @param $key string
     *            a cryptographically random string
     * @param $algo int
     *            the encryption algorithm
     * @param $mode int
     *            the block cipher mode
     * @return string
     */
    public static function decrypt ($text, $key = self::KEY, $algo = MCRYPT_RIJNDAEL_256, 
            $mode = MCRYPT_MODE_CBC)
    {
        $hash = substr($text, 0, 64);
        $text = substr($text, 64);
        
        // Invalid HMAC?
        if (hash('sha256', $key . $text) != $hash)
            return;
            
            // Get IV off end of encrypted string
        $iv = substr($text, - mcrypt_get_iv_size($algo, $mode));
        
        // Decrypt string using IV and remove trailing \x0 padding added by
        // mcrypt
        return rtrim(
            mcrypt_decrypt(
                $algo, 
                hash('sha256', $key, TRUE), 
                substr($text, 0, - strlen($iv)), 
                $mode, 
                $iv
            ), 
            "\x0"
        );
    }
}

