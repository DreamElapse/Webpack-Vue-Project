<?php
/**
 * ====================================
 * 微信关注会员 - 标签管理
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-01-19 15:02
 * ====================================
 * File: UserTagController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;
use Common\Extend\WechatUserTag;

class UserTagController extends CpanelController{
    protected $tableName = 'UserTag';
	
	protected $allowAction = array('select');
	
	private $WechatUserTag = NULL;
	
	public function __construct() {
        parent::__construct();
		$this->WechatUserTag = new WechatUserTag();
    }
    
	public function select(){
		$type = I('request.type','','trim');
		if($type == 'select'){
			$data = $this->dbModel->tree(I('request.'));
			$this->ajaxReturn($data);
            exit;
		}
	}
	
	/*
	*	标签列表
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function Index(){
		if(IS_AJAX) {
            $params = I('request.');
            $this->dbModel->filter($params);
			$data = $this->dbModel->grid($params);
            $this->ajaxReturn($data);
            exit;
        }
        $this->display();
    }
	
	/*
	*	禁用 / 启用
	*	@Author Lemonice
	*	@return exit
	*/
	public function doDisplay(){
		$id = I('post.id',0,'intval');
		$display = I('post.display',0,'intval');
		
		$info = $this->dbModel->field('id')->where("id = '$id'")->find();
		if(!$info){
			$this->error('此标签不存在', '', true);
		}
		
		$result = $this->dbModel->create(array('display'=>$display,'update_aid' => login('user_id'),'update_time'=>time()));
		$result = $this->dbModel->where("id = '$id'")->save();
		if($result > 0){
			D('Admin')->addLog(($display==1 ? '启用' : '禁用') . '会员标签[ID:'.$id.']', login('user_id'));
			$this->success(($display==1 ? '启用' : '禁用').'成功', true);
		}else{
			$this->error(L('SAVE') . L('ERROR'), '', true);
		}
	}
	
	/*
	*	显示编辑添加的页面
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function form(){
		$id = I('get.id',0,'intval');
		$info = array();
		if($id > 0){
			$info = $this->dbModel->where("id = '$id'")->find();
		}
		$this->assign('info', $info);
        $this->display($tpl);
    }
	
	/*
	*	保存标签
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function save(){
		$params = I('post.');
			
		if(empty($params['name'])){
			$this->ajaxReturn(array(
				'status'=>0,
				'info'=>'请填写标签名称',
			));
		}
		
		$id = isset($params['id']) && intval($params['id']) > 0 ? $params['id'] : 0;
		
		if($id > 0){
			$info = $this->dbModel->field('id,tag_id,name')->where("id = '$id'")->find();
			if(!isset($info['tag_id'])){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'标签不存在',
				));
			}
			$result = $this->WechatUserTag->edit($info['tag_id'], $params['name']);
			if($result['errcode'] != 0){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'['.$result['errcode'].']微信返回错误：'.($result['error']!='' ? $result['error'] : $result['errmsg']),
				));
			}
			$params['update_aid'] = login('user_id');
		}else{
			$result = $this->WechatUserTag->add($params['name']);
			if($result['errcode'] != 0){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'['.$result['errcode'].']微信返回错误：'.($result['error']!='' ? $result['error'] : $result['errmsg']),
				));
			}
			$params['tag_id'] = $result['tag']['id'];
			$params['create_aid'] = login('user_id');
		}
		
		$this->dbModel->create($params);
		
		if($id > 0){  //编辑
			$this->dbModel->where("id = '$id'")->save();
		}else{
			$this->dbModel->add();
		}
		
		D('Admin')->addLog(($id>0 ? '编辑' : '添加') . '会员标签[ID:'.$id.']', login('user_id'));
		
		$this->ajaxReturn(array(
			'status'=>1,
			'info'=>($id>0 ? '编辑成功' : '添加成功'),
		));
	}
	
	/*
	*	删除标签
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function delete(){
		$id = I('request.id',0,'intval');
		if($id <= 0){
			$this->error('请先选择标签');
		}
		$tag_id = $this->dbModel->where("id = '$id'")->getField('tag_id');
		if(!$tag_id){
			$this->error('选择的标签不存在');
		}
		$result = $this->WechatUserTag->delete($tag_id);
		if($result['errcode'] == 0){  //删除成功
			$rs = $this->dbModel->where("id = '$id'")->delete();
			$rs = D('WechatTagBind')->where("tag_id = '$tag_id'")->delete();  //删除对应的标签会员绑定关系
			
			D('Admin')->addLog('删除会员标签[ID:'.$id.', tag_id:'.$tag_id.']', login('user_id'));
			
			$this->success('删除成功');
		}
		$this->error('['.$result['errcode'].']微信返回错误：'.($result['error']!='' ? $result['error'] : $result['errmsg']));
	}
}