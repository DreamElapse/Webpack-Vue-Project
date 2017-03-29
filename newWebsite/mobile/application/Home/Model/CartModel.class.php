<?php

/**
 * ====================================
 * 购物车模型
 * ====================================
 * Author: 9006765
 * Date: 2016-06-28 14:30
 * ====================================
 * File: CartModel.class.php
 * ====================================
 */

namespace Home\Model;
use Common\Model\CommonModel;
use Common\Extend\Time;
class CartModel extends CommonModel{
	
	/*
	*	清除购物车所有商品勾选, 删除对应商品 - 文章页面支付
	*	@Author 9009123 (Lemonice)
	*	@return true or false
	*/
	public function specialCleanCart($goods_id = '',$user_id = 0){
		if($goods_id == ''){
			return false;
		}
		$where = array();
		if($user_id <= 0){
			$where[] = 'session_id = "'.session_id().'"';
		}else{
			$where[] = '(user_id = '.$user_id.' or session_id = "'.session_id().'")' ;
		}
		
		$act_list = M('goods_activity')->field('act_id')->where("goods_id IN($goods_id)")->select();
		$act_id = array();
		if(!empty($act_list)){
			foreach($act_list as $value){
				$act_id[] = $value['act_id'];
			}
		}
		if(!empty($act_id)){
			$act_id = implode(',',$act_id);
			$where[] = "((goods_id IN($goods_id) and extension_code = '') OR (goods_id IN($act_id) and extension_code = 'package_buy') OR (parent_id IN($act_id) and extension_code = 'package_goods'))";  
		}else{
			$where[] = "goods_id IN($goods_id) and extension_code = ''";
		}
		return $this->where(implode(' and ',$where))->delete();
	}

    /**
     * 购物车添加数据
     * @param $data 购物车商品
     */
    public function dataToCart($data){
        $insert_ids = array();
        foreach($data as  $k => $v){
            if((!isset($v['session_id']) || empty($v['session_id']))){
                $data[$k]['session_id'] = session_id();
            }
            $data = $this->create($v);
            $insert_id = $this->add();
            if($insert_id===false || !is_numeric($insert_id)){
                if(!empty($insert_ids)){
                    foreach($insert_ids as $v){
                        $this->execute('delete from __TABLE__   where rec_id = '.$v);
                    }
                }
               return false;
            }
            $insert_ids[] = $insert_id;
            if($v['extension_code'] != 'package_goods'){
                session('cart_select.'.$insert_id,$insert_id);
            }
        }
        return true;
    }


    /**
     * 转成购物车数据表数据
     * @param $all_cart_data
     * @param $add_goods_number
     * @param int $goods_info
     * @param int $package_info
     * @param array $act
     * @return array
     */
    function changeDataToCart($add_goods_number,$goods_info=array(),$package_info=array(),$user_id=0){  //goods_price,goods_number
        $cart_data = $data = $g_data =  array();
        $session_id = session_id();
        if(!empty($goods_info)) {
            $data['goods_id'] = $goods_info['goods_id'];
            $data['goods_sn'] = $goods_info['goods_sn'];
            $data['goods_name']  = $goods_info['goods_name'];
            $data['market_price'] = $goods_info['market_price'];
            $data['goods_price'] =  $goods_info['shop_price'];
            $data['is_real'] = $goods_info['is_real'];
            $data['extension_code'] = '';
            $data['parent_id'] = 0;
        }else if($package_info){
            $data['goods_id'] = $package_info['act_id'];
            $data['goods_sn'] = '';
            $data['goods_name']  = $package_info['goods_name'];
            $data['market_price'] = $package_info['market_price'];
            $data['goods_price'] = $package_info['package_price'];
            $data['is_real'] = 1;
            $data['extension_code'] = 'package_buy';
            $data['parent_id'] = 0;
        }else{
           return false;
        }
        $data['user_id'] =  $user_id;
        $data['session_id'] = $session_id;
        $data['is_real'] = 1;
        $data['is_gift'] = 0;
        $data['goods_number'] = $add_goods_number;
        $cart_data[] = $data;
        if($data['extension_code'] == 'package_buy'){
            foreach($package_info['package_goods'] as $k => $v){
                $g_data['goods_id'] = $v['goods_id'];
                $g_data['goods_sn'] = $v['goods_sn'];
                $g_data['goods_name']  = $v['goods_name'];
                $g_data['market_price'] = $v['market_price'];
                $g_data['goods_price'] =  $v['shop_price'];
                $g_data['is_real'] = $v['is_real'];
                $g_data['extension_code'] = 'package_goods';
                $g_data['parent_id'] = $package_info['act_id'];
                $g_data['user_id'] =  $user_id;
                $g_data['session_id'] = $session_id;
                $g_data['is_real'] = 1;
                $g_data['is_gift'] = 0;
                $g_data['goods_number'] = $v['goods_number'];
                $g_data['goods_price']  = $v['shop_price'];
                $cart_data[] = $g_data;
            }
        }
        return $cart_data;
    }

    /**
     * 加商品数
     * @param $rec_id
     * @param $goods_number
     */
    public function updateToCart($rec_id,$goods_number,$user_id=0){
       return $this->execute('update __TABLE__ set goods_number = goods_number+'.$goods_number.',session_id="'.session_id().'",user_id = '.$user_id.'  where rec_id = '.$rec_id);
    }

    /**
     * 购物车关联用户
     * @param $user_id
     * @return false|int
     */
    public  function updateCartUserInfo($user_id){
          $this->checkCartDoubleRecord();
         return   $this->execute("update __TABLE__ set user_id =  ".$user_id ." where  session_id = '".session_id()."'");

    }

    /**
     * 登陆前与登陆后,重复商品记录合并为一条记录
     *
     */
    public function checkCartDoubleRecord(){
          $user_id = session('user_id');
          if($user_id>0){
             $user_data =   $this->where("user_id = ".$user_id ."  and  extension_code != 'package_goods'") ->select();
              if(!empty($user_data)){
                  $session_data =  $this->where("user_id = 0  and session_id = '".session_id()."' and  extension_code != 'package_goods'") ->select();
                  if(!empty($session_data)){
                        foreach($user_data as $k => $v){
                            foreach($session_data as $kk => $vv){
                                if($vv['goods_id']==$v['goods_id'] && $vv['extension_code'] == $v['extension_code'] && $vv['is_gift'] == $v['is_gift']){
                                    if($vv['extension_code'] == 'package_buy'){
                                        if(isset($_SESSION['cart_select'][$vv['rec_id']])) unset($_SESSION['cart_select'][$vv['rec_id']]);
                                        $sql = " delete from __TABLE__ where rec_id = $vv[rec_id] or (parent_id= $vv[goods_id] and user_id = 0 and session_id ='".session_id()."')";
                                        $this->execute($sql);
                                    }else{
                                        if(isset($_SESSION['cart_select'][$vv['rec_id']])) unset($_SESSION['cart_select'][$vv['rec_id']]);
                                        $sql = " delete from __TABLE__ where rec_id = $vv[rec_id]";
                                        $this->execute($sql);
                                    }
                                    $sql = " update __TABLE__ set goods_number = goods_number + ".$vv['goods_number']." where rec_id = $v[rec_id] ";
                                    $this->execute($sql);
                                    unset($session_data[$kk]);
                                }
                            }
                        }
                  }
              }
          }
    }


    /**
     * session 临时数据转为购物车数据
     * @param $data
     * @return array|bool
     */
    public function tempGift($data=array(),$user_id = 0,$session_id=0,$temp_gift=array()){
        $gift =$value =  array();
        $goods_id = 0 ;
        if(empty($temp_gift)){
            $temp_gift = session('temp_gift');
        }
        if(empty($data)){
            $data = $temp_gift;
        }
        if(empty($session_id)){
            $session_id = session_id();
        }
        if(!is_null($data)){
            foreach($data as $k =>$v){
                $value = array();
                $value['rec_id'] = 't_'.$k;
                $value['user_id'] = $user_id;
                $value['session_id'] = $session_id;
                $value['is_gift'] = $v[4];
                $value['parent_id']  = 0;
                $value['goods_id'] = $v[0];
                $value['goods_price'] = $v[1];
                $value['goods_number'] = $v[2];
                $value['extension_code'] = $v[3];
                if(isset($v[5])){
                    $value['send_num'] = $v[5];
                }else{
                    $value['send_num'] = 0;
                }
                if(isset($v[6])){
                    $value['buy_one'] = $v[6];
                }
                if(isset($v['m'])){
                     $value['min_amount'] = $v['m'];
                }

                if($v[3] == 'package_buy'){
                    $goods_id = M('goods_activity')->where(array('act_id'=>$value['goods_id']))->getField('goods_id');
                    $goods_info = D('goods')->where(array('goods_id'=>$goods_id))->find();
                    $package = $this->getPackageInfo(0,0,$value['goods_id']);//获取套装信息 $v[0] == $package_id
                    $value['goods_name'] = $package['act_name'];
                    $value['market_price'] = $goods_info['market_price'];
                    $gift[$k] = $value;
                    foreach($package['package_goods'] as $kk => $vv){
                        $value['rec_id'] = $vv['goods_id'].'_'.$k;
                        $value['parent_id']  = $v[0];
                        $value['goods_id'] = $vv['goods_id'];
                        $value['goods_sn'] = $vv['goods_sn'];
                        $value['goods_price'] = $vv['shop_price'];
                        $value['goods_number'] = $vv['goods_number'];
                        $value['extension_code'] = 'package_goods';
                        $value['goods_name'] = $vv['goods_name'];
                        $gift[$vv['goods_id'].'_'.$k] = $value;
                    }
                }
                if(empty($v[3])){ //单品
                    $value['parent_id'] = 0;
                    $goods_info = D('goods')->where(array('goods_id'=>$value['goods_id']))->find();
                    $value['goods_sn'] = $goods_info['goods_sn'];
                    $value['goods_name'] = $goods_info['goods_name'];
                    $value['market_price'] = $goods_info['market_price'];
                    $gift[$k] = $value;
                }
            }
            return $gift;
        }else{
            return false;
        }
    }

    /**
     * 活动商品入到session
     * @param $data 加入活动商品数据
     * @return bool
     */
    function addTempGift($data){
        $k = count(session('temp_gift'));
        if(empty($k)){
            $k = 0;
        }
        session('temp_gift.'.$k,array($data['goods_id'],$data['goods_price'],$data['goods_number'],$data['extension_code'],$data['is_gift'],'m'=>$data['min_amount']));
        session('cart_select.t_'.$k,'t_'.$k);
        return $k;
    }


    /**
     * 判断活动商品是已加入
     * @param $goods_id
     * @param $is_package
     * @param $act_id
     * @return int|string
     */
    function isInTempGift($goods_id,$is_package,$act_id){
        if(!isset($_SESSION['temp_gift'])|| empty($_SESSION['temp_gift']) || is_null(session('temp_gift'))){
            return '';
        }else{
            $temp_gift = session('temp_gift');
            foreach( $temp_gift as $k => $v){
                if($is_package && $v[3]=='package_buy'&& $v[0]==$goods_id && $v[4] == $act_id){
                    return $k;
                }else if(!$is_package && empty($v[3])&& $v[0]==$goods_id && $v[4] == $act_id){
                    return $k;
                }
            }
        }
        return '';
    }


    /**
     * 删除活动商品 $k==null 时清空活动商品
     * @param $k 活动购物车对应每个商品记录键值
     */
    function delTempGift($k){
        if(is_null($k)){
            session('temp_gift',null);
            foreach(session('cart_select') as $n => $v){
                 if(!is_numeric($v)){
                    session('cart_select.'.$n,null);
                 }
            }
        }else{
            session('temp_gift.'.$k,null);
            session('cart_select.'.'t_'.$k,null);
        }
    }

    /**
     * 获取购物车数据 包括优惠活动数据
     * @param $where
     * @param bool $only_gift  是否只取活动商品 false为否
     * @param bool $only_select  是否只取勾选的
     * @param array $session_temp_gift 活动购物车数据
     * @return array|mixed
     *
     */
    function cartData($where, $only_gift = false,$only_select=true,$session_temp_gift=array()){
        if(isset($where['session_id'])){
            unset($where['session_id']);
        }
        $map['session_id'] = session_id();
        if(!empty($where['user_id']) && $where['user_id'] > 0){
            $map['user_id'] = $where['user_id'];
            $map['_logic'] = "OR";
            unset($where['user_id']);
        }else{
            unset($where['user_id']);
        }
        $where['_complex'] = $map;

        //是否只返回活动商品
		if($only_gift == false){  //返回购物车所有商品
			$all_cart_data = $this->where($where)->select();
		}else{  //只返回活动商品
			$all_cart_data = array();
		}
        $temp_gift = $this->tempGift(array(),$where['user_id'],$map['session_id'],$session_temp_gift);
        if($temp_gift){
            if(isset($where['rec_id']) && !empty($where['rec_id'][1]) ){
                foreach($temp_gift as $k => $v){
                    if(!in_array($v['rec_id'],$where['rec_id'][1])){
                        unset($temp_gift[$k]);
                    }
                }
            }
            if(is_null($all_cart_data)){
                $all_cart_data = array();
            }
            $all_cart_data = array_merge($all_cart_data,$temp_gift); //非活动商品 活动商品合并
        }
        if($only_select==true && !empty($all_cart_data)){
            foreach($sub_cart_data = &$all_cart_data as $k => $v){
                if(in_array($v['rec_id'],session('cart_select')) || ($v['extension_code'] == 'package_goods')){
                    continue;
                }else{
                    unset($all_cart_data[$k]);
                    if($v['extension_code'] == 'package_buy'){
                        foreach($sub_cart_data as $_k=>$_v){
                             if($_v['extension_code'] == 'package_goods' && $_v['parent_id']==$v['goods_id'] && $_v['is_gift']==$v['is_gift']){
                                 unset($sub_cart_data[$_k]);
                             }
                        }
                    }
                }
            }
            $all_cart_data = $sub_cart_data;
        }
        return  $all_cart_data;
    }


    /**
     * 删除购物车表的一条记录
     * @param $rec_id 购物车商品记录序号
     * @param int $user_id 用户id
     */
    public function delOneCartGoods($rec_id,$user_id=0){
        $where = '';
        if(empty($user_id)){
            $where = ' and  session_id = "'.session_id().'"';
        }else{
            $where = ' and  (user_id = '.$user_id.' or session_id = "'.session_id().'")' ;
        }
        $goods = $this->field('goods_id,extension_code,is_gift')->where('rec_id = '.$rec_id)->find();
        if($goods['extension_code'] == 'package_buy'){
            return  $this->execute('delete from __TABLE__ where (rec_id='.$rec_id.' or (parent_id='.$goods["goods_id"].' and extension_code="package_goods" and is_gift='.$goods['is_gift'].' ))  '.$where );
        }else{
            return $this->execute('delete from __TABLE__ where rec_id='.$rec_id.$where );
        }
    }

    /**
     * 购物车商品加图片
     * @param $all_cart_data 购物车商品数据
     * @return mixed
     */
    function addCartGoodsThumb($all_cart_data){

        $g_ids = $p_ids = array();
        foreach($all_cart_data as $k => $v) {
            if ($v['extension_code'] == 'package_buy') {
                $p_ids[] = $v['goods_id'];
            } else if (empty($v['extension_code'])) {
                $g_ids[] = $v['goods_id'];
            }
        }
        if(!empty($g_ids)){
            $goods_thumb = $this->getGoodsThumb($g_ids);
        }
        if(!empty($p_ids)){
            $package_thumb = $this->getPackageGoodsThumb($p_ids);
        }
        foreach($all_cart_data as $k => $v){
            if(empty($v['extension_code'])){
                if(isset($goods_thumb[$v['goods_id']])){
                    $all_cart_data[$k]['thumb'] = C('domain_source.img_domain').$goods_thumb[$v['goods_id']];
                }else{
                    $all_cart_data[$k]['thumb'] = '';//无图片
                }
            }
            if($v['extension_code'] == 'package_buy'){
                if(isset($package_thumb[$v['goods_id']])){
                    $all_cart_data[$k]['thumb'] = C('domain_source.img_domain').$package_thumb[$v['goods_id']];
                }else{
                    $all_cart_data[$k]['thumb'] = '';
                }
            }
        }
        return $all_cart_data;
    }


    /**
     * 商品活动,活动原价格, 折前价
     * @param $act  活动信息
     * @param $add_goods_id 要添加的商品id
     * @param int $is_package 是否是套装
     * @return float|int
     */
    function goodsActPrice($act,$add_goods_id,$is_package=0){
        $goods_in_act = false;
        $goods_act_price = 0;
        if($is_package==0){
            if(isset($act['gift'])){
                $gift = unserialize($act['gift']);
                foreach($gift as $k => $v){
                    if($v['id'] == $add_goods_id){
                        $goods_in_act = true;
                        if(isset($v['price'])){
                            $goods_act_price = $v['price'];
                        }else{
                            $goods_act_price = 0;
                        }
                    }
                }
            }else {
                return false;
            }
        }else {
            if(isset($act['gift_package'])){
                $gift_package = unserialize($act['gift_package']);
                foreach($gift_package as $k => $v){
                    if($v['id'] == $add_goods_id){
                        $goods_in_act = true;
                        $goods_act_price = $v['price'];
                    }
                }
            } else {
                return false;
            }
        }
        if($goods_in_act === false){
            return false;
        }
        return $goods_act_price;
    }

    /**
     * 可享受折扣
     * @param $act 活动信息
     * @param $cart_data  购物车数据
     * @param int $goods_id 要添加的商品id
     * @param int $is_package 是否是套装
     * @param int $add_goods_number 要添加商品数量
     * @return bool|float
     */
    function getDiscount($act,$cart_data,$goods_id=0,$is_package=0,$add_goods_number=0){
       if(in_array($act['act_type'],array(4,6,5))){ //4  ,6享受计件折扣或减免（受订购数量影响）//5享受折扣选购（受订购商品金额限制）
        $all_goods_num  = $total_price = 0;
        foreach($cart_data as $k => $v){
            if(($v['extension_code'] !='package_goods') && ($v['is_gift'] == $act['act_id'])){
                $all_goods_num += $v['goods_number'];
                if(empty($v['extension_code'])){
                    $is_package = 0;
                }else{
                    $is_package = 1;
                }
                $total_price += $this->goodsActPrice($act,$v['goods_id'],$is_package); //计算商品总原价
            }
        }
        $now_goods_num = $all_goods_num + $add_goods_number;
        if(!empty($goods_id) && !empty($add_goods_number)){
            $total_price += $this->goodsActPrice($act,$goods_id,$is_package);//新加入商品原价
        }
        $discount = 0;
        if(!empty($act['act_type_ext'])){
                $act_type_ext = explode(',',$act['act_type_ext']);
                foreach($act_type_ext as $k =>$vv){
                    $v = explode('|',$vv);//var_dump($v);
                    if(isset($v[0]) && isset($v[1])){
                        if($act['act_type'] == 5 && $v[0] <= $total_price){
                            if($discount>0 && $discount > $v[1]){
                                $discount = $v[1];
                            }else if($discount == 0){
                                $discount = $v[1];
                            }
                        }
                        else if ($v[0] <= $now_goods_num){
                            if($discount>0 && $discount > $v[1]){
                                $discount = $v[1];
                            }else if($discount == 0){
                                $discount = $v[1];
                            }
                        }
                    }
                }
            }else{
              return false;
            }
            return $discount/10;
        }else{
            return false;
        }
    }

    /**
     * 统计购物车内商品总价，数量
     * @param $cart_data 购物车数据
     * @return array
     */
    public function cartFee($cart_data){
        $g_price = $cate_id =$total_amount = 0;
        $get_gift_price = array('cate_id'=>array(),'package'=>0,'is_gift_num'=>array());
        $gift_total_price = $act_minus_arr =  array();
        $activity_model = D('favourable_activity');
        foreach($cart_data as $k=>$v){
            $g_price = $v['goods_price'] * $v['goods_number'];
            if($v['is_gift'] == 0){
                if($v['extension_code'] == 'package_buy'){
                    $total_amount += $g_price;
                    $goods_id = M('goods_activity')->where(array('act_id'=>$v['goods_id']))->getField('goods_id');
                    if(!empty($goods_id)){
                        $cate_id   = D('goods')->where(' goods_id = '.$goods_id)->getField('cat_id');
                        $get_gift_price['cate_id'][$cate_id] += $g_price;
                    }
                    $get_gift_price['package']['total'] += $g_price;
                    $get_gift_price['total'] += $g_price;
                }else if(empty($v['extension_code'])){
                    $total_amount += $g_price;
                    $cate_id = D('goods')->where(' goods_id = '.$v['goods_id'])->getField('cat_id');
                    $get_gift_price['cate_id'][$cate_id] += $g_price;
                    $get_gift_price['total'] += $g_price;
                }
            }else{ //活动商品处理
                $gift_total_price[$v['is_gift']] += $g_price;
                $act = D('favourable_activity')->where('act_id = '.$v['is_gift'])->find(); //if($v['is_gift']==420){var_dump($act);}
                if($v['extension_code'] == 'package_buy'){
                    $total_amount += $g_price;
                    if($act['is_join_amount'] == 1){
                        $goods_id = M('goods_activity')->where(array('act_id'=>$v['goods_id']))->getField('goods_id');
                        if(!empty($goods_id)){
                            $cate_id = D('goods')->where(' goods_id = '.$goods_id)->getField('cat_id');
                            $get_gift_price['cate_id'][$cate_id] += $g_price;
                        }
                        $get_gift_price['package']['total'] += $g_price;
                        $get_gift_price['total'] += $g_price;
                    }
                    if(isset($v['send_num']) && !empty($v['send_num'])){ //买一送一减送商品价
                        $send_one_goods_price = $v['goods_price'] * $v['send_num'];
                        $total_amount -= $send_one_goods_price;
                        if($act['is_join_amount'] == 1){
                            $get_gift_price['total'] -= $send_one_goods_price;
                        }
                    }
                    if(!isset($act_minus_arr[$v['is_gift']])){ //满立减
                         $is_mlj = $this->is_mlj($act,$cart_data);
                        if($is_mlj !== false){
                            $act_minus_arr[$act['act_id']] = $is_mlj;
                            $total_amount -= $is_mlj;
                            if($act['is_join_amount'] == 1){
                                $get_gift_price['total'] -= $is_mlj;
                            }
                        }
                    }
                    if(isset($is_gift_num[$v['is_gift']]['all_goods'])){//统计商品数量
                        $is_gift_num[$v['is_gift']]['all_goods'] += $v['goods_number'];
                        $is_gift_num[$v['is_gift']]['goods'][$v['goods_id']] = $v['goods_number'];
                    }else{
                        $is_gift_num[$v['is_gift']]['all_goods'] = $v['goods_number'];
                        $is_gift_num[$v['is_gift']]['goods'][$v['goods_id']] = $v['goods_number'];
                    }
                }else if(empty($v['extension_code'])){  //单品处理
                    $total_amount += $g_price;
                    if($act['is_join_amount'] == 1){
                        $cate_id   =  D('goods')->where(' goods_id = '.$v['goods_id'])->getField('cat_id');
                        $get_gift_price['cate_id'][$cate_id] += $g_price;
                        $get_gift_price['total'] += $g_price;

                    }
                    if(isset($v['send_num']) && !empty($v['send_num'])){ //买一送一减送商品价
                        $send_one_goods_price = $v['goods_price'] * $v['send_num'];
                        $total_amount -= $send_one_goods_price;
                        if($act['is_join_amount'] == 1){
                            $get_gift_price['total'] -= $send_one_goods_price;
                        }
                    }
                    if(!isset($act_minus_arr[$v['is_gift']])){ //满立减
                        $is_mlj = $this->is_mlj($act,$cart_data);
                        if($is_mlj !== false){
                            $act_minus_arr[$act['act_id']] = $is_mlj;
                            $total_amount -= $is_mlj;
                            if($act['is_join_amount'] == 1){
                                $get_gift_price['total']-= $is_mlj;
                            }
                        }
                    }
                    if(isset($is_gift_num[$v['is_gift']]['all_goods'])){  //统计商品数量
                        $is_gift_num[$v['is_gift']]['all_goods'] += $v['goods_number'];
                        $is_gift_num[$v['is_gift']]['goods'][$v['goods_id']] = $v['goods_number'];
                    }else{
                        $is_gift_num[$v['is_gift']]['all_goods'] = $v['goods_number'];
                        $is_gift_num[$v['is_gift']]['goods'][$v['goods_id']] = $v['goods_number'];
                    }
                }
            }
        }
        return array(
                      'cart_data' => empty($cart_data)? array():$cart_data,      //商品列表
                      'total_amount' => $total_amount, //买购商品总额
                      'get_gift_price' => $get_gift_price, //买购商品可参加活动的总额
                      'is_gift_num' => $is_gift_num,  //买购商品可参加活动的总数量
                      'gift_total_price' => $gift_total_price,//各活动商品总价明细
                      'act_minus_arr' => $act_minus_arr //满立减数组
                 );
    }

    /**
     * 是不是换购活动
     * @param $act
     * @param $goods_id
     * @param $is_package
     */
/*    function is_exchange_buy($act_id,$goods_id,$is_package=0){
          $activity_model = new FavourableActivityModel();
          $act = $activity_model -> getActivityById($act_id);
          $act = $activity_model->formatActivityGoodsData($act);
          if($is_package){
              foreach($act['gift_package'] as $k => $v){
                     if($v['id']==$goods_id){
                         if($v['price']>0 && ($v['pmin']>0 || $act['min_amount']>0)){
                             return 1;
                         }
                     }
              }
          }else{
              foreach($act['gift'] as $k => $v){
                      if($v['id']==$goods_id){
                         if($v['price']>0 && ($v['pmin']>0 || $act['min_amount']>0)){
                             return 1;
                         }
                      }
              }
          }
          return 0;
    }*/

    /**
     * 判断买一送一是否可以赠一件
     * @param $act 活动信息
     * @param $cate_data 购物车数据
     * @return bool 或 购物车列表键名
     */
    function isBuyOneSendOne($act,$cart_data){
             if($act['act_type']==1){
                      foreach($cart_data as $k => $v){
                           if($v['extension_code'] != 'package_goods' && $v['is_gift'] == $act['act_id']){
                                 if($v['buy_one'] == true){
                                     return  $k;
                                 }else{
                                     return null;
                                 }
                           }
                      }
             }else{
                 return false;
             }
    }


    /**
     * 满立减
     * @param $act 活动信息
     * @param $cart_data 购物车数据
     * @param $add_goods_id 要添加的商品id
     * @param $is_package 添加的商品是不是套装
     * @param $add_goods_number 添加的商品数
     */
    function is_mlj($act,$cart_data,$add_goods_id=0,$is_package=0,$add_goods_number=0){
        if($act['act_type'] == 13){
                 $act_total_price = 0;
                 foreach($cart_data as $k=>$v){
                       if($v['is_gift'] == $v['act_id']){
                            $act_total_price += $v['goods_price'] * $v['goods_number'];
                       }
                 }
                if($add_goods_number!=0 && $add_goods_id!=0){
                    $goods_act_price = $this->goodsActPrice($act,$add_goods_id,$is_package);
                    $act_total_price = $act_total_price + ($goods_act_price * $add_goods_number);
                }
                if (!empty($act['act_type_ext'])) {
                        if (strpos($act['act_type_ext'], ',')) {
                            $max = $min = $value = 0;
                            $buy_rule = explode(',', $act['act_type_ext']);
                            foreach ($buy_rule as $k => $v) {
                                $buy_rule[$k] = explode('|', $v);
                                if (($max < $buy_rule[$k][0]) && ($buy_rule[$k][0] <= $act_total_price)){
                                     $max = $buy_rule[$k][0];
                                     $value = $buy_rule[$k][1];
                                }
                            }
                            return $value;
                        } else {
                            $buy_rule = explode('|', $act['act_type_ext']);
                            if($buy_rule[0]<=$act_total_price){
                                 return $buy_rule[1];
                            }else{
                                 return 0;
                            }
                        }
                }

        }else{
               return false;
        }
    }



    /**
     * 根据商品goods_id获取关联套装的信息
     * @param $goods_id 商品id
     * @param int $time 指定查询时间
     * @param int $act_id 活动id
     * @return bool|mixed
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
        }
        //$package_info['start_time'] = date('Y-m-d H:i', $package_info['start_time']);
        //$package_info['end_time'] = date('Y-m-d H:i', $package_info['end_time']);
        $row = unserialize($package_info['ext_info']);
        unset($package_info['ext_info']);
        if ($row){
            foreach ($row as $key=>$val){
                $package_info[$key] = $val;
            }
        }
        //获取套装子商品及子商品属性
        $package_goods = M('PackageGoods')
            ->field('pg.package_id, pg.goods_id, pg.goods_number, g.goods_sn, g.goods_name,g.shop_price, g.market_price, g.goods_thumb, g.is_real,g.goods_number as kc_goods_number,g.is_on_sale')
            ->alias('pg')
            ->join("LEFT JOIN __GOODS__ AS g ON g.goods_id = pg.goods_id")
            ->where("pg.package_id=".$package_info['act_id'])
            ->select();
        if(empty($package_goods)){
            return false;
        }
        $package_info['package_goods'] = $package_goods;
        return $package_info;
    }



    /**
     * $goods_id 商品id
     * 获取指定商品数据
     */
    public function getGoodsData($goods_id){

        return  D('goods') -> field('goods_id,goods_name,goods_number,goods_sn,is_on_sale,market_price,shop_price') -> where('goods_id = '.$goods_id) ->find();

    }


    /**
     * 获取商品图
     * @param $goods_ids 商品id组
     */
    public function getGoodsThumb($goods_ids){
        $return = array();
        $all_goods_thumb =  D('goods')->field('goods_thumb,goods_id')->where('goods_id in( '.implode(',',$goods_ids).')') -> select();
        foreach($all_goods_thumb as $k => $v){
            $return[$v['goods_id']] = $v['goods_thumb'];
        }
        return $return;
    }

    /**
     * 获取套装图
     * @param $package_ids 套装id组
     * @return array
     */
    public function getPackageGoodsThumb($package_ids){
        $return = array();
        $all_goods_thumb =  D('goods')->field('goods_thumb,act_id')->alias('g')->join('LEFT JOIN __GOODS_ACTIVITY__ AS ga  ON g.goods_id=ga.goods_id')->where('act_id in ('.implode(',',$package_ids).')') -> select();
        foreach($all_goods_thumb as $k => $v){
            $return[$v['act_id']] = $v['goods_thumb'];
        }
        return $return;
    }

    /**
     * 获取有效期内的所有活动
     * @return mixed
     */
   public function getAllActivity(){
        $now = Time::gmTime();
        $activity = D('favourable_activity')
                    ->field('`act_id`,`act_name`,`start_time` ,`end_time`,`user_rank` ,`act_range` ,`act_range_ext`,
                                `min_amount` ,`max_amount`,`act_type` ,`act_type_ext`,`gift`,`gift_package`,
                                `is_join_amount`,`gift_range`,`gift_range_price`,`level_type`,`conflict_act`')
                   ->where('start_time<='.$now .' and end_time>= '.$now )
                   ->select();
        return $activity;
    }


    /**
     * 转活动数据为非序列化
     * @param $act 活动信息
     * @return mixed
     */
   public function formatActivityGoodsData($act){
        $act['gift'] = unserialize($act['gift']);
        if(!empty($act['gift'])){
            foreach($act['gift'] as $k => $v){
                $act['gift'][$v['id']] = $v;
                unset($act['gift'][$k]);
            }
        }

        $act['gift_package'] = unserialize($act['gift_package']);
        if(!empty($act['gift_package'])){
            foreach($act['gift_package'] as $k=>$v){
                $act['gift_package'][$v['id']] = $v;
                unset($act['gift_package'][$k]);
            }
        }
        return $act;
    }


   public function resetTempGift($temp_gift=array()){
       if(empty($temp_gift)){
           $temp_gift = session('temp_gift');
       }
       $_temp_gift = array();
       $n = 0;
       $cart_select = session('cart_select');
       foreach($temp_gift as $kk => $vv){
           session('cart_select.'.'t_'.$kk,null);
           if(!empty($vv)){
               if($cart_select['t_'.$kk] == 't_'.$kk){ //原来选中的
                   session('cart_select.'.'t_'.$n,'t_'.$n);
               }
               $_temp_gift[$n] = $vv;
               $n++;
           }
       }
       session('temp_gift', $_temp_gift);
   }

}
