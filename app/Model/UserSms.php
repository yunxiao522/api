<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/28 0028
 * Time: 14:02
 */

namespace App\Model;


class UserSms extends Base
{
    public $table;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}