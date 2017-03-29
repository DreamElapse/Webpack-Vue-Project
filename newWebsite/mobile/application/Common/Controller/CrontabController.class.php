<?php
/**
 * ====================================
 * Crontab 计划任务公共类
 * ====================================
 * Author:
 * Date:
 * ====================================
 * File: CrontabController.class.php
 * ====================================
 */
namespace Common\Controller;

use Common\Extend\Base\Config;
use Common\Extend\Time;
use Think\Controller;

class CrontabController extends Controller
{

    protected $_startTime;
    protected $cronLogModel;

    public function __construct()
    {
        parent::__construct();
        Config::init();
        $this->_startTime = Time::gmTime();
        if (is_null($this->cronLogModel)) {
            $this->cronLogModel = M('cronLog', null, 'USER_CENTER');
        }
    }

    /**
     * 创建计划任务日志
     * @param $name
     * @param $content
     * @return mixed
     */
    protected function insertLog($name, $content)
    {
        $classFun = explode('-', $name);
        $className = explode('\\', $classFun[0]);
        $title = L(substr($className[count($className) - 1], 0, -10)) . '-' . L($classFun[1]);
        $data = array(
            'name' => $name,
            'content' => $content,
            'start_time' => $this->_startTime,
            'add_time' => Time::gmTime(),
            'title' => $title
        );
        return $this->cronLogModel->add($data);
    }

    /**
     * 更新日志
     * @param $logId
     * @param $content
     */
    protected function updateLog($logId, $content)
    {
        $data = array(
            'content' => $content,
            'add_time' => Time::gmTime(),
        );
        $this->cronLogModel->where(array('id' => $logId))->save($data);
    }
}