<?php
/**
 * ====================================
 * 公共方法库
 * ====================================
 * Author: 9004396
 * Date: 2016-06-25 11:38
 * ====================================
 * File: verify.php
 * ====================================
 */
/**
 * 获取当前完整路径
 * @return string
 */
function locationHref()
{
    $url = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    $url .= $_SERVER['HTTP_HOST'];
    if ($_SERVER['SERVER_PORT'] != '80') {
        $url .= ":" . $_SERVER['SERVER_PORT'];
    }
    $url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : urlencode($_SERVER['PHP_SELF']) . '?' . urlencode($_SERVER['QUERY_STRING']);
    return $url;
}

/**
 * 根据频道和域名反馈电话号码或QQ号
 * @return array
 */
function getTel()
{
    $key = C('site_id');
    if (empty($key)) return array();
    $channel = C('CHANNEL');
    $host = C('domain');
    $campaign = I('get.campaign');
    $data = $channel[$key];
    $cKey = null;
    foreach ($data as $k => $v) {
        if (strpos($campaign, $k) !== false) {
            $cKey = $k;
        }
    }
    $result = !is_null($cKey) ? $data[$cKey] : (!empty($host[$_SERVER['HTTP_HOST']]) && is_array($host[$_SERVER['HTTP_HOST']]) ? $host[$_SERVER['HTTP_HOST']] : array());
    $tel = $result['tel'];
    strInsert($tel, 3, '-');
    strInsert($tel, 8, '-');
    $result['tel_show'] = $tel;
    return $result;
}


/*
*	取得当前的域名
*	@Author 9009123 (Lemonice)
*	@return string 当前的域名  如：http://www.baidu.com/
*/
function siteUrl()
{
    /* 协议 */
    $protocol = (is_ssl() ? 'https://' : 'http://');
    /* 域名或IP地址 */
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } elseif (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } else {
        /* 端口 */
        if (isset($_SERVER['SERVER_PORT'])) {
            $port = ':' . $_SERVER['SERVER_PORT'];
            if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
                $port = '';
            }
        } else {
            $port = '';
        }

        if (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'] . $port;
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'] . $port;
        }
    }

    return $protocol . (isset($host) && $host ? $host : '') . '/';
}

/*
*	检查目标文件夹是否存在，如果不存在则自动创建该目录
*	@Author 9009123 (Lemonice)
*	@param  string  $folder 目录路径。不能使用相对于网站根目录的URL
*	@return true or false
*/
function makeDir($folder)
{
    $reval = false;
    if (!file_exists($folder)) {
        /* 如果目录不存在则尝试创建该目录 */
        @umask(0);
        /* 将目录路径拆分成数组 */
        preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);
        /* 如果第一个字符为/则当作物理路径处理 */
        $base = ($atmp[0][0] == '/') ? '/' : '';

        /* 遍历包含路径信息的数组 */
        foreach ($atmp[1] as $val) {
            if ('' != $val) {
                $base .= $val;
                if ('..' == $val || '.' == $val) {
                    /* 如果目录为.或者..则直接补/继续下一个循环 */
                    $base .= '/';
                    continue;
                }
            } else {
                continue;
            }

            $base .= '/';

            if (!file_exists($base)) {
                /* 尝试创建目录，如果创建失败则继续循环 */
                if (@mkdir(rtrim($base, '/'), 0755)) {
                    @chmod($base, 0755);
                    $reval = true;
                }
            }
        }
    } else {
        /* 路径已经存在。返回该路径是不是一个目录 */
        $reval = is_dir($folder);
    }
    clearstatcache();
    return $reval;
}

/*
*	获取当前域名、渠道对应的电话、QQ号码
*	@Author 9009123 (Lemonice)
*	@param integer $order_sn  订单号
*	@param $integer $pay_id  支付id
*	@return void
*/
function getAdvisoryInfo($campaign = '')
{
    $data = array(
        'qq' => '',
        'tel' => '',  //拨打
        'showTel' => '',  //页面显示
        'kf' => false,
        'icp' => '',
        'copyright' => ''
    );

    $host = C('host');
    foreach ($host as $k => $v) {
        if ($k == $_SERVER['HTTP_HOST']) {
            $data = $v;
        }
    }

    $site_id = C('site_id');
    if (!empty($campaign)) {
        $channelData = C('channel');
        $channel = $channelData[$site_id];
        if (!empty($channel)) {
            foreach ($channel as $k => $v) {
                if ($k == $campaign) {
                    $data = $v;
                }
            }
        }
    }
//	if($site_id == '87' && empty($data['qq'])){
//		$data['tel'] = '02022005555';
//		$data['kf'] = true;
//	}
//	if($site_id == '14' && empty($data['tel'])){
//		$data['tel'] = '02022005555';
//	}

    if (empty($data['icp'])) {
        switch ($site_id) {
            case '87':
                $data['icp'] = '';
                break;
            case '14':
                $data['icp'] = '';
                break;
        }
    }
    if (empty($data['copyright'])) {
        switch ($site_id) {
            case '87':
                $data['copyright'] = '';
                break;
            case '14':
                $data['copyright'] = '';
                break;
        }
    }
    $data['tel'] = '02022005555';
    $data['showTel'] = '400-6133-093 / 020-2200-5555';
    $data['isq'] = $site_id == 14 ? true : false;
    $resource = array_change_key_case(C('resource'), CASE_LOWER);
    $data = array_merge($data, $resource);
    return $data;
}

/**
 *    资源链接替换方法
 * @param $content    string 替换的内容
 * @param $suffix    mix  string/array 资源的后缀
 * @param $doamin    string 替换的资源域名
 */
function replaceHtml($content, $domain = '')
{
    preg_match_all('/<img.*?src=\"(.*?.*?)\".*?>/isu', $content, $src);  //抓取链接;
    $src = isset($src[1]) ? $src[1] : array();//print_r($src);
    if (!empty($src)) {
        $src = array_unique($src);  //去除重复
        foreach ($src as $path) {
            if (!strstr($path, 'http://') && !strstr($path, 'https://') && strstr($path, '/')) {
                $new_path = substr($path, 0, 1) == '/' ? substr($path, 1) : $path;
                $content = str_replace($path, $domain . $new_path, $content);
            }
        }
    }
    return $content;
}

/*
*	二维数组排序
*	@Author 9009123 (Lemonice)
*	@param array $array  二维数组
*	@param string $field 数组里面的字段，排序的字段
*	@param string $direction 倒序或者升序
*	@return array
*/
function arraySort($array = array(), $field = '', $direction = 'DESC')
{
    if (empty($array) || $field == '') {
        return $array;
    }
    $sort = array(
        'direction' => 'SORT_' . strtoupper($direction), //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
        'field' => $field,       //排序字段
    );
    $arrSort = array();
    foreach ($array AS $uniqid => $row) {
        foreach ($row AS $key => $value) {
            $arrSort[$key][$uniqid] = $value;
        }
    }
    if ($sort['direction']) {
        array_multisort($arrSort[$sort['field']], constant($sort['direction']), $array);
    }
    return $array;
}

/*
*	检查是否为手机号码
*	@Author 9009123 (Lemonice)
*	@param string $mobile  手机号码
*	@return true or false
*/
function isMobile($mobile)
{
    return preg_match('/^1[34578]\d{9}$/', $mobile) ? true : false;
}

/**
 * 统计在线人数
 * @return int
 */
function totalOnline($isLogOut = false)
{
    $num = 0;
    $refTime = ini_get('session.gc_maxlifetime');
    $onLineFilePath = RUNTIME_PATH . 'online';
    $onlineNumber = 0;
    if (is_dir($onLineFilePath) && $dir = opendir($onLineFilePath)) {
        while (($file = readdir($dir)) !== false) {
            if (strcmp($file, "..") == 0 || strcmp($file, ".") == 0) {
                continue;
            }
            $time = date("Y-m-d H:i:s", filemtime($onLineFilePath . "/" . $file));
            $D_[$time] = $file;
            $num++;
            unset($num);
        }
        closedir($dir);
        $filename = session_id();
        $filePath = $onLineFilePath . "/" . $filename;
        if($isLogOut){
            unlink($filePath);
        }else{
            $fp = fopen($filePath, "w");
            fputs($fp, "");
            fclose($fp);
        }
        $nTime = date("Y-m-d H:i:s", mktime(date("H"), date("i") - $refTime, 0, date("m"), date("d"), date("Y")));
        $D_[$nTime] = "-";
        krsort($D_);
        $onlineAccount = array();
        while (1) {
            $vKey = key($D_);
            $onlineNumber++;
            if($D_[$vKey] != session_id()){
                $onlineAccount[] = $D_[$vKey];
            }
            if (strcmp($nTime, $vKey) == 0) {
                break;
            } else {
                array_shift($D_);
            }
        }
        array_shift($D_);
        reset($D_);
        while (count($D_) > 0) {
            $cKey = key($D_);
            unlink($onLineFilePath . "/" . $D_[$cKey]);
            if (!next($D_)) {
                break;
            }
        }
    } else {
        @chmod("..", 0777);
        @mkdir($onLineFilePath,0777);
    }
    $online = $onlineNumber-1;
    krsort($onlineAccount);
    array_shift($onlineAccount);
    $ret = array(
        'account'   => $onlineAccount,
        'total'     => $online
    );

    return $ret;
}

/**
 * 单设备登陆
 * @return bool
 */
function isLoginOnly(){
    $user_id = login('user_id');
    $session_key = F('session_key_'.$user_id);
    if(($session_key != session_id()) || (get_client_ip(0,true) != login('now_login_ip'))){
        return false;
    }
    return true;
}

