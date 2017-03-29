<?php
/**
 * ====================================
 * 口碑中心点赞记录模型
 * ====================================
 * Author: 9004396
 * Date: 2016-07-05
 * ====================================
 * File: PraiseLikeLogModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;

class PraiseLikeLogModel extends CommonModel {

    protected $tableName = 'article_like_log';
    

    /**
     * 判断该文章是否点赞
     * @author 9009221
     * @param $id
     * @param $like_key
     * @return int
     */
    public function liked($id, $like_key) {
        $where = 'article_id="'.$id.'" AND `key`="'.$like_key.'" and status = 1';
        $ret = $this->where($where)->getField('id');
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
        $ret = $this->where($where)->count();
        return $ret;
    }
}