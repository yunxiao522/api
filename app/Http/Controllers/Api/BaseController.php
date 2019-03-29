<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/22 0022
 * Time: 19:48
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    private $method;
    public $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $son_id 查询的栏目id
     * @param $column_list 栏目列表数据
     * @return array
     * Description 递归获取子栏目列表
     */
    public static function getSonList($son_id ,$column_list){
        $son_arr = [];
        foreach($column_list as $value){
            if($value['parent_id'] == $son_id){
                $son_arr[] = $value['id'];
                self::getSonList($value['id'] ,$column_list);
            }
        }
        return $son_arr;
    }

    /**
     * @param $str
     * @param $length
     * @return string
     * Description 截取字符串
     */
    public static function cut_str($str , $length){
        if(mb_strlen($str,'UTF-8') > $length){
            $item=mb_substr($str,0,$length,'UTF-8').'...';
            return $item;
        }else{
            return $str;
        }
    }

}