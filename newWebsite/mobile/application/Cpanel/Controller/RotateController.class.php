<?php
/**
 * ====================================
 * 抽奖
 * ====================================
 * Author:
 * Date:
 * ====================================
 * File: RotateController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;

class RotateController extends CpanelController {

    protected $tableName = 'Rotate';

    public function form(){
        $model = D('Rotate');
        $id = I('request.id', 0, 'intval');
        if($id){
            $editable = 0; //用于判断是否能够编辑信息,0-可编辑，1-不可编辑
            $now_time = time();
            $info = $model->rotateInfo($id);
            $prizes = array();
            if($info){
                //审核通过并且已启动的正在进行或者结束的活动都不能修改
                if($info['start_time']<=$now_time && $info['is_checked']==1){
                    $editable = 1;
                }

                $info['start_time'] = date('Y-m-d H:i:s', $info['start_time']);
                $info['end_time'] = date('Y-m-d H:i:s', $info['end_time']);

                $prizes = $model->table('wx_rotates_prize')->order('grade asc')->where(array('rotate_id'=>$id))->select();
            }

            $this->assign('info', $info);
            $this->assign('editable', $editable);
            $this->assign('prizes', $prizes);
        }
        $this->display();
    }

    public function formAdd(){
        $this->display();
    }


    //添加，编辑
    public function save(){

        $params = I('request.');

        $model = D('Rotate');
        $prizeModel = M('wx_rotates_prize', null, 'USER_CENTER');

        if(method_exists($this, '_after_before')) {
            $data = $this->_after_before($params);
        }

        $act_data = $data['act_data'];
        $prizes_data = $data['prizes'];
        $pk = $model->getPk();
//        echo '<pre>';
//        print_r($prizes_data);
//        exit;

        if($params[$pk]){
            $result = $model->where(array('id'=>$params['id']))->save($act_data);
            if(!empty($prizes_data)){
                $new_prize = array();
                foreach($prizes_data as $k=>$v){
                    if($v['id']){
                        $id = $v['id'];
                        unset($v['id']);
                        $result = $prizeModel->where(array('id'=>$id))->save($v);
                    }else{
                        unset($v['id']);
                        $new_prize[] = $v;
                    }
                }

                if(!empty($new_prize)){
                    $result = $prizeModel->addAll($new_prize);
                }
            }
        }else{
            $rotate_id = $model->add($act_data);
            if($rotate_id){
                foreach($prizes_data as &$v){
                    $v['rotate_id'] = $rotate_id;
                }
                $result = $prizeModel->addAll($prizes_data);
            }else{
                $result = flase;
            }
        }
        if($result!==flase){
            $msg = L('SAVE') . L('SUCCESS');
            $this->success($msg, '', true);
        }else{
            $this->error(L('SAVE') . L('ERROR'), '', true);
        }
    }

    //添加，保存前操作
    public function _after_before($params){

        $prizes = array();
        $act_data = array();
        $count = 0;
        if(!empty($params['act_name'])){
            $act_data['act_name'] = trim($params['act_name']);
        }
        if(!empty($params['act_title'])){
            $act_data['act_title'] = trim($params['act_title']);
        }
        if(isset($params['lottery_num'])){
            $act_data['lottery_num'] = intval($params['lottery_num']);
        }
        if(!empty($params['start_time'])){
            $act_data['start_time'] = strtotime(trim($params['start_time']));
        }
        if(!empty($params['end_time'])){
            $act_data['end_time'] = strtotime(trim($params['end_time']));
        }
        if(isset($params['base_num'])){
            $act_data['base_num'] = intval($params['base_num']);
        }
        if(isset($params['postage'])){
            $act_data['postage'] = intval($params['postage']);
        }
        if(isset($params['checked'])){
            $act_data['is_checked'] = intval($params['checked']);
        }
        if(isset($params['locked'])){
            $act_data['is_locked'] = intval($params['locked']);
        }
        if(isset($params['des_info'])){
            $act_data['des_info'] = trim($params['des_info']);
        }
        if(isset($params['shippingFree'])){
            $act_data['shipping_free'] = intval($params['shippingFree']);
        }
//        echo '<pre>';
//        print_r($params);exit;
        if(!empty($params['id'])){
            //编辑
            if(!empty($params['grade'])){
                foreach($params['grade'] as $k=>$v){
                    if(!empty($params['chance'])){
                        $count += ($prizes[$k]['chance'] / 100);
                        $prizes[$k]['chance'] = !empty($params['chance'][$k]) ? trim($params['chance'][$k]) : 0;
                    }
                    $prizes[$k]['grade'] = $v;
                    if(!empty($params['prize_num'])){
                        $prizes[$k]['prize_num'] = !empty($params['prize_num'][$k]) ? intval($params['prize_num'][$k]) : 0;
                    }
                    if(!empty($params['prize_name'])){
                        $prizes[$k]['prize_name'] = !empty($params['prize_name'][$k]) ? trim($params['prize_name'][$k]) : '';
                    }
                    if(!empty($params['goods_id'])){
                        $prizes[$k]['goods_id'] = !empty($params['goods_id'][$k]) ? intval($params['goods_id'][$k]) : 0;
                    }
                    if(!empty($params['prize_id'])){
                        $prizes[$k]['id'] = $params['prize_id'][$k];
                    }
                    $prizes[$k]['rotate_id'] = intval($params['id']);
                }
            }else if(!empty($params['prize_id']) && !empty($params['chance'])){
                foreach($params['prize_id'] as $k=>$v){
                    $prizes[$k]['id'] = $v;
                    $prizes[$k]['chance'] = $params['chance'][$k];
                }
            }
        }else{
            if(!empty($params['grade'])){
                foreach($params['grade'] as $k=>$v){
                    $prizes[$k]['grade'] = intval($v);
                    $prizes[$k]['prize_num'] = !empty($params['prize_num'][$k]) ? intval($params['prize_num'][$k]) : 0;
                    $prizes[$k]['chance'] = !empty($params['chance'][$k]) ? trim($params['chance'][$k]) : 0;
                    $prizes[$k]['prize_name'] = !empty($params['prize_name'][$k]) ? trim($params['prize_name'][$k]) : 0;
                    $prizes[$k]['goods_id'] = !empty($params['goods_id'][$k]) ? intval($params['goods_id'][$k]) : 0;
                    $count += ($prizes[$k]['chance'] / 100);
                }
            }
        }

        if($count>1){
            $this->error('总概率不能超过100%');
        }

        $return['prizes'] = $prizes;
        $return['act_data'] = $act_data;

        return $return;
    }

    //获取配置商品
    public function getGoods(){
        $model = D('Rotate');
        if(IS_AJAX){
            $where['is_on_sale'] = 1;
            $field = 'goods_id,goods_name,goods_sn';
            $goods = $model->table('goods')->where($where)->field($field)->select();
            foreach($goods as &$val){
                $val['goods_name'] = $val['goods_sn'] ? $val['goods_sn'].'-'.$val['goods_name'] : $val['goods_name'];
            }
            $this->ajaxReturn($goods);
        }
    }

    /**
     * 删除奖品
     * @param prizeId int 奖品id
     */
    public function prizeDel(){
        $prize_id = I('request.prizeId', 0, 'intval');
        $prizeModel = M('wx_rotates_prize', null, 'USER_CENTER');
        $result = $prizeModel->where(array('id'=>$prize_id))->delete();
        if($result === false){
            $this->error('删除失败');
        }else{
            $this->success('操作成功');
        }
    }

}