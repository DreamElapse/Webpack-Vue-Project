<?php
/**
 * Created by PhpStorm.
 * User: 9009078
 * Date: 2017/1/18
 * Time: 11:44
 */
namespace Cpanel\Model;
use Common\Model\CpanelModel;
use Common\Extend\PhxCrypt;

class UserReceiveLogModel extends CpanelModel
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->connection = 'CPANEL';
        $this->tablePrefix = 'py_';
        $this->tableName = 'user_receive_log';
    }


    public function filter($params) {
        $where = array();
        if(!empty($params['genre'])){
            switch ($params['genre']){
                case 'nickname':
                    $where['nickname'] = array('LIKE',"%{$params['keyword']}%");
                    break;
                case 'mobile':
                    $where['mobile'] = PhxCrypt::phxEncrypt($params['keyword']);
                    break;
            }
        }

        $start_time = empty($params['start_time']) ? 0 : strtotime($params['start_time']);
        $end_time = empty($params['end_time']) ? 0 : strtotime($params['end_time']);

        if(!empty($start_time) && !empty($end_time) && $end_time >= $start_time){
            $where['receive_time'] = array(array('EGT',$start_time),array('LT', $end_time));
        }

        $this->where($where)
            ->order('log_id desc');

        $config = C('db_config.1');
        $this->alias('url');
        $this->join($config['CONFIG']['DB_NAME'].'.'.$config['CONFIG']['DB_PREFIX'].'bind_user as bu on url.openid = bu.openid','left')
            ->field('url.*,bu.openid,bu.mobile,bu.nickname,bu.sex,bu.subscribe,bu.add_time,bu.subscribe_time');
        return $this;
    }

    public function format($data){
        if(!empty($data)){
            foreach ($data['rows'] as &$item){
                $item['mobile'] = PhxCrypt::phxDecrypt($item['mobile']);
				$item['content'] = addslashes($item['content']);
            }
        }
        return $data;
    }

}