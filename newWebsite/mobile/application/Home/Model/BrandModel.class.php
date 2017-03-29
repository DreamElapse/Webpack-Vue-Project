<?php
/**
 * ====================================
 * 品牌动态模型
 * ====================================
 * Author: 9004396
 * Date: 2016-07-08
 * ====================================
 * File: BrandModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;
use Common\Extend\Time;

class BrandModel extends CommonModel {

    protected $tableName = 'brand_dynamics';
    /**
     * 获取列表
     * @author 9009221
     * @param $page
     * @param $size
     * @return array
     */
    public function getList($page = 1, $size = 5) {
        $where = 'display = 1';
        $offset = ($page-1)*$size;
        $list = $this->where($where)->field('id,short_title,title_color,thumb_one,thumb_two,thumb_three,target,create_time')->order('is_recommend desc,is_top desc,sort desc,create_time desc')->limit($offset, $size)->select();
		if(!empty($list)){
			foreach($list as $key=>$value){
				$value['create_date'] = Time::localDate('Y-m-d H:i',$value['create_time']);
				$value['thumb_one'] = C('domain_source.img_domain').$value['thumb_one'];
				$value['thumb_two'] = C('domain_source.img_domain').$value['thumb_two'];
				$value['thumb_three'] = C('domain_source.img_domain').$value['thumb_three'];
				$list[$key] = $value;
			}
		}
        return $list;
    }

    /**
     * 获取详情
     * @author 9009221
     * @param $id
     * @return array
     */
    public function getDetail($id) {
        $where = 'display = 1 and id = '.$id;
        $ret = $this->field('id,title,content,create_time')->where($where)->find();
		if(!empty($ret)){
			$ret['create_date'] = isset($ret['create_time'])&&$ret['create_time']>0 ? date('Y-m-d H:i',$ret['create_time']) : '';
			$ret['content'] = replaceHtml($ret['content'], C('domain_source.img_domain'));
		}
        return $ret;
    }
    
}