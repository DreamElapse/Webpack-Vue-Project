<?php
/**
 *	3g 和 q 站某些不同的固定配置
 *	站点-ID：3g-87, q-14
**/
return array(
	//列表页面分类配置，cat_id : 分类id，cat_name : 分类名称
    'category_list' => array(
		'87' => array(
			array('cat_id' => '0',  'cat_name' => '新品'),
			array('cat_id' => '1',  'cat_name' => '美白'),
			array('cat_id' => '2',  'cat_name' => '斑点'),
			array('cat_id' => '5',  'cat_name' => '黑头'),
			array('cat_id' => '8',  'cat_name' => '男士专区'),
			array('cat_id' => '4',  'cat_name' => '补水'),
			//array('cat_id' => '7',  'cat_name' => '紧致'),
			array('cat_id' => '126',  'cat_name' => '美容仪器'),
//			array('cat_id' => '93', 'cat_name' => '清洁'),
			array('cat_id' => '141', 'cat_name' => '内调'),
			array('cat_id' => '3',  'cat_name' => '女士痘痘'),
			array('cat_id' => '6',  'cat_name' => '面膜'),
		),
		'14' => array(
			array('cat_id' => '0',   'keywords'=>'all', 'cat_name' => '全部'),
			array('cat_id' => '37',  'keywords'=>'mb',  'cat_name' => '美白'),
			array('cat_id' => '36',  'keywords'=>'bs',  'cat_name' => '保湿'),
			array('cat_id' => '6',   'keywords'=>'qht', 'cat_name' => '黑头'),
			array('cat_id' => '126', 'keywords'=>'dd',  'cat_name' => '痘痘'),
			array('cat_id' => '39',  'keywords'=>'mm',  'cat_name' => '面膜'),
			array('cat_id' => '129', 'keywords'=>'bd',  'cat_name' => '斑点'),
			//array('cat_id' => '19',  'keywords'=>'cz',  'cat_name' => '彩妆'),
			array('cat_id' => '135',  'keywords'=>'mryq',  'cat_name' => '美容仪器'),
//			array('cat_id' => '35',  'keywords'=>'qj',  'cat_name' => '清洁'),
			array('cat_id' => '141',  'keywords'=>'nt',  'cat_name' => '内调'),
			array('cat_id' => '40',  'keywords'=>'ns',  'cat_name' => '男士'),
		),
	),

	//列表页和详情页面显示的核心功效属性id
	'attr_core_effect' => array('87' => 2, '14' => 216),
	
	//商品规格属性id
	'attr_spec' => array('87' => 1, '14' => 213),
	
	/*
	 * 首页畅销商品
	 * 
	 */
	'hot_goods' => array(
		'87' => array(
			array('goods_id' => 269, 'goods_name' => '抑黑焕白套装'),
			array('goods_id' => 293, 'goods_name' => '清本源五谷焕彩肌底液'),
			array('goods_id' => 1155, 'goods_name' => '超声波焕肤美容仪'),
            array('goods_id' => 1287, 'goods_name' => '医用愈肤生物膜面膜(Kr.Chnskin V1.0)'),
		),
		'14' => array(
			array('goods_id' => 459, 'goods_name' => '抑黑焕白套装'),
			array('goods_id' => 293, 'goods_name' => '清本源五谷焕彩肌底液'),
            array('goods_id' => 1218, 'goods_name' => '超声波焕肤美容仪'),
            array('goods_id' => 1149, 'goods_name' => '医用愈肤生物膜面膜(Kr.Chnskin V1.0)'),
		),
	),
	
	/*
	*	专题页获取商品
	*	由于Q站与3G站旧功能不同
	*	因为暂时3G站用分类ID获取商品，Q站用商品ID获取商品
	*
	*	@说明：下面配置中，优先goods_id字段指定的商品ID（多个逗号隔开），goods_id为空则获取对应分类的商品  --  2016-09-08决定不做分类获取
	*
	*	@Author 9009123 (Lemonice)
	*/
	'special_page' => array(
		'87' => array(  //3G站
			'mb'=>array('goods_id' => '1110,269,241', 'cat_id' => 1, 'name' => '美白'),
			'qb'=>array('goods_id' => '1119,1122,1131', 'cat_id' => 2, 'name' => '淡斑'),
			'qdwoman'=>array('goods_id' => '1089,69,68,228', 'cat_id' => 3, 'name' => '女士祛痘'),
			'bs'=>array('goods_id' => '1041,1044,1038', 'cat_id' => 4, 'name' => '补水'),  //bs3
			'qht'=>array('goods_id' => '87,89,175', 'cat_id' => 5, 'name' => '祛黑头'),
			'qj'=>array('goods_id' => '930,1101', 'cat_id' => 93, 'name' => '清洁'),  //Qj
			'mm'=>array('goods_id' => '609,292,261,831,1113', 'cat_id' => 6, 'name' => '面膜'),
			'qdman'=>array('goods_id' => '67,66,1125,228', 'cat_id' => 8, 'name' => '男士专区'),
		),
		'14' => array(  //Q站
			'mb'=>array('goods_id' => '996,459,241', 'cat_id' => 37, 'name' => '美白'),
			'qb'=>array('goods_id' => '1080,1083,1086', 'cat_id' => 129, 'name' => '淡斑'),
			'qdwoman'=>array('goods_id' => '1089,801,68,228', 'cat_id' => 126, 'name' => '女士祛痘'),
			'bs'=>array('goods_id' => '933,936,930', 'cat_id' => 12, 'name' => '补水'),
			'qht'=>array('goods_id' => '1092,89,175', 'cat_id' => 6, 'name' => '祛黑头'),
			'qj'=>array('goods_id' => '825,1095', 'cat_id' => 35, 'name' => '清洁'),
			'mm'=>array('goods_id' => '609,292,261,759,295', 'cat_id' => 39, 'name' => '面膜'),
			'qdman'=>array('goods_id' => '1098,804,1011,228', 'cat_id' => 40, 'name' => '男士专区'),
		),
	),
	
	/*
	*	品牌动态 - 视频列表 - 对应的分类获取
	*
	*	@Author 9009123 (Lemonice)
	*/
	'brand_video_cat' => array(
		'87' => array(  //3G站
			'cat_id'=>36,
		),
		'14' => array(  //Q站
			'cat_id'=>63,
		),
	),
	/*
	*	常规商品下单默认邮费
	*
	*	@Author 9009123 (Lemonice)
	*/
	'shipping_fee'=>20,
	
	/*
	*	下单地址偏远地址邮费增加费用
	*
	*	@Author 9009123 (Lemonice)
	*/
	'remote_address' => array(
		15=>15,  //省份ID=>增加运费多少钱
		18=>15,
		19=>15,
		20=>15,
		21=>15,
		28=>15,
		29=>15,
	),
);