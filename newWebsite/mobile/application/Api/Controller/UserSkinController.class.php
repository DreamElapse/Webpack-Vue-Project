<?php
/**
 * ====================================
 * 用户皮肤图
 * ====================================
 * Author: 9006765
 * Date: 2016-10-26 16:09
 * ====================================
 * File:UserSkinController.class.php
 * ====================================
 */
namespace Api\Controller;
use Common\Controller\ApiController;
use Common\Extend\PhxCrypt;


class userSkinController extends ApiController {

    //密钥权限
    protected $_permission = array(
        'getData' => array('scan_payment'),
        'setSyn' => array('scan_payment'),
    );
    private $_params;
    private $_errorMsg = array(
        '100000'    => 'success',
        '200001'    => '参数缺失',
        '200002'    => 'sign校验错误',
        '200004'    => '数据库操作失败',
        '200005'    => '参数错误',
        '400001'    => '数据获取失败'
    );


    /**
     * 查询未拉取数据
     * @param int limit
     * @return array|bool|int
     */
    public function getData(){

        if(is_null($this->_params)){
            $this->_params = $_POST;
        }
        $params = $this->paraFilter($this->_params);
        $isSign = $this->verify($params,$this->_params['sign'],$this->_params['key']);
        if(!$isSign){
            $this->_ReturnData('200002');
        }

        if(!isset($this->_params['limit']) ||empty($this->_params['limit']) ||! is_numeric($this->_params['limit'])){
            $this->_params['limit'] = 100;
        }
        $where['sync_status'] = 0;
        if(isset($this->_params['mobile']) && !empty($this->_params['mobile'])){
            if(!is_array($this->_params['mobile'])){
                $tel = explode(',',$this->_params['mobile']);
            }else{
                $tel = $this->_params['mobile'];
            }
            $where['tel'] = array('IN',$tel);
        }

        $result = D('user_skin')->where($where)->limit(0,$this->_params['limit'])->order('id desc')->select();
        if($result == false){
            $this->_ReturnData('400001');
        }else{
            $this->_ReturnData('100000',$result);
        }
    }


    /**
     * 更新已拉取订单
     * @param id
     * @return bool
     */
    public function setSyn(){

           $ids = I('post.ids','');
        if( empty($ids)){
            $this->_ReturnData('200001');
        }
        $check_id = explode(',',$ids );
        if(!is_array($check_id)){
            $this->_ReturnData('200005');
        }
        foreach($check_id as $k => $v){
            if(!is_numeric($v)){
                $this->_ReturnData('200005');
            }
        }
        $user_skin_model = D('user_skin');
        $user_skin_model->create(array('sync_status'=>1));
        $save_result = $user_skin_model->where('sync_status = 0 AND id IN ('.$ids .')')->save();
        if($save_result){
            die("success");
        }else{
            die("failure");
        }
    }


    /**
     * 返回信息
     * @param $code
     * @param array $data
     * @return mixed|string
     */
    private function _ReturnData($code,$data = ''){
        $returnData = array('code' => $code, 'msg' => $this->_errorMsg[$code]);

        if(!empty($data)){
            $returnData['data'] = $data;
        }

        echo json_encode($returnData);
        exit;
    }




}

