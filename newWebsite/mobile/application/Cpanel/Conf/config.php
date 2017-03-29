<?php
/**
 * ====================================
 * 配置文件
 * ====================================
 * Author: 9004396
 * Date: 2017-01-10 19:41
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: config.php
 * ====================================
 */
return array(
    'SYSTEM_VERSION'            => '1.0',
    /*======== 其他设置 ========*/
    'CRYPT_KEY'                 => '!%6&8*!#', //加密串
    'DATE_FORMAT'               => 'Y-m-d H:i:s',

    /*======== 模板设置 ========*/
    'LAYOUT_ON'                 => true,
    'LAYOUT_NAME'               => 'layout',
    'VIEW_PATH'                 => './template/Cpanel/',
    'TMPL_PARSE_STRING'         => array(
        '__PUBLIC__' => '/public/static'
    ),

);