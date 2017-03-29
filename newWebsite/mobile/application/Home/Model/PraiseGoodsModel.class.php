<?php
/**
 * ====================================
 * 口碑中心 关联商品相关模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-08-12 14:32
 * ====================================
 * File: PraiseGoodsModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;

class PraiseGoodsModel extends CommonModel {
	
	protected $tableName = 'Goods_Article';
	
	/**
     * 获取绑定的商品
     * @author 9009123
     * @param $id
     * @return array
     */
    public function getGoods($id) {
        $goods_id = $this->where('article_id = '.$id)->getField('goods_id');
		$goods = array();
		if(!empty($goods_id) && $goods_id > 0) {
			$GoodsModel = D('Goods');
			$goods_id = $GoodsModel->where("is_on_sale = 1 AND goods_id = '$goods_id'")->getField('goods_id');
			
			if(!empty($goods_id) && $goods_id > 0){
				$goods = $GoodsModel->field('goods_name,market_price,shop_price,is_package,goods_thumb,goods_img,original_img')->where("goods_id = '$goods_id'")->find();
				if(!empty($goods)){
					$goods['effect'] = D('GoodsAttr')->where("goods_id = '$goods_id' and attr_id = 2")->getfield('attr_value');  //获取主要功效
				}
				$goods['goods_id'] = $goods_id;
			}
			$goods['goods_thumb'] = C('domain_source.img_domain').$goods['goods_thumb'];
			$goods['goods_img'] = C('domain_source.img_domain').$goods['goods_img'];
			$goods['original_img'] = C('domain_source.img_domain').$goods['original_img'];
		}
        return $goods;
    }
}