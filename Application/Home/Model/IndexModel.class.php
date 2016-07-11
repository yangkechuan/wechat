<?php
/**
 * Created by PhpStorm.
 * User: ykc
 * Date: 16-7-7
 * Time: 下午11:09
 */
namespace Home\Model;
class IndexModel {
    public function subscribe($postObj){
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
    public function msgTypeText($postObj){
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
        //增加手机归属地
        if ($this->getMobileMsg($postObj->Content)){
            $Msg    =   $this->getMobileMsg($postObj->Content);
        }
        $info       =   sprintf($template,$toUser,$fromUser,$time,$Msg);
        echo $info;
        exit;
    }
    protected function getWeather($weather){
        //天气API
        $preg       =   "/天气$/i";
        $match      =   preg_match($preg,$weather);
        if (!$match){
            return false;
        }
        preg_match_all('/(.*)天气$/i',$weather,$match);
        $city       =   $match[1][0];
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
        if ($res['HeWeather data service 3.0'][0]['status'] == 'unknown city'){
            $Msg = "城市不存在哦，亲";
            return $Msg;
        }
        $city  = $res['HeWeather data service 3.0'][0]['basic']['city'];
        $time  = $res['HeWeather data service 3.0'][0]['basic']['update']['loc'];
        $pm25  = $res['HeWeather data service 3.0'][0]['aqi']['city']['pm25'];
        $qlty  = $res['HeWeather data service 3.0'][0]['aqi']['city']['qlty'];
        $txt_d = $res['HeWeather data service 3.0'][0]['daily_forecast'][0]['cond']['txt_d'];
        $txt_n = $res['HeWeather data service 3.0'][0]['daily_forecast'][0]['cond']['txt_n'];
        $max   = $res['HeWeather data service 3.0'][0]['daily_forecast'][0]['tmp']['max'];
        $min   = $res['HeWeather data service 3.0'][0]['daily_forecast'][0]['tmp']['min'];
        $Msg = "城市：".$city."\n"."时间：".$time."\n"."pm2.5：".$pm25."\n"."空气质量：".$qlty."\n"."天气：".$txt_d."转".$txt_n."\n"."最高气温：".$max."\n"."最低气温：".$min;
        return $Msg;
    }
    protected function getMobileMsg($mobile){
        //手机号查询API
        $preg  = '/^手机号/i';
        $match = preg_match($preg, $mobile);
        if (!$match){
            return false;
        }
        preg_match_all('/^手机号(1\d{2}-?\d{4}-?\d{4})/i',$mobile ,$match);
        $phone = str_replace('-','' , $match[1][0]);
        if (!$phone){
            $Msg = '手机号要输对哦';
            return $Msg;
        }
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/mobilenumber/mobilenumber?phone='.$phone;
        $header = array(
            'apikey: 3bdb311a33696ccdc5780f8032ac5e26',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $ret = curl_exec($ch);
        $ret = json_decode($ret,true);
        $retMsg  = $ret['retMsg'];
        $phone   = $ret['retData']['phone'];
        $supplier= $ret['retData']['supplier'];
        $province= $ret['retData']['province'];
        $city    = $ret['retData']['city'];
        if ($retMsg == 'success'){
            $Msg = "手机号：".$phone."\n"."运营商：".$supplier."\n"."省份：".$province."\n"."城市：".$city;
            return $Msg;
        }
        else{
            $Msg = '未知错误，请重试一次';
            return $Msg;
        }
    }
}