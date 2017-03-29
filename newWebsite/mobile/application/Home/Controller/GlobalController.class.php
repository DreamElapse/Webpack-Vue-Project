<?php
/**
 * ====================================
 * 全局变量接口
 * ====================================
 * Author: 9004396
 * Date: 2016-07-05 17:13
 * ====================================
 * File:GlobalController.class.php
 * ====================================
 */
namespace Home\Controller;

use Common\Controller\InitController;
use Common\Extend\PhxCrypt;
use Home\Model\CartModel;

class GlobalController extends InitController
{

    public function index()
    {
    }

    /**
     * 获取购物车商品数量
     */
    public function cartGoodsNum()
    {
        $cartModel = new CartModel();
        $cart_info = $cartModel->cartData(array('user_id' => $this->user_id), false, false);
        //购物车商品总数
        $goods_nums = 0;
        foreach ($cart_info as $key => $val) {
            if ($val['parent_id'] > 0) {
                continue;
            }
            $goods_nums += $val['goods_number'];
        }
        $this->success(array('cartGoodsNum' => $goods_nums));
    }

    /**
     * 检测是否登陆
     */
    public function getUserId()
    {
        empty($this->user_id) ? $this->error() : $this->success();
    }

    /**
     * 获取电话和QQ
     */
    public function getAdvisoryInfo()
    {
        $campaign = I('request.campaign');
        $data = getAdvisoryInfo($campaign);
        $this->success($data);
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo()
    {
        if (empty($this->user_id)) {
            $this->error();
        }
        //$userInfo = session('userInfo');
        $data = D('Users')->getUserLoginInfo($this->user_id);
        if (!empty($data)) {
            unset($data['user_id']);
            unset($data['email']);
            $data['user_name'] = PhxCrypt::phxDecrypt($data['user_name']);

            $data['points_left'] = !isset($data['points_left']) ? 0 : $data['points_left'];
            $data['level'] = !isset($data['level']) || $data['level'] <= 0 ? 1 : intval($data['level']);

            $this->success($data);
        } else {
            $this->error();
        }
    }

    /**
     * 获取openId
     */
    public function getOpenId()
    {
        $source = I('post.source');
        $data = array(
            'result' => 0,
        );
        $result = isCheckWechat();
        if ($result == false) {  //不是微信打开网页
            $this->success($data);
        }
        $openId = session('sopenid');
        if (!empty($openId)) {
            $data['result'] = 2;
            $this->success($data);
        }
        $data['result'] = 1;  //支持微信支付
        $data['url'] = U('Global/getWechatOpenId', array('source' => base64_encode($source)));
        $this->success($data);
    }

    public function getWechatOpenId()
    {
        $openid = session('sopenid');
        if (empty($openid)) {
            $domain = C('WECHAT_AUTHORIZE_DOMAIN'); //获取微信授权地址
            import('Common/Extend/Payment/Wechatpay/WxPay');
            $jsApi = new \JsApi_pub();
            $jsApi->weChat_appId = APPID;
            $jsApi->weChat_appSecret = APPSECRET;
            $jsApi->api_call_url = 'http://'.$domain . $_SERVER['REQUEST_URI'];
            $code = I('get.code', NULL, 'trim');
            $host = '';
            if ($_SERVER['HTTP_HOST'] != $domain) {  //判断当前域名是否授权域名，不是授权域名组装回调地址
                $host = urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
            if (is_null($code)) {
                $url = $jsApi->createOauthUrlForCode('snsapi_base', $host);
                header("Location: $url");  //跳转过去，为了获取code
            } else {
                $url = $this->createCallbackUrl();
                if(!empty($url)){
                    header("Location: $url"); //回调访问站点
                    exit;
                }
                $jsApi->setCode($code);
                $openid = $jsApi->getOpenid();
                if (!empty($openid)) {
                    $source = base64_decode(I('get.source'));
                    if (empty($source)) {
                        $callback = '/';
                    } else {
                        $callback = siteUrl() . '#' . $source;
                    }
                    session('sopenid', $openid);
                    header("Location: $callback");  //跳转过去，为了获取code
                } else {
                    $url = $jsApi->createOauthUrlForCode('snsapi_userinfo', $host);
                    header("Location: $url");  //跳转过去，为了获取code
                }
            }
        }else{
            $url = $this->createCallbackUrl();
            if(!empty($url)){
                header("Location: $url"); //回调访问站点
                exit;
            }
        }
    }

    /**
     * 创建回调地址
     * @return string
     */
    private function createCallbackUrl(){
        $param = I('get.');
        $domain = C('WECHAT_AUTHORIZE_DOMAIN'); //获取微信授权地址
        $buff = '';
        $url = '';
        if (!empty($param) && isset($param['host']) && $param['host'] !== $domain) {
            foreach ($param as $k => $v) {
                if($k != 'host'){
                    $v = urlencode($v);
                    $buff .= $k . "=".$v."&";
                }
            }
            $request_uri = '';
            if(strlen($buff) > 0){
                $request_uri = substr($buff, 0, strlen($buff) - 1);
            }
            $url = 'http://'.$param['host'].'?'.$request_uri;
        }
        return $url;
    }
}