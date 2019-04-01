<?php


namespace App\Model;


class CommentOperate extends Base
{
    public $table = 'comment_operate';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    //重构新增评论操作方法
    public static function add($data){
        return self::insert($data);
    }
}