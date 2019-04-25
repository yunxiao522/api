<?php


namespace App\Model;

class LogLogin extends Base
{
    public $table = 'log_login';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}