<?php
namespace Home\Controller;
use Home\Model\IndexModel;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        if(IS_GET){
            $timestamp  =  I('get.timestamp','');
            $nonce      =  I('get.nonce','');
            $token      =  'weixin';
            $signature  =  I('get.signature','');
            $echosr     =  I('get.echostr');
            $arr        =  array($timestamp,$nonce,$token);
            sort($arr);
            $tmpstr     =  implode('',$arr );
            $tmpstr     =  sha1($tmpstr);

            /**
             * 只有第一次的时候才会验证，所以，不是第一次，直接跳过
             */
            if ($tmpstr == $signature && $echosr){
                echo $echosr;
                exit;
            }
            else{
                $this->responseMsg();
            }
        }
        if (IS_POST){
            $this->responseMsg();
        }
    }
    public function responseMsg(){
        $postArr                =   $GLOBALS['HTTP_RAW_POST_DATA'];
        $postObj                =   simplexml_load_string($postArr);
        $m = new IndexModel();
        if (strtolower($postObj->MsgType) == 'event'){
            //判断是否是订阅事件
            if (strtolower($postObj->Event) == 'subscribe'){
                //回复用户消息
                $m->subscribe($postObj);
            }
        }
        if (strtolower($postObj->MsgType) == 'text'){
            $m->msgTypeText($postObj);
        }
    }
    public function getWxAccessToken(){
        $AppID      = 'wx1a74afe7a74e23ec';
        $AppSecret  = '3a792e6175d0977d0c1d2806053f2504';
        $url        = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$AppID.'&secret='.$AppSecret;
        $ch         =  curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $re  = curl_exec($ch);
        $arr = json_decode($re,true);
        $access_token = $arr['access_token'];
        $expires_in   = $arr['expires_in'];
        curl_close($ch);
        return $access_token;
    }
    public function getWxServerIp(){
//        $accessToken = 'kWtnM_jYmv_JbWOT2Jk78ac3wxfXHS_97GWllTP5pKEyeZUWGzuQ6wNGirMT-Q4vCqqDudiPh_h2UWrG9vfA5GxTlLWAOYVNakPObiBRoz_Nmd-9Hw1YdWIHnyOQTXHtHFAhAGASQL';
        $accessToken    = $this->getWxAccessToken();
        $url         = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token='.$accessToken;
        $ch          = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $re = curl_exec($ch);
        $arr = json_decode($re,true);
        echo $arr['ip_list'][0];
        curl_close($ch);
    }
    public function test(){
        $str = '手机号123-4567-8900';
        preg_match_all('/^手机号(1\d{2}-?\d{4}-?\d{4})/i',$str ,$match);
        $re = $match[1][0];
//        $r = '1234567890';
//        dump( str_replace('-', '',$r ));
        dump($re);
    }
}