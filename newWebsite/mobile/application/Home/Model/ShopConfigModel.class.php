<?php
/**
 * ====================================
 * 后台配置信息模型
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-06-29 15:01
 * ====================================
 * File: ShopConfigModel.class.php
 * ====================================
 */
namespace Home\Model;
use Common\Model\CommonModel;

class ShopConfigModel extends CommonModel{
	private $type_default = array('intval'=>0,'floatval'=>0,'trim'=>'');  //类型默认值
	private $_config_type = array(  //初始化配置的值
		'intval'=>array(
			'watermark_alpha',
			'cache_time',
			'thumb_width',
			'thumb_height',
			'image_width',
			'image_height',
			'bought_goods',
			'goods_name_length',
			'top10_time',
			'show_order_type',  // 显示方式默认为列表方式
		),
		'floatval'=>array(
			'market_price_rate',
			'integral_scale',
			//'integral_percent',
			'min_goods_amount'=>0,
		),
		'trim'=>array(
			'qq',
			'ww',
		),
		
	);
	private $_config_default = array(  //初始化配置的值, 如果没有值则设置默认值
		'intval'=>array(
			'best_number'=>3,
			'new_number'=>3,
			'hot_number'=>3,
			'promote_number'=>3,
			'top_number'=>10,
			'history_number'=>5,
			'comments_number'=>5,
			'article_number'=>5,
			'page_size'=>10,
			'goods_gallery_number'=>5,
			'help_open'=>1,  // 显示方式默认为列表方式
			'default_storage'=>1,
		),
	);
	
	/*
	*	加载、获取配置信息
	*	@Author 9009123 (Lemonice)
	*	@param  $field array|string  查询的配置字段（多个逗号隔开，或者数组）
	*	@return array [config]
	*/
	public function config($field = ''){
		if($field == ''){
			return $this->loadConfig();
		}
		$data = C('SHOP_CONFIG');
		//强制加载所有配置
		if (!$data){
			$data = $this->loadConfig();
		}
		
		//检查是否已经加载过所有配置
		if (isset($data[$field])){
			return $data[$field];
		}
		
		$value = $this->where("code = '$field'")->getField('value');
		return $value;
	}
	
	/*
	*	加载、获取配置信息
	*	@Author 9009123 (Lemonice)
	*	@param  $field array|string  查询的配置字段（多个逗号隔开，或者数组）
	*	@return array [config]
	*/
	public function loadConfig($field = ''){
		$array = array();
	
		$data = C('SHOP_CONFIG');
		if (is_null($data)){
			$where = 'parent_id > 0';
			if(!is_array($field) && $field != ''){
				$field_arr = explode(',',$field);
				$where .= " and code IN('".implode("','",$field_arr)."')";
			}elseif(is_array($field) && !empty($field)){
				$where .= " and code IN('".implode("','",$field)."')";
			}
			
			$result = $this->field('code, value')->where($where)->select();
			if(!empty($result)){
				foreach ($result as $value){
					$array[$value['code']] = $value['value'];
				}
			}
			
			//初始化配置值
			$array = $this->setType($array);
			//初始化配置的值, 如果没有值则设置默认值
			$array = $this->setDefaultValue($array);
			
			/* 对字符串型设置处理 */
			$array['no_picture']           = !empty($arr['no_picture']) ? str_replace('../', './', $arr['no_picture']) : 'images/no_picture.gif'; // 修改默认商品图片的路径
			$array['one_step_buy']         = empty($arr['one_step_buy']) ? 0 : 1;
			$array['invoice_type']         = empty($arr['invoice_type']) ? array('type' => array(), 'rate' => array()) : unserialize($arr['invoice_type']);
			
			/*if (empty($array['integrate_code'])){
				$array['integrate_code'] = 'ecshop'; // 默认的会员整合插件为 ecshop
			}*/
			C('SHOP_CONFIG', $array);
		}else{
			$array = $data;
		}
		//$array['lang'] = 'zh_cn';
		return $array;
	}
	
	/*
	*	对config配置具体的值进行初始化处理
	*	@Author 9009123 (Lemonice)
	*	@param  $config array  配置内容
	*	@return array [config]
	*/
	private function setType($config){
		if(isset($this->_config_type) && !empty($this->_config_type)){
			foreach($this->_config_type as $function=>$type_field){
				if(!empty($type_field)){
					foreach($type_field as $field){
						$config[$field] = isset($config[$field]) ? $function($config[$field]) : (isset($this->type_default[$function]) ? $this->type_default[$function] : '');  //初始化配置值
					}
				}
			}
		}
		return $config;
	}
	
	/*
	*	对config配置具体的值按设置进行默认值设置
	*	@Author 9009123 (Lemonice)
	*	@param  $config array  配置内容
	*	@return array [config]
	*/
	private function setDefaultValue($config){
		if(isset($this->_config_default) && !empty($this->_config_default)){
			foreach($this->_config_default as $function=>$type_field){
				if(!empty($type_field)){
					foreach($type_field as $field=>$default_value){
						$config[$field] = isset($config[$field]) ? $function($config[$field]) : $default_value;  //设置默认值
					}
				}
			}
		}
		return $config;
	}
}