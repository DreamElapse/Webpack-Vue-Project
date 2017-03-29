<?php
/**
 * ====================================
 * 积分日志模型
 * ====================================
 * Author: 9004396
 * Date: 2017-02-24 17:51
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: UserPointLogModel.class.php
 * ====================================
 */
namespace Cpanel\Model;

use Common\Model\CpanelUserCenterModel;
use Common\Extend\PhxCrypt;

class UserPointLogModel extends CpanelUserCenterModel {

    public function filter($params){
        $where = array();
        if(!empty($params['id'])){
            $where['log.integralrecord_id'] = $params['id'];
        }

        if(!empty($params['state'])){
            $where['log.state'] = $params['state'];
        }

        if(!empty($params['rank'])){
            if($params['rank'] == '1'){
                $where['a.rank'] = array(array('eq',$params['rank']),array('eq',0), 'or');
            }else{
                $where['a.rank'] = $params['rank'];
            }
        }

        if (!empty($params['type'])) {
            $keyword = $params['keyword'];
            switch ($params['type']) {
                case 1:
                    $where['a.user_id'] = intval($keyword);
                    break;
                case 2:
                    $where['u.email'] = array('LIKE', "%{$keyword}%");
                    break;
                case 3:
                    $where['u.mobile'] = PhxCrypt::phxEncrypt($keyword);
                    break;
                case 4:
                    $where['u.user_num'] = $keyword;
                    break;
                case 6:
                    $where['u.user_name'] = $keyword;
            }
        }

        if(!empty($params['point_type'])){
            $prontType = $params['point_type']-1;
            $where['log.point_type'] = $prontType;
        }

        if(!empty($params['use_start_date']) && !empty($params['use_end_date'])){
            $time_start=strtotime($params['use_start_date'])-28800;
            $time_ent=strtotime($params['use_end_date'])+57600;
            $where['log.add_time'] = array(array('EGT', $time_start), array('ELT', $time_ent));
        }


        $this->alias('log')
            ->join('__USERS__ AS u ON u.user_id=log.user_id','left')
            ->join('__USER_ACCOUNT__ as a ON a.user_id=log.user_id')
            ->join('__USER_RANK__ as ur ON a.rank = ur.rank_id','left')
            ->field('log.log_id,log.user_id,log.order_id,log.order_sn,log.points,log.state,log.remark,log.add_time,log.point_type,u.user_num,u.mobile,u.email,u.user_name,a.rank,ur.rank_name');
        $this->order('log.log_id DESC');
        return $this->where($where);
    }

    public function format($data)
    {
        if (!empty($data)) {
            foreach ($data['rows'] as &$item) {
                switch ($item['state']){
                    case 2:
                        $item['state_text'] = '<font color="green">'.L('INTEGRAL_GOODS_REMOVE').'</font>';
                        break;
                    case 0:
                        $item['state_text'] = '<font color="green">'.L('INTEGRAL_NORMAL').'</font>';
                        break;
                    case -1:
                        $item['state_text'] = '<font color="red">'.L('INTEGRAL_REMOVE').'</font>';
                        break;
                    case -2:
                        $item['state_text'] = '<font color="green">'.L('INTEGRAL_EXPIRED').'</font>';
                        break;
                    case -3:
                        $item['state_text'] = '<font color="red">'.L('INTEGRAL_SELF_CONSUME').'</font>';
                        break;
                    case -4:
                        $item['state_text'] = '<font color="red">'.L('INTEGRAL_CUSTOMER_CONSUME').'</font>';
                        break;
                }
                switch ($item['point_type']){
                    case 0:
                        $item['type_text'] = L('ORDER_INTEGRAL');
                        break;
                    case 1:
                        $item['type_text'] = L('CHECK_IN_INTEGRAL');
                        break;
                    case 2:
                        $item['type_text'] = L('COMMENT_INTEGRAL');
                        break;
                    case 3:
                        $item['type_text'] = L('CONSUME_INTEGRAL');
                        break;
                }
                $item['mobile'] = PhxCrypt::phxDecrypt($item['mobile']);
            }
        }
        return $data;
    }
}