<?php
/**
 * ====================================
 * 维权数据推送接口
 * ====================================
 * Author: 9004396
 * Date: 2017-02-16 10:07
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: RightsController.class.php
 * ====================================
 */
namespace Api\Controller;
use Common\Controller\ApiController;

class RightsController extends ApiController{
    protected $_permission = array(
        'getRights' => array('gzbms'),
        'sync' => array('gzbms'),
        'syncStatus' => array('gzbms'),
        'getConfirm' => array('gzbms'),
        'syncConfirm' => array('gzbms'),
    );



    public function getRights(){
    $limit = I('param.limit');
    $limit = empty($limit) ? 100 :$limit;
    $rights = D('Home/Rights')->where(array('sync' => 0))->field('order_sn,mobile,remark,rid')->limit($limit)->select();
    $this->success($rights);
//    if(!empty($rights)){
//        $this->success($rights);
//    }else{
//        $this->error('data Empty');
//    }
}

    public function sync(){
        $param =  I('param.syscData');
        if(empty($param) || !is_array($param)){
            $this->error('参数格式不正确！');
        }
        $rids = array();
        foreach ($param as $item){
            if(!empty($item['rid'])){
                $rids[] = $item['rid'];
            }
        }

        if(count($rids) !== count($param)){
            $this->error('参数异常!');
        }



        $data['sync'] = 1;
        $errorData = array();
        foreach ($param as $item){
            if(empty($item['complaint_no'])){
                $errorData[] = $item['rid'];
                continue;
            }
            $isOnly = D('Home/Rights')->where(array('complaint_no' => $item['complaint_no']))->count();
            if(!empty($isOnly)){
                $errorData[] = $item['rid'];
                continue;
            }
            $data['complaint_no'] = $item['complaint_no'];
            $data['follow_kefu']  = $item['follow_kefu'];
            $result =  D('Home/Rights')->where(array('rid' => $item['rid']))->save($data);
            if(!$result){
                $errorData[] = $item['rid'];
            }
        }
        if(empty($errorData)){
            $this->success();
        }else{
            $this->error('维权编号为'.implode(',',$errorData).'数据异常');
        }
    }


    public function syncStatus(){
        $param =  I('param.syscData');
        if(empty($param) || !is_array($param)){
            $this->error('参数格式不正确！');
        }

        $nos = array();
        foreach ($param as $item){
            if(!empty($item['complaint_no'])){
                $nos[] = $item['complaint_no'];
            }
        }

        if(count($nos) !== count($param)){
            $this->error('参数异常!');
        }

        $data['sync'] = 2;
        $errorData = array();
        foreach ($param as $item){
            if(empty($item['status'])){
                $errorData[] = $item['rid'];
                continue;
            }

            $status = D('Home/Rights')->where(array('complaint_no' => $item['complaint_no']))->getField('status');
            if($status == 4){
               continue;
            }

            if(!empty($item['follow_kefu'])){
                $data['follow_kefu']  = $item['follow_kefu'];
            }
            $data['status'] = $item['status'];
            $result =  D('Home/Rights')->where(array('complaint_no' => $item['complaint_no']))->save($data);
            if(!$result){
                $errorData[] = $item['complaint_no'];
            }
        }
        if(empty($errorData)){
            $this->success();
        }else{
            $this->error('维权编号为'.implode(',',$errorData).'数据异常');
        }
    }

    public function getConfirm(){
        $limit = I('param.limit');
        $limit = empty($limit) ? 100 :$limit;
        $rights = D('Home/Rights')->where(array('sync' => 2,'status' => 4))->field('complaint_no,rid')->limit($limit)->select();
        $this->success($rights);
//        if(!empty($rights)){
//            $this->success($rights);
//        }else{
//            $this->error('data Empty');
//        }
    }

    public function syncConfirm(){
        $rid = I('param.rid');
        if(empty($rid)){
            $this->error('维权编号不能为空！');
        }
        $where = array();
        if(is_array($rid)){
            $where['rid'] = array('IN',$rid);
        }else if(is_string($rid)){
            if(strpos($rid,',') !== false){
                $where['rid'] = array('IN',$rid);
            }else{
                $where['rid'] = $rid;
            }
        }
        if(!empty($where)){
            $result =  D('Home/Rights')->where($where)->setField('sync',3);
            if($result){
                $this->success();
            }else{
                $this->error(D('Home/Rights')->getError());
            }
        }else{
            $this->error('参数格式错误!');
        }

    }
}