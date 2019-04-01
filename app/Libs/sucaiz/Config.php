<?php
/**
 * Description 获取系统设置参数扩展类
 */

namespace App\Libs\sucaiz;


use App\Http\Controllers\Api\Redis;
use App\Model\Sysconfig;

class Config
{
    public static $system_key = 'system_config_key';
    public static $system_key_ttl = 2678400;
    public static function get($key){
        $config_list = Redis::get(self::$system_key);
        if(empty($config_list)){
            $system_list = Sysconfig::getAll([],['name','value'],1000);
            $system_list = array_column($system_list,'value','name');
            Redis::set(self::$system_key,json_encode($system_list,JSON_UNESCAPED_UNICODE),self::$system_key_ttl);
        }else{
            $system_list = json_decode($config_list,true);
        }
        return isset($system_list[$key]) ? $config_list[$key] : '';
    }
}