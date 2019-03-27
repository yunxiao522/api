<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 15:58
 */

namespace App\Model;


class Click extends Base
{
    public $table = 'click';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}