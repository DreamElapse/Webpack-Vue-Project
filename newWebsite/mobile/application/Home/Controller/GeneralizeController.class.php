<?php
//===========================================================
//推广爬虫测试
//============================================================

namespace Home\Controller;
use Common\Controller\InitController;

class GeneralizeController extends InitController
{
	private $generalizeModel;
	public function __construct(){
		parent::__construct();
		$this->generalizeModel = $this->model = M('ReptileLog', 'thinkphx_', 'APPS');;
	}
    //引链一
    public function rept(){ 
        $this->reptile();
    }
    //引链二
    public function rpt(){
        $this->reptile();
    }
    //触发引链则记录信息
    protected function reptile(){
        $site_id = I('request.site', 0, 'intval');
        $url_num = I('request.num', 0, 'intval');
        $source = I('request.source');
        if(!$site_id){
            exit('Params error');
        }
        if(!$url_num){
            exit('Params error');
        }
        $ip = get_client_ip();
        $where['ip'] = $ip;
        $where['url_num'] = $url_num;
        $where['site_id'] = $site_id;
        $logs = $this->generalizeModel->where($where)->find();
        $view_page = '';
        $view_other = '';
//        print_r($logs);exit;
        if(empty($logs)){
            $url_config = load_config(CONF_PATH.'generalize_url.php');
            $url_list = $url_config[$site_id];
            if($source){
                $view_page = urlencode($source);
                $view_other = parse_url($source, PHP_URL_HOST);
            }else{
                foreach($url_list as $key=>$val){
                    if($val['url_num'] == $url_num){
                        $view_page = $val['href'];
                        $view_other = parse_url($view_page, PHP_URL_HOST);
                        break;
                    }
                }
            }
            $data['ip'] = $ip;
            $data['view_time'] = time();
            $data['view_num'] = array('exp', 'view_num+1');
            $data['content'] = json_encode($_SERVER);
            $data['view_page'] = $view_page;
            $data['view_other'] = $view_other;
            $data['url_num'] = $url_num;
            $data['site_id'] = $site_id;
            $this->generalizeModel->add($data);
        }else{
            $data['view_num'] = array('exp', 'view_num+1');
            $this->generalizeModel->where(array('id'=>$logs['id']))->save($data);
        }
    }

    //获取数据
    public function index(){
        $p = I('request.p', 1, 'intval');
        $order = I('request.order', 'asc');
        $site_id = I('request.site_id', 0);
        $url_num = I('request.num', 0);
        $ip = I('request.ip', '');
        $domain = I('request.domain', '');
        $length = I('requset.length', 20, 'intval');
        $offset = ($p - 1) * $length;
        $where = array();
        if($site_id){
            $where['site_id'] = $site_id;
        }
        if($url_num){
            $where['url_num'] = $url_num;
        }
        if($ip){
            $where['ip'] = $ip;
        }
        if($domain){
            $where['view_other'] = $domain;
        }
        $order = "view_time $order";

        $logs = $this->generalizeModel
                    ->where($where)
                    ->field('*')
                    ->order($order)
                    ->limit($offset, $length)
                    ->select();
//        echo $this->generalizeModel->getLastSql();
        foreach($logs as &$val){
            $val['view_time'] = date('Y-m-d H:i:s', $val['view_time']);
        }
        dump($logs);
    }
	
	//获取数据
    public function newIndex(){
		$sign = I('request.sign', '');
		if(!$sign || $sign != 'ab60ba3e02637f3c5f4e61126bfcbb8'){	// echo md5('newIndex_generalize');
			exit('param error!');
		}
		

		//TODO:接收参数
		$limit = 20;
        $p = I('request.p', 1, 'intval');
		$site_id = I('request.site_id', 0);
		$url_num = I('request.url_num', 0);
		$keyword = I('request.keyword', '');
		$start_time = I('request.start_time');
		$end_time = I('request.end_time');
		$selected = " selected='selected'";
		$where = array();
		$param = array('sign'=>$sign);
		//TODO:组合查询条件
		if($site_id){
			$where['site_id'] = $site_id;
			$param['site_id'] = $site_id;
		}
		if($site_id && $url_num){
			$where['site_id'] = $site_id;
			$where['url_num'] = $url_num;
			$param['site_id'] = $site_id;
			$param['url_num'] = $url_num;
		}
		if($keyword){
			$where['ip'] = $keyword;
			$param['keyword'] = $keyword;
		}
		if($start_time && $end_time){
			$where['view_time'] = array('egt', strtotime($start_time));
			$where['view_time'] = array('elt', strtotime($end_time));
			$param['start_time'] = $start_time;
			$param['end_time'] = $end_time;
		}else if($start_time){
			$where['view_time'] = array('egt', strtotime($start_time));
			$param['end_time'] = $end_time;
		}else if($end_time){
			$where['view_time'] = array('elt', strtotime($end_time));
			$param['end_time'] = $end_time;
		}

        $id = I('request.id', 0, 'intval');
        $del = I('request.del');

        if($id && $del == 1){
            $res = $this->generalizeModel->where(array('id'=>$id))->delete();
            if($res){
                redirect(U('Generalize/newIndex', $param));
            }
        }

		//TODO:加载配置
		$sites = array();
		$url_config = load_config(CONF_PATH.'generalize_url.php');
		foreach($url_config as $key=>$val){
			$sites[] = $key;
		}
		//链接
		$hrefs = array();
		if($site_id){
			foreach($url_config[$site_id] as $k=>$v){
				$hrefs[$v['url_num']] = $v['href'];
			}
		}
		
		//数据总数
		$count = $this->generalizeModel->where($where)->count();
		
		//总页数
		$page_count = ceil($count / $limit);
		if($p<=1){
			$p_pre = 1;
			if($page_count > 1){
				$p_next = 2;
			}
		}else if($p>1 && $p<$page_count){
			$p_pre = $p - 1;
			$p_next = $p + 1;
		}else if($p>=$page_count){
			$p_next = $page_count;
			$p_pre = $p - 1;
		}
		// echo $p;
		$offset = ($p - 1) * $limit;
		// print_r($where);
        $logs = $this->generalizeModel
                    ->where($where)
                    ->field('id,ip,view_time,view_num,content,view_page,view_other,url_num,site_id')
                    ->limit($offset, $limit)
//					->order($order)
                    ->select();
		// echo $this->generalizeModel->getLastSql();		
		
		
		//页面模板
		$html = '<style>';
		$html .= 'table{border-collapse:collapse;border-spacing:0;border-left:1px solid #888;border-top:1px solid #888;background:#efefef;}';
		$html .= 'th,td{border-right:1px solid #888;border-bottom:1px solid #888;padding:5px 15px;}';
		$html .= '</style>';
		$html .= '<h3>爬虫数据汇总：</h3>';
		$html .=			'<div style="margin-bottom:10px;">';
		$html .=				'<form method="post" action="'.U('Generalize/newIndex',array('sign'=>$sign)).'"><strong>站点：</strong>';
		$html .=				'<select name="site_id">';
		$html .=					'<option value="0">全部站点</option>';
			foreach($sites as $v){
				if($site_id == $v){
					$html .= '<option value="'.$v.'" '.$selected.'>'.$v.'</option>';
				}else{
					$html .= '<option value="'.$v.'">'.$v.'</option>';
				}
			}
		$html .=				'</select>';
		$html .=				' <strong>推广链接：</strong>';
		$html .=				'<select name="url_num">';
		$html .=					'<option value="0">全部链接</option>';
			foreach($hrefs as $k=>$v){
				if($url_num == $k){
					$html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
				}else{
					$html .= '<option value="'.$k.'">'.$v.'</option>';
				}
			}		
		$html .=				'</select> ';
		$html .=				'搜索：<input type="text" name="keyword" placeholder="ip搜索" value="'.$keyword.'" />';
		$html .=				' 开始时间：<input type="text" name="start_time" value="'.$start_time.'" />';
		$html .=				' 结束时间：<input type="text" name="end_time" value="'.$end_time.'" />';
		// $html .=				' <input type="hidden" name="sign" value="'.$sign.'" />';
		$html .=				' <input type="submit" name="sub" value="提交搜索" />';
		$html .=			'</form></div>';
		$html .=		'<table style="border:1px solid;">';
		$html .=			'<tr>';
		$html .=				'<td>ID</td>';
		$html .=				'<td>ip</td>';
		$html .=				'<td>访问时间</td>';
		$html .=				'<td>访问次数</td>';
		// $html .=				'<td>$_SERVER数据</td>';
		$html .=				'<td>访问链接</td>';
		$html .=				'<td>访问域名</td>';
		$html .=				'<td>链接编号</td>';
		$html .=				'<td>站点ID</td>';
		$html .=				'<td>操作</td>';
		$html .=			'</tr>';
		foreach($logs as $val){
            $val['view_time'] = date('Y-m-d H:i:s', $val['view_time']);
			$html .=			'<tr>';
			$html .=				'<td>'.$val['id'].'</td>';
			$html .=				'<td>'.$val['ip'].'</td>';
			$html .=				'<td>'.$val['view_time'].'</td>';
			$html .=				'<td>'.$val['view_num'].'</td>';
			// $html .=				'<td>'.$val['content'].'</td>';
			$html .=				'<td>'.$val['view_page'].'</td>';
			$html .=				'<td>'.$val['view_other'].'</td>';
			$html .=				'<td>'.$val['url_num'].'</td>';
			$html .=				'<td>'.$val['site_id'].'</td>';
            $param['id'] = $val['id'];
            $param['del'] = 1;
			$html .=				'<td><a target="_blank" href="'.U('Generalize/rept', array('site'=>$val['site_id'], 'num'=>$val['url_num'])).'">引链</a> <a href="'.U('Generalize/newIndex', $param).'">删除</a></td>';
			$html .=			'</tr>';
        }
		$html .=			'<tr>';
		$html .=			'<td colspan="5">&nbsp;</td>';
		$html .=				'<td>总数：'.$count.'</td>';
        unset($param['id']);
        unset($param['del']);
		$html .=				'<td><a href="'.u('Generalize/newIndex', array_merge($param, array('p'=>$p_pre))).'">上一页</a></td>';
		$html .=				'<td>当前第 '.$p.' 页</td>';
		$html .=				'<td><a href="'.u('Generalize/newIndex', array_merge($param, array('p'=>$p_next))).'">下一页</a></td>';
		$html .=			'</tr>';
		$html .=		'</table>';

		echo $html;
		exit;
    }


    public function onOffLog(){
        //TODO:接收参数
        $limit = 30;
        $p = I('request.p', 1, 'intval');
        $site_id = I('request.site_id', 0);
        $url_num = I('request.url_num', 0);
        $keyword = I('request.keyword', '');
        $start_time = I('request.start_time');
        $end_time = I('request.end_time');
        $selected = " selected='selected'";
        $where = array();
        $param = array();
        //TODO:组合查询条件
        if($site_id){
            $where['site_id'] = $site_id;
            $param['site_id'] = $site_id;
        }
        if($site_id && $url_num){
            $where['site_id'] = $site_id;
            $where['url_num'] = $url_num;
            $param['site_id'] = $site_id;
            $param['url_num'] = $url_num;
        }
        if($keyword){
            $where['ip'] = $keyword;
            $param['keyword'] = $keyword;
        }
        if($start_time && $end_time){
            $where['view_time'] = array('egt', strtotime($start_time));
            $where['view_time'] = array('elt', strtotime($end_time));
            $param['start_time'] = $start_time;
            $param['end_time'] = $end_time;
        }else if($start_time){
            $where['view_time'] = array('egt', strtotime($start_time));
            $param['end_time'] = $end_time;
        }else if($end_time){
            $where['view_time'] = array('elt', strtotime($end_time));
            $param['end_time'] = $end_time;
        }

        $id = I('request.id', 0, 'intval');
        $del = I('request.del');
        $model = M('ReptileLogSh', 'thinkphx_', 'APPS');
        if($id && $del == 1){
            $res = $model->where(array('id'=>$id))->delete();
            if($res){
                redirect(U('Generalize/onOffLog', $param));
            }
        }

        //TODO:加载配置
        $sites = array();
        $url_config = load_config(CONF_PATH.'generalize_url.php');
        foreach($url_config as $key=>$val){
            $sites[] = $key;
        }
        //链接
        $hrefs = array();
        if($site_id){
            foreach($url_config[$site_id] as $k=>$v){
                $hrefs[$v['url_num']] = $v['href'];
            }
        }

        //数据总数
        $count = $model->where($where)->count();

        //总页数
        $page_count = ceil($count / $limit);
        if($p<=1){
            $p_pre = 1;
            if($page_count > 1){
                $p_next = 2;
            }
        }else if($p>1 && $p<$page_count){
            $p_pre = $p - 1;
            $p_next = $p + 1;
        }else if($p>=$page_count){
            $p_next = $page_count;
            $p_pre = $p - 1;
        }
        // echo $p;
        $offset = ($p - 1) * $limit;
        // print_r($where);
        $logs = $model
            ->where($where)
            ->field('id,ip,view_time,view_num,content,view_page,view_other,url_num,site_id')
            ->limit($offset, $limit)
//					->order($order)
            ->select();
        // echo $this->generalizeModel->getLastSql();


        //页面模板
        $html = '<style>';
        $html .= 'table{border-collapse:collapse;border-spacing:0;border-left:1px solid #888;border-top:1px solid #888;background:#efefef;}';
        $html .= 'th,td{border-right:1px solid #888;border-bottom:1px solid #888;padding:5px 15px;}';
        $html .= '</style>';
        $html .= '<h3>爬虫数据汇总：</h3>';
        $html .=			'<div style="margin-bottom:10px;">';
        $html .=				'<form method="post" action="'.U('Generalize/onOffLog').'"><strong>站点：</strong>';
        $html .=				'<select name="site_id">';
        $html .=					'<option value="0">全部站点</option>';
        foreach($sites as $v){
            if($site_id == $v){
                $html .= '<option value="'.$v.'" '.$selected.'>'.$v.'</option>';
            }else{
                $html .= '<option value="'.$v.'">'.$v.'</option>';
            }
        }
        $html .=				'</select>';
        $html .=				' <strong>推广链接：</strong>';
        $html .=				'<select name="url_num">';
        $html .=					'<option value="0">全部链接</option>';
        foreach($hrefs as $k=>$v){
            if($url_num == $k){
                $html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }else{
                $html .= '<option value="'.$k.'">'.$v.'</option>';
            }
        }
        $html .=				'</select> ';
        $html .=				'搜索：<input type="text" name="keyword" placeholder="ip搜索" value="'.$keyword.'" />';
        $html .=				' 开始时间：<input type="text" name="start_time" value="'.$start_time.'" />';
        $html .=				' 结束时间：<input type="text" name="end_time" value="'.$end_time.'" />';
        // $html .=				' <input type="hidden" name="sign" value="'.$sign.'" />';
        $html .=				' <input type="submit" name="sub" value="提交搜索" />';
        $html .=			'</form></div>';
        $html .=		'<table style="border:1px solid;">';
        $html .=			'<tr>';
        $html .=				'<td>ID</td>';
        $html .=				'<td>ip</td>';
        $html .=				'<td>访问时间</td>';
        $html .=				'<td>访问次数</td>';
        // $html .=				'<td>$_SERVER数据</td>';
        $html .=				'<td>访问链接</td>';
        $html .=				'<td>访问域名</td>';
        $html .=				'<td>链接编号</td>';
        $html .=				'<td>站点ID</td>';
        $html .=				'<td>操作</td>';
        $html .=			'</tr>';
        foreach($logs as $val){
            $val['view_time'] = date('Y-m-d H:i:s', $val['view_time']);
            $html .=			'<tr>';
            $html .=				'<td>'.$val['id'].'</td>';
            $html .=				'<td>'.$val['ip'].'</td>';
            $html .=				'<td>'.$val['view_time'].'</td>';
            $html .=				'<td>'.$val['view_num'].'</td>';
            // $html .=				'<td>'.$val['content'].'</td>';
            $html .=				'<td>'.$val['view_page'].'</td>';
            $html .=				'<td>'.$val['view_other'].'</td>';
            $html .=				'<td>'.$val['url_num'].'</td>';
            $html .=				'<td>'.$val['site_id'].'</td>';
            $param['id'] = $val['id'];
            $param['del'] = 1;
            $html .=				'<td><a href="'.U('Generalize/onOffLog', $param).'">删除</a></td>';
            $html .=			'</tr>';
        }
        $html .=			'<tr>';
        $html .=			'<td colspan="5">&nbsp;</td>';
        $html .=				'<td>总数：'.$count.'</td>';
        unset($param['id']);
        unset($param['del']);
        $html .=				'<td><a href="'.u('Generalize/onOffLog', array_merge($param, array('p'=>$p_pre))).'">上一页</a></td>';
        $html .=				'<td>当前第 '.$p.' 页</td>';
        $html .=				'<td><a href="'.u('Generalize/onOffLog', array_merge($param, array('p'=>$p_next))).'">下一页</a></td>';
        $html .=			'</tr>';
        $html .=		'</table>';

        echo $html;
        exit;
    }
	

    //删除数据
    public function deal(){
        $id = I('request.id', 0, 'intval');
        $site_id = I('request.site_id', 0);
        if($id){
            $where['id'] = $id;
        }
        if($site_id){
            $where['site_id'] = $site_id;
        }
		if(!empty($where)){
			$this->generalizeModel->where($where)->delete();
		}
        
        echo $this->generalizeModel->getLastSql();
        exit('清除成功！');
    }
	
	//获取反爬虫推广链接的配置
	public function peizhi(){
		$url_config = load_config(CONF_PATH.'generalize_url.php');
		$site = I('request.site_id');	//站点id
		$url_num = I('request.url_num');	//链接编号
		$qq = I('request.qq');	//1-营销QQ，2-个人qq
//		$str = I('request.str', 0, 'intval');	//获取个人QQ时，加上此参数，可将QQ按照
		$msg = '';
		if($site){
			if($url_num){
				if($qq){
					if($qq == 1){
						$msg = '营销QQ：';
						$url_config = $url_config[$site][$url_num]['qq']['marketing'];
					}else if($qq == 2){
						$msg = '个人QQ：';
						$url_config = $url_config[$site][$url_num]['qq']['self'];
						// if(is_numeric($str)){
							// $url_config = array_chunk($url_config, 15, TRUE);
							// $url_config = array_chunk($url_config, 15, TRUE);
						// }
					}else{
						$msg = '链接:'.$url_config[$site][$url_num]['href'].'配置：';
						$url_config = $url_config[$site][$url_num];	
					}
				}else{
					$msg = '链接:'.$url_config[$site][$url_num]['href'].'配置：';
					$url_config = $url_config[$site][$url_num];	
				}
			}else{
				switch($site){
					case 14:
						$msg = 'Q站配置：';
						break;
					case 40:
						$msg = '白芙泥配置：';
						break;
					case 87:
						$msg = '3g站配置：';
						break;
					default:
					$msg = '所有配置：';
				}
				$url_config = $url_config[$site];	//14-q站，87-3g站，40-白芙泥
			}
		}
		
		echo '<pre>';
		echo $msg,"\n";
		echo "\n";
		print_r($url_config);
		echo '</pre>';
	}

    //读取配置，方便查看QQ
	/**
	 *	读取配置的QQ
	 *	@param $id int TMS推广链接 -> 爬虫页面列表id 
	 *
	 */
    public function newPeizhi(){
		
		$id = I('request.id', 0, 'intval');
		if($id){
			$config = S('generalize_page_data_'.$id);
			if(!$config){
				$show = '文件不存在！';
			}else{
				$show = json_decode(str_replace('jsonp=', '', $config), true);
			}
			/*$file_path = RUNTIME_PATH."Data/generalize_page/{$id}.data";
			if(file_exists($file_path)){
				$config = file_get_contents($file_path);
				$show = json_decode(str_replace('jsonp=', '', $config), true);
			}else{
				$show = '文件不存在！';
			}*/
		}else{
			//TODO:加载配置
			$url_config = load_config(CONF_PATH.'generalize_url.php');
			$show = array();
			foreach($url_config as $site_id=>$peizhi){
				foreach($peizhi as $key=>$val){
					//TODO:以链接为key重组配置
					$show[$val['href']]['链接'] = $val['href'];
					$qq_marketing = $val['qq']['marketing'];    //营销QQ 配置
					$qq_self = $val['qq']['self'];  //私人QQ 配置
					$qq_all = array();
					$arr = array();
					$qq = array();

					//营销QQ
					if(!empty($qq_marketing)){
						$show[$val['href']]['营销QQ'] = $qq_marketing;
					}

					//私人QQ
					if(!empty($qq_self)){
						foreach($qq_self as $k=>$v){
							if(is_array($v)){
								//分组的私人QQ
								$qq[$k] = implode(',', $v);
								foreach($v as $v_q){
									$qq_all[] = $v_q;
								}
								$arr[$k] = $v;
								//TODO:每个分组的总数量
								$arr[$k]['count'] = count($v);
							}else{
								//没有分组的私人QQ_01
								$qq['other'][] = $v;
								$qq_all[] = $v;
							}
						}

						//没有分组的私人QQ_02
						if(!empty($qq['other'])){
							$qq['other']['count'] = count($qq['other']);
							$arr['other'] = $qq['other'];
							unset($qq['other']['count']);
							$qq['other'] = implode(',', $qq['other']);
						}
					}
					$show[$val['href']]['私人QQ']['qq'] = $qq;    //QQ分组组合串
					$show[$val['href']]['私人QQ']['arr'] = $arr;      //QQ分组以及每组总数
					$show[$val['href']]['私人QQ']['arr_count'] = $qq_all; //全部QQ
					$show[$val['href']]['私人QQ']['sum'] = count($qq_all);    //QQ总数



				}
			}
			$url = I('request.url', '');
			if(isset($show[$url])){
				$show = $show[$url];
			}
		}
		echo '<pre>';
		print_r($show);
		echo '</pre>';
		exit;
    }


    public function getQQ(){
		$href = I('request.url','');
        $url_config = load_config(CONF_PATH.'generalize_url.php');
		$show = array();
        foreach($url_config as $key=>$val){
			foreach($val as $k=>$v){
				if(!empty($v['qq']['marketing'])){
					foreach($v['qq']['marketing'] as $qv){
						$show[$v['href']]['marketing']['总数'] = count($v['qq']['marketing']);
						$show[$v['href']]['marketing'][] = $qv;
					}
				}
				if(!empty($v['qq']['self'])){
					$count['总数'] = count($v['qq']['self']);
					$show[$v['href']]['self_str'] = implode(',', $v['qq']['self']);
					$show[$v['href']]['self'] = array_merge($count,$v['qq']['self']);
					
				}
			}
            echo '<pre>';
			if($href){
				$show[$href]['href'] = $href;
				$show = $show[$href];
				print_r($show);
				break;
			}
            print_r($show);
            // print_r($val);
        }
//        echo '<pre>';
//        print_r($url_config);
    }

    //TODO:读取日志
    public function readLog(){
        //TODO:接收参数
        $limit			= I('request.limit', 30, 'intval');
		$p				= I('request.p', 1, 'intval');
		$site_id		= I('request.site_id', 0);
        $url_num		= I('request.url_num', 0);
        $ip				= I('request.ip', '', 'trim');
        $return_type	= I('request.return_type', 0);
        $qq				= I('request.qq', '', 'trim');
        $qq_type		= I('request.qq_type', 0, 'intval');
        $qq2			= I('request.qq2', '', 'trim');
        $qq2_type		= I('request.qq2_type', '', 'trim');
        $qq_type		= I('request.qq_type', 0, 'intval');
        $sign_status	= I('request.sign_status', 0, 'intval');
        $click_qq		= I('request.click_qq', '', 'trim');
        $click_qq_type	= I('request.click_qq_type', 0, 'intval');
        $updated		= I('request.updated', 0, 'intval');
		
        $where = array();
        //TODO:组合查询条件
        if($site_id){
            $where['site_id'] = $site_id;
        }
        if($url_num){
            $where['url_num'] = $url_num;
        }
		if($ip){
			$where['ip'] = $ip;
		}
		if($return_type){
			$where['return_type'] = $return_type;
		}
        if($qq){
            $where['qq'] = $qq;
        }
		if($qq_type){
			$where['qq_type'] = $qq_type;
		}
        if($qq2){
            $where['qq2'] = $qq2;
        }
		if($qq2_type){
			$where['qq2_type'] = $qq2_type;
		}
        if(isset($_GET['click_qq'])){
            if($click_qq == 1){
                $where['click_qq'] = array('neq', '');
            }else if($click_qq){
                $where['click_qq'] = $click_qq;
            }else{
                $where['click_qq'] = array('eq', '');
            }
        }
		if($click_qq_type){
			$where['click_qq_type'] = $click_qq_type;
		}
		
        $model = M('RequestLog', 'thinkphx_', 'APPS');
        $id = I('request.id', 0, 'intval');
        if($id){
            $res = $model->where(array('id'=>$id))->delete();
            if($res){
                redirect(U('Generalize/readLog', $param));
            }
        }

        //数据总数
        $count = $model->where($where)->count();

        echo '总数：'.$count, "\n\n\n";
        $offset = ($p - 1) * $limit;
        $logs = $model
            ->where($where)
            ->field('*')
            ->limit($offset, $limit)
            ->order('id desc')
            ->select();
//        echo $model->getLastSql();exit;
        $show = I('request.show', 0, 'intval');
        foreach($logs as $key=>&$val){
            if($show){
                $val['content'] = json_decode(htmlspecialchars_decode($val['content']), true);
            }else{
                $val['content'] = stripslashes($val['content']);
            }
            $val['sign_return'] = stripslashes($val['sign_return']);
            list($time, $microtime) = explode('.', $val['request_time']);
            $val['request_time'] = date('Y-m-d H:i:s', $time).' '.$microtime;
            $val['log_time'] = date('Y-m-d H:i:s', $val['log_time']);
            if($val['sign_time']){
                list($time, $microtime) = explode('.', $val['sign_time']);
                $val['sign_time'] = date('Y-m-d H:i:s', $time).' '.$microtime;
            }
            if($val['request_time2']){
                list($time, $microtime) = explode('.', $val['request_time2']);
                $val['request_time2'] = date('Y-m-d H:i:s', $time).' '.$microtime;
            }
            if($val['click_time']){
                list($time, $microtime) = explode('.', $val['click_time']);
                $val['click_time'] = date('Y-m-d H:i:s', $time).' '.$microtime;
            }
            if($val['over_time']){
                list($time, $microtime) = explode('.', $val['over_time']);
                $val['over_time'] = date('Y-m-d H:i:s', $time).' '.$microtime;
            }

        }
        echo '<pre>';
        print_r($logs);
        exit;
    }


}
