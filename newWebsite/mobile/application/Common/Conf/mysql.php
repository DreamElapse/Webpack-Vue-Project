<?php
return array(
    /* 数据库配置 */
    'DB_TYPE'   => 'mysql', // 数据库类型
//    'DB_HOST'   => '192.168.60.55', // 服务器地址
//    'DB_NAME'   => 'chinaskindb', // 数据库名
//    'DB_USER'   => 'newxianya', // 用户名
//    'DB_PWD'    => '123abc',  // 密码
//    'DB_PORT'   => '3306', // 端口
//    'DB_PREFIX' => 'py_', // 数据库表前缀

    'DB_CONFIG' => array(
        array(
            'HOST' => C('domain.G'),
            'CONFIG' => array(
                'DB_HOST'   => '192.168.60.55', // 服务器地址
                'DB_NAME'   => 'new3gchinaskin', // 数据库名
                'DB_USER'   => 'newxianya', // 用户名
                'DB_PWD'    => '123abc',  // 密码
                'DB_PORT'   => '3306', // 端口
                'DB_PREFIX' => 'ecs_', // 数据库表前缀
            ),
            'SITE_ID' => 87,
        ),
        array(
            'HOST' => C('domain.Q'),
            'CONFIG' => array(
                'DB_HOST'   => '192.168.60.55', // 服务器地址
                'DB_NAME'   => 'wingeasycom', // 数据库名
                'DB_USER'   => 'newxianya', // 用户名
                'DB_PWD'    => '123abc',  // 密码
                'DB_PORT'   => '3306', // 端口
                'DB_PREFIX' => 'ecs_', // 数据库表前缀
            ),
            'SITE_ID' => 14,
        )
    ),
    'PAYMENT' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '192.168.60.55', // 服务器地址
        'DB_NAME'   => 'payment', // 数据库名
        'DB_USER'   => 'newxianya', // 用户名
        'DB_PWD'    => '123abc',  // 密码
        'DB_PORT'   => '3306', // 端口
    ),

    'USER_CENTER' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '192.168.60.55', // 服务器地址
        'DB_NAME'   => 'usercenter', // 数据库名
        'DB_USER'   => 'newxianya', // 用户名
        'DB_PWD'    => '123abc',  // 密码
        'DB_PORT'   => '3306', // 端口
    ),

    'APPS' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '192.168.60.55', // 服务器地址
        'DB_NAME'   => 'app', // 数据库名
        'DB_USER'   => 'newxianya', // 用户名
        'DB_PWD'    => '123abc',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'thinkphx_'
    ),

    'CPANEL' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '192.168.60.55', // 服务器地址
        'DB_NAME'   => 'chinaskin_wechat', // 数据库名
        'DB_USER'   => 'newxianya', // 用户名
        'DB_PWD'    => '123abc',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'py_'
    ),


);