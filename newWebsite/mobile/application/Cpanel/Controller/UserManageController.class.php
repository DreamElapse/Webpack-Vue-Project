<?php
/**
 * ====================================
 * 会员管理
 * ====================================
 * Author: 9006758
 * Date:
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: UserManageController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;
use Common\Extend\Base\Common;

class UserManageController extends CpanelController{
    protected $tableName = 'UserManage';

    //会员状态
    public function userState(){
        $data = D('UserManage')->returnState();
        $this->ajaxReturn($data);
    }

    /**
     * 停用会员
     */
    public function batchState(){
        $user_ids = I('request.user_id', 0, 'trim');
        $res = D('UserManage')->where(array('user_id'=>array('in', $user_ids)))->setField('state', 9);
        $this->success();
    }

    /**
     * 编辑 或 添加
     */
	public function form(){
		$user_id = I('request.user_id', 0, 'intval');
		$rankModel = D('Rank');
		$min_rank_name = $rankModel->getMinRand();
		$user_info = array('total_points'=>0,'pay_points'=>0,'points_left'=>0,'rank_name'=>$min_rank_name);
		
		//编辑
		if($user_id){
			$user_info = D('UserManage')->getUserInfo($user_id);
			if($user_info){
				$user_info['points_left'] = (int)$user_info['points_left'];
				$user_info['pay_points'] = (int)$user_info['pay_points'];
				$user_info['total_points'] = (int)$user_info['total_points'];
				if(!empty($user_info['mobile'])){
					$user_info['mobile'] = PhxCrypt::phxDecrypt($user_info['mobile']);
				}
				if($user_info['birthday']){
					$user_info['birthday'] = date('Y-m-d', $user_info['birthday']);
				}
				
				$rank_name = $rankModel->getAllRank($user_info['rank'], 'rank_name');
				$user_info['rank_name'] = !empty($rank_name['rank_name']) ? $rank_name['rank_name'] : $min_rank_name;
			}
		}
		
		$ranks = $rankModel->getAllRank(0, 'rank_name,min_points,max_points');
		$this->assign('ranks', $ranks);
		$this->assign('user_info', $user_info);
		$this->assign('user_id', $user_id);
		$this->display();
	}

    /**
     * 添加 / 编辑
     */
    public function save(){
        $userManageModel = D('UserManage');

        //可修改/添加项
        $email       = I('request.email', '', 'trim');      //邮箱
        $user_id     = I('request.user_id', 0, 'intval');   //会员id

        $user_info_data['sex'] = I('request.sex', 0, 'intval');       //性别
        $user_info_data['customer_id'] = I('request.customer_id', 0, 'intval');//客户ID
        $user_info_data['name'] = I('request.name', '', 'trim');       //姓名

        $user_data['user_name'] = I('request.user_name', '', 'trim');  //会员昵称
        $user_data['state'] = I('request.state', 0, 'intval');     //会员状态

        //邮箱验证
		if($email){
			if(!Common::isEmail($email)){
				$this->error('请输入正确的邮箱');
			}
			if($user_id){
				$email_exist = $userManageModel->where(array('email'=>$email, 'user_id'=>array('neq', $user_id)))->count();
			}else{
				$email_exist = $userManageModel->where(array('email'=>$email))->count();
			}
			if($email_exist){
				$this->error('邮箱已存在');
			}
		}
        
        $user_data['email'] = $email;
		$user_data['custom_id'] = $user_info_data['customer_id'];
		$userInfoModel = D('UserInfo');
        if($user_id){
            //更新会员信息
            $userManageModel->where(array('user_id'=>$user_id))->save($user_data);			
			$has_info = $userInfoModel->where(array('user_id'=>$user_id))->count();
            if($has_info){
                $userInfoModel->where(array('user_id'=>$user_id))->save($user_info_data);
            }else{
                $user_info_data['user_id'] = $user_id;
                $userInfoModel->add($user_info_data);
            }
			$this->success('更新成功');
        }else{
            //添加
            $password    = I('request.password', '', 'trim');   //密码
            $mobile      = I('request.mobile', '', 'trim');     //手机号
            $birthday    = I('request.birthday', '', 'trim');   //生日
            if(!Common::isMobile($mobile)){
                $this->error('请输入正确的手机号');
            }
            $mobile_phx = PhxCrypt::phxEncrypt($mobile);
            $mobile_exist = $userManageModel->where(array('mobile'=>$mobile_phx))->count();
            if($mobile_exist){
                $this->error('该手机已存在');
            }
            if(!$password){
                $this->error('密码不能为空');
            }
			if(!$birthday){
				$this->error('请填写生日');
			}

            $user_data['mobile'] = $mobile_phx;
            $user_data['password'] = md5(md5($password));
            $user_data['push_state'] = 0;
            $user_data['reg_time'] = Time::gmTime();
            $user_data['is_validated'] = 1;
            $user_data['paytype'] = '';

            //会员号
            list($year, $month, $day) = explode('-', $birthday);
            $user_num = $year . $month . $day . substr($mobile, -4);
            $user_num_exist = $userManageModel->where(array('user_num'=>array('like', "$user_num%")))->count();
            if($user_num_exist){
                $user_num = $user_num . ($user_num_exist - 1);
            }
            $user_data['user_num'] = $user_num;
			
            $new_user_id = $userManageModel->add($user_data);
			
            //会员信息表
            $user_info_data['birthday'] = strtotime($birthday);
            if($new_user_id){
                $user_info_data['user_id'] = $new_user_id;
                $user_info_data['default_address_id'] = 0;
                $user_info_data['push_state'] = 0;
                $user_info_data['add_time'] = Time::gmTime();
                $user_info_data['update_time'] = $user_info_data['add_time'];
                $new_info_id = $userInfoModel->add($user_info_data);

				//会员积分
                if($new_info_id){
                    $user_account_data['user_id'] = $new_user_id;
                    $user_account_data['customer_id'] = $user_info_data['customer_id'];
                    $user_account_data['site_id'] = 0;
                    $user_account_data['total_points'] = 0;
                    $user_account_data['pay_points'] = 0;
                    $user_account_data['expire_points'] = 0;
                    $user_account_data['points_left'] = 0;
                    $user_account_data['rank'] = 1;
                    $new_account_id = D('UserAccount')->add($user_account_data);

					//会员等级日志表
                    if($new_account_id){
                        $user_rank_log_data['site_id'] = '';
                        $user_rank_log_data['user_id'] = $new_user_id;
                        $user_rank_log_data['customer_id'] = 0;
                        $user_rank_log_data['old_rank'] = '';
                        $user_rank_log_data['new_rank'] = 1;
                        $user_rank_log_data['remark'] = '';
                        $user_rank_log_data['add_time'] = Time::gmTime();
                        $user_rank_log_data['update_time'] = $user_rank_log_data['add_time'];
                        $user_rank_log_data['mody_time'] = $user_rank_log_data['add_time'];
                        D('UserRankLog')->add($user_rank_log_data);
                    }
                }
            }
			$this->success('添加成功');
        }
    }

    /**
     * 导出
     * 根据搜索条件去导出数据，默认导出当天的数据
     */
    public function exportCsv(){
        $rank       = I('request.rank', 0, 'intval');
        $state      = I('request.state');
        $start_time = I('request.start_time');
        $end_time   = I('request.end_time');
        $last_time  = I('request.last_time');
        $type       = I('request.type');
        $keyword    = I('request.keyword');

        $params = array();
        if($rank){
            $params['rank'] = $rank;
        }
        if(is_numeric($state)){
            $params['state'] = intval($state);
        }
        if(!empty($start_time) && !empty($end_time)){
            $params['start_time'] = $start_time;
            $params['end_time'] = $end_time;
        }
        if(!empty($params['last_time'])){
            $params['last_time'] = $last_time;
        }
        if(!empty($keyword)){
            $params['keyword'] = $keyword;
            $params['type'] = $type;
        }
        if(empty($params)){
            //默认导出今天的数据
            $params['start_time'] = Time::localStrtotime('today');
            $params['end_time'] = Time::localStrtotime('tomorrow') - 1;
        }
        $UserManageModel = D('UserManage');
        $data = $UserManageModel->filter($params)->getAll();
        $str = "ID,邮箱,手机号,会员昵称,会员号,会员等级,注册时间,登陆时间,自动注册时间,状态,来源站点\n";
        $str = iconv('utf-8','gb2312',$str);

        if(!empty($data)){
            $randModel = D('Rank');
            foreach($data as $val){
                $user_id = $val['user_id'];
                $email     = iconv('utf-8','gb2312',$val['email']);
                $mobile    = iconv('utf-8','gb2312',$val['mobile']);
                $user_name = iconv('utf-8','gb2312',$val['user_name']);
                $user_num  = iconv('utf-8','gb2312',$val['user_num']);
                $rank_name = $val['rank'] > 0 ? $randModel->where(array('rank_id'=>$val['rank']))->getField('rank_name') : $randModel->getMinRand();
                $rank_name = iconv('utf-8','gb2312',$rank_name);
                $reg_time  = Time::localDate(C('DATE_FORMAT'), strtotime($val['reg_time']));
                $last_time = Time::localDate(C('DATE_FORMAT'), strtotime($val['last_time']));
                $auto_reg_time = Time::localDate(C('DATE_FORMAT'), strtotime($val['auto_reg_time']));
                $state     = iconv('utf-8','gb2312',$UserManageModel->stateText($val['state']));
                $source    = $val['source'];

                $str .= "$user_id,$email,$mobile,$user_name,$user_num,$rank_name,$reg_time,$last_time,$auto_reg_time,$state,$source\n";
            }
        }
//        echo $str;exit;
        $filename = date('Ymd').'.csv'; //设置文件名
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;
    }
}