<?php
/**
 * ====================================
 * 角色管理
 * ====================================
 * Author: Hugo
 * Date: 14-5-20 下午9:58
 * ====================================
 * File: RoleController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Extend\Base\Tree;
use Common\Controller\CpanelController;
use Cpanel\Model\MenuModel;

class RoleController extends CpanelController {
    protected $tableName = 'Role';
	protected $SitePowerModel = 'SitePower';

    public function power() {
        if(IS_AJAX && IS_POST) {
            $params = I('post.');
            $result = $this->dbModel->save(array('menu_id' => $params['menu_list'], 'id' => (int)$params['role_id']));
            $result ? $this->success(L('SAVE') . L('SUCCESS')) : $this->error(L('SAVE').L('ERROR'));
            exit;
        }
        $role_id = intval(I('role_id'));
        $this->assign('role', $this->dbModel->find($role_id));
        $menuModel = new MenuModel();
        $menu = $menuModel->field('id, pid, text')->order("pid ASC, orderby DESC")->select();
        $menu = Tree::treeArray($menu);
        $this->assign('menu', json_encode($menu));
        $this->display();
    }
}