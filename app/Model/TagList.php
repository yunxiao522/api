<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 15:56
 */

namespace App\Model;


class TagList extends Base
{
    public $table = 'tag_list';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}