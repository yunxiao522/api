<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/27 0027
 * Time: 14:52
 */

namespace App\Model;


class FeedBack extends Base
{
    public $table = 'feedback';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}