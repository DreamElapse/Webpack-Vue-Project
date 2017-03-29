<?php
/**
 * ====================================
 * 树型转换类
 * ====================================
 * Author: Hugo
 * Date: 14-5-20 下午9:28
 * ====================================
 * File: Tree.class.php
 * ====================================
 */
namespace Common\Extend\Base;

class Tree {
    static $idField = 'id';
    static $pField = 'pid';
    static $textField = 'title';

    public static function findChild(&$arr, $pid) {
        $childs = array();
        foreach ($arr as $v){
            if($v[self::$pField] == $pid){
                $childs[]=$v;
            }
        }
        return $childs;
    }

    public static function parentExists(& $arr, $pid) {
        if(!$pid) return true;
        foreach($arr as $v) {
            if($v[self::$idField] == $pid) return true;
        }
        return false;
    }

    public static function treeArray($rows, $root_id = 0) {
        $tree = array();
        foreach($rows as $k => $v) {
            //父亲找到儿子
            if($v[self::$pField] == $root_id) {
                $v['children'] = self::treeArray($rows, $v[self::$idField]);
                $tree[] = $v;
                //unset($data[$k]);
            }
        }
        return $tree;
    }

    /**
     * 递归返回选择框选项
     * @param $data
     * @param int $pid
     * @param int $level
     * @return array|null
     */
    public static function genCate($data, $pid = 0, $level = 0) {
        if($level == 10) return null;
        $l = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $level) . '└';
        static $arrCat    = array();
        $arrCat    = empty($level) ? array() : $arrCat;
        foreach($data as $k => $row) {
            if($row[self::$pField] == $pid) {
                //如果当前遍历的id不为空
                $row[self::$textField]    = $l . $row[self::$textField];
                $row['level']    = $level;
                $arrCat[]    = $row;
                self::genCate($data, $row[self::$idField], $level+1);//递归调用
            }
        }
        return $arrCat;
    }

}