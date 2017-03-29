<?php
/**
 * ====================================
 * API公共类
 * ====================================
 * Author: 9009123
 * Date: 2016-09-13 16:16
 * ====================================
 * File: ApiController.class.php
 * ====================================
 */
namespace Common\Controller;

use Common\Controller\InitController;

class ApiController extends InitController
{
    protected $status = null;  //请求状态
    protected $msg = null;  //请求信息

    //API接口认证密钥
    private $_key_config = array(
        'java' => array(
            'd877cfa2690d0b688ded2759209edea3',  //JAVA使用
            'a533c972790b0fd1e9e6cbd0e289098e',  //备注：未使用，一旦开启使用请修改此备注
            '216d8167db3e5bd18b0b7bc7bd036546',  //备注：未使用，一旦开启使用请修改此备注
            'e29cbce157e14df01998440c0e27bf0a',  //备注：未使用，一旦开启使用请修改此备注
        ),
        'baida_solt' => array(
            '5131f6caead4314643b671b624714d94',  //百搭软件使用
            '748af7cd9543a53aba388ce09ac1643f',  //备注：未使用，一旦开启使用请修改此备注
            'fa4b3c0d546f48f1e4fafa74794d641a',  //备注：未使用，一旦开启使用请修改此备注
        ),

        'scan_payment' => array(
            'd02505b02ba35ec882c23008023343f9',  //备注：后台程序调用二维码支付和图片接口  2017-03-22 14:31
            '26c42f726e3259b1b85146ba29ea29f5',  //备注：未使用，一旦开启使用请修改此备注
            '73e94176434c8eb95f9fe5fe34c8c241',  //备注：未使用，一旦开启使用请修改此备注
        ),
        'wechat' => array(
            'a462cd060ff8a7bd8f7b53114b7698dc',  //备注：后台程序调用微信模版消息接口  2016-10-31
            '659bf7d3192d7324c1b1090f445aa292',  //备注：统一获取微信accessToken接口      2017-3-16
            '4da126c3be6d3ab2396e4cc6338791b4',  //备注：未使用，一旦开启使用请修改此备注
        ),
        'wechat_coupon' => array(
            '3152cb4091255e98b92f014f71d17a76',  //备注：微信电子券接口  2016-11-09
            '131a7ba94a5fcce9a28958d6a48bc44e',  //备注：未使用，一旦开启使用请修改此备注
            '802000c0dd90b59a961d4f927458a133',  //备注：未使用，一旦开启使用请修改此备注
            'fe44357bd46c876f41203d9ee440c0a7',  //备注：未使用，一旦开启使用请修改此备注
        ),
        'gzbms' => array(                       //业务后台
            '143f6ebd78687315cc622c2c14c039e4', //维权接口使用
            '40883f0353aa6917913dd94eeacae16d',
            '444de0bde0117b3bfa82f6782003da95',
            'b65696ff1b1a17d44f9b2f43ea20334f',
            '627aa4f061b4569b9911e339f128e443',
        )
    );

    //加密返回码
    private $returnCode = array(
        '10000' => '成功',
        '10001' => 'KEY认证失败',
        '10002' => '没有权限操作',
        '10003' => 'sign校验错误',
        '10020' => '缺少必须参数或必传参数为空',

        //微信
        '10100' => '找不到订单',
        '10101' => '找不到对应的物流信息',
        '10102' => '手机号码没绑定微信号',
        '10103' => '微信返回错误',

        //支付
        '20001' => '支付类型不存在',
        '20002' => 'sign校验错误',
        '20003' => '该订单已经完成支付，无法重复操作',
        '20004' => '数据库操作失败',
        '20005' => '订单号不存在',
        '20006' => '支付金额不存在或错误',
        '20007' => '订单标题不存在',
        '20008' => '二维码生成失败',
        '20011' => '此支付方式不存在或者参数错误！',
        '20012' => '此支付方式还没有被启用！',


        //微信电子券
        '30001' => '请求超时',
        '30002' => '手机号码不存在',
        '30003' => '数据不存在',
        '30004' => '身份证号码不存在',
        '30005' => '用户姓名不存在',
        '30006' => '无法识别用户',
        '30007' => '系统错误',
        '30008' => '已短信通知过客户',
    );


    public function __construct()
    {
        parent::__construct();

        //检查如果有自定义返回码，则合并
        if (isset($this->_returnCode) && !empty($this->_returnCode)) {
            $this->returnCode = array_merge($this->returnCode, $this->_returnCode);
        }

        //检查接口权限，KEY授权
        if (isset($this->_permission) && !empty($this->_permission)) {
            $key = I('request.key', '', 'trim');
            if ($key == '') {
                $this->error('10001');
            }
            $key = strtolower($key);
            $action_name = ACTION_NAME;
            //检查设置设置了权限
            if (isset($this->_permission[$action_name]) && !empty($this->_permission[$action_name])) {
                $is_pass = false; //是否认证通过
                foreach ($this->_permission[$action_name] as $name) {
                    //有设置权限，有授权
                    if (isset($this->_key_config[$name]) && !empty($this->_key_config[$name]) && in_array($key, $this->_key_config[$name])) {
                        $is_pass = true;
                        break;
                    }
                }
                if ($is_pass === false) {  //没被授权，阻止访问
                    $this->error('10002');
                }
            }
        }
    }

    /*
    *	成功返回结果
    *	@Author 9009123 (Lemonice)
    *	@param  anything $data  返回的数据
    *	@return exit && JSON
    */
    protected function success($data = '')
    {
        $status = '10000';
        $return = array(
            'status' => $status,
            'msg' => $this->returnCode[$status],
            'data' => $data
        );

        if (!is_null($this->status)) {
            $return['status'] = $this->status;
        }
        if (!is_null($this->msg)) {
            $return['msg'] = $this->msg;
        }
        $this->ajaxReturn($return);
    }

    /*
    *	错误返回结果
    *	@Author 9009123 (Lemonice)
    *	@param  $status 状态码
    *	@param  anything $data 错误信息
    *	@return exit && JSON
    */
    protected function error($status = '', $data = '')
    {
        $msg = isset($this->returnCode[$status]) ? $this->returnCode[$status] : '';
        $return = array(
            'status' => $status,
            'msg' => $msg,
            'data' => $data
        );
        if (!is_null($this->status)) {
            $return['status'] = $this->status;
        }
        if (!is_null($this->msg)) {
            $return['msg'] = $this->msg;
        }
        $this->ajaxReturn($return);
    }


    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * @return array 去掉空值与签名参数后的新签名参数组
     */
    protected function paraFilter($para)
    {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $val == "") continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 校验
     * @param $params
     * @param $sign
     * @param $key
     * @return bool
     */
    public function verify($params, $sign, $key)
    {
        $isSign = false;
        $verify = base64_encode(md5(http_build_query($params) . md5($key)));
        if ($verify === $sign) {
            $isSign = true;
        }
        return $isSign;
    }
}