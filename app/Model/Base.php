<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/21 0021
 * Time: 16:22
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    public $table;
    private $cache_ttl = 600;
    public $primaryKey = 'id';
    public $timestamps = false;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @param $where
     * @param string $field
     * @return mixed
     * Description 查找单条数据
     */
    public static function getOne($where ,$field = '*'){
        return self::where($where)->first($field);
    }


    /**
     * @param $where
     * @param $data
     * @return mixed
     * Description 修改数据
     */
    public static function edit($where ,$data){
        return self::where($where)->update($data);
    }

    /**
     * @param $where
     * @param $field
     * @param $num
     * @return mixed
     * Description 增加字段值
     */
    public static function incr($where,$field,$num = 1){
        return self::where($where)->increment($field,$num);
    }

    /**
     * @param $where
     * @param $field
     * @param $num
     * @return mixed
     * Description 减少字段值
     */
    public static function decr($where,$field,$num = 1){
        return self::where($where)->decrement($field,$num);
    }

    /**
     * @param $data
     * @return mixed
     * Description 插入数据
     */
    public static function add($data){
        return self::insert($data);
    }

    /**
     * @param $where
     * @param $field
     * @return string
     * Description 获取条件获取某一列数据
     */
    public static function getField($where,$field){
        $res = self::where($where)->first($field);
        if(!isset($res[$field])){
            return '';
        }else{
            return $res[$field];
        }
    }
}