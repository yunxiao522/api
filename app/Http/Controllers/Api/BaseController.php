<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/22 0022
 * Time: 19:48
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    private $method;
    public function __construct(Request $request)
    {
        $this->method = $request->method();
    }
}