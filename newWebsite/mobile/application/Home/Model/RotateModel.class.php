<?php
/**
 * ====================================
 * 抽奖模型
 * ====================================
 * Author:
 * Date:
 * ====================================
 * File:RotateModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\UserCenterModel;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;

class RotateModel extends UserCenterModel{
    protected $tableName = 'wx_rotates';

    /**
     * 根据时间自动获取转盘活动
     * @return mixed
     */
	public function getRotateInfo($field = ''){
		$now_time = time();
        $where['is_checked'] = 1;
        $where['is_locked'] = 1;
        $where['start_time'] = array('elt', $now_time);
        $where['end_time'] = array('gt', $now_time);
		if(!$field){
			$field = '*';
		}
		$rotate_info = $this->where($where)
						->field($field)
						->find();
		return $rotate_info;
	} 
	
	/*
	 * 获取奖品
	 * @param $rotate_id int 活动id
	 * @param $prize_id int 奖品id
	 * @param $field string 字段
	 */
	public function getPrizeInfo($rotate_id, $prize_id=0, $field=''){
		$where['rotate_id'] = $rotate_id;
		$field = $field ? $field : '*';
		if($prize_id){
			$where['id'] = $prize_id;
			$prizes = $this->table('wx_rotates_prize')->where($where)->field($field)->find();
		}else{
			$prizes = $this->table('wx_rotates_prize')->where($where)->field($field)->select();
		}
		return $prizes;
	}
	
    /**
     * 获取转盘活动数据
     * @param $rotate_id    转盘活动id
     * @param string $fields    读取的字段数据
     * @return mixed
     */
    public function rotateInfo($rotate_id, $fields=''){
        $where['id'] = $rotate_id;
        $where['is_checked'] = 1;
        $where['is_locked'] = 1;
        $time = time();
        $where['start_time'] = array('elt', $time);
        $where['end_time'] = array('gt', $time);
        if(!$fields){
            $fields = 'id,act_name,act_title,des_info,lottery_num,shipping_free';
        }
        $rotate_info = $this->where($where)->field($fields)->find();
        // if(!$rotate_info){
            // return false;
        // }
        // if($get_prize){
            // $prizes = $this->getPrize($rotate_info['id'], 0, 'id,grade,prize_name,goods_id');
            // if(!$prizes){
                // return false;
            // }
            // $rotate_info['prize_cfg'] = $prizes;
        // }

        return $rotate_info;
    }

    /**
     * 获取转盘奖品
     * @param $rotate_id 转盘活动id
     * @param int $prize_id 奖品id
     * @param string $fields    字段
     * @return mixed
     */
    /* public function getPrize($rotate_id, $prize_id=0, $fields=''){
        $where['rotate_id'] = $rotate_id;
        if(!$fields){
            $fields = '*';
        }
        if($prize_id){
            $where['id'] = $prize_id;
            $prizes = $this->table('wx_rotates_prize')->where($where)->field($fields)->find();
        }else{
            $prizes = $this->table('wx_rotates_prize')->where($where)->field($fields)->select();
        }
        return $prizes;
    } */

    /**
     * 抽奖记录
     * @param $rotate_id    转盘活动id
     * @param string $open_id   微信用户openID
     * @param bool $goods_id    ture:读取中实物奖，false：全部
     * @param string $fields
     * @param int $limit
     * @return mixed
     */
    public function rotateLog($rotate_id, $open_id='', $goods_id=false, $fields='', $limit=10){
        $where['rotate_id'] = $rotate_id;
        if($open_id){
            $where['open_id'] = $open_id;
        }
        if($goods_id){
            $where['goods_id'] = array('gt', 0);
        }
		$fields = $fields ? $fields : '*';

        $logs = $this->table('wx_rotates_log')
            ->where($where)
            ->field($fields)
            ->order('add_time desc')
            ->limit($limit)
            ->select();
        if($logs){
            foreach($logs as &$val){
                $val['add_time'] = date('Y-m-d H:i:s', $val['add_time']);
            }
        }

        return $logs;
    }

    /**
     * 返回用户当天抽奖次数
     * @param $rotate_id
     * @param $open_id
     * @return mixed
     */
    public function logTimes($rotate_id, $open_id){
        $today_start = strtotime('today');
        $today_end = strtotime('tomorrow') - 1;
        $where['rotate_id'] = $rotate_id;
        $where['open_id'] = $open_id;
        $where['add_time'] = array('between',array($today_start,$today_end));
        return $this->table('wx_rotates_log')->where($where)->count();
    }

    /**
     * 判断用户是否已经中奖
     * @param $rotate_id
     * @param $open_id
     * @return mixed
     */
    public function isWinner($rotate_id, $open_id){
        $where['rotate_id'] = $rotate_id;
        $where['open_id'] = $open_id;
        $where['goods_id'] = array('gt', 0);
        return $this->table('wx_rotates_log')->where($where)->count();
    }

    /**
     * 判断中奖纪录的奖品的等级
     * @param $rotate_id
     * @param $goods_id
     * @param string $filed
     * @return mixed
     */
    public function prizeGrade($rotate_id, $goods_id, $filed='*'){
        $where['rotate_id'] = $rotate_id;
        if(is_array($goods_id)){
            $where['goods_id'] = array('in', $goods_id);
            $res = $this->table('wx_rotates_prize')->where($where)->field($filed)->select();
        }else{
            $where['goods_id'] = $goods_id;
            $res = $this->table('wx_rotates_prize')->where($where)->field($filed)->find();
        }
        return $res;
    }

}