<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 16:01
 */

namespace App\Model;


class Column extends Base
{
    public $table = 'column';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}