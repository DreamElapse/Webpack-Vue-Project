<?php
/**
 * ====================================
 * 会员中心 里面的积分模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2017-02-08 13:49
 * ====================================
 * File: IntegralCenterModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\UserCenterModel;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;

class IntegralCenterModel extends UserCenterModel{
	protected $_config = 'USER_CENTER';
    protected $tableName = 'point_exchange';
	
	private $img_host = 'http://my.chinaskin.cn/public/upload/goods';  //商品域名地址
	
	/*
	*	分页处理
	*	@Author Lemonice
	*	@param  string $user_id 用户ID
	*	@param  int $page 当前页，第几页
	*	@param  int $pageSize 每页显示多少条
	*	@return array
	*/
    public function getPage($user_id = 0, $page = 1, $pageSize = 0) {
		if($user_id <= 0){
			return array('page' => $page, 'pageSize' => $pageSize, 'total' => 0, 'pageTotal' => 1, 'list' => array());
		}
		
		$now_time = Time::gmtime();
		$total = 0;
		$pageTotal = 1;
		
		$result = $this->getPointsLeft($user_id);  //查询用户可用积分, 会计算被冻结的积分在内
		$user_points = $result['user_points'];
		if(!$user_points){
			$user_points = 0;
		}
		
		$field = 'p.point,p.price,p.exchange_id,p.goods_name,p.per_number,g.shop_price,g.goods_id';
		$where = "p.status=2 AND g.is_on_sale=1 AND (start_time = 0 OR start_time <= '$now_time') AND (end_time = 0 OR end_time > '$now_time') AND max_number > 0 AND p.point_limit <=".$user_points;
		$order_by = 'exchange_id DESC';
		
		//是否启用分页
		if($pageSize > 0){
			$this->alias(' AS p')->join("__GOODS__ AS g ON p.goods_id=g.goods_id", 'left');
			$total = $this->where($where)->count();  //统计总记录数
			$this->page($page.','.$pageSize);
			$pageTotal = ceil($total / $pageSize);  //计算总页数
		}else{
			$page = 1;
		}
		$this->alias(' AS p')->join("__GOODS__ AS g ON p.goods_id=g.goods_id", 'left');
		$list = $this->field($field)->where($where)->order($order_by)->select();
		$total = $total > 0 ? $total : count($list);
		
		if(!empty($list)){
			foreach($list as $key=>$info){
				$list[$key] = $this->infoFormat($info);
			}
		}
        return array('page' => $page, 'pageSize' => $pageSize, 'total' => (int)$total, 'pageTotal' => $pageTotal, 'list' => $list);
    }
	
	/*
	*	商品详情
	*	@Author Lemonice
	*	@param  int $exchange_id  积分商品ID
	*	@param  int $user_id 用户ID
	*	@param  bool $check_points 是否检查当前用户的可用积分是否足以兑换
	*	@return array
	*/
    public function getInfo($exchange_id = 0, $user_id = 0, $check_points = false) {
		if($exchange_id <= 0 && $user_id <= 0){
			return array();
		}
		$now_time = Time::gmtime();
		$result = $this->getPointsLeft($user_id);  //查询用户可用积分, 会计算被冻结的积分在内
		$user_points = $result['user_points'];
		if(!$user_points){
			$user_points = 0;
		}
		
		$field = 'p.point,p.shipping_fee,p.shipping_fee_remote,p.price,p.exchange_id,p.goods_name,p.per_number,g.shop_price,g.market_price,g.goods_id,g.goods_sn,g.goods_brief';
		$where = "p.status=2 AND g.is_on_sale=1 AND (start_time = 0 OR start_time <= '$now_time') AND (end_time = 0 OR end_time > '$now_time') AND max_number > 0 AND p.point_limit <=".$user_points." AND exchange_id = '".$exchange_id."'";
		
		$this->alias(' AS p')->join("__GOODS__ AS g ON p.goods_id=g.goods_id", 'left');
		$info = $this->field($field)->where($where)->find();
		
		if($check_points == true && $user_points < $info['point']){
			return array();  //可用积分不足以兑换
		}
        return $this->infoFormat($info);
    }
	
	/*
	*	获取用户的可用积分 - 会计算被冻结的积分在内
	*	@Author Lemonice
	*	@param  int $user_id 用户ID
	*	@return array
	*/
    public function getPointsLeft($user_id = 0) {
		$integral = array(
			'user_points'=>0,
			'points_left'=>0,
			'freeze'=>0,
		);
		if($user_id <= 0){
			return $integral;
		}
		
		$user_points = D('UserAccount')->where("user_id = '$user_id'")->getField('points_left');  //查询用户可用积分
		if(!$user_points){
			return $integral;
		}
		//获取被冻结的积分
		$freeze = D('UserPointFreeze')->getUserFreezeSum($user_id);
		
		$integral = array(
			'user_points'=>($user_points + $freeze > 0 ? $user_points + $freeze : 0),
			'points_left'=>$user_points,
			'freeze'=>$freeze,
		);
        return $integral;
    }
	
	/*
	*	处理积分商城商品的详情信息
	*	@Author Lemonice
	*	@param  array $row 商品详情
	*	@return array
	*/
	public function infoFormat($row = array()){
		if(empty($row)){
			return $row;
		}
		$row['shop_price'] = sprintf("%d",$row['shop_price']);
		$row['goods_name'] = $row['goods_name'];
		$act_id = $this->_checkPackage($row['goods_id']);
		
		if($act_id <= 0){ //如果不是套装                  
			$goods_attr = $this->_getGoodsAttr($row['goods_id']);
			$row['goods_type'] = 'goods';
			$row['is_package'] = 0;
			$row['guide'] = isset($goods_attr[9][0]) ? trim($goods_attr[9][0]) : '';
			if($goods_attr){                       
				$row['weight'] = isset($goods_attr[7][0])?trim($goods_attr[7][0]):"";
				$effect_info = isset($goods_attr[8][0])?preg_split("/，|；|;|,/",$goods_attr[8][0]):array();
				if($effect_info){
					foreach ($effect_info as $a => $e){                             
						$effect_info[$a] = str_replace(array('.','。'),'',$e);                               
					}                           
				}
				if(count($effect_info) > 4){
					$effect_info = array_slice($effect_info,0,4);
				}
				$row['effect'] = $effect_info;
			}else{
				$row['weight'] = '';
				$row['effect'] = array();
			}
		}else{
			$row['goods_type'] = 'package_goods';
			$row['is_package'] = 1;
			$goods_attr = $this->_getGoodsAttr($row['goods_id']);
			$row['guide'] = isset($goods_attr[9][0]) ? trim($goods_attr[9][0]) : '';
			if($goods_attr){                       
				$row['weight'] = isset($goods_attr[7][0])?trim($goods_attr[7][0]):"";
				$effect_info = isset($goods_attr[8][0])?preg_split("/，|；|;|,/",$goods_attr[8][0]):array();
				if($effect_info){
					foreach ($effect_info as $a => $e){                             
						$effect_info[$a] = str_replace(array('.','。'),'',$e);                               
					}                           
				}
				if(count($effect_info) > 4){
					$effect_info = array_slice($effect_info,0,4);
				}
				$row['effect'] = $effect_info;
			}else{
				$row['weight'] = '';
				$row['effect'] = array();
			}
			$row['package_id'] = $act_id;
			$package_info = $this->_getPackageInfo($row['package_id']);
			if($package_info){
				foreach ($package_info as $key =>$val){
					$package_info[$key]['goods_name'] = $val['goods_name'];
				}
			}
			if(count($package_info)>4){
				$package_info = array_slice($package_info,0,4);
			}
			$row['package_goods'] = $package_info;
		}
		
		$goods_img = $this->_getGoodsImg($row['goods_id']);
		if(!empty($goods_img)){
			$row['image'] = $this->makeThumbImgPath($goods_img);
		}else{
			$row['image'] = $this->makeThumbImgPath(array(0=>array('img_url'=>'no_picture.gif','img_desc'=>'暂无图片')));
		}
		return $row;
	}
	
	/*
	*	检查是否为套装
	*	@Author Lemonice
	*	@return act_id
	*/
	private function _checkPackage($goods_id = 0) {
		if($goods_id <= 0){
			return 0;
		}
		$GoodsModel = D('GoodsCenter');
		$GoodsModel->alias(' AS g')->join("__GOODS_ACTIVITY__ AS ga ON g.goods_id=ga.goods_id",'left');
		$act_id = $GoodsModel->where("g.is_on_sale = 1 AND ga.goods_id = g.goods_id AND ga.goods_id = '$goods_id'")->getField('ga.act_id');
		return $act_id ? $act_id : 0;
    }
	
	/*
	*	获取对应商品的属性
	*	@Author Lemonice
	*	@param int $goods_id 商品ID
	*	@return array
	*/
	private function _getGoodsAttr($goods_id) {
		$GoodsAttrModel = D('GoodsAttrCenter');
		$rows = $GoodsAttrModel->field('attr_id,attr_value')->where("goods_id = '$goods_id'")->select();
		
        if(empty($rows)){
			return array();
		}
        $attr = $this->_getAttrInfo();
		
        $goods_attr = array();
        foreach ($rows as $k => $row){
            if(isset($attr[$row['attr_id']])){
                $goods_attr[$row['attr_id']][] = $row['attr_value'];
            }          
        }
        return $goods_attr;
    }
	
	/*
	*	获取所有商品的属性名称
	*	@Author Lemonice
	*	@return array
	*/
	private function _getAttrInfo() {
		$AttributeModel = D('AttributeCenter');
		$rows = $AttributeModel->field('attr_id,attr_name')->order('attr_id')->select();
        $attr_arr = array();
        if($rows) {
            foreach ($rows as $k => $row) {
                $attr_arr[$row['attr_id']] = $row['attr_name'];
            }
        }
        return $attr_arr;
    }
	
	/*
	*	获取套装详情
	*	@Author Lemonice
	*	@param int $package_id 套装ID
	*	@return array
	*/
	private function _getPackageInfo($package_id) {
		$PackageGoodsModel = D('PackageGoodsCenter');
		$PackageGoodsModel->alias(' AS pg')->join("__GOODS__ AS g ON pg.goods_id=g.goods_id",'left')->join("__GOODS_ATTR__ AS ga ON pg.goods_id=ga.goods_id",'left');
		$rows = $PackageGoodsModel->field('pg.goods_number,ga.attr_id,ga.attr_value,g.goods_name,g.goods_id,g.goods_sn,g.shop_price,g.market_price')->where("pg.package_id=".$package_id." AND attr_id = 7")->order('g.goods_id')->select();
        return $rows;
    }
	
	/*
	*	获取对应商品的图片
	*	@Author Lemonice
	*	@param int $goods_id 商品ID
	*	@return array
	*/
	private function _getGoodsImg($goods_id) {
		$rows = D('GoodsGalleryCenter')->field('img_url,img_desc')->where("goods_id=".$goods_id)->select();
        return $rows;
    }
	
	/*
	*	获取对应商品的图片
	*	@Author Lemonice
	*	@param array $img 商品图片，多张图
	*	@return string
	*/
	private function makeThumbImgPath($img) {
		if(empty($img)){
			return $img;
		}
		foreach($img as $key=>$value){
			$img[$key]['img_url'] = $this->img_host."/thumb/".$value['img_url'];
		}
        return $img;
    }
}
