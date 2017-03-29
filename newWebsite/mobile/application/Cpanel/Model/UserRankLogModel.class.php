<?php
/**
 * ====================================
 * 会员等级日志
 * ====================================
 * Author: 9004396
 * Date: 2017-02-27 09:24
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: UserRankLogModel.class.php
 * ====================================
 */
namespace Cpanel\Model;

use Common\Extend\Time;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\PhxCrypt;

class UserRankLogModel extends CpanelUserCenterModel
{

    public function filter($params)
    {
        $where = array();

        if(!empty($params['id'])){
            $where['log.integralrecord_id'] = $params['id'];
        }

        if (!empty($params['state'])) {
            if ($params['state'] > 0) {
                $where['log.state'] = ($params['state'] - 1);
            } else {
                $where['log.state'] = $params['state'];
            }
        }

        if (!empty($params['type'])) {
            $keyword = $params['keyword'];
            switch ($params['type']) {
                case 1:
                    $where['account.user_id'] = intval($keyword);
                    break;
                case 2:
                    $where['user.email'] = array('LIKE', "%{$keyword}%");
                    break;
                case 3:
                    $where['user.mobile'] = PhxCrypt::phxEncrypt($keyword);
                    break;
                case 4:
                    $where['user.user_num'] = $keyword;
                    break;
                case 6:
                    $where['user.user_name'] = $keyword;
            }
        }

        if (!empty($params['rank'])) {
            if ($params['rank'] == '1') {
                $where['account.rank'] = array(array('eq',$params['rank']),array('eq',0), 'or');
            } else {
                $where['account.rank'] = $params['rank'];
            }
        }

        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $time_start = Time::localStrtotime($params['start_time']);
            $time_ent = Time::localStrtotime($params['end_time']);
            $where['log.add_time'] = array(array('EGT', $time_start), array('ELT', $time_ent));
        }
        if($params['sort'] && $params['order']){
            $order = 'log.'.$params['sort'].' '.$params['order'];
        }else{
            $order = 'log.id DESC';
        }

        $this->alias('log')
            ->join("__USERS__ as user on log.user_id = user.user_id",'left')
            ->join("__USER_ACCOUNT__ as account on account.user_id = log.user_id",'left')
            ->field('log.id,log.site_id,log.state,log.user_id,log.customer_id,log.old_rank,log.new_rank,log.remark,log.add_time,log.update_time,log.mody_time,user.user_num,user.mobile,user.email,user.user_name,account.rank');
        $this->order($order);
        return $this->where($where);
    }

    public function format($data){
        $rank = $this->getRank();
        if (!empty($data)) {
            foreach ($data['rows'] as &$item) {
                $item['mobile'] = PhxCrypt::phxDecrypt($item['mobile']);
                $item['new_rank'] = empty($rank[$item['new_rank']]) ? $rank[0] : $rank[$item['new_rank']];
                $item['old_rank'] = empty($rank[$item['old_rank']]) ? $rank[0] : $rank[$item['old_rank']];
                $item['rank'] = empty($item['rank']) ? $rank[0] :$rank[$item['rank']];

                switch($item['state']){
                    case -1:
                        $item['state'] = '<b style=color:red;>删除（订单退货）</b>';
                        break;
                    case -2:
                        $item['state'] = '<b style=color:green;>积分过期</b>';
                        break;
                    case -3:
                        $item['state'] = '<b style=color:red;>消费</b>';
                        break;
                    case -4:
                        $item['state'] = '<b style=color:red;>等级规则变动（更新等级）</b>';
                        break;
                    default:
                        $item['state'] = '<b style=color:green;>正常</b>';
                }
            }
        }
        return $data;
    }

    private function getRank(){
        $data = array($this->getMinRandName());
        $rankModel = D('Rank');
        $ret  = $rankModel->getRank();
        if(!empty($ret) && is_array($ret)){
            foreach ($ret as $item){
                $data[$item['rank_id']] = $item['rank_name'];
            }
        }
        return $data;
    }

    protected function getMinRandName(){
        return D('Rank')->getMinRand();
    }

}