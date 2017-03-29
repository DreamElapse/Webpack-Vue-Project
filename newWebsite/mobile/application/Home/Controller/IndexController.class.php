<?php
namespace Home\Controller;

use Common\Controller\InitController;

class IndexController extends InitController
{
    public function index()
    {}

    public function test(){
        header("Content-Type: image/png");
        $code = 'https://qr.alipay.com/bax07142qepqonqru5t84040';
        import('Common/Extend/QrCode');
        echo \QRcode::png($code,null,'L','5',0);
    }
}
