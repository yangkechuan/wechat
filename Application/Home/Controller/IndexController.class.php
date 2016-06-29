<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        if(IS_GET){
            $timestamp  =  I('get.timestamp','');
            $nonce      =  I('get.nonce','');
            $token      =  'weixin';
            $signature  =  I('get.signature','');
            $arr        =  array($timestamp,$nonce,$token);
            sort($arr);
            $tmpstr     =  implode('',$arr );
            $tmpstr     =  sha1($tmpstr);
            if ($tmpstr == $signature){
                echo I('get.echostr','none');
                exit;
            }
        }
    }
}