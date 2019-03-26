<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 11:44
 */

namespace App\Model;


class Article extends Base
{
    public $table = 'article';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}