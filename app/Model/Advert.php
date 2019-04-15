<?php


namespace App\Model;


class Advert extends Base
{
    public $table = 'advert';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}