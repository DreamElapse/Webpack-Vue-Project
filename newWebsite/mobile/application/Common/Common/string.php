<?php
/**
 * ====================================
 * 处理字符串 公共函数
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-06-27 16:23
 * ====================================
 * File: string.php
 * ====================================
 */

/*
*	过滤int型逗号隔开的多个数字的字符串 , 仅保留数字和分隔符
*	原样返回 , 函数用意在于过滤敏感字符
*	@Author 9009123 (Lemonice)
*	@param  string $id 需要过滤的字符串 如：1,3,a,6,7,8,9,6,15
*	@param  string $ext 分隔符
*	@param  bool $repeat [true or false] 是否去重复 
*	@return string  如：1,3,6,7,8,9,15
*/
function filterInt($id = '', $ext = ',', $repeat = true){
	if(!$id || $id == ''){
		return '';
	}
	$id_array = array();
	if(strstr($id, $ext)){  //多个ID
		$ids = explode($ext, $id);
		foreach($ids as $number){
			if(intval($number) > 0 && ($repeat == false || !in_array($number,$id_array))){
				$id_array[] = intval($number);
			}
		}
	}elseif(intval($id) > 0){  //单个ID
		$id_array[] = intval($id);
	}
	return !empty($id_array) ? implode($ext,$id_array) : '';
}

/*
*	格式化商品价格
*	@Author 9009123 (Lemonice)
*	@param   float   $price  商品价格
*	@return string
*/
function priceFormat($price, $change_price = true){
	$ShopConfig = D('ShopConfig');
    if ($change_price){
        switch ($ShopConfig->config('price_format')){
            case 0:
                $price = number_format($price, 2, '.', '');
                break;
            case 1: // 保留不为 0 的尾数
                $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price, 2, '.', ''));
                if (substr($price, -1) == '.'){
                    $price = substr($price, 0, -1);
                }
                break;
            case 2: // 不四舍五入，保留1位
                $price = substr(number_format($price, 2, '.', ''), 0, -1);
                break;
            case 3: // 直接取整
                $price = intval($price);
                break;
            case 4: // 四舍五入，保留 1 位
                $price = number_format($price, 1, '.', '');
                break;
            case 5: // 先四舍五入，不保留小数
                $price = round($price);
                break;
        }
    }else{
        $price = number_format($price, 2, '.', '');
    }
    return sprintf($ShopConfig->config('currency_format'), $price);
}

/**
 * 字符串指定位置插入一段字符串
 * @param $str
 * @param $i
 * @param $subStr
 */
function strInsert(&$str,$i,$subStr){
	$starStr = '';
	$lastStr = '';
	for ($j = 0; $j < $i; $j++){
		$starStr .= $str[$j];
	}
	for ($j == $i; $j < mb_strlen($str); $j++){
		$lastStr.=$str[$j];
	}
	$str = ($starStr.$subStr.$lastStr);
}
