<?php
/**
 * ====================================
 * 品牌动态
 * ====================================
 * Author: 9009221
 * Date: 2016-07-08
 * ====================================
 * File: BrandController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;

class BrandController extends InitController{

    public function  __construct() {
        parent::__construct();
        $this->mbrand = D('Brand');
    }

    /*
    *	品牌动态列表
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function brand_list() {
        //if (IS_AJAX) {
            $page = I('page', 1);
			$pageSize = I('pageSize', 5, 'intval');
            $data = $this->mbrand->getList($page,$pageSize);
            if ($data) {
                $this->success($data);
            } else {
                $this->error('没有数据');
            }
        //} else {
        //    $this->display();
        //}
    }

    /*
    *	品牌动态详情
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function brand_detail() {
        if (IS_AJAX) {
            $id = I('id', '', 'intval');
            if (!$id) {
                $this->error('文章id不能为空');
            }
            $data = $this->mbrand->getDetail($id);
            $this->success($data);
        } else {
            $this->display();
        }
    }
	
    /*
    *	品牌动态 - 视频获取
    *	@Author 9009123
    *	@param
    *	@return exit && JSON
    */
    public function brandVideoList() {
		$site_id = C('SITE_ID');
		$brand_video_cat = C('BRAND_VIDEO_CAT');  //加载配置文件
		$brand_video_cat = isset($brand_video_cat[$site_id]) ? $brand_video_cat[$site_id] : array();
		$cat_id = $brand_video_cat['cat_id'];  //对应这个分类的配置
		
		$ArticleModel = D('Article');
        $video_list = $ArticleModel->getVideoList($cat_id);
		
		if(!empty($video_list)){
			foreach($video_list as $key=>$value){
				$value['video_url'] = '/public/video/'.$value['video_name'];
				$video_list[$key] = $value;
			}
		}
		$this->success($video_list);
    }
}