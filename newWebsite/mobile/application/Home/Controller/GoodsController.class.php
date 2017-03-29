<?php
/**
 * ====================================
 * 商品控制器
 * ====================================
 * Author: 9006758
 * Date: 2016/6/28
 * ====================================
 * File: GoodsController.class.php
 * ====================================
 */

namespace Home\Controller;

use Common\Controller\InitController;
use Common\Extend\Time;
use Home\Model\CartModel;

class GoodsController extends InitController
{

    public function index()
    {
    }

    /*
    *	获取商品列表 - 根据商品ID获取
    *	@Author 9009123 (Lemonice)
    *	@return array
    */
    public function getListForId()
    {
        $goods_id = I('goods_id', '', 'trim');
        if ($goods_id == '') {
            $this->error('请传商品ID！');
        }
        $GoodsModel = D('Goods');
        $goods_list = $GoodsModel->getList($goods_id, NULL, NULL, 0, 0, 1, 0);

        $this->success($goods_list);
    }

    /**
     *    下拉商品分类
     *
     */
    public function getCates()
    {
        // $cates = D('Goods')->getCates();
        $cates = C('category_list.' . C('SITE_ID'));
        $this->success($cates);
    }

    /**
     *    商品二级页
     *
     * @param int $page 页数 默认1
     * @param int $package 单品套装区分 默认0-不区分，1-单品，2-套装
     * @param string $price 排序 asc-升序， desc-降序
     * @param int $cid 分类id 默认0
     * @param string $keyword 搜索的字符
     *
     */
    public function lists()
    {
        $goodsModel = D('Goods');

        //页码
        $page = I('request.page', 1, 'intval');

        //单品或套装筛选
        $is_package = I('request.package', 0, 'intval');
        //价格排序
        $price = I('request.price', NULL, 'trim');

        //分类筛选
        $cate_id = I('request.cid', 0, 'intval');

        //关键词搜索，搜索分类，以过去分类下的商品
        $keyword = I('request.keyword', NULL);

        $goods = $goodsModel->getList(NULL, $price, $keyword, $cate_id, $is_package, $page);

        $this->success($goods);
    }

    /**
     * 商品详情页面
     */
    public function detail()
    {

        $good_id = I('request.gid', 0, 'intval');
        $package = I('request.is_package', 0, 'intval');
        if ($good_id <= 0) {
            $this->error('商品编号错误！');
        }

        $goodsModel = D('Goods');
        $now_time = Time::gmTime();

        if ($package) {
            //因某些页面传过来的是套装id，所以做以下出来
            $is_package = $goodsModel->getPackageInfo(0, $now_time, $good_id);
            if (!empty($is_package)) {
                $good_id = $is_package['goods_id'];
            } else {
                $this->error('套装未绑定商品');
            }
        }

        $good_info = $goodsModel->getGoodInfo($good_id);

        //商品不存在
        if (!$good_info) {
            $this->error('not exist or off the shelf!');
        }

        // 判断是否为套装，是则读取该套装下的子商品信息
        if ($good_info['is_package'] == 1) {
            $package_info = $goodsModel->getPackageInfo($good_id, $now_time);
            if (!$package_info) {
                $good_info['is_on_sale'] = $package_info['is_on_sale'];
            }
            $good_info['package_info'] = $package_info;
        }

        if ($good_info['is_on_sale'] == 0) {
            $this->error('not exist or off the shelf!');
        }

        /**
         *    评论表中缺少字段 imgs
         *    `imgs` text COMMENT '评论相册',
         *    sql : ALTER TABLE `ecs_comment` ADD `imgs` text COMMENT '评论相册'；
         */
        //获取商品对应评论
        // $good_comments = $goodsModel->getComments($good_id, 0, 0, 3);
        // $good_info['good_comments'] = $good_comments;

        //商品相册
        $good_imgs = $goodsModel->getGallery($good_id);
        $good_info['good_imgs'] = $good_imgs;

        $this->success($good_info);
    }

    /**
     *    获取搭配购买的商品
     * @param $gid int 商品id 必传参数
     *
     */
    public function getLinkGoods()
    {
        $goodsModel = D('Goods');
        $good_id = I('request.gid', 0, 'intval');
        if ($good_id <= 0) {
            $this->error('套装以下架');
        }
        $link_goods = $goodsModel->getLinkGoods($good_id);

        $this->success($link_goods);
    }


    /**
     *    获取商品评价接口
     *    商品id 为必传参数
     *    页码page，每页显示数量limit，获取的评论类型type 为可选传参数
     *    评论类型，获取追加评论，type=0：为全部，其他看评论设置
     */
    public function getGoodComment()
    {
        $good_id = I('request.gid', 0, 'intval');
        if ($good_id <= 0) {
            $this->error('params error!');
        }
        $limit = I('request.limit', 0, 'intval');
        $page = I('request.page', 0, 'intval');
        if ($page > 0) {
            $page = ($page - 1) * $limit;
        } else {
            $page = 0;
        }
        $get_type = I('request.type', 0, 'intval');
        $comments = D('Goods')->getComments($good_id, $get_type, $page, $limit, 'show_time DESC');
        foreach ($comments as &$val) {
            $val['show_time'] = date('Y-m-d', $val['show_time']);
        }

        $this->success($comments);
    }

    /**
     *    商品添加到购物车
     *
     * @param request .gid 商品id（套装或者单品goods_id）
     *
     */
    public function addToCart()
    {
        $goods_id = I('request.gid', 0, 'intval');
        if ($goods_id <= 0) {
            $this->error('param error!');
        }
        $goods_number = I('request.num', 1, 'intval');

        $goodsModel = D('Goods');
        $goods_info = $goodsModel->getGoodInfo($goods_id);
        if (CHECK_STOCK) {
            if ($goods_info['goods_number'] <= 0) {
                $this->error('库存不足');
            }
        }

        if (empty($goods_info)) {
            $this->error('商品已下架!');
        }
        $user_id = session('user_id') ? session('user_id') : 0;
        $cart_data = array(
            'user_id' => $user_id,
            'session_id' => session_id(),
            'goods_id' => '',
            'goods_sn' => $goods_info['goods_sn'],
            'product_id' => 0,
            'goods_name' => '',
            'market_price' => '',
            'goods_price' => '',
            'goods_number' => $goods_number,
            'goods_attr' => '',
            'is_real' => $goods_info['is_real'],
            'extension_code' => '',
            'parent_id' => '',
            'rec_type' => 0,
            'is_gift' => 0,
            'is_shipping' => 0,
            'can_handsel' => 0,
            'goods_attr_id' => '',
        );
        $cartModel = new cartModel();
        if (!$goods_info['is_package']) {
            //单品
            $_map['session_id'] = session_id();
            if ($user_id && $user_id > 0) {
                $_map['user_id'] = $user_id;
                $_map['_logic'] = "OR";
            }
            $where['_complex'] = $_map;
            $where['goods_id'] = $goods_info['goods_id'];
            // $where['session_id'] = session_id();
            $where['extension_code'] = array('neq', 'package_goods');

            $goods_exist = $cartModel->where($where)->find();
            if (empty($goods_exist)) {
                $cart_data['goods_id'] = $goods_info['goods_id'];
                $cart_data['goods_name'] = $goods_info['goods_name'];
                $cart_data['market_price'] = $goods_info['market_price'];
                $cart_data['goods_price'] = $goods_info['shop_price'];
                $add_id = $cartModel->add($cart_data);
                session('cart_select.' . $add_id, $add_id);
            } else {
                $cartModel->where($where)->save(array('goods_number' => array('exp', 'goods_number+' . $goods_number)));
                session('cart_select.' . $goods_exist['rec_id'], $goods_exist['rec_id']);
            }

        } else {
            //套装
            $package_info = $goodsModel->getPackageInfo($goods_id);
            if (!$package_info) {
                $this->error('商品已下架');
            }

            $_map['session_id'] = session_id();
            if ($user_id && $user_id > 0) {
                $_map['user_id'] = $user_id;
                $_map['_logic'] = "OR";
            }
            $where['_complex'] = $_map;
            $where['goods_id'] = $package_info['act_id'];
            $where['extension_code'] = 'package_buy';

            $goods_exist = $cartModel->where($where)->find();
            if (empty($goods_exist)) {
                $cart_data['goods_id'] = $package_info['act_id'];
                $cart_data['goods_name'] = $package_info['act_name'];
                $cart_data['market_price'] = $goods_info['market_price'];
                $cart_data['goods_price'] = $package_info['package_price'];
                $cart_data['extension_code'] = 'package_buy';
                $add_id = $cartModel->add($cart_data);
                session('cart_select.' . $add_id, $add_id);

                //子商品加入购物车
                foreach ($package_info['package_goods'] as $key => $val) {
                    $package_goods_data[$key]['user_id'] = session('user_id') ? session('user_id') : 0;
                    $package_goods_data[$key]['session_id'] = session_id();
                    $package_goods_data[$key]['goods_id'] = $val['goods_id'];
                    $package_goods_data[$key]['goods_sn'] = $val['goods_sn'];
                    $package_goods_data[$key]['goods_name'] = $val['goods_name'];
                    $package_goods_data[$key]['market_price'] = $val['market_price'];
                    $package_goods_data[$key]['goods_price'] = $val['shop_price'];
                    $package_goods_data[$key]['goods_number'] = $val['goods_number'];
                    $package_goods_data[$key]['is_real'] = $val['is_real'];
                    $package_goods_data[$key]['extension_code'] = 'package_goods';
                    $package_goods_data[$key]['parent_id'] = $val['package_id'];
                }
                $cartModel->addAll($package_goods_data);

            } else {

                $cartModel->where($where)->save(array('goods_number' => array('exp', 'goods_number+' . $goods_number)));
                session('cart_select.' . $goods_exist['rec_id'], $goods_exist['rec_id']);
            }

        }

        $cart_info = $cartModel->cartData(array('user_id' => session('user_id') ? session('user_id') : 0), false, false);
        //购物车商品总数
        $goods_nums = 0;
        foreach ($cart_info as $key => $val) {
            if ($val['parent_id'] > 0) {
                continue;
            }
            $goods_nums += $val['goods_number'];
        }
        $this->success(array('goods_num' => $goods_nums));
    }

    /**
     *    首页晒单商品
     *
     */
    public function hotGoods()
    {
        $goods_ids = C('hot_goods.' . C('SITE_ID'));
        $goodsModel = D('Goods');
        $index_hot_goods = array();
        foreach ($goods_ids as $val) {
            $index_hot_goods[] = $val['goods_id'];
        }
        $hotGoods = $goodsModel->where(array('is_on_sale' => 1, 'is_show' => 1, 'is_delete' => 0, 'goods_id' => array('in', $index_hot_goods)))
            ->field('goods_id,goods_name,market_price,shop_price,goods_img,goods_thumb,original_img,is_package')
            ->select();
        foreach ($hotGoods as &$val) {
            $val['goods_img'] = C('domain_source.img_domain') . $val['goods_img'];
            $val['goods_thumb'] = C('domain_source.img_domain') . $val['goods_thumb'];
            $val['original_img'] = C('domain_source.img_domain') . $val['original_img'];
        }
        $this->success($hotGoods);
    }


    /*
    *	专题页 - 获取商品, 根据不同类型获取不同商品
    *	@Author 9009123 (Lemonice)
    *	@return array
    */
    public function specialPage()
    {
        $site_id = C('site_id');
        $page_name = I('request.page_name', 'mb', 'trim');
        $limit = I('request.limit', 0, 'intval');
        $page = I('request.page', 0, 'intval');
        $get_type = I('request.type', 0, 'intval');

        if ($page > 0) {
            $page = ($page - 1) * $limit;
        } else {
            $page = 0;
        }

        $data = array(
            'name' => '',  //专题页名称
            'goods_list' => array(),  //商品列表
            'comment_list' => array(),  //评论列表
        );
        $special_page = C('SPECIAL_PAGE');  //加载配置文件
        $special_page = isset($special_page[$site_id]) ? $special_page[$site_id] : array();
        if (!empty($special_page) && isset($special_page[$page_name])) {
            $config = $special_page[$page_name];  //对应这个分类的配置
            $GoodsModel = D('Goods');
            $data['goods_list'] = $GoodsModel->getSpecialList($config['goods_id']);
            $data['comment_list'] = $GoodsModel->getComments($config['goods_id'], $get_type, $page, $limit, 'show_time desc');
            $data['name'] = $config['name'];
        }
        $this->success($data);
    }

    /**
     * 判断商品是否收藏
     */
    public function isCollectGoods()
    {
        if (empty($this->user_id)) {
            $this->error('请先登陆。');
        }
        $goods_id = I('request.goods_id', 0);
        $site_id = C('site_id');
        if (empty($goods_id)) {
            $this->error('该商品不存在！');
        }
        $collectGoodsInfo = D('CollectGoods')->where(array('goods_id' => $goods_id, 'user_id' => $this->user_id, 'site_id' => $site_id))->count();
        if ($collectGoodsInfo > 0) {
            $this->success('该商品已经收藏！');
        } else {
            $this->error('该商品未收藏！','',2);
        }
    }


    /**
     * 收藏商品
     */
    public function collectGoods()
    {
        if (empty($this->user_id)) {
            $this->error('请先登陆。');
        }
        $goods_id = I('request.goods_id', 0);
        $type = I('request.type'); //商品类型：1，单品；2，套装；3，团购；4，秒杀；5，积分兑换;6,免费试用
        $site_id = C('site_id');
        $url = I('request.source'); //当前地址,前端发送#后面的字符串
        if (empty($goods_id)) {
            $this->error('该商品不存在！');
        }
        $collectGoodsInfo = D('CollectGoods')->where(array('goods_id' => $goods_id,'user_id' => $this->user_id,'site_id' => $site_id))->count();
        if($collectGoodsInfo > 0){
            $result = D('CollectGoods')->where(array('goods_id' => $goods_id,'user_id' => $this->user_id,'site_id' => $site_id))->delete();
            if($result){
                $this->success('取消收藏成功！');
            }else{
                $this->error('取消收藏失败！');
            }
        }else{
            if (empty($type)) {
                $this->error('该商品类型不存在！');
            }
            $goodsInfo = D('Goods')->field('goods_img,goods_name')->where('goods_id = ' . $goods_id)->find();
            $data['site_id'] = $site_id;
            $data['type'] = $type;
            $data['user_id'] = $this->user_id;
            $data['goods_id'] = $goods_id;
            $data['name'] = $goodsInfo['goods_name'];
            $data['img'] = C('domain_source.img_domain') . $goodsInfo['goods_img'];
            $data['url'] = empty($url) ? siteUrl() : siteUrl() . '#' . $url;
            $data['add_time'] = time();
            $result = D('CollectGoods')->add($data);
            if ($result) {
                $this->success('收藏成功！');
            } else {
                $this->error('收藏失败');
            }
        }

    }

}