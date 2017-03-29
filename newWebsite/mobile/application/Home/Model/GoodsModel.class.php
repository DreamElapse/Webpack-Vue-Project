<?php
/**
 * ====================================
 * 商品相关数据模型
 * ====================================
 * Author: 
 * Date: 
 * ====================================
 * File: GoodsModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;
use Common\Extend\Time;

class GoodsModel extends CommonModel{
	
	/**
	 *	获取商品相关相册
	 *  @Author: 9009123 (Lemonice) 
	 *	@param string $goods_id 商品id，多个逗号隔开
	 *	@param string $price 是否价格排序，asc or desc
	 *	@param string $keyword  检索关键字
	 *	@param int    $cate_id  分类ID
	 *	@param int    $is_package 是否只搜索套装商品
	 *	@param int    $page 分页，当前第几页
	 *	@param int    $pageSize 分页，每页显示多少条，默认8条
	 *	return array
	 */
	public function getList($goods_id = NULL, $price = NULL, $keyword = NULL, $cate_id = 0, $is_package = 0, $page = 1, $pageSize = 8){
		
		//下拉商品分类
		// $cates = $goodsModel->getCates();
		
		// 默认的筛选条件及排序
		// $where = "g.is_on_sale = 1 AND g.is_show = 1 AND g.is_delete=0 AND g.is_alone_sale = 1 ";
		$where = "g.is_on_sale = 1 AND g.is_show = 1 AND g.is_delete=0 ";
		$order_by = array("g.is_hot DESC,g.sort_order ASC,g.goods_id DESC");
		
		if($goods_id !== NULL && $goods_id != ''){
			$where .= " AND g.goods_id IN($goods_id)";
		}
		
		//是否显示套装
		if($is_package >= 3 || $is_package <= 0){
			$is_package = 0;
		}
		if($is_package > 0){
			$is_package -= 1;
			$where .= " AND g.is_package=$is_package";
		}
		
		//价格排序
		if($price !== NULL && $price != ''){
			$price_order_by = trim($price);
			if($price_order_by != 'asc' && $price_order_by != 'desc'){
				$price_order_by = 'asc';
			}
			$order_by[0] = "g.shop_price $price_order_by";
		}
		if(!empty($order_by) && ($goods_id == NULL || $goods_id == '')){
			$this->order(implode(',',$order_by));
		}
		if($pageSize > 0){
			$this->page($page,$pageSize);
		}
		
		//获取核心功效分类id
		$attr_id = C("attr_core_effect.".C('SITE_ID'));
		
		//分类筛选
		$cate_ids = array();
		
		//关键词搜索，搜索分类，以过去分类下的商品
		if(!empty($keyword)){
			$cate_ids = array();
			$keyword_cates = M('Category')
							->where("cat_name LIKE '%$keyword%' OR keywords LIKE '%$keyword%'")
							->field('cat_id')
							->select();
			foreach($keyword_cates as $key=>$val){
				$cate_ids[] = $val['cat_id'];
			}
			$keyword_where = "(g.goods_name like '%$keyword%' OR g.keywords like '%$keyword%')";
			
			//组装分类查询条件
			if(!empty($cate_ids)){
				$cate_ids = array_unique($cate_ids);
				$cate_ids_str = implode(',', $cate_ids);
				$keyword_where .= " OR g.cat_id IN($cate_ids_str) ";
			}
			$where .= " AND ($keyword_where)";
			
		}else if($cate_id > 0 && empty($goods_id)){
			$cate_ids[] = $cate_id;
			//获取扩展分类
			$extend_goods_ids = M('Goods_cat')->field('goods_id')->where(array('cat_id'=>$cate_id))->select();
			
			//获取绑定该分类的商品
			$goods_ids = M('Goods')->where(array('cat_id'=>$cate_id))->field('goods_id')->select();
			
			$union_goods_ids = array_merge($goods_ids,$extend_goods_ids);
			$goods_id_arr = array(0);
			foreach($union_goods_ids as $val){
				$goods_id_arr[] = $val['goods_id'];
			}
			$goods_id_arr_str = implode(',', $goods_id_arr);
			$where .= " AND g.goods_id IN($goods_id_arr_str)";
		}else{
			
			$cates = C('category_list.'.C('SITE_ID'));
			$cat_id_str = ',';
			foreach($cates as $k=>$val){
				$cate_ids[] = $val['cat_id'];
			}
			$cat_id_str = implode($cate_ids, ',');
			$extend_goods_ids = M('Goods_cat')->field('goods_id')->where("cat_id IN ($cat_id_str)")->select();
			foreach($extend_goods_ids as $k=>$v){
				$extend_goods_ids[$k] = $v['goods_id'];
			}
			$extend_goods_ids = implode($extend_goods_ids, ',');
			
			$where .= " AND (g.cat_id IN($cat_id_str) OR g.goods_id IN($extend_goods_ids))";
		}
		
		$goods = $this
			->alias('g')
			->field('g.goods_id,g.goods_sn,g.cat_id,g.is_real,g.goods_name,g.is_package,g.click_count,g.market_price,g.shop_price,g.shop_price as goods_price,g.promote_price,g.goods_thumb,g.goods_img,g.original_img,g.goods_guanggao,g.virtual_sale,ga.attr_value')
			->join("LEFT JOIN __GOODS_ATTR__ AS ga ON g.goods_id=ga.goods_id AND ga.attr_id=$attr_id")
			->where($where)
			->select();
		
		foreach($goods as &$val){
			$val['goods_thumb'] = C('domain_source.img_domain').$val['goods_thumb'];
			$val['goods_img'] = C('domain_source.img_domain').$val['goods_img'];
			$val['original_img'] = C('domain_source.img_domain').$val['original_img'];
		}
			// echo $this->getLastSql();exit;
		// dump($goods);exit;
		return $goods;
	}
	
	/**
	 *	获取专题页商品
	 *  @Author: 9009123 (Lemonice) 
	 *	@param string $goods_id 商品id，多个逗号隔开
	 *	@param string $order 排序
	 *	return array
	 */
	public function getSpecialList($goods_id = NULL){
		if(!$goods_id){
			return array();
		}
		
		//$user_info = session('userInfo');
		$user_info = D('Users')->getUserLoginInfo($this->user_id);
		//$user_info = isset($user_info[$this->user_id]) ? $user_info[$this->user_id] : array();
		
		$member_discount = 1;
		if(!empty($user_info)){
			$member_discount = D('UserRank')->where("min_points = '$user_info[min_points]' and max_points = '$user_info[max_points]'")->getField('discount');
			$member_discount = $member_discount > 0 ? $member_discount / 100 : 1;  //会员折扣
		}
		
		$this->field("g.goods_id, g.goods_name, g.market_price, g.is_package, IFNULL(mp.user_price, g.shop_price * '$member_discount') AS shop_price, g.goods_thumb, g.goods_img,g.original_img");
		//, g.promote_price, promote_start_date, promote_end_date
		$this->alias(' AS g')->join("__MEMBER_PRICE__ AS mp ON mp.goods_id = g.goods_id AND mp.user_rank = '$user_info[level]'", 'left');
		$this->where("g.is_on_sale = 1  AND g.is_delete= 0 AND g.goods_id IN(" . $goods_id . ")");  // AND g.is_best = 1
		$list = $this->select();
		
		if(!empty($list)){
			$GoodsAttrModel = D('GoodsAttr');
			$GoodsActivityModel = D('GoodsActivity');
			//获取核心功效分类id
			$attr_id = C("attr_core_effect.".C('SITE_ID'));
			$array = array();
			$time = Time::gmTime();
			foreach($list as $key=>$row){
				$is_allow = true;
				
				$row["goods_img"] = C('domain_source.img_domain').$row["goods_img"];
				$row["original_img"] = C('domain_source.img_domain').$row["original_img"];
				$row["goods_thumb"] = C('domain_source.img_domain').$row["goods_thumb"];
				
				$row['package_id'] = 0;
				if($row['is_package'] > 0){  //套装
					$packageinfo = $GoodsActivityModel->field('act_id,ext_info')->where("goods_id = '$row[goods_id]' and start_time <= '$time' and end_time >= '$time'")->find();  //获取套装
					
					if($packageinfo != ''){
						$row['package_id'] = $packageinfo['act_id'];  //套装ID
						$ext_info = unserialize($packageinfo['ext_info']);
						$row['shop_price'] = $ext_info['package_price'];  //套装价格
					}else{
						$is_allow = false;
					}
					//获取套装子商品信息
					$package_goods_list = M('package_goods')->field('goods_id,goods_number')->where("package_id = '$row[package_id]'")->select();
					if(!empty($package_goods_list)){
						foreach($package_goods_list as $key=>$value){
							$value['goods_name'] = $this->field('goods_name')->where("goods_id = '$value[goods_id]'")->getField('goods_name');
							
							$value['goods_format'] = $GoodsAttrModel->where("goods_id = '$value[goods_id]' and attr_id = '1'")->getField('attr_value');  //获取商品规格
							$value['goods_core'] = $GoodsAttrModel->where("goods_id = '$value[goods_id]' and attr_id = '2'")->getField('attr_value');  //获取商品核心功效
							
							$package_goods_list[$key] = $value;
						}
					}
					$row['package_goods_list'] = $package_goods_list;
				}
				
				//获取核心功效
				$row['attr_value'] = $GoodsAttrModel->where("goods_id = '$row[goods_id]' and attr_id = '$attr_id'")->getField('attr_value');
				
				if($member_discount > 0 && floor($row['shop_price']) < $row['shop_price']){
					$row['shop_price'] = floor($row['shop_price']) + 1;
				}
				
				if($is_allow == true){
					$array[] = $row;
				}
			}
			$list = $array;
		}
        return $list;
	}
	
	/**
	 *	获取商品相关相册
	 *	@param $goods_id 商品id
	 *
	 *	return array
	 */
	public function getGallery($goods_id){
		$imgs = M('GoodsGallery')->field('goods_id,img_id,img_url,thumb_url')->where("goods_id=$goods_id")->select();
		if($imgs){
			foreach($imgs as &$val){
				$val['img_url'] = !empty($val['img_url']) ? C('domain_source.img_domain').$val['img_url'] : '';
				$val['thumb_url'] = !empty($val['thumb_url']) ? C('domain_source.img_domain').$val['thumb_url'] : '';
			}
		}
		
		return $imgs;
	}
	
	/**
	 *	获取二级页面下拉的分类
	 *	
	 *	return array
	 */
	public function getCates(){
		$cates = M('Category')->where("parent_id=0 AND show_in_nav=1 AND is_show=1")
				->field('cat_id,cat_name,keywords,cat_desc,parent_id')
				->order("sort_order ASC, cat_id ASC")
				->select();
		return $cates;
	}
	
	/**
	 *	获取商品相信
	 *	@param integer $goods_id 商品id
	 *	return array
	 */
	public function getGoodInfo($goods_id){
		//获取核心功效分类id
		$attr_id = C("attr_core_effect.".C('SITE_ID'));
		
		$field = 'g.goods_id, g.cat_id, g.goods_name, g.click_count, g.brand_id, g.goods_number, g.goods_weight, g.market_price, g.shop_price,g.goods_sn, ';
		$field .= 'g.goods_desc, g.goods_thumb, g.goods_img, g.original_img, g.is_real, g.is_best, g.is_new, g.is_hot, g.keywords, ';
		$field .= 'g.is_promotion, g.is_package, g.goods_guanggao, g.virtual_sale, g.is_on_sale, ';
		$field .= 'c.cat_name, c.measure_unit, b.brand_name, ';
		$field .= 'ga.attr_value';
		
		$good_info = $this->alias('g')
					->field($field)
					->join('LEFT JOIN __CATEGORY__ AS c ON g.cat_id = c.cat_id')
					->join('LEFT JOIN __BRAND__ AS b ON g.brand_id = b.brand_id')
					->join("LEFT JOIN __GOODS_ATTR__ AS ga ON ga.goods_id = g.goods_id AND ga.attr_id=$attr_id")
					->where("g.goods_id=$goods_id AND g.is_delete=0 AND is_on_sale=1")
					->find();
					
		if(empty($good_info)){
			return false;
		}
		//重量修正，数据表中的为千克单位
		if($good_info['goods_weight']>0){
			if($good_info['goods_weight']>=1){
				$good_info['goods_weight'] = $good_info['goods_weight'].'kg';
			}else{
				$good_info['goods_weight'] = ($good_info['goods_weight']*1000).'g';
			}
		}
		$good_info['goods_thumb'] = C('domain_source.img_domain').$good_info['goods_thumb'];
		$good_info['goods_img'] = C('domain_source.img_domain').$good_info['goods_img'];
		$good_info['original_img'] = C('domain_source.img_domain').$good_info['original_img'];
		
		return $good_info;
	}
	
	
	
	/**
	 *	根据商品goods_id获取关联套装的信息
	 *	@param integer $goods_id 商品id
	 *	$param time 时间戳
	 *	return array or false	
	 */
	public function getPackageInfo($goods_id, $time=0,$act_id=0){
		//套装信息
        if(empty($goods_id) && $act_id > 0){
            $package_info = M('GoodsActivity')
                ->where("act_id=$act_id")
                ->find();
        }else{
            if($goods_id <= 0){
                return false;
            }
            $package_info = M('GoodsActivity')
                ->where("goods_id=$goods_id")
                ->find();
        }
        if(empty($package_info)){
            return $package_info;
        }
			
		if($time==0){
			$time = Time::gmTime();
		}
		//判断套装时间是否在可销售时间内，否则表示下架
		if ($package_info['start_time'] <= $time && $package_info['end_time'] >= $time){
			$package_info['is_on_sale'] = 1;
		}else{
			$package_info['is_on_sale'] = 0;
			return false;
		}
		$package_info['start_time'] = date('Y-m-d H:i', $package_info['start_time']);
		$package_info['end_time'] = date('Y-m-d H:i', $package_info['end_time']);
		$row = unserialize($package_info['ext_info']);
		unset($package_info['ext_info']);
		if ($row){
			foreach ($row as $key=>$val){
				$package_info[$key] = $val;
			}
		}
		//规格id
		$attr_spec_id = C("attr_spec.".C('SITE_ID'));
		
		//获取套装子商品及子商品属性
		$package_goods = M('PackageGoods')
						->field('pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, g.goods_sn, g.goods_name,g.shop_price, g.market_price, g.goods_thumb, g.is_real,g.goods_number as kc_goods_number,g.is_on_sale,ga.attr_value as goods_weight')
						->alias('pg')
						->join("LEFT JOIN __GOODS__ AS g ON g.goods_id = pg.goods_id")
						->join("LEFT JOIN __GOODS_ATTR__ AS ga ON pg.goods_id = ga.goods_id AND ga.attr_id = $attr_spec_id")
						->where("pg.package_id=".$package_info['act_id'])
						->order("pg.package_id ASC, pg.goods_id ASC")
						->select();
		foreach($package_goods as $key=>$val){
			if($val['goods_weight']>0){
				if($val['goods_weight']>=1){
					$val['goods_weight'] = $val['goods_weight'].'kg';
				}else{
					$val['goods_weight'] = ($val['goods_weight']*1000).'g';
				}
			}else{
				$val['goods_weight'] = 0;
			}
			$package_goods[$key] = $val;
		}
		$package_info['package_goods'] = $package_goods;
		
		return $package_info;
	}
	
	/**
	 *	获取商品评论
	 *	@param $goods_id 商品id，多个ID之间逗号隔开
	 *	@param $page 页码
	 *	@param $limit 每页数量
	 *	@param $get_type 标签分类id，0-不区分
	 *	return array;
	 */
	public function getComments($goods_id, $get_type=0, $page=0, $limit=3, $order_by=''){
		if($goods_id == ''){
			return array();
		}
		$where[] = "id_value IN($goods_id)";
		$where[] = 'status = 0';
		$where[] = 'show_status = 1';
		$where[] = 'is_client = 0';
		if($get_type>0){
			$where[] = "type_id = '$get_type'";
		}
		
		$order = 'id DESC';
		if(!empty($order_by)){
			$order = $order_by.','.$order;
		}
		
		$good_comments = M('Comments')
						->field("id,id_value,user_name,show_time,content,show_time,level,is_client,pic,pic1,like_num,z_content,z_date")
						->where(implode(' and ',$where))
						->limit($page, $limit)
						->order($order)
						->select();
		if(!empty($good_comments)){
			$source_domain = C('source_domain.14');
			foreach($good_comments as $key=>$val){
				if(!empty($val['show_time'])){
					$val['show_time'] = date('Y-m-d', $val['show_time']);
				}
				if(!empty($val['pic'])){
					$good_comments[$key]['pic'] = $source_domain['img_domain'].$val['pic'];
				}
				if(!empty($val['pic'])){
					$good_comments[$key]['pic1'] = $source_domain['img_domain'].$val['pic1'];
				}
			}
		}
		return $good_comments;
	}
	
	/**
	 *	获取商品评论
	 *	@param $goods_id 商品id
	 *	return array
	 */
	public function getLinkGoods($goods_id){
		$link_goods = M('link_goods')
					->field('lg.link_goods_id AS goods_id, g.goods_name,g.shop_price,g.market_price,g.is_package,g.goods_thumb,original_img')
					->alias('lg')
					->join("LEFT JOIN __GOODS__ AS g ON lg.link_goods_id = g.goods_id")
					->where("lg.goods_id=$goods_id AND g.is_on_sale=1")
					->select();
		return $link_goods;
	}





    /**
     * 获取商品图
     * @param $goods_ids
     */
    public function getGoodsThumb($goods_ids){
        $return = array();
        $all_goods_thumb =  $this->field('goods_thumb,goods_id')->where('goods_id in( '.implode(',',$goods_ids).')') -> select();
        foreach($all_goods_thumb as $k => $v){
            $return[$v['goods_id']] = C('domain_source.img_domain').$goods['goods_thumb'];
        }
        return $return;
    }

    /**
     * 获取套装图
     * @param $package_ids
     * @return array
     */
    public function getPackageGoodsThumb($package_ids){
        $return = array();
        $all_goods_thumb =  $this->field('goods_thumb,act_id')->alias('g')->join('LEFT JOIN __GOODS_ACTIVITY__ AS ga  ON g.goods_id=ga.goods_id')->where('act_id in ('.implode(',',$package_ids).')') -> select();
        foreach($all_goods_thumb as $k => $v){
			$return[$v['act_id']] = C('domain_source.img_domain').$v['goods_thumb'];
        }
        return $return;
    }
}