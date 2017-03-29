<?php
/**
 * ====================================
 * 微信分组群发管理
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-01-17 09:40
 * ====================================
 * File: WechatMessageMassController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;
use Common\Extend\WechatMessageMass;

class WechatMessageMassController extends CpanelController{
    protected $tableName = 'wechat_message_mass';
	

    private $WechatMessageMass = NULL;
	
	protected $allowAction = array('selectType');
	
	public function __construct() {
        parent::__construct();
		$this->WechatMessageMass = new WechatMessageMass();
    }
	
	/*
	*	群发列表
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
		$this->assign('type_list', $this->WechatMessageMass->msgType);
        $this->display();
    }
	
	/*
	*	获取类型下拉菜单
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function selectType(){
		$data = array();
		foreach($this->WechatMessageMass->msgType as $key=>$value){
			$data[] = array(
				'id'=>$key,
				'text'=>$value,
			);
		}
		$this->ajaxReturn($data);
		exit;
    }
	
	/*
	*	添加、编辑群发任务的显示页面
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function form(){
		$id = I('get.id',0,'intval');
		$info = array();
		if($id > 0){
			$info = $this->dbModel->where("id = '$id'")->find();
		}
		
		//获取视频缩略图
		$thumb_media_list = D('WechatMedia')->field('media_id,title')->where("type = 'image' and display = 1")->order('create_time desc')->select();
		$this->assign('thumb_media_list', $thumb_media_list);
		$this->assign('info', $info);
        $this->display();
    }
	
	/*
	*	保存群发任务 --> 保存
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function save(){
		if(isset($_REQUEST['dosubmit'])){
			$params = I('post.');
			
			$id = isset($params['id']) && intval($params['id']) > 0 ? $params['id'] : 0;
			$params['tag_id'] = isset($params['tag_id']) ? intval($params['tag_id']) : '';
			
			if($params['tag_id'] == '' && $params['tag_id'] !== 0){
				$this->error('请选择标签');
			}
			if(empty($params['type'])){
				$this->error('请选择素材类型');
			}
			if($params['type'] != 'text' && empty($params['media_id'])){
				$this->error('请选择素材');
			}
			if(empty($params['title'])){
				$this->error('请填写标题');
			}
			if($params['type'] == 'text' && empty($params['content'])){
				$this->error('请输入文本内容');
			}
			
			$params['tag_id'] = $params['tag_id'] <= 0 ? 0 : $params['tag_id'];
			
			$params[($id<=0 ? 'create_aid' : 'update_aid')] = login('user_id');
			
			$params[($id<=0 ? 'create_time' : 'update_time')] = time();
			$this->dbModel->create($params);
			
			if($id > 0){  //编辑
				$this->dbModel->where("id = '$id'")->save();
			}else{
				$this->dbModel->add();
			}
			
			D('Admin')->addLog(($id>0 ? '编辑' : '添加').'群发任务[ID:'.$id.', title:'.$params['title'].', tag_id:'.$params['tag_id'].', type:'.$params['type'].']', login('user_id'));
			
			$this->success(($id>0 ? '编辑成功' : '添加成功'));
		}
		$this->error('出错了');
	}
	
	/*
	*	删除群发任务
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function delete(){
		$id = I('id', 0, 'intval');
		if($id <= 0){
			$this->error('请选择一个群发信息');
		}
		$msg_id = $this->dbModel->where("id = '$id'")->getField('msg_id');
		/*if($msg_id == '' || $msg_id === false || $msg_id === NULL){
			$this->error('此群发信息不存在');
		}*/
		if($msg_id > 0){
			$result = $this->WechatMessageMass->deleteMass($msg_id);  //开始发送
			if(isset($result['errcode']) && $result['errcode'] != 0 && $result['errcode'] != '40007'){  //删除成功, //40007: media_id不存在，有可能是微信后台不见了这个素材
				$this->error('微信返回错误：['.$result['errcode'].']'.$result['errmsg']);
				exit;
			}
		}
		$this->dbModel->where("id = '$id'")->delete();
		D('Admin')->addLog('删除群发任务[ID:'.$id.', msg_id:'.$msg_id.']', login('user_id'));
		$this->success('删除成功');
	}
	
	/*
	*	开始群发任务 - 推送给微信
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function send(){
		$id = I('request.id', 0, 'intval');
		if($id <= 0){
			$this->error('请选择一个群发信息');
		}
		$info = $this->dbModel->where("id = '$id'")->find();
		if(empty($info)){
			$this->error('此群发信息不存在');
		}
		if($info['type'] != 'text'){
			$info['media_id'] = D('WechatMedia')->where("id = '$info[media_id]'")->getField('media_id');
			if(!$info['media_id']){
				$this->error('选择的素材不存在');
			}
		}
		
		$result = $this->WechatMessageMass->massToTag($info);  //开始发送
		
		if(isset($result['errcode']) && $result['errcode'] != 0){
			$this->error('微信返回错误：['.$result['errcode'].']'.$result['errmsg']);
			exit;
		}
		$this->dbModel->create(array(
			'msg_id'=>$result['msg_id'],
			'msg_data_id'=>$result['msg_data_id'],
			'submit_success_time'=>time(),
		));
		$this->dbModel->where("id = '$id'")->save();
		
		D('Admin')->addLog('推送群发任务至微信[ID:'.$id.', msg_id:'.$result['msg_id'].', msg_data_id:'.$result['msg_data_id'].', type:'.$info['type'].']', login('user_id'));
		
		$this->success('发送成功，任务开始推送!');
	}
}

