<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/27 0027
 * Time: 13:11
 */

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

}