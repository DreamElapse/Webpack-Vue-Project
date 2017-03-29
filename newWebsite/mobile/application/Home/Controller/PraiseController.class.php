<?php
/**
 * ====================================
 * 口碑中心
 * ====================================
 * Author: 9009221
 * Date: 2016-07-05
 * ====================================
 * File: PraiseController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;

class PraiseController extends InitController{
	private $praise_like_log;

    public function  __construct() {
        parent::__construct();
        $this->mpraise = D('Praise');
		
		$this->praise_like_log = D('PraiseLikeLog');
    }

    /*
    *	口碑中心列表
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function praise_list() {
        if (IS_AJAX) {
            $cat_id = I('cat_id', 0);
            $page = I('page',1);
            $data = $this->mpraise->getList($cat_id, $page);
			
			$like_key = cookie('like_key');
            if (!$like_key) {
                $like_key = session_id();
            }
            cookie('like_key', $like_key, 3600*24*356);
			
			if(!empty($data)){
				foreach($data as $key=>$value){
					$liked = $this->praise_like_log->liked($value['article_id'],session_id());
					$data[$key]['liked'] = !empty($liked) ? 1 : 0;
				}
			}
            if ($data) {
                $this->success($data);
            } else {
                $this->error('没有数据');
            }
        } else {
            $this->display();
        }
    }

    /*
    *	口碑中心详情
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function praise_detail() {
        if (IS_AJAX) {
            $id = I('id', '', 'intval');
            if (!$id) {
                $this->error('文章id不能为空');
            }
            $data = $this->mpraise->getDetail($id);
            $this->success($data);
        } else {
            $this->display();
        }
    }
	
	/*
    *	口碑中心详情 -  粉丝推荐
    *	@Author 9009123
    *	@return exit && JSON
    */
    public function detailGoods() {
		$id = I('id', 0, 'intval');
		if ($id <= 0) {
			$this->error('文章id不能为空');
		}
		$data = D('PraiseGoods')->getGoods($id);
		$this->success($data);
    }
	
	/*
    *	口碑中心详情 -  口碑分享
    *	@Author 9009123
    *	@return exit && JSON
    */
    public function detailRand() {
		$id = I('id', 0, 'intval');
		if ($id <= 0) {
			$this->error('文章id不能为空');
		}
		$data = $this->mpraise->randList($id);
		$this->success($data);
    }

    /*
    *	口碑中心点赞
    *	@Author 9009221
    *	@param
    *	@return exit && JSON
    */
    public function praise_like() {
        $id = I('id', '', 'intval');
        if (!$id) {
            $this->error('文章id不能为空');
        }
        $like_key = cookie('like_key');
        if (!$like_key) {
            $like_key = session_id();
        }
        cookie('like_key', $like_key, 3600*24*356);
        $liked = $this->praise_like_log->liked($id,$like_key);
        $click_num = $this->praise_like_log->click_num($id,$like_key,1800);
        if ($click_num > 2) {
            $this->error('您对该文章操作太频繁了');
        }
		
        if(empty($liked)){
            $ret = $this->mpraise->insert_like($id,$like_key);
        }else{
            $ret = $this->mpraise->update_dis_like($id,$liked);
        }
        if ($ret) {
            $this->success();
        } else {
            $this->error('点赞功能有问题，请联系技术');
        }
    }
	
	/*
    *	首页晒单,只获取指定，推荐排序的四条
    *	@Author 9006758
    *	@param
    *	@return exit && JSON
    */
    public function praiseIndex() {
		$pageNum = I('request.pageNum', 4, 'intavl');
        $praise_index = $this->mpraise->getList(0, 1, $pageNum, 'article_type DESC,is_show_relate DESC,article_id DESC');
		$this->success($praise_index );
    }
    
}