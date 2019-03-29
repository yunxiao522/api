<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/29 0029
 * Time: 11:52
 */

namespace App\Model;


class Search extends Base
{
    public $table = 'search';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}