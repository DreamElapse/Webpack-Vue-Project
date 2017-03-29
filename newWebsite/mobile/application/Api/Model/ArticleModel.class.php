<?php
/**
 * ====================================
 * 口碑中心/品牌动态 文章模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-08-29 14:32
 * ====================================
 * File: ArticleModel.class.php
 * ====================================
 */
namespace Api\Model;
use Common\Model\CommonModel;

class ArticleModel extends CommonModel {
	/*
	*	获取视频
	*	@Author Lemonice
	*	@param  int $cat_id  分类ID
	*	@return array
	*/
    public function getVideoList($cat_id = 0){
		$list = $this->field('article_id,title,file_url,video_name')->where("cat_id = '$cat_id'")->select();
		return $list;
    }
}