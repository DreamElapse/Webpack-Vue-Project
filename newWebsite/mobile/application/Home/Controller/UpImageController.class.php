<?php
/**
 * ====================================
 * 购物车 控制器
 * ====================================
 * Author: 9006765
 * Date: 2016-06-28 14:32
 * ====================================
 * File: CartController.class.php
 * ====================================
 */


namespace Home\Controller;

use Common\Controller\InitController;
use Common\Extend\Time;
use Common\Extend\PhxCrypt;

class UpImageController extends InitController {


    public function __construct(){
        parent::__construct();

    }


    public function index(){

        if(!isset($_POST['phoneNum']) || empty($_POST['phoneNum'])){
            $this->error('手机不能为空');
        }

        if(empty($_POST['upload_preview'])){
            $this->error('请上传图片');
        }

        $tel = PhxCrypt::phxEncrypt($_POST['phoneNum']);

        $res = array();
        $savePath =  "/upload";
        $path = APP_ROOT. $savePath;
        if(!is_dir($path)){
            if(!mkdir($path, 0777)){
                $this->error('无法创建目录');
            }
        }
        $data['tel'] = $tel;
        $data['add_time'] = Time::gmTime();
        $userSkin = D('userSkin');
        if(!is_string($_POST['upload_preview'])){
            $this->error('数据格式不正确!');
        }else{
            $_POST['upload_preview'] = json_decode($_POST['upload_preview']);
        }
        if(!is_array($_POST['upload_preview'])){
            $this->error('数据格式不正确!');
        }
        foreach($_POST['upload_preview'] as $base){
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base, $result)){
                $uid = $this->random_filename();
                $type = '.jpg';
                $imgPath = $path.'/'.$uid.$type;
                $put_res = file_put_contents($imgPath, base64_decode(str_replace($result[1], '', $base)));
                if($put_res === FALSE){
                    $this->error('无法写入文件!');
                }
                $data['pic'] = $savePath.'/'.$uid.$type;
                $userSkin -> create($data);
                $userSkin -> add();
            }else{
                $this->error('图片格式有误!');
            }
        }
        $this->success();

    }

/**
 * 生成随机的数字串
 *
 * @author: weber liu
 * @return string
 */
  private  function random_filename()
  {
        $str = '';
        for($i = 0; $i < 9; $i++)
        {
            $str .= mt_rand(0, 9);
        }
        return Time::gmtime() . $str;
  }


}