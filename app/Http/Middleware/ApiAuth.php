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
    public function handle($request,$next){
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'Origin,No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With, token');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
        $response->header('Access-Control-Allow-Credentials', 'true');
        return $next($request);
    }
}