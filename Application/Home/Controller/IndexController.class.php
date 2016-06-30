<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        if(IS_GET){
            /**
             * 1.获得参数  timestamp,nonce,token,signature,echostr
             * 2.行程数组
             * 3.排序
             * 4.拼接字符串
             * 5.加密
             * 6.比较
             */
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
        /**
         * 1.获取到微信推送过来的post数据（xml）
                <xml>
                <ToUserName><![CDATA[toUser]]></ToUserName>
                <FromUserName><![CDATA[FromUser]]></FromUserName>
                <CreateTime>123456789</CreateTime>
                <MsgType><![CDATA[event]]></MsgType>
                <Event><![CDATA[subscribe]]></Event>
                </xml>
         *2.判断该数据包是否是订阅的事件推送
         * 3.回复用户
                <xml>
                <ToUserName><![CDATA[toUser]]></ToUserName>
                <FromUserName><![CDATA[fromUser]]></FromUserName>
                <CreateTime>12345678</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[你好]]></Content>
                </xml>
         */
        $postArr                =   $GLOBALS['HTTP_RAW_POST_DATA'];
        $postObj                =   simplexml_load_string($postArr);
        if (strtolower($postObj->MsgType) == 'event'){
            //判断是否是订阅事件
            if (strtolower($postObj->Event) == 'subscribe'){
                //回复用户消息
                $toUser         =   $postObj->FromUserName;
                $fromUser       =   $postObj->ToUserName;
                $time           =   time();
                $MsgType        =   'text';
                $content        =   '欢迎关注，我是夜微凉';
                $template       =   "
                               <xml>
                               <ToUserName><![CDATA[%s]]></ToUserName>
                               <FromUserName><![CDATA[%s]]></FromUserName>
                               <CreateTime>%u</CreateTime>
                               <MsgType><![CDATA[%s]]></MsgType>
                               <Content><![CDATA[%s]]></Content>
                               </xml>
                ";
                $info           =   sprintf($template,$toUser,$fromUser,$time,$MsgType,$content);
                echo $info;
                exit;
            }
        }
        /**
         * 1.检测发送的消息
            <xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[fromUser]]></FromUserName>
            <CreateTime>1348831860</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[this is a test]]></Content>
            <MsgId>1234567890123456</MsgId>
            </xml>
         * 2.回复文本消息
            <xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[fromUser]]></FromUserName>
            <CreateTime>12345678</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[你好]]></Content>
            </xml>
         */
        if (strtolower($postObj->MsgType) == 'text'){
            $fromUser   =   $postObj->ToUserName;
            $toUser     =   $postObj->FromUserName;
            $time       =   time();
            $content    =   "欢迎关注，我是夜微凉";
            //准备回复
            $template   =   "
                        <xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%u</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        </xml>
            ";
            $info       =   sprintf($template,$toUser,$fromUser,$time,$content);
            echo $info;
            exit;
        }
    }
}