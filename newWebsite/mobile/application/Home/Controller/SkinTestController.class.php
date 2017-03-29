<?php
/**
 * ====================================
 * 肌肤测试
 * ====================================
 * Author: 9009123 (Lemonice)
 * Date: 2016-09-12
 * ====================================
 * File: SkinTestController.class.php
 * ====================================
 */
namespace Home\Controller;
use Common\Controller\InitController;
use Common\Extend\PhxCrypt;
use Common\Extend\Time;

class SkinTestController extends InitController {
    private $skinTestDb = null;  //数据表实例化
	
	private $feature = array(
		'no01'=>'不干也不油',
		'no02'=>'干燥，毛孔细',
		'no03'=>'T区油，U区干',
		'no04'=>'全脸泛油，易长痘'
	);

	private $skin_color = array(
		'no05'=>'白皙',
		'no06'=>'粉嫩',
		'no07'=>'暗黄',
		'no08'=>'大麦色'
	);

	private $skin_problem = array(
		'no09'=> '黑头/毛孔',
		'no10'=> '黑眼圈/眼袋/脂肪粒/眼纹',
		'no11'=> '痘痘/痘印',
		'no12'=> '干燥/紧绷',
		'no13'=> '暗黄/斑点',
		'no14'=> '皱纹',
		'no15'=> '出油'
	);

    public function _initialize() {
		parent::_initialize();
		$site_id = C('SITE_ID');
		$tableName = $site_id==14 ? 'skin_test' : 'sear_test';  //不同站点不同数据表名称
        $this->skinTestDb = M($tableName);
    }

    /*
    *	肌肤测试 - 逆龄美肌保卫战数据提交
    *	@Author 9009123 (Lemonice)
    *	@return exit && JSON
    */
    public function defendWar() {
		$session_id = session_id();
		$result = $this->skinTestDb->field('session_id')->where("session_id='".$session_id."'")->select();
		
		$num = 0;
		$time = Time::gmTime();
		if(!empty($result)){
			foreach($result as $v){
				if(($time-$v['create_date']) <=24*3600*30){
					$num++;
				}
			}
		}
		
		if($num>=3){
			$this->error('你操作太频繁了');
		}
		$params = I('request.params','','trim');
		if($params == ''){
			$this->error('您的操作有误');
		}
		$data = json_decode(stripslashes($params),true);
		if(!is_array($data) || empty($data)){
			$this->error('您的操作有误');
		}
		
		$skin_problem_value = '';
		if(strpos($data['skin_problem'],',')){
			$skin_problem_keys = array();
			$skin_problem_keys = explode(',',$data['skin_problem']);
			foreach($skin_problem_keys as $k){
				if(empty($skin_problem_value)){
					$skin_problem_value = $this->skin_problem[$k];
				}else{
					$skin_problem_value .= ';'.$this->skin_problem[$k];
				}
			}
		}else{
			$skin_problem_value =  $this->skin_problem[$data['skin_problem']];
		}

		if(mb_strlen($data['birth_month'])<=1){
			$data['birth_month'] = '0'.$data['birth_month'];
		}

		if(mb_strlen($data['birth_date'])<=1){
			$data['birth_date'] = '0'.$data['birth_date'];
		}
		$birthdate = $data['birth_year'].'-'.$data['birth_month'].'-'.$data['birth_date'];
		$data['phone'] = PhxCrypt::phxEncrypt(trim($data['phone']));
		$ip = get_client_ip();
		
		$phone_data = $this->skinTestDb->field('id,times')->where("phone='".$data['phone']."'")->find();

		if(isset($phone_data['id'])){
			if($phone_data['times']>10){
				$this->error('你操作太频繁了');
			}else{
				$this->skinTestDb->create(array(
					'feature'=>$this->feature[$data['feature']],
					'skin_color'=>$this->skin_color[$data['skin_color']],
					'skin_problem'=>$skin_problem_value,
					'sex'=>$data['sex'],
					'datatype'=>$data['data_type'],
					'birthdate'=>$birthdate,
					'phone'=>$data['phone'],
					'qq'=>$data['qq'],
					'ip'=>$ip,
					'session_id'=>$session_id,
					'username'=>$data['username'],
					'times'=>$phone_data['times']+1
				));
				$result = $this->skinTestDb->where("phone='".$data['phone']."'")->save();
				
				if($result !== false){
					$this->success('修改成功');
				}else{
					$this->error('服务器出错，请重试');
				}
			}
		}else{
			$this->skinTestDb->create(array(
				'feature'=>$this->feature[$data['feature']],
				'skin_color'=>$this->skin_color[$data['skin_color']],
				'skin_problem'=>$skin_problem_value,
				'sex'=>$data['sex'],
				'datatype'=>$data['data_type'],
				'birthdate'=>$birthdate,
				'phone'=>$data['phone'],
				'qq'=>$data['qq'],
				'ip'=>$ip,
				'session_id'=>$session_id,
				'create_date'=>$time,
				'username'=>$data['username']
			));
			$addres_id = $this->skinTestDb->add();
			
			if($addres_id > 0){
				$this->success('提交成功');
			}else{
				$this->error('服务器出错，请重试');
			}
		}
    }
}