<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/27 0027
 * Time: 11:41
 */

namespace App\Model;


class Admin extends Base
{
    public $table = 'admin';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}