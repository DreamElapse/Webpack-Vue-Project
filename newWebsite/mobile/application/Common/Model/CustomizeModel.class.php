<?php
/**
 * ====================================
 * 自定义库表公共模型
 * ====================================
 * Author: 9004396
 * Date: 2016-07-06 17:18
 * ====================================
 * File:CustomizeModel.class.php
 * ====================================
 */
namespace Common\Model;
use Think\Model;

class CustomizeModel extends Model{
    protected $_config = null;
    protected $_table = null;

    protected $user_id = 0;

    public function __construct()
    {
        if(empty($this->_config)){
            $config = '';
        }else{
            if(is_string($this->_config)){
                $config = C($this->_config);
            }else{
                $config = $this->_config;
            }
        }
        if(is_null($this->_table)){
            $this->_table = '';
        }
        parent::__construct($this->_table, $config['DB_PREFIX'], $config);
    }

    public function _initialize(){
        if(is_null($this->user_id)){
            $this->user_id = session('user_id');
        }
    }



}