<?php
/**
 * ====================================
 * 会员管理模型
 * ====================================
 * Author: 9004396
 * Date:
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: UserManageModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
//use Common\Extend\Base\Common;
use Common\Model\CpanelUserCenterModel;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;

class UserManageModel extends CpanelUserCenterModel
{
    protected $tableName = 'users';

    //会员状态
    protected $_state = array(
        0 => '正常',
        1 => '自动注册会员',
        9 => '停用',
        -1 => '禁止登陆',
    );

    public function filter(&$params){

        //会员状态
        if(is_numeric($params['state'])){
            $where['user.state'] = $params['state'];
        }
        //会员等级
        if(!empty($params['rank'])){
            if ($params['rank'] == '1') {
                $where['account.rank'] = array(array('eq',$params['rank']),array('eq',0), 'or');
            } else {
                $where['account.rank'] = $params['rank'];
            }
        }
        //关键词搜索
        if(!empty($params['keyword'])){
            $keyword = trim($params['keyword']);
            switch ($params['type']) {
                case 1:
                    $where['user.user_id'] = intval($keyword);
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
                    $where['user.user_name'] = array('LIKE', "%{$keyword}%");
                default:
                    $where['_string'] = "(user.user_id='$keyword' or user.email like '%$keyword%' or user.mobile='" . PhxCrypt::phxEncrypt($keyword) . "' or user.user_num='$keyword' or user.user_name like '%$keyword%')";
            }
        }
        //上次登陆时间
        if(!empty($params['last_time'])){
            $last_time = Time::localStrtotime($params['last_time']);
            //$where['last_time'] = array(array('gt', $last_time), array('lt', $last_time), 'and');
			$where['last_time'] = array('gt', $last_time);
        }
        //注册时间(包括自动注册时间)
        if(!empty($params['start_time']) && $params['end_time']){
            $time_start = Time::localStrtotime(trim($params['start_time']));
            $time_ent = Time::localStrtotime(trim($params['end_time']));
            $where['_string'] = "((user.reg_time > '{$time_start}' AND user.reg_time < '{$time_ent}') or (user.auto_reg_time > '{$time_start}' AND user.auto_reg_time < '{$time_ent}'))";
        }
        
        if(!empty($params['sort']) && $params['sort']=='rank'){
            $params['sort'] = 'account.'.$params['sort'];
        }else{
            $params['sort'] = !empty($params['sort']) ? 'user.'.$params['sort'] : 'user.user_id';
        }
        $params['order'] = !empty($params['order']) ? $params['order'] : 'desc';

        $filds = 'user.user_id,user.auto_reg_time,user.email,user.mobile,user.user_num,user.user_name,';
        $filds .= 'user.state,user.reg_time,user.last_time,account.rank,info.source';
        $this->alias('user')
            ->join("left join __USER_ACCOUNT__ as account on account.user_id = user.user_id")
            ->join("left join __USER_INFO__ as info on info.user_id = user.user_id")
            ->field($filds)
            ->where($where)
            ->order($params['sort'].' '.$params['order']);

        return $this;
    }

    public function format($data){
        $user_rank = D('Rank')->field('rank_id,rank_name')->select();
        foreach($user_rank as $k=>$v){
            $arr[$v['rank_id']] = $v['rank_name'];
        }
        $arr[0] = D('Rank')->getMinRand();
        if(!empty($data['rows'])){
            foreach($data['rows'] as &$val){
                $val['id_num'] = $val['user_id'];
                $val['mobile'] = PhxCrypt::phxDecrypt($val['mobile']);
                $val['rank'] = $arr[(int)$val['rank']];
                if($val['state'] == -1 ||$val['state']==9){
                    $val['stateText'] = '<b style="color:red;">'.$this->_state[$val['state']].'</b>';
                }else{
                    $val['stateText'] = $this->_state[$val['state']];
                }
				$val['reg_time'] = Time::localDate('Y-m-d H:i:s', strtotime($val['reg_time']));
				$val['auto_reg_time'] = Time::localDate('Y-m-d H:i:s', strtotime($val['auto_reg_time']));
				$val['last_time'] = Time::localDate('Y-m-d H:i:s', strtotime($val['last_time']));
            }
        }
        return $data;
    }

    public function returnState(){
        $data = array(
            array(
                'id' => '请选择状态',
                'text' => '-- 请选择状态 --',
                'selected' => true,
            )
        );
        foreach($this->_state as $k=>$v){
            $res[] = array('id'=>$k, 'text'=>$v);
        }
        return array_merge($data,$res);
    }
	
	/**
	 * 获取会员信息
	 * 
	 * @param $user_id int 会员id
	 */
	public function getUserInfo($user_id){
		$where['user.user_id'] = $user_id;
		$field = 'user.email,user.mobile,user.user_num,user.user_name,user.password,';
		$field .= 'user.state,user.push_state,user.last_ip,user.email,user.mobile,';
		$field .= 'info.birthday,info.sex,info.customer_id,info.name,';
		$field .= 'info.default_address_id,info.push_state as push_state2,';
		$field .= 'account.total_points,account.pay_points,account.points_left,account.site_id,account.rank';
		
		$user_info = $this->alias('user')
						->join("left join __USER_INFO__ as info on user.user_id=info.user_id")
						->join("left join __USER_ACCOUNT__ as account on account.user_id=user.user_id")
						->where($where)
                        ->field($field)
						->find();
		return $user_info;
	}

    public function stateText($state){
        return $this->_state[$state];
    }

}
