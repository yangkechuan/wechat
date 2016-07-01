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
            $Msg        =   "祝您生活愉快";
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

            //增加天气接口
            if ($this->getWeather($postObj->Content)){
                $Msg    =   $this->getWeather($postObj->Content);
            }
            $info       =   sprintf($template,$toUser,$fromUser,$time,$Msg);
            echo $info;
            exit;
        }
    }
    protected function getWeather($weather){

        $preg       =   "/天气$/i";
        $match      =   preg_match($preg,$weather);
        if (!$match){
            return false;
        }
        preg_match_all('/(.*)天气$/i',$weather,$match);
        $city       =   $match[1][0];

//        $url        =   "http://wthrcdn.etouch.cn/weather_mini?city=";
//        $weatherMsg =   file_get_contents($url.$city);
//        $weatherMsg =   json_decode($weatherMsg);
//        $Msg        =   $weatherMsg['data']['forecast'];
//        $Msg        =   implode('\n',$Msg );
//        return $Msg;

        $ch = curl_init();
        $url = 'http://apis.baidu.com/heweather/weather/free?city='.$city;
        $header = array(
            'apikey: 3bdb311a33696ccdc5780f8032ac5e26',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        $res = json_decode($res,true);
//        dump($res);

        $city  = $res['HeWeather data service 3.0'][0]['basic']['city'];
        $time  = $res['HeWeather data service 3.0'][0]['basic']['update']['loc'];
        $pm25  = $res['HeWeather data service 3.0'][0]['aqi']['city']['pm25'];
        $qlty  = $res['HeWeather data service 3.0'][0]['aqi']['city']['qlty'];
        $txt_d = $res['HeWeather data service 3.0'][0]['daily_forecast'][0]['cond']['txt_d'];
        $txt_n = $res['HeWeather data service 3.0'][0]['daily_forecast'][0]['cond']['txt_n'];
        $max   = $res['HeWeather data service 3.0'][0]['daily_forecast'][0]['tmp']['max'];
        $min   = $res['HeWeather data service 3.0'][0]['daily_forecast'][0]['tmp']['min'];


        $Msg   = array(
            '城市'        => $city,
            '时间'        => $time,
            'pm2.5'      => $pm25,
            '空气质量'    =>  $qlty,
            '天气'        =>  $txt_d,
            '转'         =>   $txt_n,
            '最高气温'    =>    $max,
            '最低气温'      =>  $min
        );
        $Msg = "城市：".$city."\n"."时间:".$time."\n"."pm2.5：".$pm25."\n"."空气质量：".$qlty."\n"."天气：".$txt_d."转".$txt_n."\n"."最高气温：".$max."\n"."最低气温".$min;
        return $Msg;
    }

}