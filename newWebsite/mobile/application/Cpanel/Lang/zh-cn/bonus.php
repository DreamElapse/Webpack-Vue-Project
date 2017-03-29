<?php
return array(
	'send_by'=>array(  //发放方式
		SEND_BY_USER => '按用户发放',
		SEND_BY_GOODS => '按商品发放',
		SEND_BY_ORDER => '按订单发放',
		SEND_BY_PRINT => '线下发放',
		SEND_BY_HAND => '手动领取',
	),
	'coupon_type'=>array(  //优惠类型
		COUPON_TYPE_COMMON=>'普通',
		COUPON_TYPE_SHIPPING=>'免邮',
		COUPON_TYPE_ENTITY=>'实物',
		COUPON_TYPE_DISCOUNT=>'折扣',
	),
	'coupon_range'=>array(  //优惠范围
		COUPON_RANGE_ALL_GOODS=>'全部商品',
		COUPON_RANGE_CLASS=>'指定分类',
		COUPON_RANGE_PACKAGE=>'指定套装',
		COUPON_RANGE_GOODS=>'指定单品',
		COUPON_RANGE_ACT=>'指定活动',
		COUPON_RANGE_GOODS_PACKAGE=>'指定单品和套装',
	),
);