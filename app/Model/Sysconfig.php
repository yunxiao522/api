<?php


namespace App\Model;


class Sysconfig extends Base
{
    public $table = 'sysconfig';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}