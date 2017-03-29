<?php
/**
 * ====================================
 * 微信管理
 * ====================================
 * Author: 9004396
 * Date: 2017-01-13 09:40
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: WeChatController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;
use Common\Extend\Wechat;

class WeChatMenuController extends CpanelController{
    protected $tableName = 'wechat_menu';

    public function create(){
        if(method_exists($this->dbModel,'filter')){
            $this->dbModel->filter(array('locked' => 0));
        }
        $meun = array();
        $data = $this->dbModel->grid();
        foreach ($data as $item){
            if($item['children']){
                $child = array();
                foreach ($item['children'] as $children){
                    if ($children['action'] == 'url'){
                        $child[] = array('name' => $children['text'],'type' => 'view','url' => $children['action_param']);
                    }else{
                        $child[] = array('name' => $children['text'],'type' => 'click','key' => $children['action_param']);
                    }
                }
                $meun['button'][] = array('name' => $item['text'],'sub_button' => $child);
            }else{
                if($item['url']){
                    $meun['button'][] = array('name' => $item['text'], 'type' => 'view', 'url' => $item['action_param']);
                }else{
                    $meun['button'][] = array('name' => $item['text'], 'type' => 'click', 'key' => $item['action_param']);
                }
            }
        }
        Wechat::$app_id = C('APPID');
        Wechat::$app_secret = C('APPSECRET');
        $ret = Wechat::createMenu($meun);
        if($ret['errcode'] == 0){
            $this->ajaxReturn(array('status' => 0,'msg' => 'ok'));
        }else{
            $this->ajaxReturn(array('status' => 1,'msg' => '状态码：'.$ret['errcode'].'错误信息：'.$ret['errmsg']));
        }
    }

    public function remove(){
        Wechat::$app_id = C('APPID');
        Wechat::$app_secret = C('APPSECRET');
        $ret = Wechat::removeMenu();
        if($ret['errcode'] == 0){
            $this->ajaxReturn(array('status' => 0,'msg' => 'ok'));
        }else{
            $this->ajaxReturn(array('status' => 1,'msg' => '状态码：'.$ret['errcode'].'错误信息：'.$ret['errmsg']));
        }
    }

}