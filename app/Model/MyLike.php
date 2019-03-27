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
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}