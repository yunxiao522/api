<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/25 0025
 * Time: 10:04
 */

namespace App\Model;


class ArticleHot extends Base
{
    public $table = 'article_hot';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}