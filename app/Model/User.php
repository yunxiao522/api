<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/21 0021
 * Time: 16:22
 */

namespace App\Model;


class User extends Base
{
    public $table = 'user';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}