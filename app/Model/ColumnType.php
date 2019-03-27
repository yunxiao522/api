<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/27 0027
 * Time: 13:36
 */

namespace App\Model;


class ColumnType extends Base
{
    public $table = 'column_type';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}