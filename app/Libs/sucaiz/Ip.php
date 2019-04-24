<?php


namespace App\Libs\sucaiz;


class Ip
{
    public static function getIpCity($ip){
        //组合url,获取数据
        $url="http://ip.taobao.com/service/getIpInfo.php?ip=$ip";
        $result = Http::request($url,false,'get');
        $content = json_decode($result,true);
        if($content['code'] == 0){
            if($content['data']['region'] == $content['data']['city']){
                $city = $content['data']['city'];
            }else{
                $city = $content['data']['region'] .$content['data']['city'];
            }
            if($content['data']['country'] != '中国'){
                $city = $content['data']['country'] .$city;
            }
            return $city;
        }else{
            return '';
        }
    }
}