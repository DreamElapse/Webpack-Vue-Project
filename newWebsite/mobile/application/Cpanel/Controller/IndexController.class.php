<?php
/**
 * 管理平台首页
 * User: 91336
 * Date: 2015/3/24
 * Time: 10:23
 */
namespace Cpanel\Controller;

use Common\Controller\CpanelController;
use Common\Extend\Base\Tree;
use Common\Extend\SystemInfo;
use Cpanel\Model\MenuModel;

class IndexController extends CpanelController
{

    protected $allowAction = '*';


    public function index()
    {
        layout(false);
        $this->getSystem();
        $userInfo = D('admin')->getRow(login('user_id'));
        formatTime($userInfo);
        $this->userInfo = $userInfo;
        $this->display();
    }

    public function phpInfo(){
        phpinfo();
    }

    public function menu()
    {
        $where = array('display' => 1);
        if (!login('is_open')) {
            if (login('menu')) {
                $where['id'] = array('IN', array_keys(login('menu')));
            } else {
                $where['id'] = 0;
            }
        }
        $menuModel = new MenuModel();
        $menu_list = $menuModel->field('id,pid,text,module, controller,method,icon')->where($where)->select();
        if ($menu_list) {
            foreach ($menu_list as $key => $row) {
                $action = empty($row['method']) || $row['method'] == '*' ? C('DEFAULT_ACTION') : $row['method'];
                $href = array($row['controller'], $action);
                if (!empty($row['module'])) {
                    array_unshift($href, $row['module']);
                }
                $row['href'] = U(join('/', $href));
                $menu_list[$key] = $row;
            }
            $menu_list = Tree::treeArray($menu_list);
        }
        $this->ajaxReturn($menu_list);
    }


    public function getSystem()
    {
        $sysInfo = SystemInfo::getInfo();
        $os = explode(' ', php_uname());
        $net_state = array(); //网络使用情况

        $strs = @file("/proc/net/dev");
        if ($sysInfo['sysReShow'] == 'show' && false !== $strs) {
            for ($i = 2; $i < count($strs); $i++) {
                preg_match_all("/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info);
                $net_state[]= "{$info[1][0]} : 已接收 : <font color=\"#CC0000\"><span id=\"NetInput{$i}\">" . $sysInfo['NetInput' . $i] . "</span></font> GB &nbsp;&nbsp;&nbsp;&nbsp;已发送 : <font color=\"#CC0000\"><span id=\"NetOut{$i}\">" . $sysInfo['NetOut' . $i] . "</span></font> GB <br />";
            }
        }
        $this->sysInfo = $sysInfo;
        $this->os = $os;
        $this->netState = $net_state;
    }
}