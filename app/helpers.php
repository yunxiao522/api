<?php
//自定义助手函数定义文件
if(!function_exists('cut_str')){
    //截取字符串
    function cut_str($str , $length){
        if(mb_strlen($str,'UTF-8') > $length){
            $item=mb_substr($str,0,$length,'UTF-8').'...';
            return $item;
        }else{
            return $str;
        }
    }
}
if(!function_exists('is_phone')){
    //判断字符串是否是手机号码
    function is_phone($str){
        $phone_rule = '/^1[345678]\d{9}$/';
        if(preg_match( $phone_rule,$str)){
            return true;
        }else{
            return false;
        }
    }
}
if(!function_exists('is_email')){
    //判断字符串是否是邮箱地址
    function is_email($str){
        $email_rule = "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix";
        if(preg_match($email_rule,$str)){
            return true;
        }else{
            return false;
        }
    }
}