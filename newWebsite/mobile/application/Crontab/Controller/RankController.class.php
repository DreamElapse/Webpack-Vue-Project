<?php
/**
 * ====================================
 * 会员等级计划任务
 * ====================================
 * Author: 9004396
 * Date: 2017-03-09 17:04
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: RankController.class.php
 * ====================================
 */
namespace Crontab\Controller;
use Common\Controller\CrontabController;
use Common\Extend\Time;

class RankController extends CrontabController{

    private $userRankModel;
    private $userAccountModel;
    private $userRankLogModel;
    private $accountCorrectModel;

    public function __construct()
    {
        parent::__construct();
        $this->userRankModel = D('Home/UserRank');
        $this->userAccountModel = D('Home/UserAccount');
        $this->userRankLogModel = D('Home/UserRankLog');
        $this->accountCorrectModel = D('Home/AccountCorrect');
    }

    public function correctRank(){
        $logid = $this->insertLog(__CLASS__ . '-' . __FUNCTION__, '开始执行');
        $userAccount = $this->userAccountModel
            ->alias(' AS account')
            ->join('__ACCOUNT_CORRECT__ as correct ON account.user_id = correct.user_id','LEFT')
            ->limit(1000)
            ->field('account.*,correct.operate_time')
            ->order('correct.operate_time ASC , account.id ASC')
            ->select();
        if(empty($userAccount)){
            $this->updateLog($logid,'处理0条记录');
        }
        $num = 0;
        $logNum = 0;
        foreach ($userAccount as $account){
            if(!empty($account['operate_time'])){
                $this->accountCorrectModel->where(array('user_id' => $account['user_id']))->save(array('operate_time' => Time::gmTime()));
            }else{
                $this->accountCorrectModel->add(array('user_id' => $account['user_id'], 'operate_time' => Time::gmTime()));
            }
            $totalPoints = $account['total_points'];
            $level = $this->getRank($totalPoints); //通过总积分获取当前等级
            if($level == $account['rank']){ //等级相等，则不执行
                continue;
            }
            //等级差异,调整等级
            $data = array(
                'rank' => $level
            );
            $result = $this->userAccountModel->where(array('id' => $account['id']))->save($data);
            if($result != false){ //更新成功处理日志
                $num++;
                $RankLog = array(
                    'state'     => '-4',
                    'user_id'   => $account['user_id'],
                    'old_rank'  => $account['rank'],
                    'new_rank'  => $level,
                    'add_time' => Time::gmTime()
                );
                $Lret = $this->userRankLogModel->add($RankLog);
                if(!empty($Lret)){
                    $logNum++;
                }
            }
        }
        $this->updateLog($logid,'处理'.$num."条记录，新增".$logNum."条日志");
    }

    private function getRank($point = 0){
        $rank = $this->userRankModel->field('rank_id,min_points')->order('min_points desc')->select();
        $level = 0;
        if(!empty($rank)){
            foreach ($rank as $item){
                if($point >= $item['min_points']){
                    $level = $item['rank_id'];
                    break;
                }
            }
        }
        return $level;
    }
}