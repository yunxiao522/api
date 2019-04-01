<?php


namespace App\Model;


class CommentOperate extends Base
{
    public $table = 'comment_operate';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}