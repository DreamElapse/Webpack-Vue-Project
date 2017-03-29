<?php
/*
 * 百度编辑器控制器
 * @author Ocean
 * @date 2015-7-20
 */
namespace Cpanel\Controller;
use Common\Extend\WechatUploader;
use Common\Controller\CpanelController;
use Common\Extend\WechatMedia;

class WechatUeditorController extends CpanelController{
	
	protected $tableName = 'wechat_uploader_image';
    protected $allowAction = '*';
    private $_CONFIG = array();
    private $url;
	
	static $imageType = array('wechat_media');  //路径模块文件夹
	private $module_name = NULL;  //模块名称，用于替换路径{type}

    public function __construct() {
        parent::__construct();		
        header("Content-Type: text/html; charset=utf-8");
        $this->_CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(APP_ROOT . "public/static/ueditor/php/wechat_config.json")), true);
    }

    public function index(){
		$this->module_name = (isset($_GET['savePath'])&&$_GET['savePath']!='' ? strtolower($_GET['savePath']) : 'other');
		
		if(!in_array($this->module_name,self::$imageType)){
            return json_encode(array(
				'state'=> '不允许操作的模块名称'
			));
        }
		
        $action = I('get.action');
        switch ($action) {
            case 'config':
                $result =  json_encode($this->_CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
				/* 上传配置 */
                $result = $this->_doUpload($action);
                break;
            /* 列出图片 */
            case 'listimage':
                $result = $this->_doList($action);
                break;
            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }
		
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }
    
    protected function _doUpload($action){
		$time = time();
        $base64 = "upload";
        switch (htmlspecialchars($action)) {
            case 'uploadimage':
                $config = array(
					"watermark" => isset($_GET['watermark'])&&$_GET['watermark']==1 ? true : false,  //是否增加水印
					"watermark_position" => isset($_GET['watermark_position'])&&$_GET['watermark_position']!='' ? trim($_GET['watermark_position']) : '7',  //水印位置，9宫格
					"watermark_file" => isset($_GET['watermark_file'])&&$_GET['watermark_file']!='' ? trim($_GET['watermark_file']) : 'ciji.png',  //水印文件名称
                    "pathFormat" => $this->_CONFIG['imagePathFormat'],
                    "maxSize" => $this->_CONFIG['imageMaxSize'],
                    "allowFiles" => $this->_CONFIG['imageAllowFiles']
                );
                $fieldName = $this->_CONFIG['imageFieldName'];
                break;
            default:
				$result = json_encode(array(
                    'state'=> '不允许上传的文件类型'
                ));
				echo $result;
				exit;
                break;
        }
		$config['imgtype'] = $this->module_name;
        /* 生成上传实例对象并完成上传 */
        $up = new WechatUploader($fieldName, $config, $base64);
		$info = $up->getFileInfo();
		
		$root = substr(APP_ROOT,0,-1);  //根目录
		
		$wechatMedia = new WechatMedia();
		
		$result = $wechatMedia->uploadImageTextFile($root . $info['url']);
		if(isset($result['url']) && $result['url'] != ''){
			$wechat_url = $result['url'];
			$this->dbModel->create(array(
				'title'=>$info['title'],
				'dir_name'=>date('Ymd',$time),
				'file_path'=>$info['url'],
				'wechat_url'=>$wechat_url,
				'original'=>$info['original'],
				'type'=>$info['type'],
				'size'=>$info['size'],
				'create_aid'=>login('user_id'),
				'create_time'=>$time,
			));
			$this->dbModel->add();
		}else{
			if(file_exists($root . $info['url'])){
				unlink($root . $info['url']);
			}
			$info = json_encode(array(
				'state'=> '['.$result['errcode'].']微信返回错误：'.$result['errmsg']
			));
		}
        /* 返回数据 */
        return json_encode($info);
    }
    
    protected function _doList($action){
        switch ($action) {
            /* 列出图片 */
            case 'listimage':
            default:
                $allowFiles = $this->_CONFIG['imageManagerAllowFiles'];
                $listSize = $this->_CONFIG['imageManagerListSize'];
                $path = $this->_CONFIG['imageManagerListPath'];
        }
		
		$path = str_replace("{type}", (isset($this->module_name) ? $this->module_name : ''), $path);
		
		
		if(isset($_GET['dir_list']) && $_GET['dir_list'] == 1){  //获取文件夹  Add By Lemonice
			$dir_list = $this->dbModel->field('dir_name')->group('dir_name')->order('dir_name desc')->select();
			$list = array();
			if(!empty($dir_list)){
				foreach($dir_list as $value){
					$list[] = $value['dir_name'];
				}
			}
			/* 返回数据 */
			$result = json_encode(array(
				"state" => "SUCCESS",
				"is_dir" => 1,
				"list" => $list,
				"start" => 0,
				"total" => count($list)
			));
		}else{  //获取文件
			$allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
		
			/* 获取参数 */
			$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
			$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
			$end = $start + $size;
			
			$dir_name = isset($_GET['dir_name']) ? trim($_GET['dir_name']) : '';
			
			$file_list = $this->dbModel->field('file_path as url,create_time as mtime')->where("dir_name = '$dir_name'")->order('create_time desc')->limit($start,$size)->select();
			
			
			$list = array();
			if(!empty($file_list)){
				foreach($file_list as $key=>$value){
					$file_s = getimagesize(APP_ROOT.$value['url']);
					$value['width'] = isset($file_s["0"]) ? $file_s["0"] : 0;  //获取图片的宽
					$value['height'] = isset($file_s["1"]) ? $file_s["1"] : 0;  //获取图片的高
					$list[] = $value;
				}
			}else{
				return json_encode(array(
					"state" => "no match file",
					"is_dir" => 0,
					"list" => array(),
					"start" => $start,
					"total" => 0
				));
			}
			
			/* 返回数据 */
			$result = json_encode(array(
				"state" => "SUCCESS",
				"list" => $list,
				"start" => $start,
				"total" => count($list)
			));
		}
		
		return $result;
    }
	
	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	private function _fetchFile($path, $allowFiles, &$files = array()) {
		if (!is_dir($path)) return null;
		if(substr($path, strlen($path) - 1) != '/') $path .= '/';
		$handle = opendir($path);
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				$path2 = $path . $file;
				if (is_dir($path2)) {
					$this->_fetchFile($path2, $allowFiles, $files);
				} else {
					if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
						$files[] = array(
							'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
							'mtime'=> filemtime($path2)
						);
					}
				}
			}
		}
		return $files;
	}
	
	/**
	 * 遍历获取目录下的文件夹  Add By Lemonice
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	private function _fetchDir($path)
	{
		if (!is_dir($path)) return null;
		if(substr($path, strlen($path) - 1) != '/') $path .= '/';
		$handle = opendir($path);
		$dirs = array();
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				$path2 = $path . $file;
				if (is_dir($path2)) {
					$dirs[] = $file;
				}
			}
		}
		return $dirs;
	}
}