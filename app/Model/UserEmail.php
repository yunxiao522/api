<?php


namespace App\Model;


class UserEmail extends Base
{
    public $table = 'user_email';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}