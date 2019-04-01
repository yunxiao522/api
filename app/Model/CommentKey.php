<?php


namespace App\Model;


class CommentKey extends Base
{
    public $table = 'comment_key';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}