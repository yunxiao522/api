<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/29 0029
 * Time: 12:49
 */

namespace App\Model;


class SearchColumn extends Base
{
    public $table;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}