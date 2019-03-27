<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/27 0027
 * Time: 14:16
 */

namespace App\Model;


class ArticleImages extends Base
{
    public $table = 'article_images';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}