<?php
/**
 * ====================================
 * 口碑中心 文章分类模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-08-12 14:32
 * ====================================
 * File: ArticleCatModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;

class ArticleCatModel extends CommonModel {
	
	/**
     * 所有父子类id
     * @author 9009123
     * @param $cat_name
     * @return array
     */
	public function wxCatIds($cat_name){
		$wx_catids = array();
		$wx_catids[0] = $this->where("cat_name='$cat_name'")->getField('cat_id');
		if(!empty($wx_catids[0])){
			$catids = $this->field('cat_id')->where("parent_id = $wx_catids[0]")->select();
			foreach($catids as $catid){
				$wx_catids[] = $catid['cat_id'];
			}
		}
		return $wx_catids;
	}
	
	/**
     * 根据中文名称，获取ID
     * @author 9009123
     * @param $cat_name
     * @return array
     */
	public function nameToId($cat_name){
		if(is_array($cat_name)){
			$str = '"'.implode('","',$cat_name).'"';
		}else{
			$str = $cat_name;
		}
		return $this->field('cat_id')->where("cat_name in ($str)")->select();
	}
}