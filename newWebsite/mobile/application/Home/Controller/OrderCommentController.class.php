<?php
/**
 * ====================================
 * 订单评论 控制器
 * ====================================
 * Author: 9006765
 * Date: 2017-02-20
 * ====================================
 * File: OrderCommentController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\Curl;


class OrderCommentController extends InitController{

    protected  $user_info;
	public function __construct(){
		parent::__construct();
        $this->user_id = $this->checkLogin();
        $this->user_info = D('users')->getUserInfo($this->user_id);
	}

    public function comment(){
        if(!isset($_POST['comment']) || empty($_POST['comment'])){
            $this->error('参数缺失');
        }

        $f_keys = array(
            'target' => 'evalTarget', //评价目标
            'name'=>'name',     //  '评价对象名称'
            'scope'=>'scope', // 星级
            'order_sn'=>'rid', //来源订号
            // 'dime'=>'evalDime',
            // 'channel'=>'channel',
            // 'type'=>'evalType',
            // 'content'=>'content',
        );
        $keys = array(
            'code'=>'code',
            'company'=>'company',
            'dept' =>'dept',
            'mcontent'=>'msgContent',
            'from_site'=>'from_site',
        );

        $p = array();
        $add_p = array();
        $n = 0;
        $post_data = json_decode(str_replace('\\','',$_POST['comment']));
        if(isset($post_data[0]->order_sn) && !empty($post_data[0]->order_sn)){
            $where[] = "site_id = ".C('SITE_ID');
            $where[] = "user_id = ".$this->user_id;
            $where[] = "order_sn = ".$post_data[0]->order_sn;
            $OrderInfoCenter = D('OrderInfoCenter');
            $field = 'consignee';
            $order_info = $OrderInfoCenter->field($field)->where(implode(' and ',$where))->find();
        }


        foreach($post_data as $kk=>$pv){
            foreach($f_keys as $k => $v){
                if (!isset($pv->$k) || empty($pv->$k)){
                    $key_msg = $this->keyTranslate($v);
                    if($key_msg == '未能识别的键名'){
                        $this->error($key_msg.$k);

                    }else{
                        if($pv->$k==0){
                            $this->error( $key_msg.'不能为0！');
                        }else{
                            $this->error( '缺少'.$key_msg.'参数');
                        }
                    }
                }else{
                    $p[$kk][$v] = $pv->$k;
                }
                if($k == 'target' && $pv->$k == '01'){
                    $p[$kk]['evalDime']  = '0102';
                    $p[$kk]['content']   = '质量';
                }else if($k == 'target' && $pv->$k == '02'){
                    $p[$kk]['evalDime']  = '0202';
                    $p[$kk]['content']   = '售中服务满意度（购物体验）';
                }
            }
            foreach($keys as $k => $v){
                if(isset($pv->$k) && !empty($pv->$k)){
                    $p[$kk][$v] = $pv->$k;
                }
            }
            if(!isset($p['from_site']) || empty($p['from_site'])){
                $p[$kk]['from_site'] = C('SITE_ID');
            }
            $p[$kk]['channel'] = '01';
            $p[$kk]['evalType'] = '01';
            $p[$kk]['evalPerson'] = empty($order_info['consignee'] )?  '匿名' : $order_info['consignee'] ;
            if($p[$kk]['evalTarget'] == '01'){
                $add_p[$kk] = $p[$kk];
                $add_p[$kk]['evalDime'] = '0103';
                $add_p[$kk]['content'] = '价格';
            }
        }
        if(!empty($add_p)){
            foreach($add_p as $k => $v){
                if(!empty($v)){
                    $kk++;
                    $p[$kk] = $v;
                }
            }
        }
        $is_comment = Curl::getApiResponse('http://api.chinaskin.cn/Comment/getComment',array('order_sn'=>$p[$kk]['rid'],'from_site'=>$p[$kk]['from_site']));
        if($is_comment['error'] == 'A0000'){
             $this->error('您已评论');
        }
        $post_data['comment'] = $p;
        $r = Curl::getApiResponse('http://api.chinaskin.cn/Comment/pageComment',$post_data);//var_dump($r);
        $this->ajaxReturn($r);
    }

    /**
     * 获取评论信息，用于判断是否评论
     */
    public function getComment(){

        $post_data['order_sn'] =  I('post.order_sn',0);
        if(empty($post_data['order_sn'])){
            $r = array('error'=>'A00001','message'=>'订单号不能为空','data'=>'');
            $this->ajaxReturn($r);
        }
        $post_data['from_site'] =  C('SITE_ID');
        $r = Curl::getApiResponse('http://api.chinaskin.cn/Comment/getComment',$post_data);
        $this->ajaxReturn($r);

    }


    /**
     * 字段名称对照
     * @param $k
     * @return string
     */
    private function keyTranslate($k){

        $data = array(
            'evalTarget' => '评价目标',
            'evalDime' => '评价维度',
            'name'=> '评价对象名称',
            'channel' => '评价渠道',
            'evalType' => '评价方式',
            'content'=> '评价内容',
            'scope'=> '评分',
            'evalPerson'=> '评价人',
            'evalDate'=> '评价时间',
            'msgContent'=>'客户留言',
            'code'=>'评价对象编码',
            'company'=>'公司',
            'dept'=>'部门',
            'rid'=>'来源订单号',
            'from_site'=>'来源站',
        );
        if(isset($data[$k])){
            return $data[$k];
        }else{
            return '未能识别的键名';
        }
    }


/*
*	检查当前是否登录
*	@return int [user_id]
*/
    private function checkLogin(){
        $user_id = $this->getUserId();  //用户ID
        if($user_id <= 0){
            $this->error($this->not_login_msg);  //没登录
        }
        return $user_id;
    }

 /*
  *	获取当前登录用户ID
  * *	@return int [user_id]
  */
    private function getUserId(){
        $user_id = D('users')->getUser('user_id');  //用户ID
        $user_id = $user_id ? $user_id : 0;
        return $user_id;
    }



}