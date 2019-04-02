<?php


namespace App\Model;


class MyDown extends Base
{
    public $table = 'my_down';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}