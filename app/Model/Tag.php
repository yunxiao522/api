<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 15:55
 */

namespace App\Model;


class Tag extends Base
{
    public $table = 'tag';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}