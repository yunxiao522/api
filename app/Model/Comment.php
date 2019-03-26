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
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}