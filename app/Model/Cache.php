<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 14:04
 */

namespace App\Model;


use App\Http\Controllers\Api\Redis;

class Cache
{
    public $table;
    private static $redis;
    public static function execute($table,$data,$type,$ttl){
        if($type == 'get'){

        }
    }


}