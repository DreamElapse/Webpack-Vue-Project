<?php
/**
 * ====================================
 * 时间类
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-06-29 18:23
 * ====================================
 * File: Time.class.php
 * ====================================
 */
namespace Common\Extend;

class Time{
    static $timezone=8;
    
    /**
	 * 获得当前格林威治时间的时间戳
	 *
	 * @return  integer
	 */
	static function gmTime(){
	    return (time() - date('Z'));
	}

	/**
	 * 获得服务器的时区
	 *
	 * @return  integer
	 */
	static function serverTimezone(){
	    if (function_exists('date_default_timezone_get')) {
	        return date_default_timezone_get();
	    }else{
	        return date('Z') / 3600;
	    }
	}

	/**
	 *  生成一个用户自定义时区日期的GMT时间戳
	 *
	 * @access  public
	 * @param   int     $hour
	 * @param   int     $minute
	 * @param   int     $second
	 * @param   int     $month
	 * @param   int     $day
	 * @param   int     $year
	 *
	 * @return void
	 */
	static function localMktime($hour = NULL , $minute= NULL, $second = NULL,  $month = NULL,  $day = NULL,  $year = NULL){

	    /**
	    * $time = mktime($hour, $minute, $second, $month, $day, $year) - date('Z') + (date('Z') - $timezone * 3600)
	    * 先用mktime生成时间戳，再减去date('Z')转换为GMT时间，然后修正为用户自定义时间。以下是化简后结果
	    **/
	    $time = mktime($hour, $minute, $second, $month, $day, $year) - self::$timezone * 3600;

	    return $time;
	}

	/**
	 * 将GMT时间戳格式化为用户自定义时区日期
	 *
	 * @param  string       $format
	 * @param  integer      $time       该参数必须是一个GMT的时间戳
	 *
	 * @return  string
	 */

	static function localDate($format, $time = NULL){
	    if ($time === NULL){
	        $time = self::gmTime();
	    }elseif ($time <= 0){
	        return '';
	    }

	    $time += (self::$timezone * 3600);

	    return date($format, $time);
	}


	/**
	 * 转换字符串形式的时间表达式为GMT时间戳
	 *
	 * @param   string  $str
	 *
	 * @return  integer
	 */
	static function gmstr2time($str){
	    $time = strtotime($str);

	    if ($time > 0){
	        $time -= date('Z');
	    }

	    return $time;
	}

	/**
	 *  将一个用户自定义时区的日期转为GMT时间戳
	 *
	 * @access  public
	 * @param   string      $str
	 *
	 * @return  integer
	 */
	static function localStrtotime($str){

	    /**
	    * $time = mktime($hour, $minute, $second, $month, $day, $year) - date('Z') + (date('Z') - $timezone * 3600)
	    * 先用mktime生成时间戳，再减去date('Z')转换为GMT时间，然后修正为用户自定义时间。以下是化简后结果
	    **/
	    $time = strtotime($str) - self::$timezone * 3600;

	    return $time;

	}

	/**
	 * 获得用户所在时区指定的时间戳
	 *
	 * @param   $timestamp  integer     该时间戳必须是一个服务器本地的时间戳
	 *
	 * @return  array
	 */
	static function localGettime($timestamp = NULL){
	    $tmp = self::localGetdate($timestamp);
	    return $tmp[0];
	}

	/**
	 * 获得用户所在时区指定的日期和时间信息
	 *
	 * @param   $timestamp  integer     该时间戳必须是一个服务器本地的时间戳
	 *
	 * @return  array
	 */
	static function localGetdate($timestamp = NULL){
	    /* 如果时间戳为空，则获得服务器的当前时间 */
	    if ($timestamp === NULL){
	        $timestamp = time();
	    }

	    $gmt        = $timestamp - date('Z');       // 得到该时间的格林威治时间
	    $local_time = $gmt + (self::$timezone * 3600);    // 转换为用户所在时区的时间戳

	    return getdate($local_time);
	}
}
?>