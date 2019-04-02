<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/27 0027
 * Time: 12:53
 */

namespace App\Model;


class MyLike extends Base
{
    public $table = 'my_like';
    public static $like_type = [1=>'文档',2=>'评论'];
    public static $like_class = [1=>['title'=>'手机壁纸','column_id'=>54],['title'=>'素材资讯','column_id'=>24],['title'=>'桌面壁纸','column_id'=>1]];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}