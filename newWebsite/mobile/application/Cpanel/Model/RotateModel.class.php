<?php
/**
 * ====================================
 * 大转盘模型
 * ====================================
 * Author:
 * Date:
 * ====================================
 * File: RotateModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelUserCenterModel;

class RotateModel extends CpanelUserCenterModel {

    protected $tableName = 'wx_rotates';

    public function filter(&$params){
        $sort = !empty($params['sort']) ? $params['sort'] : 'id';
        $order = !empty($params['order']) ? $params['order'] : 'desc';
        $keyword = !empty($params['keywords']) ? trim($params['keywords']) : '';
        if($keyword){
            $where['_string'] = "act_name like '%$keyword%' or act_title like '%$keyword%'";
        }
        $this->where($where)->order($sort.' '.$order);
    }

    public function format($data){
        if(!empty($data['rows'])){
            foreach($data['rows'] as &$val){
                if($val['is_locked'] == 1){
                    $val['is_locked'] = '<span style="font-size:18px;" class="fa fa-check green"> </span>';
                }else{
                    $val['is_locked'] = '<span style="font-size:18px;" class="fa fa-close red"> </span>';
                }

                if($val['is_checked'] == 1){
                    $val['is_checked'] = '<span style="font-size:18px;" class="fa fa-check green"> </span>';
                }else{
                    $val['is_checked'] = '<span style="font-size:18px;" class="fa fa-close red"> </span>';
                }
                if($val['shipping_free'] == 1){
                    $val['shipping_free'] = '<span style="font-size:18px;" class="fa fa-check green"> </span>';
                }else{
                    $val['shipping_free'] = '<span style="font-size:18px;" class="fa fa-close red"> </span>';
                }

            }
        }
        return $data;
    }

    /**
     * 获取活动信息
     * @param $rotate_id
     * @param string $field
     * @return mixed
     */
    public function rotateInfo($rotate_id, $field=''){
        if(empty($field)){
            $field = 'id,act_name,act_title,des_info,is_locked,is_checked,lottery_num,start_time,end_time,shipping_free,postage';
        }
        return $this->where(array('id'=>$rotate_id))->field($field)->find();
    }

}