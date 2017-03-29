<?php
/**
 * ====================================
 * 购物车 控制器
 * ====================================
 * Author: 9006765
 * Date: 2016-06-28 14:32
 * ====================================
 * File: CartController.class.php
 * ====================================
 */


namespace Home\Controller;

use Common\Controller\InitController;
use Home\Model\CartModel;
use Common\Extend\Time;

class CartController extends InitController {

    protected $cart_model;
    protected $activity_model;
    protected $goods_model;
    protected $user_id;
    protected $buy_rule;
    protected $act_info;

    public function __construct(){
        parent::__construct();

        $this->cart_model = new CartModel();
        $this->activity_model = D('favourable_activity');
        $this->goods_model = D('goods');
        $this->user_id = is_null(session('user_id')) ? 0 : session('user_id');
    }

    /**
     * 商品加入购物车
     *
     */
   public function addGoodsToCart(){
      $is_in_cart = $is_on_sale = 0;
      $goods_id = I('post.goods_id',0);
      if(empty($goods_id)){
           $this->error('请指定商品');
      }
      if(!is_numeric($goods_id)){
          $this->error('商品id=='.$goods_id.',无效');
      }
      $act_id = I('post.act_id',0);
      if(!is_numeric($act_id)){
           $this->error('act_id=='.$act_id.',无效');
      }
      $option = I('post.option','');
      $is_package = I('post.is_package',0);
      if(!is_numeric($is_package)){
          $this->error('标识is_package=='.$is_package.'，无效');
      }

      $goods_number = I('post.goods_number',1);
      if(empty($goods_number)){
           $goods_number = 1;
      }
      if(!is_numeric($goods_number)){
          $this->error('商品数goods_number=='.$goods_number.'，无效');
      }
      if($is_package == 0){
            $goods_info = $this->cart_model->getGoodsData($goods_id);//var_dump($goods_info);
            if(empty($goods_info)){
                $this->error('商品缺货下架，抓紧咨询客服抢购吧！');
            }

            if($goods_info['is_on_sale'] == 0){
                 //商品下架
                 $this->error('商品缺货下架，抓紧咨询客服抢购吧！');
            }
            if(CHECK_STOCK && $goods_info['goods_number']<=0){
                 $this->error('商品缺货下架，抓紧咨询客服抢购吧！');
            }
            //不是套装进一步判断是不是套装
            $package_info = $this->cart_model->getPackageInfo($goods_id,0,0);
            if(!empty($package_info)){
                $package_info['market_price'] = $goods_info['market_price'] ;
                $this->check_pack($package_info);
                $is_package = 1;
                $goods_id = $package_info['act_id'];//var_dump($package_info);echo $goods_id;
            }
        }
        if($act_id >0){ //活动

            $this->ActGoodsToCart($goods_id,$is_package,$goods_number,$act_id,$option,$package_info,$goods_info);

        }else {  //非活动
            if($is_package == 1){  //添加套装时
                if(!isset($package_info) || empty($package_info)){
                    $package_info = $this->cart_model->getPackageInfo(0,0,$goods_id); //var_dump($package_info);
                     //检查套装
                    $this->check_pack($package_info);
                    $package_info['market_price'] = $this->goods_model->where(array('goods_id'=>$package_info['goods_id']))->getField('market_price');
                }
                if(empty($this->user_id)){
                    $is_in_cart = $this->cart_model->where(array('goods_id'=>$goods_id,'extension_code'=>'package_buy','session_id'=>session_id(),'is_gift'=>0))->getField('rec_id');
                }else{
                    $is_in_cart = $this->cart_model->where(array('goods_id'=>$goods_id,'extension_code'=>'package_buy','_complex'=>array('user_id'=>$this->user_id,'_logic'=>'or','session_id'=>session_id()),'is_gift'=>0))->getField('rec_id');
                }
            }else{  //添加单品时
                if(empty($this->user_id)){
                    $is_in_cart = $this->cart_model->where(array('goods_id'=>$goods_id,'extension_code'=>'','session_id'=>session_id(),'is_gift'=>0))->getField('rec_id');
                }else{
                    $is_in_cart = $this->cart_model->where(array('goods_id'=>$goods_id,'extension_code'=>'','_complex'=>array('user_id'=>$this->user_id,'_logic'=>'or','session_id'=>session_id()),'is_gift'=>0))->getField('rec_id');
                }
            }
            if(isset($is_in_cart) && $is_in_cart> 0){
                if($option == 'select'){
                    session('cart_select.'.$is_in_cart,$is_in_cart);
                    $this->success('选中成功');
                }else{
                    $r = $this->cart_model->updateToCart($is_in_cart, $goods_number,$this->user_id);
                    if($r){
                        if(!in_array($is_in_cart,array(session('cart_select')))){
                            session('cart_select.'.$is_in_cart,$is_in_cart);
                        }
                        $this->success('商品添加成功');
                    }else{
                        $this->error('商品添加失败');
                    }
                }
            }else{
                if(!isset($goods_info) || (isset($package_info) && !empty($package_info))){
                    $goods_info = array();
                }
                if(!isset($package_info) || empty($package_info)){
                    $package_info = array();
                }
                $data = $this->cart_model->changeDataToCart($goods_number,$goods_info,$package_info,$this->user_id);
                $r = $this->cart_model->dataToCart($data); //加入到购物车
                if($r){
                    $this->success('商品添加成功');
                }else{
                    $this->error('商品添加失败');
                }
            }
        }
   }


    /**
     *
     * 活动商品加入购物车内调方法
     * @param $goods_id 要添加的商品id
     * @param $is_package 是否是套装
     * @param $add_goods_number 添加商品数
     * @param $act_id 活动id
     * @param $option 操作类型，勾选还是活加
     * @param array $package_info 套装信息
     * @param array $goods_info 商品信息
     */
    private function ActGoodsToCart($goods_id,$is_package,$add_goods_number,$act_id,$option,$package_info=array(),$goods_info=array()){
        $now = 0;
        $act = $user_rank = array();
        if($act_id){
            $act = $this->activity_model->where('act_id = '.$act_id)->find();
            if(empty($act)){
                $this->error('未找到对应活动');
            }
            $now = Time::gmTime();
            if(($act['start_time']) > $now){
                $this -> error('活动未开始');
            }
            if(($act['end_time']) < $now){
                $this -> error('活动已结束');
            }
        }else{
            $this->error('未指定活动');
        }

        if(!$goods_id){
            $this->error('缺少商品参数');
        }else{
            if(empty($add_goods_number)){
                $this->error('请提交添加商品的数量');
            }
            if($is_package){
                if(empty($package_info)){
                    $package_info = $this->cart_model->getPackageInfo(0,0,$goods_id);
                    $this->check_pack($package_info);
                    $package_info['market_price'] = $this->goods_model->where(array('goods_id'=>$package_info['goods_id']))->getField('market_price');
                }
            }else{
                if(empty($goods_info)){
                    $goods_info = $this->cart_model->getGoodsData($goods_id);
                    if($goods_info['is_on_sale'] == 0){
                        //商品已下架
                        $this->error('商品缺货下架，抓紧咨询客服抢购吧！');
                    }
                    if(CHECK_STOCK && $goods_info['goods_number']<=0){
                        //商品已售完
                        $this->error('商品缺货下架，抓紧咨询客服抢购吧！');
                    }
                }
            }
        }

        $user_rank = explode(',',$act['user_rank']);
        if(!in_array(0,$user_rank)){
            if(empty($this->user_id)){
                $this->error('请登陆后才能参加该活动');
            }
        }

        $cart_select = session('cart_select');
        if(!empty($cart_select)){
            $all_cart_data = $this->cart_model->cartData(array('user_id'=>$this->user_id,'rec_id'=>array('in',$cart_select)));
        }else{
            $all_cart_data = array();
        }

        $all_cart_data = $this->cart_model->cartFee($all_cart_data);//购物车统计
        $check_result = $this->checkActToCart($act,$all_cart_data,$goods_id,$is_package,$add_goods_number,1); //检查购物车是否可以加入活动商品
        if($check_result !== true){
            $this->error('您暂未能参加该活动');
        }

        $goods_act_price = $this->cart_model->goodsActPrice($act,$goods_id,$is_package);//商品活动价
        $discount = $this->cart_model->getDiscount($act,$all_cart_data['cart_data'],$goods_id,$is_package,$add_goods_number);
        if($discount !== false){
            $goods_act_price = round($goods_act_price * $discount);
        }else{
            if($act['act_type']==1){
                if($add_goods_number > 1){
                    $this->error('该活动一次加入商品数量不能大于1');
                }
            }
            $is_buy_one_send_one = $this->cart_model->isBuyOneSendOne($act,$all_cart_data['cart_data'],$goods_id,$is_package);
        }

        $k = $this->cart_model->isInTempGift($goods_id,$is_package,$act_id);
        if (($k !== ''|| ($k === 0))){  //session购物车存在这个商品时
            if(!isset($temp_gift) || empty($temp_gift)){
                $temp_gift = session('temp_gift');
            }
            $temp_gift[$k][1] =  $goods_act_price;
            if(!in_array('t_'.$k,session('cart_select'))){//没勾选时
                if($option != 'select'){
                    $this->error('该商品已在购物车未选上，请勾选上或删除再操作');
                }else{
                    session('cart_select.t_'.$k,'t_'.$k);
                    $temp_gift[$k][2] = $add_goods_number;
                }
            }else{
                $temp_gift[$k][2] += $add_goods_number;
            }
            //更新的商品顺序改为最后加入
/*            $replace_array= array();
            foreach($temp_gift as $t => $a){
                if($t>$k){
                    $replace_array[] = $a;
                }
            }
            if(!empty($replace_array)){
                $replace_array[] = $temp_gift[$k];
                array_splice($temp_gift,$k,count($temp_gift),$replace_array); //更新的商品顺序改为最后加入
            }*/
            session('temp_gift',$temp_gift);
        }else{
            $temp_gift2 = session('temp_gift');//$temp_gift2已加入商品
            $goods_order = session('goods_order');
            if(count($goods_order)!=count($temp_gift2)){
                foreach($temp_gift2 as $ka => $va){
                    $check_goods_order[$ka] = (empty($va[3])?0:1).$va[0].$va[4];
                }
                foreach($goods_order as $ka =>$va){
                    if(!in_array($va,$check_goods_order)){
                        unset($goods_order[$ka]);
                    }
                }
            }
            $this_min_amount = $act['min_amount'];
            if($act['min_amount']>0){
                if($is_package==0){
                    $act_gift = unserialize($act['gift']);
                    foreach($act_gift as $c=>$e){
                        if($e['id']==$goods_id){
                            if($e['pmin']>$act['min_amount']){
                                $this_min_amount = $e['pmin'];
                            }
                        }
                    }
                }else{
                    $act_gift_package = unserialize($act['gift_package']);
                    foreach($act_gift_package as $c=>$e){
                        if($e['id']==$goods_id){
                            if($e['pmin']>$act['min_amount']){
                                $this_min_amount = $e['pmin'];
                            }
                        }
                    }
                }
            }
            $k = $this->cart_model->addTempGift(array('goods_id'=>$goods_id,'goods_price'=>$goods_act_price,'goods_number'=>$add_goods_number,'extension_code'=>($is_package? 'package_buy':''),'is_gift'=>$act_id,'min_amount'=>$this_min_amount));

             //记录商品添加顺序
            if(empty($goods_order)){
                $goods_order[0]  = $is_package.$goods_id.$act_id;
            }else{
                $goods_order_k = array_search($is_package.$goods_id.$act_id,$goods_order);
                if($goods_order_k === false){
                    array_push($goods_order,$is_package.$goods_id.$act_id);
                }
            }
            session('goods_order',$goods_order);
        }

        if(isset($is_buy_one_send_one) && ($is_buy_one_send_one!==false)){ //买一送一
            if(!isset($temp_gift) || empty($temp_gift)){
                $temp_gift = session('temp_gift');
            }
            if(is_null($is_buy_one_send_one)){
                $temp_gift[$k][6] = true;
            }else{
                $key  = substr($all_cart_data['cart_data'][$is_buy_one_send_one]['rec_id'],2); //获取session记录中的键名
                unset($temp_gift[$key][6]);  //清除buy_one记录
                if( $all_cart_data['cart_data'][$is_buy_one_send_one]['goods_price'] < $goods_act_price){ //判断哪个价格低,价格低的赠品加1
                    $n = $key;
                }else{
                    $n = $k;
                }
                if(isset($temp_gift[$n][5])){
                    $temp_gift[$n][5]++;
                }else{
                    $temp_gift[$n][5] = 1;
                }
            }
            session('temp_gift',$temp_gift);
        }
        if($discount!==false){ //有折扣更新同个活动其他商品价格
            if(!isset($temp_gift) || empty($temp_gift)){
                $temp_gift = session('temp_gift');
            }
            foreach($temp_gift as $kk => $v){ //同个活动其它商品价格
                if($v[4] == $act_id  && $kk != $k ){
                    if(empty($v[3])){
                        $is_package = 0;
                    }else{
                        $is_package = 1;
                    }
                    $goods_act_price = $this->cart_model->goodsActPrice($act,$v[0],$is_package);
                    $goods_act_price = round($goods_act_price * $discount);
                    $temp_gift[$kk][1] = $goods_act_price;
                }
            }
            session('temp_gift',$temp_gift);
        }
        $this->success();
    }
	
	/*
	*	清除购物车勾选、对应商品 - 文章页面支付
	*	@Author 9009123 (Lemonice)
	*	@return exit & json
	*/
	public function cleanCart(){
		$goods_id = I('goods_id','','trim');
		if($goods_id == ''){
			$this->error('请传商品ID');
		}
		$result = $this->cart_model->specialCleanCart($goods_id,$this->user_id);
		$this->delAllGoods(true);  //把所有商品的勾选取消
		$this->success();
	}

   /*
    * 全不选
	* @param bool $is_return 是否返回、是否不中断输出   -- Add By 9009123 (Lemonice)
    */
   public function delAllGoods($is_return = false){
       session('cart_select',null);
       $temp_gift= session('temp_gift');
       foreach($temp_gift as $k =>$v){
           if(isset($v[5]) && $v[5]>0){ //买一送一活动已有赠品  清理当前买一送一活动
               if(isset($temp_gift[$k][5])){
                     unset($temp_gift[$k][5]);
               }
               $goods_number = $temp_gift[$k][2];
               if($goods_number > 1){
                    $temp_gift[$k][2] = 1;  //买一送一一次只能加一个
               }
           }
           if(isset($temp_gift[$k][6])){
               unset($temp_gift[$k][6]);
           }
           $act_id = $v[4];
           $act = $this->activity_model->where('act_id = '.$act_id)->find();
           $goods_price = $v[1];
           if($goods_price <= 0){  //赠品不保留
               unset($temp_gift[$k]);
           }else if($goods_price > 0 && intval($act['min_amount'])>0){//换购不保留
               unset($temp_gift[$k]);
           }
       }
       //去除勾选并重置为连续顺序
       $_temp_gift = array();
       $n = 0;
       foreach($temp_gift as $kk => $vv){
           session('cart_select.'.'t_'.$kk,null);
           if(!empty($vv)){
               $_temp_gift[$n] = $vv;
               $n++;
           }
       }
       $temp_gift = $_temp_gift;
       session('temp_gift',$temp_gift);
       $this->success();
   }

    /**
     * 全选所有商品
     * @return bool
     */
   public function selectAllGoods(){
           $all_cart_data = $this->cart_model->cartData(array('user_id'=>$this->user_id),false,false);
           $min_amount_array = array();
           foreach($all_cart_data as $c => $e){
                  if($e['extension_code'] !=  'package_goods'){
                        $_all_cart_data[] = $e;
                        if($e['is_gift'] > 0 && isset($e['min_amount'])){
                            $min_amount_array[] = $e['min_amount'];
                        }else{
                            $min_amount_array[] = 0;
                        }
                  }
           }
           array_multisort($min_amount_array,SORT_ASC,$_all_cart_data);
           foreach($_all_cart_data as $k => $v) {
               if ($v['extension_code'] == 'package_goods') {
                   continue;
               }
               if (is_numeric($v['rec_id'])) {
                   $cart_select = session('cart_select');
                   if (!in_array($v['rec_id'], $cart_select)) {
                       session('cart_select.' . $v['rec_id'], $v['rec_id']);
                   }
               } else { //活动商品清除再由用户加。
                   if (strlen($v['rec_id']) >= 3) {
                       $k = substr($v['rec_id'], 2);
                       if (is_numeric($k)) {
                           $temp_gift_k = session('temp_gift.' . $k);
                           $cart_select = session('cart_select.t_' . $k);
                           if (!is_null($temp_gift_k) && is_null($cart_select)) {
                               $g_info = array('goods_id' => $temp_gift_k[0], 'goods_number' => $temp_gift_k[2], 'is_package' => empty($temp_gift_k[3]) ? 0 : 1, 'act_id' => $temp_gift_k[4]);
                               $select_act_goods_to_cart = $this->selectActGoodsToCart($k, $g_info);//var_dump($g_info,$select_act_goods_to_cart);
                               if ($select_act_goods_to_cart === false) { //如果删除键值则重新排列

                               }
                           } else {
                               //$this->error('列号错误或有商品不符合条件已被删除');
                           }
                       }
                   }
               }
           }
         $this->cart_model->resetTempGift();
         $this->success();
   }

    /**
     * 显示购物车
     */
    public function showCart(){
        $page_size = I('post.page_size',10);
        $page = I('post.page',1);
        $all_cart_data = $this->cart_model->cartData(array('user_id'=>$this->user_id),false,false);//var_dump($all_cart_data);
        $del_rec_id = 0;
        foreach($all_cart_data as $k=>$v){  //检查是否有下架和库存,是下架或无库存则清除
              if($v['extension_code'] == 'package_buy'){   //套装检测
                  $package_info = $this->cart_model->getPackageInfo(0,0,$v['goods_id']);
                  if(empty($package_info)){
                      $del_rec_id = $v['rec_id'];
                  }else{
       /*
                     // 允许可不绑商品
                      $goods_info = $this->cart_model->getGoodsData($package_info['goods_id']);
                      if($package_info===false || (CHECK_STOCK && $goods_info['goods_number']<=0)){
                          $del_rec_id = $v['rec_id'];
                      }*/
                      foreach($package_info['package_goods'] as $pg){
                          if($pg['is_on_sale'] == 0 || (CHECK_STOCK && $pg['kc_goods_number']<=0)){
                              $del_rec_id = $v['rec_id'];
                          }
                      }
                  }
              }
              if($v['extension_code'] == ''){ //单品检测
                  $goods_info = $this->cart_model->getGoodsData($v['goods_id']);
                  if(empty($goods_info) || $goods_info['is_on_sale'] == 0 || (CHECK_STOCK && $goods_info['goods_number']<=0)){
                      $del_rec_id = $v['rec_id'];
                  }
              }
              if(!empty($del_rec_id) ){   //有要删除记录，执行删除
                  if(is_numeric($v['rec_id'])){
                      $this->cart_model->delOneCartGoods($v['rec_id'],$this->user_id);
                  }else{ //活动商品删除
                      session('temp_gift.'.substr($v['rec_id'],2),null);
                      session('cart_select.'.'t_'.substr($v['rec_id'],2),null);
                      $this->cart_model->resetTempGift();
                  }
                  $del_rec_id = 0;
              }
        }
        $this->dealCartAct();//清除不可用的活动商品
        $all_cart_data = $this->cart_model->cartData(array('user_id'=>$this->user_id),false,false); //清理后再获取购物车商品
        $all_cart_data = $this->cart_model->addCartGoodsThumb($all_cart_data);//echo '<pre>';var_dump($all_cart_data);die;

        foreach($all_cart_data as $k => $v) {
            if(isset($v['session_id'])){
                unset($all_cart_data[$k]['session_id']);
            }
            $all_cart_data[$k]['goods_number'] = intval($all_cart_data[$k]['goods_number']);
            if(in_array($v['rec_id'],session('cart_select'))){
               $all_cart_data[$k]['select'] = true;
               $all_cart_select_data[] = $all_cart_data[$k];
            }else{
               $all_cart_data[$k]['select'] = false;
            }
        }
        $all_cart_select_data_fee = $this->cart_model->cartFee($all_cart_select_data);
        $goods_activity = D('goods_activity');
        $order_temp = array();
        foreach($all_cart_data as $k => $v){
            if($v['extension_code'] == 'package_goods'){ //子商品不显示
                unset($all_cart_data[$k]);
                continue;
            }
            if($v['is_gift']>0){
                $is_package = 0;
                if($v['extension_code'] == 'package_buy'){
                     $is_package = 1;
                }
                $act = $this->activity_model->where('act_id = '.$v['is_gift'])->find();
                $check_act_cart = $this->checkActToCart($act,$all_cart_select_data_fee,$v['goods_id'],$is_package,1,0);
                if($check_act_cart === true){
                    $all_cart_data[$k]['add_one'] = true;
                }else{
                    $all_cart_data[$k]['add_one'] = false;
                }
                $all_cart_data[$k]['cart_sn'] = 't_'.$is_package.$v['goods_id'].$v['is_gift'];
                $order_temp[$is_package.$v['goods_id'].$v['is_gift']]=$k;
            }else{
                $all_cart_data[$k]['cart_sn'] = $v['rec_id'];
                $all_cart_data[$k]['add_one'] = true;
            }
          //活动套装可以不绑商品
            if($v['extension_code'] == 'package_buy'){
                 $all_cart_data[$k]['pg_id'] = $goods_activity->where(array('act_id'=>$v['goods_id']))->getField('goods_id');
            }else{
                 $all_cart_data[$k]['pg_id']  = 0;
            }
        }
       $goods_order = session('goods_order');
        if(!empty($goods_order)){
            krsort($goods_order);
            $new_cart_data=array();
            foreach($all_cart_data as $k=>$v){
                if($v['is_gift']==0){
                    $new_cart_data[] =$v;
                }
            }
            foreach($goods_order as $k => $va){
                if(isset($all_cart_data[$order_temp[$va]])){
                    $new_cart_data[] = $all_cart_data[$order_temp[$va]];
                }
            }
            $all_cart_data = $new_cart_data;
        }
       $all_cart_data_pages = array_chunk($all_cart_data ,$page_size);
       $out_all_cart['cart_goods_page_data'] = $all_cart_data_pages[$page-1];
       $out_all_cart['total_page']  = count($all_cart_data_pages);
       $out_all_cart['total_amount'] = $all_cart_select_data_fee['total_amount'];
       $act_minus_arr['act_minus_arr'] =  $all_cart_select_data_fee['act_minus_arr'];
       $this->ajaxReturn($out_all_cart);
    }


    /**
     * 活动列表
     */
    function activityList(){
        $only_gift = isset($_GET['gift'])? $_GET['gift'] :0;
        //获购物车商品
        $cart_select = session('cart_select');
        if(empty($cart_select)){
            $all_cart_data = array();
        }else{
            $all_cart_data = $this->cart_model->cartData(array('user_id'=>$this->user_id,'rec_id'=>array('in',session('cart_select'))));
        }
        $all_cart_data = $this->cart_model->cartFee($all_cart_data);
        $all_activity =  $this->cart_model->getAllActivity();
        $gift_goods_ids = $gift_package_ids = array();
        foreach($all_activity as $k => $v) { //去除不可用的活动商品
            $v = $this->cart_model->formatActivityGoodsData($v);
            $is_buy_one_send_one =$this->cart_model->isBuyOneSendOne($v,$all_cart_data['cart_data']);
            if (!empty($v['gift']) ) {
                $is_package = 0;
                foreach ($v['gift'] as $kk => $vv) {
                    $check_act_cart = $this->checkActToCart($v, $all_cart_data, $vv['id'], $is_package, 1, 0);//var_dump($v,$check_act_cart);echo '<br><br>';die;
                    if($check_act_cart !== true){ //var_dump($v,$vv);
                         unset($v['gift'][$kk]);
                    }else{
                         $gift_goods_ids[] = $vv['id'];
                         if(!isset($all_activity[$k]['is_free_gift'])){
                             if($vv['price']==0){
                                 $v['is_free_gift'] = 1;
                             }else{
                                 $v['is_free_gift'] = 0;
                             }
                         }
                         if(!isset($all_activity[$k]['is_exchange_buy'])){
                             if($vv['price'] >0 && ($vv['pmin']>50 || $v['min_amount'] >0)){
                                 $v['is_exchange_buy'] = 1;
                             }else{
                                 $v['is_exchange_buy'] = 0;
                             }
                         }
                         if($only_gift==1 && $v['is_exchange_buy'] == 0 && $v['is_free_gift'] == 0){
                             unset($v['gift'][$kk]);
                             continue;
                         }
                         if(!is_null($is_buy_one_send_one) && $is_buy_one_send_one === false){
                             if($v['gift'][$kk]['price'] >0 && ($v['gift'][$kk]['price']  <= $all_cart_data[$is_buy_one_send_one]['goods_price'])){
                                 $v['gift'][$kk]['price'] = 0;
                             }
                         }
                         unset($v['gift'][$kk]['remarks']);
                         unset($v['gift'][$kk]['pmax']);
                         unset($v['gift'][$kk]['num']);
                         unset($v['gift'][$kk]['pmin']);
                         unset($v['gift'][$kk]['buylimit']);
                     }
                }
            }
            if (!empty($v['gift_package'])) {
                $is_package = 1;
                foreach ($v['gift_package'] as $kk => $vv) {
                    $check_act_cart = $this->checkActToCart($v, $all_cart_data, $vv['id'], $is_package, 1, 0);//var_dump($check_act_cart);echo '<br><br>';die;
                    if($check_act_cart !== true){
                        unset($v['gift_package'][$kk]);
                    }else{
                        $gift_package_ids[] = $vv['id'];
                        if(!isset($all_activity[$k]['is_free_gift'])){
                            if($vv['price']==0){
                                $v['is_free_gift'] = 1;
                            }else{
                                $v['is_free_gift'] = 0;
                            }
                        }
                        if(!isset($all_activity[$k]['is_exchange_buy'])){
                            if($vv['price'] >0 && ($vv['pmin']>0 || $v['min_amount'] >0)){
                                $v['is_exchange_buy'] = 1;
                            }else{
                                $v['is_exchange_buy'] = 0;
                            }
                        }
                        if($only_gift==1 && $v['is_exchange_buy'] == 0 && $v['is_free_gift'] == 0){
                            unset($v['gift_package'][$kk]);
                            continue;
                        }

                        $package_info = $this->cart_model->getPackageInfo(0,0,$vv['id']);
                        foreach($package_info['package_goods'] as $pgk=>$pgv){
                            if($pgv['is_on_sale'] == 0) {
                                //'套装单品'.$package_info['goods_id'].'已下架'
                                unset($v['gift_package'][$kk]);
                                continue;
                            }
                            if(CHECK_STOCK &&$pgv['goods_number'] == 0){
                                //'套装单品'.$package_info['goods_id'].'无库存'
                                unset($v['gift_package'][$kk]);
                                continue;
                            }
                        }
                        if(!is_null($is_buy_one_send_one) && $is_buy_one_send_one === false){
                            if($v['gift_package'][$kk]['price']>0 && ($v['gift_package'][$kk]['price'] <= $all_cart_data[$is_buy_one_send_one]['goods_price'])){
                                $v['gift_package'][$kk]['price'] = 0;
                            }
                        }
                        unset($v['gift_package'][$kk]['remarks']);
                        unset($v['gift_package'][$kk]['pmax']);
                        unset($v['gift_package'][$kk]['num']);
                        unset($v['gift_package'][$kk]['pmin']);
                        unset($v['gift_package'][$kk]['buylimit']);
                    }
                }
            }
            //var_dump($v,(empty($v['gift']) && empty($v['gift_package'])));
            if(empty($v['gift']) && empty($v['gift_package'])){
                unset($all_activity[$k]);
            }else{
                $all_activity[$k] = $v;
            }
        }

        if(!empty($gift_goods_ids)){
            $goods_thumbs = $this->cart_model->getGoodsThumb($gift_goods_ids);
        }
        if(!empty($package_thumbs)){
            $package_thumbs = $this->cart_model->getPackageGoodsThumb($gift_package_ids);
        }
        foreach($all_activity as $k => $v){
               if(!empty($v['gift'])){
                    foreach ($v['gift'] as $kk => $vv){
                        if(isset($goods_thumbs[$vv['id']]) && !empty($goods_thumbs[$vv['id']])){
                          $all_activity[$k]['gift'][$kk]['thumb'] = C('domain_source.img_domain').$goods_thumbs[$vv['id']];
                        }else{
                           $all_activity[$k]['gift'][$kk]['thumb'] = '';
                        }
                    }
               }
               if(!empty($v['gift_package'])){
                    foreach ($v['gift_package'] as $kk => $vv){
                        if(isset($package_thumbs[$vv['id']]) && !empty($package_thumbs[$vv['id']])){
                            $all_activity[$k]['gift_package'][$kk]['thumb'] = C('domain_source.img_domain').$package_thumbs[$vv['id']];
                        }else{
                            $all_activity[$k]['gift_package'][$kk]['thumb'] = '';
                        }
                    }
               }
            unset($all_activity[$k]['user_rank']);
            unset($all_activity[$k]['act_range']);
            unset($all_activity[$k]['act_range_ext']);
            unset($all_activity[$k]['max_amount']);
            unset($all_activity[$k]['act_type']);
            unset($all_activity[$k]['act_type_ext']);
           // unset($all_activity[$k]['gift_range_ext']);
            unset($all_activity[$k]['start_time']);
            unset($all_activity[$k]['end_time']);
            unset($all_activity[$k]['min_amount']);
            unset($all_activity[$k]['gift_range']);
            unset($all_activity[$k]['gift_range_price']);
            unset($all_activity[$k]['level_type']);
            //unset($all_activity[$k]['stock_limited']);
        }
        $this->ajaxReturn($all_activity);
    }




    /**
     * 减一个商品
     */
    function mineOneGoods(){

        $goods_id = I('post.goods_id',0);
        if(!is_numeric($goods_id)){
            $this->error('商品id=='.$goods_id.',无效');
        }
        $is_package = I('post.is_package',0);
        if(!is_numeric($is_package)){
            $this->error('is_package=='.$is_package.',无效');
        }

        if($is_package == 0){
            //不是套装进一步判断是不是套装
            $package_info = $this->cart_model->getPackageInfo($goods_id,0,0);
            if(!empty($package_info)){
                $is_package = 1;
                $goods_id = $package_info['act_id'];//var_dump($package_info);echo $goods_id;
            }
        }
        $is_gift   = I('post.act_id',0);
        if(!is_numeric($is_gift)){
            $this->error('act_id=='.$is_gift.',无效');
        }
        if($is_gift>0){
            $is_in_temp_gift = $this->cart_model->isInTempGift($goods_id,$is_package,$is_gift);
            if($is_in_temp_gift === ''){
                $this->error('找不到对应商品');
            }
            $temp_gift = session('temp_gift');
            if( $temp_gift[$is_in_temp_gift]['2']>1){
                if(isset($temp_gift[$is_in_temp_gift][6]) && $temp_gift[$is_in_temp_gift][6]){ //如果买一还没送一则直减一
                    $temp_gift[$is_in_temp_gift][6] = false;
                    $temp_gift[$is_in_temp_gift]['2']--;
                    session('temp_gift',$temp_gift);
                }else if(isset($temp_gift[$is_in_temp_gift][5]) && $temp_gift[$is_in_temp_gift][5]>0){ //如果买一并送一
                    foreach($temp_gift as $k => $v){
                         if($v[4] == $is_gift){
                             unset($temp_gift[$k]);
                         }
                    }
                    $this->cart_model->resetTempGift();
                }else{
                    $temp_gift[$is_in_temp_gift]['2']--;
                    session('temp_gift',$temp_gift);
                    $act = $this -> activity_model -> where('act_id = '.$is_gift)->find();
                    if(in_array($act['act_type'],array(4,5,6))){

                        $cart_select = session('cart_select');
                        if(!empty($cart_select)){
                            $all_cart_data = $this->cart_model->cartData(array('user_id'=>$this->user_id,'rec_id'=>array('in',$cart_select)));
                        }else{
                            $all_cart_data = array();
                        }
                        $all_cart_data = $this->cart_model->cartFee($all_cart_data);//购物车统计
                        $discount = $this->cart_model->getDiscount($act,$all_cart_data['cart_data']);
                        if($discount !== false){
                            foreach($temp_gift as $k => $v){
                                if($v['4'] == $is_gift){
                                    $goods_act_price = $this->cart_model->goodsActPrice($act,$v[0],empty($v[3])? 0 : 1);//商品活动价
                                    $goods_act_price = round($goods_act_price * $discount);
                                    $temp_gift[$k][1] = $goods_act_price;
                                }
                            }
                            session('temp_gift',$temp_gift);
                        }
                    }
                }
            }else{
                $this->error('商品数量不能少于1');
            }

        } else if($is_gift == 0){ //普通商品
            if($is_package){
                $extension_code = 'extension_code = "package_buy"';
            }else{
                $extension_code = 'extension_code = ""';
            }
            $where = 'goods_id = '.$goods_id .' and is_gift = '.$is_gift.' and '.$extension_code;
            if($this->user_id>0){
                $where .=' and (user_id = '.$this->user_id.' or session_id = "'.session_id().'")';
            }else{
                $where .=' and session_id = "'.session_id().'"';
            }
            $cartOneGoods = $this->cart_model->field('rec_id,goods_number')->where($where)->find();
            if(!empty($cartOneGoods)){
                if($cartOneGoods['goods_number']>1) {
                    $this->cart_model->updateToCart($cartOneGoods['rec_id'],-1);
                }else{
                   $this->error('商品数量不能小于1');
                }
            }else{
                $this->error('找不到对应商品');
            }
        }
        $this->dealCartAct();//清除不可用的活动商品
        $this->success();
    }


    /**
     * 去除勾选单个商品 ,当$_POST[real_del]==1 则是删除商品
     */
    public function delGoods(){
              $real_del = I('post.real_del',0);
              $rec_id = I('post.rec_id',0);
              $where = '';
              if(is_numeric($rec_id)){
                  if($this->user_id>0){
                      $where .=' and user_id = '.$this->user_id;
                  }else{
                      $where .=' and session_id = "'.session_id().'"';
                  }
                  $one_goods_info =  $this->cart_model->where('rec_id = '.$rec_id .$where)->find();
                  if(!empty($one_goods_info)){
                      if($one_goods_info['extension_code'] == 'package_goods'){
                          $this->error('参数错误');
                      }else{
                          session('cart_select.'.$rec_id,null);//去除勾选
                          if($real_del==1){
                               $this->cart_model->delOneCartGoods($rec_id,$this->user_id);
                          }
                      }
                  }else{
                      $this->error('找不到对应记录');
                  }
              }else{
                  if(strlen($rec_id)>=3){
                      $key = substr($rec_id,2);
                      if(!is_numeric($key)){
                          $this->error('rec_id=='.$rec_id.'，无效');
                      }
                      $one_temp_gift = session('temp_gift.'.$key);
                      $one_cart_select = session('cart_select.'.$rec_id);
                      if(is_null($one_temp_gift)){
                          $this->error('找不到对应记录');
                      }else{
                          if(empty($real_del)){
                              if(is_null($one_cart_select)){
                                  $this->error('该记录已去除选中状态');
                              }
                          }else{
                              session('temp_gift.'.$key,null);
                          }
                          if(!is_null($one_cart_select)){
                              session('cart_select.'.$rec_id,null);
                          }
                          $act_id = $one_temp_gift[4];
                          $temp_gift= session('temp_gift');
                          if(isset($one_temp_gift[5]) && $one_temp_gift[5]>0){ //买一送一活动已有赠品
                              foreach($temp_gift as $k => $v){ //清理当前买一送一活动
                                  if($v['4'] == $act_id){
                                      session('cart_select.'.'t_'.$k,null);
                                      if($real_del == 1){
                                          unset($temp_gift[$k]);  //彻底删除
                                          continue;
                                      }
                                      if(isset($temp_gift[$k][5])){
                                          unset($temp_gift[$k][5]);
                                      }
                                      if(isset($temp_gift[$k][6])){
                                          unset($temp_gift[$k][6]);
                                      }
                                      if($temp_gift[$k][2]>1){
                                          $temp_gift[$k][2] = 1;  //买一送一一次只能加一个
                                      }
                                  }
                              }
                          }else if(!isset($one_temp_gift[6]) ||(isset($one_temp_gift[6]) && $one_temp_gift[6]==false)){//可能是折扣商品,修改同活动商品价格
                               $act = $this->activity_model->where('act_id = '.$act_id)->find();
                               if(in_array($act['act_type'],array(4,6,5))){
                                   $cart_select = session('cart_select');
                                   if(!empty($cart_select)){
                                       $all_cart_data = $this->cart_model->cartData(array('user_id'=>$this->user_id,'rec_id'=>array('in',$cart_select)));
                                   }else{
                                       $all_cart_data = array();
                                   }
                                   $all_cart_data = $this->cart_model->cartFee($all_cart_data);//购物车统计
                                   $discount = $this->cart_model->getDiscount($act,$all_cart_data['cart_data']);
                                   if($discount !== false){
                                       foreach($temp_gift as $kk => $v){
                                           if($v['4'] == $act_id && ('t_'.$kk!=$rec_id)){
                                               $goods_act_price = $this->cart_model->goodsActPrice($act,$v[0],empty($v[3])? 0 : 1);//商品活动价
                                               $goods_act_price = round($goods_act_price * $discount);
                                               $temp_gift[$kk][1] = $goods_act_price;
                                           }
                                       }
                                   }
                               }else{
                                    if($one_temp_gift[1] <= 0){  //赠品不保留,去勾选也彻底删除
                                        unset($temp_gift[$key]);
                                    }else if($one_temp_gift[1] > 0 && intval($act['min_amount'])>0){ //换购不保留
                                        unset($temp_gift[$key]);
                                    }
                               }
                          }
                          //活动商品放回session
                          $this->cart_model->resetTempGift($temp_gift);
                      }
                  }else{
                      $this->error('参数长度错误');
                  }
              }
           $this->dealCartAct();//清除不可用的活动商品
           $this->success();
    }

    /**
     * 用户登陆关联购物车
     */
/*    function updateCartUserInfo(){
        if($this->user_id>0){
            $result = $this->cart_model->updateCartUserInfo($this->user_id);
            if($result){
                $this->success();
            }else{
                $this->error('更新购物车用户信息失败');
            }
        }else{
            $this->error('你还未登陆');
        }
    }
*/


    /**
     * 取可参与活动的商品总价
     * @param $act  活动信息
     * @param $data 购物车统计后数据
     * @param $error_exit  是否终止退出
     */
  private function actTotalPrice($act,$data,$error_exit=1){

        switch($act['act_rang']){
            case 0 :
                 return isset($data['get_gift_price']['total'])? $data['get_gift_price']['total'] : 0 ;
                 break;
            case 1 :
                return isset($data['get_gift_price']['package']['total'])? $data['get_gift_price']['package']['total'] : 0 ;
                break;
            case 2 :
                if($act['act_rang_ext']){
                    if(strpos($act['act_rang_ext'],',')){
                        $act_rang_ext = explode(',',$act['act_rang_ext']);
                        $cate_id_t_price = 0;
                        foreach($act_rang_ext as $k => $v){
                            if(isset($data['get_gift_price']['cate_id'][$v])){
                                $cate_id_t_price += $data['get_gift_price']['cate_id'][$v];
                            }
                        }
                    }else{
                        $cate_id_t_price = $data['get_gift_price']['cate_id'][$act['act_rang_ext']];
                    }
                    return  $cate_id_t_price;
                }else{
                    return  $this->error('活动商品范围不能为空','',0,$error_exit);
                }
               break;
            case 3 : //指定商品
                if($act['act_rang_ext']){
                    $goods_t_price = 0;
                    if(strpos($act['act_rang_ext'],',')){
                        $act_rang_ext = explode(',',$act['act_rang_ext']);
                        foreach($act_rang_ext as $vv){
                            foreach($data['cart_data'] as $k => $v){
                                if(empty($v['extension_code']) && $v['goods_id'] == $vv){
                                    $goods_t_price += $v['goods_number'] * $v['goods_price'];
                                }
                            }
                        }
                    }else{
                        foreach($data['cart_data'] as $k => $v){
                            if(empty($v['extension_code']) && $v['goods_id'] == $act['act_rang_ext']){
                                $goods_t_price += $v['goods_number'] * $v['goods_price'];
                            }
                        }
                    }
                   return  $goods_t_price;
                }else{
                    return  $this->error('活动商品范围不能为空','',0,$error_exit);
                }
              break;
            case 4 : //指定套装
                if($act['act_rang_ext']){
                    $goods_t_price = 0;
                    if(strpos($act['act_rang_ext'],',')){
                        $act_rang_ext = explode(',',$act['act_rang_ext']);
                        foreach($act_rang_ext as $vv){
                            foreach($data['cart_data'] as $k => $v){
                                if($v['extension_code']=='package_buy' && $v['goods_id'] == $vv){
                                    $goods_t_price += $v['goods_number'] * $v['goods_price'];
                                }
                            }
                        }
                    }else{
                        foreach($data['cart_data'] as $k => $v){
                            if($v['extension_code']=='package_buy' && $v['goods_id'] ==$act['act_rang_ext']){
                                $goods_t_price += $v['goods_number'] * $v['goods_price'];
                            }
                        }
                    }
                    return $goods_t_price;
                }else{
                    return $this->error('活动商品范围不能为空','',0,$error_exit);
                }
              break;
            default :
                return 0;
               break;
        }
   }




    /**
     * 查购物车是否满足加入活动商品
     * @param $act   活动信息
     * @param $data //统计后的购物车数据
     * @param $add_goods_id (goods_id or  act_id) 要添加的商品id
     * @param int $is_package 是否是套装
     * @param int $add_goods_number 添加的商品数
     * @param $error_exit 是否中断退出
     * @return bool
     */
  private  function checkActToCart($act,$data,$add_goods_id,$is_package=0,$add_goods_number=0,$error_exit=0){
               if(isset($act['conflict_act']) && !empty($act['conflict_act'])){
                   $conflict_act = unserialize($act['conflict_act']);
                   $conflict_act_ids = array();
                   foreach($conflict_act as $k => $v){
                       $conflict_act_ids[] = $v['act_id'];
                   }
                   foreach($data['cart_data'] as $k => $v){
                       if(in_array($v['is_gift'],$conflict_act_ids)){
                           $other_act = $this->activity_model->where('act_id = '.$v['is_gift'])->find();
                           return $this->error($act['act_name'].' 与 '.$other_act['act_name'].' 只能参与一个活动','',0,$error_exit);
                       }
                   }
               }
               $check_all_num_price = $this->checkActAllNumPrice($act, $data,$add_goods_id,$is_package,$add_goods_number,$error_exit);
               if($check_all_num_price === true){
                     return  $this->checkActGoods($act,$data,$add_goods_id,$is_package,$add_goods_number,$error_exit);
                   }else{
                     return $check_all_num_price;
               }
  }


    /**
     * 检查活动中的商品
     * @param $act 活动信息
     * @param $data  统计后台购物车信息
     * @param $goods_id 添加的商品
     * @param $is_package 是否是套装
     * @param $error_exit 是否中断退出
     * @param int $add_goods_number 添加商品数
     *
     */
  private function checkActGoods($act,$data,$add_goods_id,$is_package,$add_goods_number=0,$error_exit=0){
        if ($is_package) {
            $act['gift_package'] = !empty($act['gift_package']) ? (is_array($act['gift_package'])? $act['gift_package']:unserialize($act['gift_package'])) : null;
            foreach ($act['gift_package'] as $k => $v) {
                if ($v['id'] == $add_goods_id) {
                    /*   if (isset($v['num']) && $v['num'] != 0) { //检查单个商品数据
                           if (isset($data['is_gift_num'][$act['act_id']]['goods'][$v['id']]) && $v['num'] <= $data['is_gift_num'][$act['act_id']]['goods'][$v['id']]+$add_goods_number) {
                               $this->error('优惠商品数量已超优惠商品限制');
                           }
                       }*/
                    if (isset($v['pmin']) && $v['pmin'] > 0) {
                        if (isset($data['get_gift_price']['total']) && $data['get_gift_price']['total'] < $v['pmin']) {
                            return $this->error('您所选购的还没达到该优惠商品的下限','',0,$error_exit);
                        }
                    }
                    return true;
                }
            }
        } else {
            $act['gift'] = !empty($act['gift']) ? (is_array($act['gift'])? $act['gift']:unserialize($act['gift'])) : null;
            foreach ($act['gift'] as $k => $v) {
                if ($v['id'] == $add_goods_id) {
                    /* if (isset($v['num']) && $v['num'] != 0) { //检查单个商品数据
                         if (isset($data['is_gift_num'][$act['act_id']]['goods'][$v['id']]) && $v['num'] <= $data['is_gift_num'][$act['act_id']]['goods'][$v['id']]+$add_goods_number) {
                             $this->error('优惠商品数量已超优惠商品限制');
                         }
                     }*/
                    if (isset($v['pmin']) && $v['pmin'] > 0) {
                        if (isset($data['get_gift_price']['total']) && $data['get_gift_price']['total'] < $v['pmin']) {
                          return   $this->error('您所选购的还没达到该优惠商品购买下限','',0,$error_exit);
                        }
                    }
                    return true;
                }
            }
        }
        return $this->error('未找到对应商品','',0,$error_exit);
   }


    /**
     * 检查活动总价总数量
     * @param $act 活动信息
     * @param $data  统计后的购物车数据
     * @param int $add_goods_id 要添加的商品id
     * @param int $is_package 是否是套装
     * @param int $add_goods_number 添加的商品数
     * @param int $error_exit 是否是中断退出
     * @return array|bool
     */
  private  function checkActAllNumPrice($act,$data,$add_goods_id=0,$is_package=0,$add_goods_number=0,$error_exit=0){ //var_dump($data);
            $act_total_price = $this->actTotalPrice($act,$data,$error_exit);
            if(is_array($act_total_price) && $act_total_price['status'] == 0){
               return  $this->error($act_total_price['msg'],'',0,$error_exit);
            }
            if( intval($act['min_amount'])> 0){ //选购下限
                 if($act_total_price<=0){
                        return $this->error('没找到您购物车可参与本活动的商品总价，不能参加该活动','',0,$error_exit);
                 }else if($act_total_price < $act['min_amount']){
                        return $this->error('您还差'.($act['min_amount']-$act_total_price).'元就可以选购该商品','',0,$error_exit);
                 }
            }
            if (in_array($act['act_type'], array(0, 7, 8, 9, 10, 11))) {
                if ($act['act_type_ext'] != 0 && isset($data['is_gift_num'][$act['act_id']]['all_goods']) && !empty($data['is_gift_num'][$act['act_id']]['all_goods'])) {
                    if ($act['act_type_ext'] == 1) {
                        return  $this->error('您已参加了该活动，不可重复参加','',0,$error_exit);
                    }else if (($data['is_gift_num'][$act['act_id']]['all_goods']+$add_goods_number) > $act['act_type_ext']) {
                        return  $this->error('优惠商品数量已超活动限制','',0,$error_exit);
                    }
                }
            }

            if (in_array($act['act_type'],array(4,6)) ) { //4享受折扣选购（受订购商品数量限制） 6受计件折扣或减免（受订购数量影响）
                    //不限制，不达条件按活动原价计
            }
            if ($act['act_type'] == 5) { //享受折扣选购（受订购商品金额限制）
                //不限制，不达条件按活动原价计
            }
            if ($act['act_type'] == 3) { //享受限量选购（受订购商品金额限制） 换购
                if (!empty($act['act_type_ext'])) {
                    if (strpos($act['act_type_ext'], ',')) {
                        $max = $min = 0;
                        $buy_rule = explode(',', $act['act_type_ext']);
                        $now_min_amount = null;
                        $t_num =  $data['is_gift_num'][$act['act_id']]['all_goods']+$add_goods_number;
                        foreach($buy_rule as $k =>$v){
                            $buy_rule[$k] = explode('|', $v);
                            $buy_rule[$k][0] = intval($buy_rule[$k][0]);
                            $buy_rule[$k][1] = intval($buy_rule[$k][1]);
                            if($max<$buy_rule[$k][1]){
                                $max = $buy_rule[$k][1];
                            }
                            if($min>$buy_rule[$k][0] || $k==0){
                                $min = $buy_rule[$k][0];
                            }
                            if($t_num == $buy_rule[$k][1]){
                                $now_min_amount =  $buy_rule[$k][0];
                            }
                        }
                        if ($max < $t_num) {
                           return  $this->error('优惠商品数量已超活动限制','',0,$error_exit);
                        }
                        if($act_total_price<$now_min_amount){
                            return  $this->error('总金额还没达到购买此商品要求','',0,$error_exit);
                        }

                        if($min > $act['min_amount']){
                           return $this->error('指定最小起订金额与订购金额下限冲突,后台数据错误!','',0,$error_exit);
                        }
                    } else {
                       if (strpos('|', $act['act_type_ext']) !== false) {
                          $buy_rule = explode('|', $act['act_type_ext']);
                          $t_num = $data['is_gift_num'][$act['act_id']]['all_goods'] + $add_goods_number;
                          if (isset($data['is_gift_num'][$act['act_id']]['all_goods']) && !empty($data['is_gift_num'][$act['act_id']]['all_goods'])) {
                              if ($buy_rule[1] == 1) {
                                  return $this->error('您已参加了该活动，不可重复参加', '', 0, $error_exit);
                              } else if ($buy_rule[1] < $t_num) {
                                  return $this->error('优惠商品数量已超活动限制', '', 0, $error_exit);
                              }
                          }
                          if ($buy_rule[0] > $act['min_amount']) {
                              return $this->error('指定最小起订金额与订购金额下限冲突,后台数据错误!', '', 0, $error_exit);
                          }
                       }else{
                            $this->error('规则数据有误', '', 0, $error_exit);
                       }
                    }
                }
            }
           if ($act['act_type'] == 1) { //买一送一
                    //不限制
           }
         if($act['act_type'] == 2){  //享受单品等价选购（受订购商品金额限制）订购满：a元，可选购总价b的优惠品
            $goods_act_price = $this->cart_model->goodsActPrice($act,$data,$add_goods_id,$add_goods_number,$is_package);//商品活动价
            if (!empty($act['act_type_ext'])) {
                if (strpos($act['act_type_ext'], ',')) {
                    $min = $max =0;
                    $rule_key = null;
                    $buy_rule = explode(',', $act['act_type_ext']);
                    foreach($buy_rule as $k =>$v){
                        $buy_rule[$k] = explode('|', $v);
                        if($min>$buy_rule[$k][0] || $k==0){
                            $min = $buy_rule[$k][0];
                        }
                        if($max<= $buy_rule[$k][0]  &&  $buy_rule[$k][0]<= $act_total_price){
                            $rule_key = $k;
                            $max = $buy_rule[$k][0];
                        }
                    }
                    if(is_null($rule_key)){
                        return  $this->error('您还未能参加该活动','',0,$error_exit);
                    }
                    if(($data['gift_total_price'][$act['act_id']]+$goods_act_price)>$buy_rule[$rule_key][1]){
                        return  $this->error('优惠商品金额超过限制','',0,$error_exit);
                    }
                    if($min > $act['min_amount']){
                        return  $this->error('最小起订金额与订购金额下限冲突,后台数据错误!','',0,$error_exit);
                    }
                } else {
                    $buy_rule = explode('|', $act['act_type_ext']);
                    if($buy_rule[0] > $act['min_amount']){
                        return  $this->error('最小起订金额与订购金额下限冲突,后台数据错误!','',0,$error_exit);
                    }
                    if(($data['gift_total_price'][$act['act_id']]+$goods_act_price)> $buy_rule[1]){
                        return  $this->error('优惠商品金额超过限制','',0,$error_exit);
                    }
                }
            }
         }
         return true;
    }



    /**
     * 整理检查活动商品 先删除再加入
     *
     * @return mixed
     */
  private   function dealCartAct(){
        $temp_gift = session('temp_gift');
        session('temp_gift',null);
        $_temp_gift = array();
        $n = 0;
        foreach ($temp_gift as $c=>$e){
            $min_amount_array[$c] = $e['m'];
        }
        array_multisort($min_amount_array,SORT_ASC,$temp_gift);
        foreach($temp_gift as $k => $v){
            $goods_id = $v[0];
           // $goods_price = $v[1];
            $goods_number = $v[2];
            $is_package = empty($v[3])? 0 : 1;
            $act_id = $v[4];
            $send_num = isset($v[5])? $v[5] : 0;
            $buy_one = isset($v[6])? $v[6] : false;
            if(empty($goods_id)||empty($goods_number)||empty($act_id)){
                unset($temp_gift[$k]);
                continue;
            }
            if(!in_array('t_'.$k,session('cart_select'))){ //没选中的
                $_temp_gift[$n] = $v;
                session('temp_gift',$_temp_gift);
                $n++;
                continue;
            }
            if($send_num>1 || $buy_one){ //买一送一，在删减时处理
                $_temp_gift[$n] = $v;
                session('temp_gift',$_temp_gift);
                session('cart_select.t_'.$n,'t_'.$n);
                $n++;
                continue;
            }
            $act = $this->activity_model->where('act_id = '.$act_id)->find();
            if(in_array($act['act_type'],array(4,5,6))){ //折扣,在删减时处理
                $_temp_gift[$n] = $v;
                session('temp_gift',$_temp_gift);
                session('cart_select.t_'.$n,'t_'.$n);
                $n++;
                continue;
            }
            if($is_package){
                $package_info = $this->cart_model->getPackageInfo(0,0,$goods_id);//var_dump($package_info);
                if(empty($package_info)){
                    unset($temp_gift[$k]);//未找到对应商品,检查是否有绑商品
                    continue;
                }
                if($package_info['is_on_sale']==0){
                    unset($temp_gift[$k]);
                    continue;
                    //商品已下架
                }
                foreach($package_info['package_goods'] as $kk=>$vv){
                    if($vv['is_on_sale'] == 0 ){ //单品已下架
                        unset($temp_gift[$k]);
                        continue;
                    }
                    if(CHECK_STOCK && $vv['kc_goods_number'] <= 0){ //单品已售完
                        unset($temp_gift[$k]);
                        continue;
                    }
                }

            }else{
                $goods_info = $this->cart_model->getGoodsData($goods_id);
                if($goods_info['is_on_sale'] == 0){ //商品已下架
                    unset($temp_gift[$k]);
                    continue;
                }
                if(CHECK_STOCK && $goods_info['goods_number']<=0){ //商品已售完
                    unset($temp_gift[$k]);
                    continue;
                }
            }
            $cart_select = session('cart_select');
            if(!empty($cart_select)){
                $all_cart_data = $this->cart_model->cartData(array('user_id'=>$this->user_id,'rec_id'=>array('in',$cart_select)));
            }else{
                $all_cart_data = array();
            }
            $all_cart_data = $this->cart_model->cartFee($all_cart_data);//购物车统计
            $check_result = $this->checkActToCart($act,$all_cart_data,$goods_id,$is_package,$goods_number,0); //检查购物车是否可以加入活动商品
          if($check_result !== true){ //累加测试添加入购物车
                $num = 1;
                while($goods_number>=$num){
                    $check_result = $this->checkActToCart($act,$all_cart_data,$goods_id,$is_package,1,0);
                    if($check_result === true){
                        $v[2] = $num;
                        $_temp_gift[$n] = $v;
                        session('temp_gift',$_temp_gift);
                        session('cart_select.t_'.$n,'t_'.$n);
                        //重新统计购物车
                        $cart_select = session('cart_select');
                        if(!empty($cart_select)){
                            $all_cart_data = $this->cart_model->cartData(array('user_id'=>$this->user_id,'rec_id'=>array('in',$cart_select)));
                        }else{
                            $all_cart_data = array();
                        }
                        $all_cart_data = $this->cart_model->cartFee($all_cart_data);//购物车统计
                        $num++;//累加添加入购物车
                    }else{
                      break;
                    }
                }
                if(isset($_temp_gift[$n])){
                    $n++;
                }else{
                   // unset($temp_gift[$k]);
                    continue;
                }
            }else{
                $_temp_gift[$n] = $v;
                session('temp_gift',$_temp_gift);
                session('cart_select.t_'.$n,'t_'.$n);
                $n++;
            }
        }
  }

    /**
     * 活动商品加入购物车
     * @param $sn  活动商品购物车中序号
     * @param $g_info 商品信息
     * @return bool
     */
    private function selectActGoodsToCart($sn,$g_info){
        $act_id = $goods_id = $now = $is_package = 0;
        $act = $user_rank = $goods_info = $package_info = array();
        $goods_id = $g_info['goods_id'];
        if(!$goods_id){
            session('temp_gift.'.$sn,null); //缺少商品参数
            return false;
        }else{
            $is_package = $g_info['is_package'];
            $add_goods_number = $g_info['goods_number'];
            if(empty($add_goods_number)){
                session('temp_gift.'.$sn,null); //缺少添加商品的数量
                return false;
            }
            if($is_package){
                $package_info = $this->cart_model->getPackageInfo(0,0,$goods_id);//var_dump($package_info);

                if(empty($package_info)){
                    session('temp_gift.'.$sn,null); //未找到对应商品,检查是否有绑商品
                    return false;
                }
                if($package_info['is_on_sale']==0){
                    session('temp_gift.'.$sn,null); //商品已下架
                    return false;
                }

                foreach($package_info['package_goods'] as $k=>$v){
                    if($v['is_on_sale'] == 0){
                        session('temp_gift.'.$sn,null); //单品已下架
                        return false;
                    }
                    if(CHECK_STOCK && $v['kc_goods_number']<=0){
                        session('temp_gift.'.$sn,null); //单品已售完
                        return false;
                    }
                }

            }else{
                $goods_info = $this->cart_model->getGoodsData($goods_id);
                if($goods_info['is_on_sale'] == 0){
                    session('temp_gift.'.$sn,null); //商品已下架
                    return false;
                }
                if(CHECK_STOCK && $goods_info['goods_number']<=0){
                    session('temp_gift.'.$sn,null); //商品已售完
                    return false;
                }
            }
        }
        if($act_id = $g_info['act_id']){
            $act = $this->activity_model->where('act_id = '.$act_id)->find();
            if(empty($act)){
                session('temp_gift.'.$sn,null); //未找到对应活动
                return false;
            }
            $now = Time::gmTime();
            if(($act['start_time']) > $now){
                session('temp_gift.'.$sn,null); // 活动未开始
                return false;
            }
            if(($act['end_time'] ) < $now){
                session('temp_gift.'.$sn,null); //活动已结束;
                return false;
            }
        }else{
            session('temp_gift.'.$sn,null);
            return false; //未指定活动
        }


        $user_rank = explode(',',$act['user_rank']);
        if(!in_array(0,$user_rank)){
            if(empty($this->user_id)){
                return false; //请登陆后才能参加该活动
            }
        }

        $cart_select = session('cart_select');
        if(!empty($cart_select)){
            $all_cart_data = $this->cart_model->cartData(array('user_id'=>$this->user_id,'rec_id'=>array('in',$cart_select)));
        }else{
            $all_cart_data = array();
        }

        $all_cart_data = $this->cart_model->cartFee($all_cart_data);//购物车统计
        $check_result = false;
        while($check_result !== true){
            $check_result = $this->checkActToCart($act,$all_cart_data,$goods_id,$is_package,$add_goods_number,0); //检查购物车是否可以加入活动商品
            if($add_goods_number<=1 || $check_result==true){
                break;
            }else{
                $add_goods_number--;
            }
        }
        if($check_result !== true){
            return false; //暂未能参加该活动
        }

        $goods_act_price = $this->cart_model->goodsActPrice($act,$goods_id,$is_package);//商品活动价
        $discount = $this->cart_model->getDiscount($act,$all_cart_data['cart_data'],$goods_id,$is_package,$add_goods_number);
        if($discount !== false){
            $goods_act_price = round($goods_act_price * $discount);
        }else{
            if($act['act_type']==1){
                if($add_goods_number > 1){
                    $add_goods_number = 1;  //该活动一次加入商品数量不能大于1
                }
            }
            $is_buy_one_send_one = $this->cart_model->isBuyOneSendOne($act,$all_cart_data['cart_data'],$goods_id,$is_package);
        }

        $k = $this->cart_model->isInTempGift($goods_id,$is_package,$act_id);
        if (($k !==''|| ($k===0))){  //session购物车存在这个商品时
            $temp_gift = session('temp_gift');
            $temp_gift[$k][1] =  $goods_act_price;
            if(session('cart_select.'.'t_'.$k) != 't_'.$k  ){//没勾选时
                session('cart_select.t_'.$k,'t_'.$k);
                $temp_gift[$k][2] = $add_goods_number;
            }else{
                $temp_gift[$k][2] += $add_goods_number;
            }
            //更新的商品顺序改为最后加入
    /*        $replace_array= array();
            foreach($temp_gift as $t => $a){
                if($t>$k){
                    $replace_array[]=$a;
                }
            }
            if(!empty($replace_array)){
                $replace_array[] = $temp_gift[$k];
                array_splice($temp_gift,$k,count($temp_gift),$replace_array); //更新的商品顺序改为最后加入
            }*/
            session('temp_gift',$temp_gift);
        }else{
            $k = $this->cart_model->addTempGift(array('goods_id'=>$goods_id,'goods_price'=>$goods_act_price,'goods_number'=>$add_goods_number,'extension_code'=>($is_package? 'package_buy':''),'is_gift'=>$act_id));
        }

        if(isset($is_buy_one_send_one) && ($is_buy_one_send_one !== false)){ //买一送一
            if(!isset($temp_gift) || empty($temp_gift)){
                $temp_gift = session('temp_gift');
            }
            if(is_null($is_buy_one_send_one)){
                $temp_gift[$k][6] = true;
            }else{
                $key  = substr($all_cart_data['cart_data'][$is_buy_one_send_one]['rec_id'],2); //获取session记录中的键名
                unset($temp_gift[$key][6]);  //清除buy_one记录
                if( $all_cart_data['cart_data'][$is_buy_one_send_one]['goods_price'] < $goods_act_price){ //判断哪个价格低,价格低的赠品数加1
                    $n = $key;
                }else{
                    $n = $k;
                }
                if(isset($temp_gift[$n][5])){
                    $temp_gift[$n][5]++;
                }else{
                    $temp_gift[$n][5] = 1;
                }
            }
            session('temp_gift',$temp_gift);
        }
        if($discount!==false){ //有折扣更新同个活动其他商品价格
            if(!isset($temp_gift) && empty($temp_gift)){
                $temp_gift = session('temp_gift');
            }
            foreach($temp_gift as $kk => $v){ //同个活动其它商品价格
                if($v[4] == $act_id  && $kk != $k ){
                    if(empty($v[3])){
                        $is_package = 0;
                    }else{
                        $is_package = 1;
                    }
                    $goods_act_price = $this->cart_model->goodsActPrice($act,$v[0],$is_package);
                    $goods_act_price = round($goods_act_price * $discount);
                    $temp_gift[$kk][1] = $goods_act_price;
                }
            }
            session('temp_gift',$temp_gift);
        }
        return true;
    }


    /**
     * 检测套装状态
     * @param $package_info 套装信息
     */

  private function check_pack($package_info){
        if(empty($package_info)){
            $this->error('获取套装信息失败,可能已下架');
        }
/*    //允许可不绑商品
        if(empty($package_info['goods_id'])){
          $this->error('此套装没绑定商品');
        }
*/
        if($package_info['is_on_sale'] == 0){
            //套装'.$package_info['goods_id'].'已下架
            $this->error('商品缺货下架，抓紧咨询客服抢购吧!');
        }
        foreach($package_info['package_goods'] as $k=>$v){
            if($v['is_on_sale'] == 0) {
                //'套装单品'.$package_info['goods_id'].'已下架'
                $this->error('商品缺货下架，抓紧咨询客服抢购吧!');
            }
            if(CHECK_STOCK &&$v['goods_number'] == 0){
                //套装单品'.$package_info['goods_id'].'无库存
                $this->error('商品缺货下架，抓紧咨询客服抢购吧!');
            }
        }
  }

}