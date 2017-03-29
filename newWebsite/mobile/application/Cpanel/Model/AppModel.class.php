<?php
/**
 * Created by PhpStorm.
 * User: 9008389
 * Date: 2015/12/7
 * Time: 15:03
 */

namespace Cpanel\Model;
use Common\Model\CpanelModel;
use Org\Util\String;

class AppModel extends CpanelModel {
    /**
     * @var 自动验证
     */
    protected $_validate = array(
        array('app_name','require','{%APP_NAME_EMPTY}'),
        array('app_name', 'validate', '{%APP_NAME_FORMAT_WRANG}', self::EXISTS_VALIDATE, 'callback', self::MODEL_BOTH),
        array('app_name','','{%APP_NAME_EXITS}', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
    );

    /**
     * 验证应用名称是否含有特殊字符
     * @param $app_name
     * @return bool
     */
    public function validate($app_name) {
        if(preg_match("/[\.\/\*\?\!]$/", $app_name)){
            return false;
        }
        return true;
    }

    /**
     * 获得应用加密字符
     * @param int $len
     * @param int $subLen
     * @return string
     */
    public function getRandString($len = 16, $subLen = 16){
        $string = String::randString($len);
        return substr(md5(md5($string)),0, $subLen);
    }

}