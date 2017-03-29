<?php
/**
 * ====================================
 * 微信分组群发模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-01-20 14:05
 * ====================================
 * File: WechatMessageMassModel.class.php
 * ====================================
 */
namespace Cpanel\Model;
use Common\Model\CpanelModel;

class WechatMessageMassModel extends CpanelModel {
	protected $tableName = 'wechat_message_mass';
	
	public function grid($params) {
		$orderBy = isset($params['sort']) ? trim($params['sort']) . ' ' .  trim($params['order']) : "update_time desc,create_time desc";
        $page = isset($params['page']) && $params['page'] > 0 ? intval($params['page']) : 1;
        $pageSize = isset($params['rows']) && $params['rows'] > 0 ? intval($params['rows']) : 10;
		
		$type = isset($params['type']) ? trim($params['type']) : 'image';
		
		$where = array();
		
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
		
		if(isset($info['submit_success_time']) && $info['submit_success_time'] > 0){
			$info['submit_success_date'] = date('Y-m-d H:i:s',$info['submit_success_time']);
		}else{
			$info['submit_success_date'] = '<span class="red">未开始</span>';
		}
		
		if(isset($info['msg_id']) && $info['msg_id'] > 0){
			$info['msg_name'] = $info['msg_id'];
		}else{
			$info['msg_name'] = '<span class="red">未推送</span>';
		}
		
		if(isset($info['type'])){
			$WechatMessageMass = new \Common\Extend\WechatMessageMass();
			$msg_type = $WechatMessageMass->msgType;
			$info['type_name'] = $msg_type[$info['type']];
		}
		
		$info['send_ignore_reprint_switch'] = '<span class="'.($info['send_ignore_reprint']==1 ? 'green' : 'red').'">'.($info['send_ignore_reprint']==1 ? '继续发送' : '结束发送').'</span>';
		
		if(isset($info['tag_id']) && $info['tag_id'] > 0){
			$info['tag_name'] = D('UserTag')->where("tag_id = '$info[tag_id]'")->getField('name');
			if(!$info['tag_name']){
				$info['tag_name'] = '<span class="red">未知标签</span>';
			}
		}else{
			
			$info['tag_name'] = '<span class="red">所有会员</span>';
		}
		return $info;
	}
}