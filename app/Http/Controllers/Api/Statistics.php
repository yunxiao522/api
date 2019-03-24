<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 13:01
 */

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;

class Statistics extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
}