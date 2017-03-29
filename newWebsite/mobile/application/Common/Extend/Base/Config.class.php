<?php
/**
 * ====================================
 * 配置逻辑处理
 * ====================================
 * Author: Hugo
 * Date: 14-5-22 下午11:16
 * ====================================
 * File: ConfigService.class.php
 * ====================================
 */
namespace Common\Extend\Base;
class Config
{

    //初始化配置选项
    public static function init()
    {
        $config = S('DB_CONFIG_DATA');
        if (!$config) {
            $config = self::lists();
            S('DB_CONFIG_DATA', $config);
        }
        C($config); //添加配置
        self::getWechat();
    }

    //查询配置选项
    public static function lists()
    {
        $config = C('cpanel');
        if (C('DB_NAME') == $config['DB_NAME']) {
            $dbModel = M('Config');
        } else {
            $dbModel = M('Config', $config['DB_PREFIX'], 'CPANEL');
        }
        $data = $dbModel->field('type,name,value')->select();
        $config = array();
        if ($data && is_array($data)) {
            foreach ($data as $value) {
                $config[$value['name']] = self::parse($value['type'], $value['value']);
            }
        }
        return $config;
    }

    public static function getWechat()
    {
        $config = C('cpanel');
        if (C('DB_NAME') == $config['DB_NAME']) {
            $dbModel = M('WechatAccount');
        } else {
            $dbModel = M('WechatAccount', $config['DB_PREFIX'], 'CPANEL');
        }
        $data = $dbModel->field('token,app_id,app_secret,machine_id,pay_key,crypted,encoding_aes_key')->where(array('defaulted' => 1))->find();
        if (!empty($data)) {
            C('token',$data['token']);  //令牌
            C('appid',$data['app_id']);  //应用ID
            C('appsecret',$data['app_secret']); //应用密钥
			C('wechat_machine_id',$data['machine_id']);  //商务号
			C('wechat_pay_key',$data['pay_key']);  //支付密钥
        }
		
        define('TOKEN', (isset($data['token']) ? $data['token'] : ''));  //令牌
        define('APPID', (isset($data['app_id']) ? $data['app_id'] : ''));  //应用ID
        define('APPSECRET', (isset($data['app_secret']) ? $data['app_secret'] : ''));  //应用密钥
		define('WECHAT_MACHINE_ID', (isset($data['machine_id']) ? $data['machine_id'] : ''));  //商务号
		define('WECHAT_PAY_KEY', (isset($data['pay_key']) ? $data['pay_key'] : ''));  //支付密钥
		
        return $data;
    }

    /**
     * 根据配置类型解析配置
     * @param integer $type 配置类型
     * @param string $value 配置值
     * @return array
     */
    private static function parse($type, $value)
    {
        switch ($type) {
            case 4: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if (strpos($value, ':')) {
                    $value = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k] = is_numeric($v) ? floatval($v) : $v;
                    }
                } else {
                    $value = $array;
                }
                break;
        }
        return $value;
    }
}