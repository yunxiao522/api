<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/23 0023
 * Time: 19:09
 */

namespace App\Http\Middleware;
use App\Http\Controllers\Api\Auth;
use Closure;

class ApiAuth extends Auth
{
    public function handle($request, Closure $next, $role)
    {

    }
}