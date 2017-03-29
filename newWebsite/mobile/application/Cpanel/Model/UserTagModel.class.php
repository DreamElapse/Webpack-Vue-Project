<?php
/**
 * 会员标签
 * Author: Lemonice  (9009123)
 * Email: chengciming@126.com
 * Date: 2017-01-19 15:36
 * File: UserTagModel.class.php
 */
namespace Cpanel\Model;
use Common\Extend\Base\Common;
use Common\Model\CpanelModel;

class UserTagModel extends CpanelModel {
	protected $tableName = 'user_tag';
	
	public function tree($params, $checkbox = false) {
		$where = array();
		$where[] = 'display = 1';
        $data = $this->field('tag_id as id,name as text')->where($where)->order('update_time desc,create_time desc')->getAll();
		if(!empty($data)){
			if($checkbox == true){
				if(!empty($data)){
					foreach($data as $key=>$value){
						$data[$key]['pid'] = 0;
					}
				}
				Common::tree($data, $params['selected'], $params['type']);
				
				if(isset($params['select_name']) && isset($data[0]['text'])){
					$data[0]['text'] = $params['select_name'];
				}
				if(isset($params['select_value']) && isset($data[0]['id'])){
					$data[0]['id'] = $params['select_value'];
				}
			}else{
				$list = array(
					array(
						'id'=>0,
						'text'=>(isset($params['select_name']) ? $params['select_name'] : '请选择标签'),
					),
				);
				$data = array_merge($list,$data);
			}
		}
		
		return $data;
    }
	
	public function grid($params) {
		$orderBy = isset($params['sort']) ? trim($params['sort']) . ' ' .  trim($params['order']) : "update_time desc";
        $page = isset($params['page']) && $params['page'] > 0 ? intval($params['page']) : 1;
        $pageSize = isset($params['rows']) && $params['rows'] > 0 ? intval($params['rows']) : 10;
		
		
		$where = array();
		
		if(isset($params['display']) && $params['display'] != '-1'){
			$where[] = "display = '".intval($params['display'])."'";
		}
		$params['keyword'] = trim($params['keyword']);
		if($params['keyword'] != ''){
			$where[] = "name LIKE '%$params[keyword]%'";
		}
		$where = !empty($where) ? implode(' and ',$where) : $where;
		
        //统计总记录数
        $options = $this->options;
		$total = $this->where($where)->count();
		
        //排序并获取分页记录
        $options['order'] = empty($options['order']) ? $orderBy : $options['order'];
        $this->options = $options;
		$this->limit($pageSize)->page($page);
		
        $data = $this->where($where)->select();
		
		if(!empty($data)){
			foreach($data as $key=>$value){
				$data[$key] = $this->infoFormat($value);
			}
		}
		
		return array('total' => (int)$total, 'rows' => (empty($data) ? false : $data), 'pagecount' => ceil($total / $pageSize));
    }
	
	public function infoFormat($info){
		$AdminModel = D('Admin');
		if(isset($info['create_aid']) && $info['create_aid'] > 0){
			$user = $AdminModel->field('user_name,real_name')->where("user_id = '$info[create_aid]'")->find();
			$info['create_aname'] = isset($user['user_name']) ? $user['user_name'] : '';
			$info['create_aname'] = (isset($user['real_name']) ? $user['real_name'] : '') .'/'. $info['create_aname'];
		}else{
			$info['create_aname'] = '';
		}
		if(isset($info['update_aid']) && $info['update_aid'] > 0){
			$user = $AdminModel->field('user_name,real_name')->where("user_id = '$info[update_aid]'")->find();
			$info['update_aname'] = isset($user['user_name']) ? $user['user_name'] : '';
			$info['update_aname'] = (isset($user['real_name']) ? $user['real_name'] : '') .'/'. $info['update_aname'];
		}else{
			$info['update_aname'] = '';
		}
		if(isset($info['create_time']) && $info['create_time'] > 0){
			$info['create_time'] = date('Y-m-d H:i:s',$info['create_time']);
		}else{
			$info['create_time'] = '';
		}
		if(isset($info['update_time']) && $info['update_time'] > 0){
			$info['update_time'] = date('Y-m-d H:i:s',$info['update_time']);
		}else{
			$info['update_time'] = '';
		}
		
		$info['switch'] = '<a href="javascript:;" onclick="doDisplay(this);" display="'.$info['display'].'" data_id="'.$info['id'].'"'.($info['display']==1 ? ' title="点击切换为禁用"' : ' title="点击切换为启用"').' class="easyui-linkbutton"><span style="font-size:18px;" class="fa '.($info['display']==1 ? 'fa-check green' : 'fa-close red').'"> </span></a>';
		
		return $info;
	}
}