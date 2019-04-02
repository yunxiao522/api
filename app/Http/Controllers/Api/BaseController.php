<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/22 0022
 * Time: 19:48
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Article;
use App\Model\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libs\sucaiz\Config;

class BaseController extends Controller
{
    private $method;
    private static $article_state_url = '/article.html';
    public $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        //添加验证规则
        Validator::extend('phone',function($attribute, $value, $parameters, $validator){
            return preg_match('/^1[345678]\d{9}$/', $value);
        });
        Validator::extend('sms_code',function ($a,$v,$p,$validator){
            if(mb_strlen($v,'UTF-8') != 6){
                return false;
            }else{
                return true;
            }
        });
        Validator::extend('email_code',function ($a,$v,$p,$validator){
            if(mb_strlen($v ,'UTF-8') != 6){
                return false;
            }else{
                return true;
            }
        });
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

    /**
     * @param int $aid
     * @param bool $state
     * @param bool $full
     * @param int $page
     * @return string
     * Descriptiion 获取文档访问url
     */
    public static function getArticleUrl($aid = 0 ,$state = false ,$full = false,$page = 0){
        if($aid == 0 || !is_numeric($aid)){
            return '';
        }
        //判断获取的链接属性
        if($state){
            $article_info = Article::getOne(['id'=>$aid]);
            //判断是否单独设置了文件路径
            if(empty($article_info['redirecturl'])){
                $column_info = Column::getOne(['id'=>$article_info['column_id']]);
                //将文档命名规则字符串处理成小写
                $namerule = strtolower($column_info['namerule']);
                //取出年月日和文档id存入数组
                $name_info = [
                    '{y}' => date('Y', $article_info['pubdate']),
                    '{m}' => date('m', $article_info['pubdate']),
                    '{d}' => date('d', $article_info['pubdate']),
                    '{aid}' => $aid,
                ];
                if(!$page){
                    $name_info['_{page}'] = '';
                }else{
                    $page++;
                    $name_info['_{page}'] = "_$page";
                }
                //循环替换文档名规则内容
                foreach ($name_info as $key => $value) {
                    $namerule = str_replace($key, $value, $namerule);
                }
                //组合文档访问url
                $file = $column_info['type_dir'] . $namerule;
            }else{
                $file = $article_info['redirecturl'];
            }
            if($full){
                return Config::get('cfg_hostsite') .rtrim($file ,'/');
            }else{
                return rtrim($file ,'/');
            }
        }else{
            if($full){
                return Config::get('cfg_hostsite') .self::$article_state_url .'?id=' .$aid;
            }else{
                return self::$article_state_url .'?id=' .$aid;
            }
        }
    }

}