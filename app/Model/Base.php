<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/21 0021
 * Time: 16:22
 */

namespace App\Model;


use App\Http\Controllers\Api\Redis;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    public $table;
    public static $cache_ttl = 600;
    public $primaryKey = 'id';
    public static $pk = 'id';
    public $timestamps = false;
    public static $limit = 20;
    public static $cache_key = 'table_pk';

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
    public static function getOne($where, $field = '*' ,$order = ['id','desc'])
    {
        return self::where($where)->orderBy($order[0],$order[1])->first($field);
    }


    /**
     * @param $where
     * @param $data
     * @return mixed
     * Description 修改数据
     */
    public static function edit($where, $data)
    {
        return self::where($where)->update($data);
    }

    /**
     * @param $where
     * @param $field
     * @param $num
     * @return mixed
     * Description 增加字段值
     */
    public static function incr($where, $field, $num = 1)
    {
        return self::where($where)->increment($field, $num);
    }

    /**
     * @param $where
     * @param $field
     * @param $num
     * @return mixed
     * Description 减少字段值
     */
    public static function decr($where, $field, $num = 1)
    {
        return self::where($where)->decrement($field, $num);
    }

    /**
     * @param $data
     * @return mixed
     * Description 插入数据
     */
    public static function add($data)
    {
        return self::insertGetId($data);
    }

    /**
     * @param $where
     * @param $field
     * @return string
     * Description 获取条件获取某一列数据
     */
    public static function getField($where, $field)
    {
        $res = self::where($where)->first($field);
        if (!isset($res[$field])) {
            return '';
        } else {
            return $res[$field];
        }
    }

    /**
     * @param $where
     * @param string $field
     * @return mixed
     * Description 统计符合条件的条数
     */
    public static function getCount($where, $field = '')
    {
        $field = empty($field) ? '' : $field;
        return self::where($where)->count();
    }

    /**
     * @param array $where
     * @param string $field
     * @param array $limit
     * @param array $order
     * @return array
     * Description 获取符合条件的列表数据
     */
    public static function getList($where = [], $field = '*', $limit = 0, $order = ['id', 'desc'])
    {
        $page = request('page');
        if (empty($page) || !is_numeric($page)) {
            $page = 1;
        }
        $limit = $limit == 0 || $limit >= self::$limit ? self::$limit : $limit;
        $limits = ($page - 1) * $limit;
        $res = self::where($where)->skip($limits)->take($limit)->orderBy($order[0], $order[1])->get($field)->toArray();
        $count = self::getCount($where);
        return [
            'count' => $count,
            'data' => $res,
            'current_page' => $page,
            'page' => ceil($count / $limit)
        ];
    }

    /**
     * @param array $where
     * @param string $field
     * @param array $limit
     * @param array $order
     * @return mixed
     * Description 获取符合条件的数据
     */
    public static function getALL($where = [], $field = '*', $limit = 100, $order = ['id', 'desc'])
    {
        $res = self::where($where)->skip(0)->take($limit)->get($field)->toArray();
        return $res;
    }

    /**
     * @param array $where
     * @param array $wherein
     * @param string $field
     * @param int $limit
     * @param array $order
     * @return mixed
     * Description 获取符合条件的全部数据,查询条件中有使用in,可使用此方法
     */
    public static function getAllIn($where = [], $wherein = [], $field = '*', $limit = 100, $order = ['id', 'desc'])
    {
        $res = self::where($where)->whereIn($wherein[0], $wherein[1])->skip(0)->take($limit)->get($field)->toArray();
        return $res;
    }

    /**
     * @param array $where
     * @param array $whereIn
     * @param string $field
     * @param int $limit
     * @param array $order
     * @return array
     * Description 获取符合条件的全部数据,查询条件中有使用in,可使用此方法
     */
    public static function getListIn($where = [], $whereIn = [], $field = "*", $limit = 0, $order = ['id', 'desc'])
    {
        $page = request('page');
        if (empty($page) || !is_numeric($page) || $page <= 0) {
            $page = 1;
        }
        $limit = $limit == 0 || $limit >= self::$limit ? self::$limit : $limit;
        $limits = ($page - 1) * $limit;
        $res = self::where($where)->whereIn($whereIn[0], $whereIn[1])->skip($limits)->take($limit)->orderBy($order[0], $order[1])->get($field)->toArray();
        $count = self::where($where)->whereIn($whereIn[0], $whereIn[1])->count();
        return [
            'count' => $count,
            'data' => $res,
            'current_page' => $page,
            'page' => ceil($count / $limit)
        ];
    }

    /**
     * @param array $where
     * @return mixed
     * Description 删除符合条件的数据
     */
    public static function del($where = []){
        return self::where($where)->delete();
    }

    public static function __callStatic($method, $parameters)
    {
        //判断是否查询单条数据,如果是则判断缓存内是否有记录
        return parent::__callStatic($method, $parameters); // TODO: Change the autogenerated stubd
    }

    private static function getCacheKey($id){
        return str_replace(['table','pk'],[(new static())->getTable(),$id],self::$cache_key);
    }

}