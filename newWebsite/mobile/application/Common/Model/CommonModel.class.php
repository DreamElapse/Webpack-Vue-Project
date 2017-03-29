<?php
/**
 * ====================================
 * 公共模型
 * ====================================
 * Author: 9004396
 * Date: 2016-06-25 10:23
 * ====================================
 * File: CommonModel.class.php
 * ====================================
 */
namespace Common\Model;
use Think\Model;

class CommonModel extends Model{


    protected $user_id = 0;


    public function _initialize(){
        if(empty($this->user_id)){
            $this->user_id = session('user_id');
        }
    }


    /**
     * 获取用户信息
     * @param string $field 获取所需字段，空值为全部字段
     * @return mixed
     */
    public function getUser($field = ''){
        if($field == 'user_id'){
            return $this->user_id;
        }
        $user = M('Users',null,'USER_CENTER');
        $user->where(array('user_id' => $this->user_id));
        if(!empty($field)){
            $fields = is_string($field) ? explode(',', $field) : $field;
            if(count($fields) == 1){
                return $user->getField($fields);
            }else{
                return $user->field($fields)->find();
            }
        }else{
            return $user->find();
        }
    }

}