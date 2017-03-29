<?php
/**
 * Created by PhpStorm.
 * User: 9009078
 * Date: 2017/2/3
 * Time: 15:53
 */
namespace Home\Controller;
use Common\Controller\InitController;

class SignInController extends InitController
{
    private $dbModel = NULL;  //储存地址数据表对象

    public function __construct(){
        parent::__construct();
        $this->dbModel = D('UserSigninLog');
        $this->bindUserModel = D('BindUser');
        $this->userSigninLogModel = D('UserSigninLog');
    }

    public function info(){
        $bind_id = I('request.bind_id',0,'intval');
        if($bind_id <= 0){
            $this->error('用户不存在');
        }
        $row_bind_user = $this->bindUserModel->info($bind_id);
        $row_user_signin_log = $this->userSigninLogModel->getSigninInfo($bind_id);
        $data['bind_id'] = $bind_id;
        $data['nickname'] = $row_bind_user['nickname'];
        $data['headimgurl'] = $row_bind_user['headimgurl'];
        $data['points_left'] = $row_bind_user['points_left'];
        $data['days'] = $row_user_signin_log['days'];
        $data['is_sign'] = $row_user_signin_log['is_sign'];
        $data['last_signin_time'] = $row_user_signin_log['last_signin_time'];
        $this->success($data);
    }
}