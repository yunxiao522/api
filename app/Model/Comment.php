<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 13:26
 */

namespace App\Model;


class Comment extends Base
{
    public $table = 'comment';
    public static $comment_type = [1=>'我的评论',2=>'我的跟评',3=>'回复我的'];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    //获取楼层数
    public static function getTier($where){
        return self::where($where)->orderBy('tier','desc')->first('tier');
    }
}