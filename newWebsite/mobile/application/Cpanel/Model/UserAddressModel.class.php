<?php
/**
 * ====================================
 * 微信会员模型
 * ====================================
 * Author: 9004396
 * Date: 2017-01-11 13:52
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: RankModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Extend\Base\Common;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;

class UserAddressModel extends CpanelUserCenterModel
{
    protected $tableName = 'user_address';

    //会员状态
    private $_pushState = array(
        0 => '未推送',
        1 => '已推送',
        2 => '数据有更新未推送',
        3 => '禁止登陆',
    );

    public function filter(&$params){

		$user_id = intval($params['user_id']);
		if($user_id){
			$this->where(array('user_id'=>$user_id));
		}
		$params['sort'] = !empty($params['sort']) ? trim($params['sort']) : 'address_id';
		$params['order'] = !empty($params['order']) ? trim($params['order']) : 'desc';
		$this->order($params['sort'].' '.$params['order']);
        return $this;
    }

    public function format($data){
		$regionModel = M('region', null, 'USER_CENTER');
        if(!empty($data['rows'])){
            foreach($data['rows'] as &$val){
                $val['push_state'] = $val['push_state'] == 1 ? $this->_pushState[$val['push_state']] : '<b style="color:red;">'.$this->_pushState[$val['push_state']].'</b>';
				$val['add_time'] = Time::localDate(C('DATE_FORMAT'), strtotime($val['add_time']));
				$val['update_time'] = Time::localDate(C('DATE_FORMAT'), strtotime($val['update_time']));
				$val['province'] = $regionModel->where(array('region_id'=>$val['province']))->getField('region_name');
				$val['city'] = $regionModel->where(array('region_id'=>$val['city']))->getField('region_name');
				$val['district'] = $regionModel->where(array('region_id'=>$val['district']))->getField('region_name');
				$val['town'] = !empty($val['town']) ? $regionModel->where(array('region_id'=>$val['town']))->getField('region_name') : '';
				$val['address'] = $val['province'].'&nbsp;&nbsp;'.$val['city'].'&nbsp;&nbsp;'.$val['district'].'&nbsp;&nbsp;'.$val['town'].'&nbsp;&nbsp;'.$val['address'];
				$val['zipcode'] = empty($val['zipcode']) ? '暂无记录' : $val['zipcode'];
            }
        }
        return $data;
    }

}
