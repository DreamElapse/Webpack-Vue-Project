<?php
/**
 * ====================================
 * 微信公众帐号模型
 * ====================================
 * Author: 9004396
 * Date: 2017-02-22 14:19
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: WechatAccountModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelModel;

class WechatAccountModel extends CpanelModel{

    protected $_validate = array(
        array('text','require','{%text_lost}'),
        array('token','require','{%token_lost}'),
        array('app_id','require','{%app_id_lost}'),
        array('app_secret','require','{%app_secret_lost}'),
        array('encoding_aes_key', 'isAesKey', '{%aes_key_lost}', self::MUST_VALIDATE, 'callback'),
    );

    protected function isAesKey($value){
        $params = I('post.');
        if($params['crypted'] > 0 && empty($value)){
            return false;
        }
        return true;
    }

    public function filter($params){
        $where = array();
        if($params['keyword']){
            $where['text'] = array('LIKE', "%{$params['keyword']}%");
        }
        return $this->where($where);
    }
}