<?php
/**
 * ====================================
 * 这里是说明
 * ====================================
 * Author: Tommy
 * Date: 2015 2015/4/12 21:03
 * ====================================
 * File: AdminModel.class.php
 * ====================================
 */

namespace Cpanel\Model;
use Common\Model\CpanelModel;

class AdminModel extends CpanelModel {
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
        array('password', 'password', self::MODEL_BOTH, 'function'),
    );

    protected $_validate = array(
        array('user_name','require','{%USER_NAME_REQUIRE}'),
        array('real_name','require','{%REAL_NAME_REQUIRE}'),
        array('user_name','','{%USER_NAME_EXISTS}', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
        array('confirm_password','password','{%CONFIRM_PASSWORD_ERROR}', self::EXISTS_VALIDATE, 'confirm'), // 验证确认密码是否和密码一致
        array('password', '6,32', '{%PASSWORD_ERROR}', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
    );

    public function modifyPassword($params) {
        $_validate = array(
            array('confirm_password','password','{%CONFIRM_PASSWORD_ERROR}', self::MUST_VALIDATE, 'confirm'),
            array('password', '6,32', '{%PASSWORD_ERROR}', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        );
        $this->validate($_validate);
        if($this->create($params)) {
            return $this->save();
        }
        return $this->getError();
    }

    /**
     * 获取列表
     * @param array $params
     * @return mixed
     */
    public function filter($params) {
        $where = array();
        if($params['keyword']){
            $where['a.user_name'] = array('LIKE', "%{$params['keyword']}%");
            $where['a.real_name'] = array('LIKE', "%{$params['keyword']}%");
            $where['_logic'] = 'OR';
        }

        if($params['role_id']) {
            $where['a.role_id'] = intval($params['role_id']);
        }

        if($params['group_id']) {
            $where['a.group_id'] = intval($params['group_id']);
        }

        $this->alias(' AS a')
            ->join("__GROUP__ AS g ON a.group_id = g.id", 'left')
            ->join("__ROLE__ AS r ON a.role_id = r.id", 'left')
            ->field('a.user_id, a.user_name, a.real_name, a.sex, a.locked, a.group_id, a.role_id, a.create_time, a.update_time, a.is_open, g.text as group_name, r.text as role_name');

        return $this->where($where);
    }
	
	/**
     * 记录日志列表
     * @param $message
     * @return bool
     */
    public function logList($user_id = '',$keyword = '',$page = 1,$pageSize = 10) {
        $model = M('AdminLog');
		
		$where = array();
		if($user_id != ''){
			$where[] = "user_id IN($user_id)";
		}
		if($keyword != ''){
			$where[] = "note like '%$keyword%'";
		}
		
		
        //统计总记录数
        $options = $this->options;
        $total = $model->where(!empty($where) ? implode(' and ',$where) : '')->count();
		
        //排序并获取分页记录
        $model->options = $options;
        $model->limit($pageSize)->page($page);
        $list = $model->where(!empty($where) ? implode(' and ',$where) : '')->order('create_time desc')->select();
        
		if(!empty($list)){
			foreach($list as $key=>$value){
				$admin = $this->field('user_name,real_name,sex')->where("user_id = '$value[user_id]'")->find();
				$value['user_name'] = isset($admin['user_name']) ? $admin['user_name'] : '';
				$value['real_name'] = isset($admin['real_name']) ? $admin['real_name'] : '';
				$value['sex'] = isset($admin['sex']) ? $admin['sex'] : 0;
				
				$value['create_date'] = date('Y-m-d H:i:s',$value['create_time']);
				
				$list[$key] = $value;
			}
		}
        return array('total' => (int)$total, 'rows' => (empty($list) ? false : $list), 'pagecount' => ceil($total / $pageSize));
    }

    /**
     * 插入记录日志
     * @param $message
     * @return bool
     */
    public function addLog($message, $user_id = USER_ID) {
        $model = M('AdminLog');
        $model->data(array(
            'user_id' => $user_id,
            'module_name' => MODULE_NAME,
            'controller_name' => CONTROLLER_NAME,
            'action_name' => ACTION_NAME,
            'note' => $message,
            'create_time' => time()
        ));
        $result = $model->add();
        return $result ? true : false;
    }
	/*
	*	获取规则权限
	*	@param $role_id  规则数据表ID
	*	@parem $custom_menu string  自定义权限（用于管理员编译自定义权限） Add By Lemonice
	*	@return array
	*/
    public function getMenu($role_id,$custom_menu = '') {
        //读取管理员权限菜单
        $menu_id = M('Role')->where("id = '$role_id'")->getField('menu_id');
				
		if(!empty($custom_menu))
			$menu_id = !empty($menu_id) ? $menu_id.','.$custom_menu : $custom_menu;
		
		if(empty($menu_id)) return null;
		
        $data = M('Menu')
            ->field('id, concat_ws("-", module, controller, method) as power, module, controller, other_method')
            ->where("id IN ({$menu_id})")
            ->select();
        $menu = array();
		
        foreach($data as $row){
            $menu[$row['id']][] = strtolower($row['power']);
            if($row['other_method']) {
                $otherMethod = explode(',', $row['other_method']);
                foreach($otherMethod as $method) {
                    $menu[$row['id']][] =strtolower(join('-',array($row['module'], $row['controller'], $method)));
                }
            }
        }
        return $menu;
    }

    /**
     * 获取个人信息
     * @param $user_id
     * @return mixed
     */
    public function getRow($user_id) {
        $this->alias(' AS a');
        $this->join('__ROLE__ AS r ON a.role_id = r.id', 'LEFT');
        $this->join('__GROUP__ AS g ON a.group_id = g.id', 'LEFT');
        $this->field('a.*, r.text as role_name, g.text as group_name');
        $this->where(array(
            'a.user_id' => $user_id
        ));
        return $this->find();
    }
}