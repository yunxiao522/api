<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 15:59
 */

namespace App\Model;


class LogVisit extends Base
{
    public $table = 'log_visit';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}