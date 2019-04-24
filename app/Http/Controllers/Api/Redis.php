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

    /**
     * @param $key
     * @return mixed
     * Description 获取key的值
     */
    public static function get($key){
        self::getRedis();
        return self::$redis->get($key);
    }

    /**
     * @param $key
     * @param $value
     * @param $ttl
     * @return mixed
     * Description 设置key的值
     */
    public static function set($key ,$value ,$ttl){
        self::getRedis();
        return self::$redis->set($key,$value,$ttl);
    }

    /**
     * @param $key
     * @param int $num
     * @param $ttl
     * Description 增加莫个key的值
     */
    public static function inc($key,$num = 1,$ttl){
        self::getRedis();
        if(empty(self::get($key))){
            self::$redis->set($key,0,$ttl);
            self::$redis->incr($key,$num);
        }else{
            self::$redis->incr($key,$num);
        }
    }

    /**
     * @param $table
     * @param $field
     * @return mixed
     * Description 获取hash的莫个field值
     */
    public static function hget($table,$field){
        self::getRedis();
        return self::$redis->hGet($table,$field);
    }

    /**
     * @param $table
     * @param $field
     * @param $value
     * @return mixed
     * Description 设置hash莫个field的value
     */
    public static function hset($table,$field,$value){
        self::getRedis();
        return self::$redis->hSet($table,$field,$value);
    }

    /**
     * @param $table
     * @param $field
     * @return mixed
     * Description 删除hash某个field的值
     */
    public static function hdel($table,$field){
        self::getRedis();
        return self::$redis->hdel($table,$field);
    }

    /**
     * @param $key
     * @return bool
     * Description 判断莫个hash表是否存在
     */
    public static function hexist($key){
        self::getRedis();
        $result = self::$redis->hexists($key);
        if($result == 1){
            return true;
        }else{
            return false;
        }
    }
}