<?php
/**
 * ====================================
 * 积分模型
 * ====================================
 * Author: 9004396
 * Date: 2017-02-24 10:07
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: IntegralModel.class.php
 * ====================================
 */
namespace Cpanel\Model;

use Common\Extend\PhxCrypt;
use Common\Model\CpanelUserCenterModel;

class IntegralModel extends CpanelUserCenterModel
{
    protected $tableName = 'user_account';

    public function filter($params)
    {
        $where = array();
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
        if(!empty($params['rank'])){
            if($params['rank'] == '1'){
                $where['_string'] = 'a.rank = 1 OR a.rank = 0';
            }else{
                $where['a.rank'] = $params['rank'];
            }
        }
        $min_integral = empty($params['min_integral']) ? 0 : $params['min_integral'];
        $max_integral = empty($params['max_integral']) ? 0 : $params['max_integral'];
        if ($max_integral > 0 && $min_integral > 0 && $min_integral <= $max_integral) {
            $where['a.points_left'] = array(array('EGT', $min_integral), array('ELT', $max_integral));
        } elseif ($min_integral > 0 && $max_integral == 0) {
            $where['a.points_left'] = array('EGT', $min_integral);
        } elseif ($max_integral > 0 && $min_integral == 0) {
            $where['a.points_left'] = array('ELT', $max_integral);
        }
        $this->alias('a')
            ->join('__USERS__ AS u ON a.user_id = u.user_id', 'left')
            ->join('__USER_RANK__ as ur ON a.rank = ur.rank_id','left')
            ->field('a.id,a.user_id,a.customer_id,a.total_points,a.pay_points,a.points_left,a.expire_points,a.rank,u.user_num,u.mobile,u.email,u.user_name,ur.rank_name');
        $this->order('a.id DESC');
        return $this->where($where);
    }


    public function format($data)
    {
        if (!empty($data)) {
            foreach ($data['rows'] as &$item) {
                $item['rank_name'] = empty($item['rank']) ? '普通会员' : $item['rank_name'];
                $item['mobile'] = PhxCrypt::phxDecrypt($item['mobile']);
            }
        }
        return $data;
    }


}