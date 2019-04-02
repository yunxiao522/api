<?php
//自定义助手函数定义文件
if(function_exists('cut_str')){
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