<?php
return array(
    'MODULE_ALLOW_LIST'     =>  array('Home','Api','Common','Crontab','Cpanel'),
    'DEFAULT_MODULE'        =>  'Home',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
    'DEFAULT_CHARSET'       =>  'utf-8', // 默认输出编码
    'DEFAULT_TIMEZONE'      =>  'PRC',  // 默认时区

    'URL_CASE_INSENSITIVE'      => true, //设置URL是否大小写敏感
    'LOAD_EXT_CONFIG'           => 'domain,mysql,constant,spread,config_3g_q,wechatCode', //加载扩展配置
    'LOAD_EXT_FILE'             => 'verify,string',
    'DATE_FORMAT'               => 'Y-m-d H:i:s',


    /*======== 语言配置 ========*/
    'LANG_SWITCH_ON'            => true,   // 开启语言包功能
    'DEFAULT_LANG'              => 'zh-cn', // 允许切换的语言列表 用逗号分隔


    'URL_MODEL'                 => 2, //设置URL模式
    'URL_HTML_SUFFIX'           => 'shtml|json',
    

    'VIEW_PATH'                 => './template/',
    'TMPL_ACTION_ERROR'         => 'Public:message',
    'TMPL_ACTION_SUCCESS'       => 'Public:message',


    'WECHAT_AUTHORIZE_DOMAIN'   => 'q.chinaskin.cn'  //微信授权域名
);