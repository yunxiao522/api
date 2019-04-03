<?php


namespace App\Model;


class ArticleBody extends Base
{
    public $table = 'article_body';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}