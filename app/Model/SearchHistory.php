<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/29 0029
 * Time: 12:02
 */

namespace App\Model;


class SearchHistory extends Base
{
    public $table = 'search_history';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}