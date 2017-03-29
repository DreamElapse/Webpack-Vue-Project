<?php
/**
 * ====================================
 * 后台公共类
 * ====================================
 * Author: 9004396
 * Date: 2017-01-10 19:45
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: CpanelController.class.php
 * ====================================
 */
namespace Common\Controller;

use Common\Extend\Base\Common;

class CpanelController extends PublicController {
    protected $dbModel;
    protected $tableName;
    protected $template;
    protected $user;

    public function __construct() {
        parent::__construct();
        $this->tableName and ($this->dbModel = D(ucfirst($this->tableName)));
        if($user_id = login('user_id')) {
            if(false == $this->access()) $this->error(L('NOT_ACCESS'));
            $this->assign('user', $this->user = login());
        }else {
            $jumpUrl = U('passport/index');
            CONTROLLER_NAME == 'Index' ? redirect($jumpUrl) : $this->error(L('LOGIN_AGAIN'), $jumpUrl);
        }
//        if(!isLoginOnly()){
//            $this->error("帐号已在其他设备登录(ip:".login('now_login_ip').")，您已被迫下线！", U('passport/logout'));
//        }
        $online = totalOnline();
        $this->onLinkTotal = $online['total'];
        $this->account = array(array('real_name' => login('real_name')));
        if(!empty($online['account'])){
            $account = D('admin')->where(array('session_key' => array('IN',$online['account'])))->field('real_name')->select();
            $this->account = array_merge($this->account,$account);
        }
    }

    /**
     * 验证操作权限
     * 设计思路:
     * 1、每个控制器中都可以设置不需要验证的操作;
     * 2、带get的方法不需要验证
     * 3、如果当前菜单的ID在管理员所拥有的菜单ID中则验证通过;
     * @return bool
     */
    final protected function access($action = ACTION_NAME) {
        //如果是超级管理员不需要验证
        if(login('is_open')) return true;

        $action = strtolower($action);
        $controller = strtolower(CONTROLLER_NAME);
        $module = strtolower(MODULE_NAME);

        //如果在允许访问列表内，不需要验证权限
        if(!empty($this->allowAction) && ($this->allowAction == '*' || in_array($action, array_map_recursive('strtolower', $this->allowAction)))) return true;

        //如果未分配菜单，则不允许访问
        if(is_null(login('menu'))) return false;

        //验证当前操作是否有权限
        return power(array(
            join('-', array($module, $controller, $action)),
            join('-', array($module, $controller, '*'))
        ));
    }

    /**
     * 查列表
     */
    public function index() {
        if(IS_AJAX) {
            $params = I('request.');
            //先判断Model层是否存在
            if(method_exists($this->dbModel, 'grid')) {
                if(method_exists($this->dbModel, 'filter')){
                    $this->dbModel->filter($params);
                }

                $data = $this->dbModel->grid($params);
                if(method_exists($this->dbModel, 'format')) {
                    $data = $this->dbModel->format($data);
                }
            }
            //默认输出树结构
            else {
                if($params) {
                    $fields = $this->dbModel->getDbFields();
                    $where = array();
                    foreach($params as $key => $val) {
                        if(in_array($key, $fields)) {
                            if(is_numeric($val)) {
                                $where[$key] = $val;
                            }else{
                                $where[$key] = array('like', "%$val%");
                            }
                        }
                    }
                    $this->dbModel->where($where);
                }
                $data = $this->dbModel->select();
                formatTime($data);
                Common::tree($data, $params['selected'], $params['type']);
            }
            $this->ajaxReturn($data);
            exit;
        }
        $this->display($this->template);
    }

    /**
     * 添加或更改记录
     */
    public function save() {
        $params = I('post.');
        if(method_exists($this, '_before_save')) {
            $params = $this->_before_save($params);
        }
        ($data = $this->dbModel->create($params)) or $this->error($this->dbModel->getError(), '', true);
        $pk = $this->dbModel->getPk();
        $insert = false;
        //调用保存前需要处理的方法
        if($params[$pk]) {
            $result = $this->dbModel->save();
//            $result = true;
        }else {
            $result = $this->dbModel->add();
            $params[$pk] = $this->dbModel->getLastInsID();
            $insert = true;
        }
        if($result !== false) {
            //调用保存后需要处理的方法
            if(method_exists($this, '_after_save')) {
                $params['is_insert'] = $insert;
                $this->_after_save($params);
            }
            $msg = L('SAVE') . L('SUCCESS');
            if(isset($_GET['saveBt_true']) && $insert == true){
                $msg = array('msg'=>L('SAVE') . L('SUCCESS'),'id_name'=>$pk, 'id_value'=>$params[$pk]);
            }
            $this->success($msg, '', true);
        }else {
            $this->error(L('SAVE') . L('ERROR'), '', true);
        }
    }

    /**
     * 删除记录
     */
    public function delete() {
        $itemId = I('request.item_id');
        $itemId or $this->error(L('item_lost'));
        $where = array();
        $pk = $this->dbModel->getPk();
        if(strpos($itemId, ',') !== false) {
            $where[$pk] = array('IN', $itemId);
        }else {
            $where[$pk] = $itemId;
        }
        if($this->dbModel->where($where)->delete()) {
            if(method_exists($this, '_after_delete')) {
                $this->_after_delete($itemId);
            }
            $this->success(L('DELETE') . L('SUCCESS'));
        }else {
            $this->error(L('DELETE') . L('ERROR'));
        }
    }
	
	/*
	*	成功返回结果
	*	@Author 9009123 (Lemonice)
	*	@param  anything $data  返回的数据
	*	@return exit && JSON
	*/
	protected function success($message='',$jumpUrl='',$ajax=false){
		parent::success($message, $jumpUrl, $ajax);
		exit;
	}
	
	/*
	*	错误返回结果
	*	@Author 9009123 (Lemonice)
	*	@param  anything $data  错误信息
	*	@return exit && JSON
	*/
	protected function error($message='',$jumpUrl='',$ajax=false){
		parent::error($message, $jumpUrl, $ajax);
		exit;
	}
}