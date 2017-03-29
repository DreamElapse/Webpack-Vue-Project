<?php
/**
 * ====================================
 * 商品分类模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-07-18 14:18
 * ====================================
 * File: CategoryModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;

class CategoryModel extends CommonModel{
	/*
	*	获取购物车中商品分类信息
	*	@Author 9009123 (Lemonice)
	*	@param  array  $rows  购物车商品，包括赠品等，但是不包括套餐下的商品
	*	@return array
	*/
	public function getCartCategory($rows = array()){
		$act_ids = $goods_ids = $cat_ids = $package_ids = $package_goods_ids = array();
		$subtotal = array(  //价钱小计
			'goods'=>array(),
			'package'=>array(),
			'act'=>array()
		);
		if($rows){   
			$act_temp_arr = array();
			foreach ($rows as $row){
				if($row['extension_code'] == ''){
					$goods_ids[] = $row['goods_id'];
					$subtotal['goods'][$row['goods_id']] = $row['goods_price']*$row['goods_number'];  //单品小计
				}elseif($row['extension_code'] == 'package_buy'){
					$package_ids[] = $row['goods_id'];
					$subtotal['package'][$row['goods_id']] = $row['goods_price']*$row['goods_number'];   //套装小计
				}
				if($row['is_gift'] > 0){
					$act_ids[] = $row['is_gift'];  //活动id
					$act_temp_arr[$row['is_gift']][] = $row['goods_price']*$row['goods_number'];
				}
			}
			foreach($act_temp_arr as $akey => $aval){
				$subtotal['act'][$akey] = array_sum($aval);    //优惠活动小计
			}
			if(!empty($package_ids)){
				$package_ids = array_unique($package_ids);
				$goods_id_array = D('GoodsActivity')->field('goods_id')->where("act_id IN(".implode(',',$package_ids).")")->select();
				$package_goods_ids = array();
				if(!empty($goods_id_array)){
					foreach($goods_id_array as $value){
						$package_goods_ids[] = $value['goods_id'];  ////获取套装ID对应的goods_id
					}
				}
			}
			if(!empty($goods_ids) || !empty($package_goods_ids)){
				$combin_goods_ids = array_unique(array_merge($goods_ids,$package_goods_ids));
				$cat_id_array = D('Goods')->field('cat_id')->where("goods_id IN(".implode(',',$combin_goods_ids).")")->select();
				if($cat_id_array){
					$temp = '';
					$temp_array = array();
					foreach ($cat_id_array as $cat){
						if(!in_array($cat['cat_id'],$temp_array)){
							$temp .= $this->recursiveCatTree($cat['cat_id']).",";  //获取下级ID
							$temp_array[] = $cat['cat_id'];
						}
					}
					$cat_ids = explode(",",rtrim($temp,","));
				}
			}
		}
		
		return array(
			'goods_ids' => array_unique($goods_ids),
			'cat_ids' => array_unique($cat_ids),
			'package_ids' => array_unique($package_ids),
			'act_ids'=>  array_unique($act_ids),
			'subtotal'=> $subtotal
		);
	}
	
	/*
	*	遍历分类目录树,从子分类到顶级分类
	*	@Author 9009123 (Lemonice)
	*	@param int $cat_id
	*	@return string 返回以逗号隔开的字符串
	*/
	public function recursiveCatTree($cat_id){
		$cats = $this->field('cat_id,parent_id')->where("cat_id = '$cat_id'")->select();
        $cat_tree = '';
		if(!empty($cats)){
			foreach ($cats as $cat){
				$cat_tree .= $cat['cat_id'].",";
				if($cat['parent_id'] > 0){
					$cat_tree .= $this->recursiveCatTree($cat['parent_id']);
				}
			}
		}
		return rtrim($cat_tree,",");
	}
}