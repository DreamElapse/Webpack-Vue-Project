<?php
/**
 * ====================================
 * 登录管理
 * ====================================
 * Author: Tommy
 * Date: 2015 2015/4/5 21:21
 * ====================================
 * File: PassportController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\PublicController;
use Cpanel\Model\AdminModel;
use Think\Verify;

class PassportController extends PublicController {
    //登录首页
    public function index() {
        layout(false);
        $this->display('Index:login');
    }

    public function login() {
        $params = I('post.');
        $params['login_name'] or $this->error(L('login_name_lost'));
        $params['login_password'] or $this->error(L('login_password_lost'));

        if(C('verify_close')){
            $verify = new Verify();
            if(!$verify->check($params['verify_code'])) {

            }
                $this->error(L('VERIFY_CODE_ERROR'));
        }

        $adminModel = new AdminModel();
        $where['user_name'] = strip_tags($params['login_name']);
        $data = $adminModel->where($where)->find();

        //管理员不存在
        $data or $this->error(L('ACCOUNT_NOT_EXISTS'));

        //密码不正确
        $data['password'] == password($params['login_password']) or $this->error(L('PASSWORD_ERROR'));
        unset($data['password']);
        //已被锁定
        !$data['locked'] or $this->error(L('not_allow_login'));

        $data['menu'] = $adminModel->getMenu($data['role_id'],$data['menu_id']);//读取菜单权限
		
        $data['login_time'] = time();
        session('login_cookie', serialize($data));

        $update = array(
//            'session_key' => md5(session_id() . $data['login_time']),
            'session_key' => session_id(),
            'last_login_time' => $data['now_login_time'],
            'now_login_time' => $data['login_time'],
            'last_login_ip' => $data['now_login_ip'],
            'now_login_ip' => get_client_ip()
        );
        //记录登录验证加密串
        F('session_key_' . $data['user_id'], $update['session_key']);

        //更新登录信息
        $adminModel->data($update)->where("user_id = %d", intval($data['user_id']))->save();
        $adminModel->addLog(L('have_login'), $data['user_id']);
        totalOnline();
        $this->success();
    }

    /**
     * 退出登录
     */
    public function logout() {
        session('login_cookie', null);
        totalOnline(true);
        session_destroy();
        redirect(U('index/index'));
    }

    public function verify() {
        $obj = new Verify();
        $obj->entry();
    }
    
    /**
     * 测试分享
     */
    public function fenxiang() {
        $this->display('Index:fenxiang');
    }
}