<?php
/**
 * 微信素材
 * Author: Lemonice  (9009123)
 * Email: chengciming@126.com
 * Date: 2017-01-17 18:06
 * File: WechatMediaModel.class.php
 */
namespace Cpanel\Model;
use Common\Extend\Base\Common;
use Common\Model\CpanelModel;

class WechatMediaModel extends CpanelModel {
	protected $tableName = 'wechat_media';
	
	
	public function grid($params) {
		$orderBy = isset($params['sort']) ? trim($params['sort']) . ' ' .  trim($params['order']) : "media_id asc,create_time desc";
        $page = isset($params['page']) && $params['page'] > 0 ? intval($params['page']) : 1;
        $pageSize = isset($params['rows']) && $params['rows'] > 0 ? intval($params['rows']) : 10;
		
		$type = isset($params['type']) ? trim($params['type']) : 'image';
		
		$where = array(
			'type'=>$type,
		);
		
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
		
		if(isset($info['media_id'])){
			$info['media_id_text'] = '<input class="list_media_id_text easyui-textbox" style="width:100%;" value="'.$info['media_id'].'" />';
		}
		if(isset($info['wechat_url'])){
			$info['wechat_url_text'] = '<input class="list_url_text easyui-textbox" style="width:100%;" value="'.$info['wechat_url'].'" />';
		}
		
		if(isset($info['show_cover_pic'])){
			$info['show_cover_pic_switch'] = '<a href="javascript:;" onclick="doShowCoverPic(this);" show_cover_pic="'.$info['show_cover_pic'].'" data_id="'.$info['id'].'"'.($info['show_cover_pic']==1 ? ' title="点击切换为不显示"' : ' title="点击切换为显示"').' class="easyui-linkbutton"><span style="font-size:18px;" class="fa '.($info['show_cover_pic']==1 ? 'fa-check green' : 'fa-close red').'"> </span></a>';
		}
		if(isset($info['thumb_media_id'])){
			$info['file_path'] = $this->where("type = 'image' AND media_id = '$info[thumb_media_id]'")->getField('file_path');
			$info['thumb_media_id_text'] = '<a href="javascript:;" onclick="showThumb(this);" file_path="'.$info['file_path'].'" thumb_media_id="'.$info['thumb_media_id'].'" data_id="'.$info['id'].'" class="list_show_thumb easyui-linkbutton"><span style="font-size:14px;" class="fa fa-eye"> </span></a>';
		}
		
		if(isset($info['update_wechat_time']) && isset($info['last_update_time']) && $info['last_update_time'] > 0){
			$status = $info['update_wechat_time'] >= $info['last_update_time'] ? 0 : 1;  //当前是否需要更新
			if($status == 1){  //需要更新到微信
				if($info['media_id'] != ''){
					$info['update_status'] = '<a href="javascript:;" onclick="doUpdateToWechat(this);" data_id="'.$info['id'].'" title="点击更新到微信'.($info['update_wechat_time'] > 0 ? '，上次更新时间：'.date('Y-m-d H:i:s',$info['update_wechat_time']) : '').'" class="easyui-linkbutton"><span style="font-size:12px;" class="fa red">待更新</span></a>';
				}else{
					$info['update_status'] = '<a href="javascript:;" class="easyui-linkbutton"><span style="font-size:12px; color:blue;" class="fa">待推送</span></a>';
				}
			}else{  //不用更新到微信
				$info['update_status'] = '<span title="更新时间：'.date('Y-m-d H:i:s',$info['update_wechat_time']).'" class="easyui-linkbutton"><span style="font-size:12px;" class="fa green">已更新</span></span>';
			}
		}
		
		
		$info['switch'] = '<a href="javascript:;" onclick="doDisplay(this);" display="'.$info['display'].'" data_id="'.$info['id'].'"'.($info['display']==1 ? ' title="点击切换为禁用"' : ' title="点击切换为启用"').' class="easyui-linkbutton"><span style="font-size:18px;" class="fa '.($info['display']==1 ? 'fa-check green' : 'fa-close red').'"> </span></a>';
		
		return $info;
	}
	
	
	/*
	*	图文 内容包含图片的，替换为微信图片链接
	*	@Author Lemonice
	*	@param $content 图文内容
	*	@return string
	*/
	public function replaceWechatUrl($content = ''){
		if($content == ''){
			return $content;
		}
		$WechatUploaderImage = D('WechatUploaderImage');
		preg_match_all('/<img[^>]*src\s*=\s*[\'"]?([a-zA-Z0-9_\/\.:]+)[^\'" >]*/isu', $content, $src);
			
		$src = isset($src[1]) ? $src[1] : '';
		if(is_array($src) && !empty($src)){
			foreach($src as $url){
				$wechat_url = $WechatUploaderImage->where("file_path = '$url'")->getField('wechat_url');
				if(!empty($wechat_url)){
					$content = str_replace($url, $wechat_url, $content);
				}
			}
		}
		return $content;
	}
	
	/*
	*	组成发送给微信的图文详情
	*	@Author Lemonice
	*	@param $data 图文内容
	*	@return string
	*/
	public function pushWechatMediaData($data = array()){
		if(empty($data)){
			return $data;
		}
		$return = array(
			'title'=>$data['title'],  //标题
			'thumb_media_id'=>$data['thumb_media_id'],  // 图文消息的封面图片素材id（必须是永久mediaID） 
			'author'=>$data['author'],  //作者
			'digest'=>$data['describe'],  //图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空 
			'show_cover_pic'=>$data['show_cover_pic'],  // 是否显示封面，0为false，即不显示，1为true，即显示 
			'content'=>$data['content'],  // 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS 
			'content_source_url'=>$data['wechat_url'], // 图文消息的原文地址，即点击"阅读原文"后的URL 
		);
		return $return;
	}
	
	/*
	*	获取下拉菜单列表数据
	*	@Author Lemonice
	*	@param $data 图文内容
	*	@return string
	*/
	public function selectTree($params) {
		$type = isset($params['type']) ? $params['type'] : 'news';
		//获取数据权限
        $list = $this->field('id,title as text')->where("display = 1 and media_id != '' AND type = '$type'")->order("update_time desc, create_time desc")->select();
		
		if(!empty($list)){
			if(isset($params['selected'])){
				foreach($list as $key=>$value){
					if($params['selected'] == $value['id']){
						$list[$key]['selected'] = true;
					}
				}
			}
			$list = array_merge(array(
				0=>array(
					'id'=>0,
					'text'=>'请选择素材',
				)
			),$list);
			
		}
		return $list;
    }
}