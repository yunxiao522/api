<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/21 0021
 * Time: 15:41
 */

namespace App\Http\Controllers\Api;

class Redis
{
    //存储redis实例
    private static $redis;
    //redis主机id
    public static $host = '127.0.0.1';
    //redis主机端口
    public static $post = '6379';
    //redis使用的数据库
    public static $db;
    //获取redis实例
    public static function getRedis(){
        try{
            self::$redis = new \Redis();
            self::$redis->connect(self::$host,self::$post);
            $db = isset(self::$db) ? self::$db : 0;
            self::$redis->select($db);
        }catch (Exception $e){
            Response::setHeaderCode('500','server error');
            Response::fail('server error');
        }
    }

    public static function get($key){
        self::getRedis();
        return self::$redis->get($key);
    }

    public static function set($key ,$value ,$ttl){
        self::getRedis();
        return self::$redis->set($key,$value,$ttl);
    }

    public static function inc($key,$num = 1,$ttl){
        self::getRedis();
        if(empty(self::get($key))){
            self::$redis->set($key,0,$ttl);
            self::$redis->incr($key,$num);
        }else{
            self::$redis->incr($key,$num);
        }
    }
}