<?php
/**
 * ====================================
 * 管理员操作
 * ====================================
 * Author: Tommy
 * Date: 2015 2015/5/10 15:58
 * ====================================
 * File: AdminController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Extend\Base\Tree;
use Common\Controller\CpanelController;
use Cpanel\Model\MenuModel;

class AdminController extends CpanelController {
    protected $tableName = 'Admin';
	protected $RoleModel = 'Role';
	protected $SitePowerModel = 'SitePower';
	
	
    protected $allowAction = array('information');

    public function information() {
        if(IS_POST) {
            $params = I('post.', '', 'trim');
            if(!empty($params['password'])) {
                $params['user_id'] = $this->user['user_id'];
                $result = $this->dbModel->modifyPassword($params);
                if($result) {
                    $this->success(L('SAVE') . L('SUCCESS'));
                }else {
                    $this->error($result);
                }
            }
            exit;
        }
        $info = $this->dbModel->getRow($this->user['user_id']);
        $this->assign('info', $info);
        $this->display();
    }
	
	//查看日记
	public function logList(){
		if(IS_POST) {
            $user_id = trim(I('user_id'));
			$keyword = trim(I('keyword'));
			
			$page = intval(I('page'));
			$pageSize = intval(I('rows'));
				
			$list = $this->dbModel->logList($user_id, $keyword, $page, $pageSize);
			
            $this->ajaxReturn($list);
            exit;
        }
        $this->display();
	}
	
	//分配功能权限
	public function power()
	{
		$user_id = intval(I('user_id'));
		$user = $this->dbModel->getRow($user_id);
		$RoleModel = D($this->RoleModel);
		$role = $RoleModel->getOne("id = '$user[role_id]'");
		$menuModel = new MenuModel();
        $menu = $menuModel->field('id, pid, text')->order("pid ASC, orderby DESC")->select();
        $menu = Tree::treeArray($menu);
		
		$role_menu_id = $role['menu_id'];
		$user_menu_id = $user['menu_id'];
		
		$this->assign('user_id', $user_id);
		$this->assign('role_menu_id', $role_menu_id);
		$this->assign('user_menu_id', $user_menu_id);
        $this->assign('menu', json_encode($menu));
		
        $this->display();
	}
	
    //编辑管理员状态
    public function lock() {
        $params = I('post.');
        if($params['item_id']) {
            $where = array(
                'user_id' => array('in', $params['item_id'])
            );
            $result = $this->dbModel->where($where)->setField('locked', (int)$params['locked']);
            if($result) {
                $this->success(L('EDIT').L('SUCCESS'));
            }else
                $this->error(L('EDIT').L('ERROR'));
        }
        $this->error(L('SELECT_NODE') . L('ADMIN'));
    }
}