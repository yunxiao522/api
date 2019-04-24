<?php


namespace App\Libs\sucaiz;


class Http
{
    public static function request($url, $https = true, $method = 'get', $data = null ,$headers = '')
    {
        //1.初始化
        $ch = curl_init($url);
        //2.设置curl
        //返回数据不输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(!empty($headers)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        //满足https
        if ($https == true) {
            //绕过ssl验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        //满足post
        if ($method === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        //    curl_setopt($ch, CURLOPT_NOSIGNAL, true);    //注意，毫秒超时一定要设置这个
        //    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); //超时时间200毫秒
        //3.发送请求
        $content = curl_exec($ch);
        //4.关闭资源
        curl_close($ch);
        return $content;
    }
}