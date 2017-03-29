<?php
/**
 * ====================================
 * 公共数据模型类
 * ====================================
 * Author: 9004396
 * Date: 2017-01-11 09:34
 * ====================================
 * Project: new.m.chinaskin.cn
 * File: CpanelModel.class.php
 * ====================================
 */
namespace Common\Model;
use Think\Model;

class CpanelModel extends Model{
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * 验证名称是否存在
     * @param $region_name
     * @param $data
     * @return bool
     */
    protected function _validateName($text) {
        $data = I('post.');
        $where = array();

        $where['pid'] = (int)$data['pid'];
        $where['text'] = trim($data['text']);

        if(!empty($data['id'])) {
            $where['id'] = array('neq', (int)$data['id']);
        }
        return $this->where($where)->count() > 0 ? false : true;
    }

    /**
     * 验证上级ID是否正确
     * @param $pid
     * @return bool
     */
    protected function _validatePid($pid) {
        return $pid != I('post.id');
    }

    /**
     * 根据key切换数据库
     * @param int $key
     */
    protected function switchDb($key = 0){
        $config = C('db_config.'.$key);
        $this->connection = array_merge($config['CONFIG'], array('DB_TYPE' => C('DB_TYPE')));
        $this->tablePrefix = $this->connection['DB_PREFIX'];
    }

    /**
     * easyui分页处理
     * @param array $where
     * @param string $order_by
     * @param string $join
     * @param string $alias
     * @param int $pagesize
     * @return array
     */
    public function grid($params = array()) {
        $orderBy = isset($params['sort']) ? trim($params['sort']) . ' ' .  trim($params['order']) : '';
        $page = isset($params['page']) && $params['page'] > 0 ? intval($params['page']) : 1;
        $pageSize = isset($params['rows']) && $params['rows'] > 0 ? intval($params['rows']) : 10;

        //统计总记录数
        $options = $this->options;
        $total = $this->count();

        //排序并获取分页记录
        $options['order'] = empty($options['order']) ? $orderBy : $options['order'];
        $this->options = $options;
        $this->limit($pageSize)->page($page);
        $rows = $this->getAll();
        return array('total' => (int)$total, 'rows' => (empty($rows) ? false : $rows), 'pagecount' => ceil($total / $pageSize));
    }

    /**
     * 查询全部记录
     * @return mixed
     */
    public function getAll($where = '', $filed = '') {
        if(!empty($where))
            $this->where($where);
        if(!empty($filed))
            $this->field($filed);
        $rows = $this->select();
        formatTime($rows);
        return $rows;
    }

    /**
     * 获取sql语句
     * @author Lemonice <chengciming@126.com>
     * @return string
     */
    public function getSql()
    {
        return $this->getLastSql();
    }

    /**
     * 查询单条记录
     * @author Lemonice <chengciming@126.com>
     * @return mixed
     */
    public function getOne($where = '', $filed = '') {
        if(!empty($where))
            $this->where($where);
        if(!empty($filed))
            $this->field($filed);
        $rows = $this->limit(1)->select();
        return isset($rows[0]) ? $rows[0] : array();
    }

    /**
     * 获取指定数据库的所有表名
     * @author huajie <banhuajie@163.com>
     */
    public function getTables(){
        $tables = M()->query('SHOW TABLES;');
        foreach ($tables as $key=>$value){
            $table_name = $value['Tables_in_'.C('DB_NAME')];
            $table_name = substr($table_name, strlen(C('DB_PREFIX')));
            $tables[$key] = $table_name;
        }
        return $tables;
    }
}