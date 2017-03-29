<?php
/**
 * ====================================
 * 微信素材管理
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-01-17 09:40
 * ====================================
 * File: WechatMediaController.class.php
 * ====================================
 */
namespace Cpanel\Controller;
use Common\Controller\CpanelController;
use Common\Extend\WechatMedia;

class WechatMediaController extends CpanelController{
    protected $tableName = 'WechatMedia';
	
	
	//允许上传的文件后缀和文件大小
	private $suffix_array = array(
		'image'=>array(
			'size'=>2097152,  //2M
			'suffix'=>array(
				'bmp',
				'png',
				'jpeg',
				'jpg',
				'gif'
			)
		),
		'voice'=>array(
			'size'=>2097152,  //2M
			'suffix'=>array(
				'mp3',
				'wma',
				'wav',
				'amr',
			)
		),
		'video'=>array(
			'size'=>10485760,  //10M
			'suffix'=>array(
				'mp4',
			)
		),
	);
	
	private $wechatMedia = NULL;
	
	protected $allowAction = array('select');
	
	public function __construct() {
        parent::__construct();
		$this->wechatMedia = new WechatMedia();
    }
	
	/*
	*	永久素材列表
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function Index(){
		$type = I('request.type','news','trim');
		if(IS_AJAX) {
            $params = I('request.');
			$params['type'] = $type;
            $this->dbModel->filter($params);
			$data = $this->dbModel->grid($params);
            $this->ajaxReturn($data);
            exit;
        }
		$tpl = $type == 'news' ? 'index' : 'index_file';
		$this->assign('type_list', $this->wechatMedia->type);
		$this->assign('type', $type);
        $this->display($tpl);
    }
	
	/*
	*	获取永久素材下拉菜单
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function select(){
		$data = $this->dbModel->selectTree(I('request.'));
		$this->ajaxReturn($data);
		exit;
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
			$this->error('此素材不存在', '', true);
		}
		
		$result = $this->dbModel->create(array('display'=>$display,'update_aid' => login('user_id'),'update_time'=>time()));
		$result = $this->dbModel->where("id = '$id'")->save();
		if($result > 0){
			D('Admin')->addLog(($display==1 ? '启用' : '禁用') . '微信素材[ID:'.$id.']', login('user_id'));
			$this->success(($display==1 ? '启用' : '禁用').'成功', true);
		}else{
			$this->error(L('SAVE') . L('ERROR'), '', true);
		}
	}
	
	/*
	*	更新图文素材到微信
	*	@Author Lemonice
	*	@return exit
	*/
	public function doUpdateToWechat(){
		$ids = I('post.id','','trim');  //如果是多ID，则把多个图文绑定到多图文素材
		if($ids == ''){
			$this->error('请先选择图文素材', '', true);
		}
		
		$list = $this->dbModel->field('id,media_id,title,thumb_media_id,author,describe,show_cover_pic,content,wechat_url')->where("id IN($ids) and type = 'news'")->order('id asc')->select();
		if(!$list){
			$this->error('图文素材不存在', '', true);
		}
		
		if(count($list) > 8){  //多图文只支持最多8个
			$this->error('多图文最多不能超过8个', '', true);
		}
		
		if(count($list) > 1){  //添加多图文
			foreach($list as $key=>$info){
				$info['content'] = $this->dbModel->replaceWechatUrl($info['content']);
				$data[] = $this->dbModel->pushWechatMediaData($info);
			}
			$result = $this->wechatMedia->addImageText($data);
			if(isset($result['media_id']) && $result['media_id'] != ''){
				$this->dbModel->create(array('media_id'=>$result['media_id'],'update_wechat_time'=>time()));
				$this->dbModel->where("id IN($ids) and type = 'news'")->save();
				
				D('Admin')->addLog('添加微信素材多图文[media_id:'.$result['media_id'].']', login('user_id'));
				
				$this->success('添加成功', true);
			}
		}else if(count($list) == 1){
			$info = $list[0];
			
			$info['content'] = $this->dbModel->replaceWechatUrl($info['content']);
			$data = $this->dbModel->pushWechatMediaData($info);
			
			if($info['media_id'] != ''){  //编辑图文
				$index = 0;  //多图文中的第几个图文
				$list = $this->dbModel->field('id,media_id')->where("media_id = '$info[media_id]' and type = 'news'")->order('id asc')->select();
				if(count($list) > 1){  //多图文的，找到第几个图文
					foreach($list as $key=>$value){
						if($value['id'] == $info['id']){
							$index = $key;  //第几个
							break;
						}
					}
				}
				
				$result = $this->wechatMedia->editImageText($info['media_id'], $data, $index);
				if($result['errcode'] == 0){  //修改成功
					$this->dbModel->create(array('update_wechat_time'=>time()));
					$this->dbModel->where("id = '$info[id]' and type = 'news'")->save();
					
					D('Admin')->addLog('编辑微信素材多图文[ID:'.$info['id'].', media_id:'.$info['media_id'].', index='.$index.']', login('user_id'));
					
					$this->success('更新成功', true);
				}
				
			}else{  //添加单图文
				$result = $this->wechatMedia->addImageText(array(0=>$data));
				if(isset($result['media_id']) && $result['media_id'] != ''){
					$this->dbModel->create(array('media_id'=>$result['media_id'],'update_wechat_time'=>time()));
					$this->dbModel->where("id = '$info[id]' and type = 'news'")->save();
					
					D('Admin')->addLog('添加微信素材单图文[ID:'.$info['id'].', media_id:'.$result['media_id'].']', login('user_id'));
					
					$this->success('添加成功', true);
				}
			}
		}else{
			$this->error('操作的图文不存在', '', true);
		}
		$this->error('['.$result['errcode'].']微信返回错误：'.$result['errmsg'], '', true);
	}
	
	/*
	*	图文 显示、不显示封面开关
	*	@Author Lemonice
	*	@return exit
	*/
	public function doShowCoverPic(){
		$id = I('post.id',0,'intval');
		$show_cover_pic = I('post.show_cover_pic',0,'intval');
		
		$info = $this->dbModel->field('id')->where("id = '$id'")->find();
		if(!$info){
			$this->error('此素材不存在', '', true);
		}
		
		$result = $this->dbModel->create(array('show_cover_pic'=>$show_cover_pic,'update_aid' => login('user_id'),'update_time'=>time(),'last_update_time'=>time()));
		$result = $this->dbModel->where("id = '$id'")->save();
		if($result > 0){
			D('Admin')->addLog(($show_cover_pic==1 ? '开启【显示封面】' : '关闭【显示封面】').', 微信素材图文[ID:'.$id.']', login('user_id'));
			
			$this->success(($show_cover_pic==1 ? '切换为【显示封面】' : '切换为【不显示封面】').'成功', true);
		}else{
			$this->error(L('SAVE') . L('ERROR'), '', true);
		}
	}
	
	/*
	*	永久素材添加、编辑
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function form(){
		$type = I('request.type','news','trim');
		$tpl = $type == 'news' ? 'form' : 'form_file';
		$info = array();
		if($type == 'news'){  //图文
			$id = I('get.id',0,'intval');
			if($id > 0){
				$info = $this->dbModel->where("id = '$id'")->find();
			}
			
			$thumb_media_list = $this->dbModel->field('media_id,title')->where("type = 'image' and display = 1")->order('create_time desc')->select();
			$this->assign('thumb_media_list', $thumb_media_list);
		}
		$this->assign('type_list', $this->wechatMedia->type);
		$this->assign('type', $type);
		$this->assign('info', $info);
        $this->display($tpl);
    }
	
	/*
	*	永久图文素材添加、编辑 --> 保存
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function saveImageText(){
		if(isset($_REQUEST['dosubmit'])){
			$params = I('post.');
			
			if(empty($params['title'])){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'请填写标题',
				));
			}
			$id = isset($params['id']) && intval($params['id']) > 0 ? $params['id'] : 0;
			$params['content'] = htmlspecialchars_decode($params['content']);
			
			$http = isset($_SERVER['SERVER_PROTOCOL']) ? (strstr(strtoupper($_SERVER['SERVER_PROTOCOL']),'HTTPS') ? 'https' : 'http') : 'http';
			$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
			$site = $http.'://'.$domain.'/';
			$params['content'] = str_replace('\\','',$params['content']);
			preg_match_all('/<img[^>]*src\s*=\s*[\'"]?([a-zA-Z0-9_\/\.:]+)[^\'" >]*/isu', $params['content'], $src);
			
			$src = isset($src[1]) ? $src[1] : '';
			if(is_array($src) && !empty($src)){
				foreach($src as $url){
					$new_url = str_replace($site, '/', $url);
					$params['content'] = str_replace($url, $new_url, $params['content']);  //存数据库
				}
			}
			//添加时候如果选择了封面图片，默认开启显示封面
			/*if($id <= 0 && !empty($params['thumb_media_id'])){
				$params['show_cover_pic'] = 1;  //显示封面
			}else if($id > 0 && empty($params['thumb_media_id'])){  //编辑时候，如果不选择封面，则默认不开启显示封面
				$params['show_cover_pic'] = 0;  //不显示封面
			}*/
			if(isset($params['show_cover_pic'])){
				unset($params['show_cover_pic']);
			}
			
			$params[($id<=0 ? 'create_aid' : 'update_aid')] = login('user_id');
			
			$params['last_update_time'] = time();
			$this->dbModel->create($params);
			
			
			if($id > 0){  //编辑
				$this->dbModel->where("id = '$id'")->save();
			}else{
				$this->dbModel->add();
			}
			
			D('Admin')->addLog(($id > 0 ? '编辑' : '添加').', 微信素材文件[ID:'.$id.', type:'.$params['type'].']', login('user_id'));
			
			$this->ajaxReturn(array(
				'status'=>1,
				'info'=>($id>0 ? '编辑成功' : '添加成功'),
			));
		}
		$this->ajaxReturn(array(
			'status'=>0,
			'info'=>'出错了',
		));
	}
	
	/*
	*	永久素材添加 --> 保存
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
    public function addFile(){
		if(isset($_REQUEST['dosubmit'])){
			$param = I('post.');
			$type = I('post.type','news','trim');
			$title = I('post.title','','trim');
			$describe = I('post.describe','','trim');
			$media = isset($_FILES['media']) ? $_FILES['media'] : array();
			
			if($title == ''){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'请输入标题',
				));
			}
			if($id <= 0 && empty($media)){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'请选择一个文件',
				));
			}
			if(isset($media['error']) && $media['error'] != 0){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'上传出错了',
				));
			}
			
			if(!isset($this->suffix_array[$type])){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'上传出错了',
				));
			}
			$config = $this->suffix_array[$type];
			
			if($media['size'] > $config['size']){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'您上传的文件大小不符合',
				));
			}
			$name_array = explode('.',$media['name']);
			$suffix = isset($name_array[count($name_array)-1]) ? strtolower($name_array[count($name_array)-1]) : '';
			if(!in_array($suffix, $config['suffix'])){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'您上传的文件类型不符合',
				));
			}
			
			$root = substr(APP_ROOT,0,-1);  //根目录
			$path = '/upload/wechat_tmp/';
			$file_path = $root . $path . md5(time().rand(100000,999999)) . '.' . $suffix;
			makeDir($root . $path);  //检查目录是否存在，不存在则创建
			if(!move_uploaded_file($media['tmp_name'], $file_path) && !copy($media['tmp_name'], $file_path)){
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'上传文件失败',
				));
			}
			
			$wechat = $this->wechatMedia->uploadFile($type, $file_path, $title, $describe);
			if(isset($wechat['errcode']) && isset($wechat['errmsg'])){
				unlink($file_path);
				if($wechat['errcode'] == '45009'){  //达到最大调用次数
					$this->ajaxReturn(array(
						'status'=>0,
						'info'=>'当前操作已经达到今日微信接口的限制次数。',
					));
				}
				$this->ajaxReturn(array(
					'status'=>0,
					'info'=>'['.$wechat['errcode'].']微信返回错误：'.$wechat['errmsg'],
				));
			}
			
			$media_id = $wechat['media_id'];
			$wechat_url = $wechat['url'];
			
			$data = array(
				'type'=>$type,
				'media_id'=>$media_id,
				'title'=>$title,
				'describe'=>$describe,
				'wechat_url'=>$wechat_url,
				'file_path'=>str_replace($root,'',$file_path),
				'create_aid'=>login('user_id'),
				'create_time'=>time(),
			);
			$this->dbModel->create($data);
			$insert_id = $this->dbModel->add();
			
			D('Admin')->addLog('上传微信素材文件[ID:'.$insert_id.', type:'.$type.']', login('user_id'));
			
			$this->ajaxReturn(array(
				'status'=>1,
				'info'=>'添加成功',
			));
		}
		$this->ajaxReturn(array(
			'status'=>0,
			'info'=>'出错了',
		));
    }
	
	/*
	*	删除永久素材
	*	@Author 9009123 (Lemonice)
	*	@return exit
	*/
	public function delete(){
		$id = I('request.id','','trim');
		if($id == ''){
			$this->error('请先勾选素材');
		}
		$list = $this->dbModel->field('id,media_id,type,title,file_path')->where("id IN($id)")->select();

		$error_array = array();
		$success_count = 0;
		if(!empty($list)){
			$root = substr(APP_ROOT,0,-1);  //根目录
			foreach($list as $value){
				if(!empty($value['media_id'])){
					$result = $this->wechatMedia->delete($value['media_id'],$value['type']);
					if($result['errcode'] == 0 || $result['errcode'] == '40007'){  //删除成功, //40007: media_id不存在，有可能是微信后台不见了这个素材
						if($value['type'] == 'news'){ //图文素材
							$rs = $this->dbModel->where("media_id = '$value[media_id]'")->delete();  //删除所有media_id相同的，可能存在多图文
						}else{
							$rs = $this->dbModel->where("id = '$value[id]'")->delete();
						}
						D('Admin')->addLog('删除素材[ID:'.$value['id'].', type:'.$value['type'].', media_id:'.$value['media_id'].', title:'.$value['title'].']', login('user_id'));
						if($rs && file_exists($root . $value['file_path'])){
							unlink($root . $value['file_path']);
						}
						$success_count += 1;
					}else{
						if($result['errcode'] == '45009'){  //达到最大调用次数
							$error_array[] = $value['title'].'：当前操作已经达到今日微信接口的限制次数';
						}else{
							$error_array[] = $value['title'].'：['.$result['errcode'].']微信返回错误：'.$result['errmsg'];
						}						
					}
				}else{
					D('Admin')->addLog('删除素材[ID:'.$value['id'].', type:'.$value['type'].', media_id:'.$value['media_id'].', title:'.$value['title'].']', login('user_id'));
					$rs = $this->dbModel->where("id = '$value[id]'")->delete();  //删除记录
					$success_count += 1;
				}
			}
		}
		if($success_count > 0){
			$msg = '成功删除了'.$success_count.'个素材';
			if(!empty($error_array)){
				$msg .= '另外有如下素材删除出错：<br />'.implode('<br />',$error_array);
			}
			$this->success($msg);
		}else{
			$msg = '删除失败，原因如下：<br />'.implode('<br />',$error_array);
			$this->error($msg);
		}
	}
}