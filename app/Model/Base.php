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
        return self::where($where)->first($field)->get();
    }
}