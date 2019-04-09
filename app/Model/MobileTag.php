<?php


namespace App\Model;


class MobileTag extends Base
{
    public $table = 'miniapp_tag';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}