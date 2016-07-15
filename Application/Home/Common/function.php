<?php
/**
 * Created by PhpStorm.
 * User: ykc
 * Date: 2016/7/14 0014
 * Time: 13:25
 */

function curl($url,$type = 'get',$header='',$res = 'json',$arr = ''){
    $ch = curl_init();
    if (!empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    if (strtolower($type) == 'post'){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
    }
    $output = curl_exec($ch);
    curl_exec($ch);
    curl_close($ch);
    if (strtolower($res) == 'json'){
        return json_decode($output,true);
    }
}
