<?php
/**
 * ====================================
 * 口碑中心模型
 * ====================================
 * Author: 9004396
 * Date: 2016-07-05
 * ====================================
 * File: PraiseModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;

class PraiseModel extends CommonModel {

    protected $tableName = 'article';
    /**
     * 获取列表
     * @author 9009221
     * @param $cat_id
     * @param $page
     * @param $size
     * @return array
     */
    public function getList($cat_id, $page = 1, $size = 5, $order = '') {
		$cat_id_array = array();
		$orders = empty($order) ? 'article_id DESC' : $order;
        if (!$cat_id) {
            $cat = $this->getCat('口碑中心');
            foreach ($cat as $value) {
                $cat_id_array[] = $value['cat_id'];
            }
			$cat_id_array[] = $cat_id;
            $cat_id = implode(',', $cat_id_array);
        }
		$where = array();
		if($cat_id != ''){
			$where[] = 'cat_id in ('.$cat_id.')';
		}
		$where[] = 'is_open = 1';
        $offset = ($page-1)*$size;
        $list = $this->where(implode(' and ', $where))
				->field('article_id,title,add_time,file_url,link,thumb_url,fav_count,cat_id,author,keywords,description,author_age')
				->limit($offset, $size)
				->order($orders)
				->select();
		foreach($list as &$val){
			$val['thumb_url'] = C('domain_source.img_domain').$val['thumb_url'];
			$val['file_url'] = C('domain_source.img_domain').$val['file_url'];
		}
        return $list;
    }
	
	/*
    *	随机推荐文章 -  口碑分享
    *	@Author 9009123
	*	@param $id
	*	@param $num
	*	@param $_cat_ids
    *	@return string
    */
	public function randList($id,$num=4,$_cat_ids=array()){
		$cat_id = $this->where("article_id = '$id'")->getField('cat_id');
		
		$ArticleCat = D('ArticleCat');
		
		//检查是否微信访问，特殊处理
		$wx_catids = $ArticleCat->wxCatIds('微信');
		if(isset($wx_catids[0]) && $wx_catids[0]>0 && in_array($cat_id,$wx_catids)){
			return $this->randList($id,3,$wx_catids);
		}
		
		$where = ' 1 = 1 ';
		if(empty($_cat_ids)){
			$article_cate = array('mb'=>'美白','bs'=>'保湿','qj'=>'清洁','ns'=>'男士','jz'=>'紧致','qt'=>'其他');//所有分类定义
			$cat_ids = $ArticleCat->nameToId($article_cate);
			if( count($cat_ids)>=1){
				foreach($cat_ids as $k=>$v){
					$_cat_ids[] = $v['cat_id'];
				}
			}
		}
		if(count($_cat_ids)>=1 ) {
			$where .= ' AND cat_id in (' . implode(',', $_cat_ids) . ')';
		}
	
		if(!empty($id)){
			$where .= ' AND article_id != '.$id;
		}
		$return = $this->field('article_id,title,fav_count')->where($where)->order('rand()')->limit($num)->select();
		foreach($return as $k =>$v){
			$return[$k]['title'] = str_replace(array('<br>','<br/>','<br />','&lt;br&gt;'),',',strtolower($v['title']));
		}
	
		if(!empty($return)){
			return $return;
		}else{
			return '';
		}
	}

    /**
     * 获取详情
     * @author 9009221
     * @param $id
     * @return array
     */
    public function getDetail($id) {
        $where = 'article_id = '.$id;
        $ret = $this->where($where)->find();
		$ret['content'] = replaceHtml($ret['content'], C('domain_source.img_domain'));
        return $ret;
    }

    /**
     * 判断该文章是否点赞
     * @author 9009221
     * @param $id
     * @param $like_key
     * @return int
     */
    public function liked($id, $like_key) {
        $where = 'article_id='.$id.' AND `key`="'.$like_key.'" and status = 1';
		//echo $where;die;
        $ret = D('ArticleLikeLog')->where($where)->getField('id');
        return $ret;
    }

    /**
     * 获取点赞次数
     * @author 9009221
     * @param $article_id
     * @param $key
     * @param $time
     * @return int
     */
    public function click_num($article_id,$key,$time) {
        $time = time() - $time;
        $where = array(
            'article_id'    => $article_id,
            'key'           => $key,
            'time'          => array('gt',$time)
        );
//        $where = 'article_id='.$article_id .' AND `key`="'.$key.'" AND `time`>'.$time;
//        $ret = $this->table('ecs_article_like_log')->field('count(*) as num')->where($where)->getField('num');
        $ret = D('ArticleLikeLog')->where($where)->count();
        return $ret;
    }

    /**
     * 点赞
     * @author 9009221
     * @param $article_id
     * @param $key
     * @return int
     */
    public function insert_like($article_id,$key) {
        $this->where('article_id='.$article_id)->setInc('fav_count');
        $data = array(
            'article_id' => $article_id,
            'key' => $key,
            'ip' => get_client_ip(),
            'time' => time(),
            'status' => 1
        );
        $ret = D('ArticleLikeLog')->add($data);
		
        return $ret;
    }

    /**
     * 取消点赞
     * @author 9009221
     * @param $article_id
     * @param $id
     * @return int
     */
    public function update_dis_like($article_id,$id){
        $this->where('article_id='.$article_id)->setDec('fav_count');
        $data = array(
            'status' => 0,
            'updatetime' => time()
        );
        $ret = D('ArticleLikeLog')->where('id='.$id)->save($data);
        return $ret;
    }

    /**
     * 获取分类
     * @author 9009221
     * @param $catname
     * @return array
     */
    public function getCat($catname) {
        $cat_id = $this->table('ecs_article_cat')->where("cat_name='".$catname."'")->getField('cat_id');
        $cats = $this->table('ecs_article_cat')->field('cat_id,cat_name')->where('parent_id='.$cat_id)->select();
        return $cats;
    }
}