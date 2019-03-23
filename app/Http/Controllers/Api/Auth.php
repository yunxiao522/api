<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/21 0021
 * Time: 14:32
 */

namespace App\Http\Controllers\Api;
use App\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Auth extends BaseController
{
    //认证token有效时间
    private static $expiration = 7200;
    //获取认证token限流信息，[单位时间内,可刷新的次数]
    public static $quota = [600,10];
    public static $token_key = 'hash';
    public static $token_quota_key = 'user_quota';
    public static function start(){
        self::checkAuth();
    }

    public static function quota(){
        return false;
    }

    /**
     * Description 获取用户token,用于认证
     */
    public function getToken(Request $request){
        $user = $request->user;
        $pass = md5($request->input('pass'));
        $token_quota_key = str_replace('user',$user,self::$token_quota_key);
        $n = Redis::get($token_quota_key);
        if(!empty($n) && self::quota() && $n > self::$quota[1]){
            Response::setHeaderCode(403,'refresh too fast');
            Response::send_msg('refresh too fast');
        }
        $user_token = User::getOne(['username'=>$user,'password'=>$pass],['token']);
        if(empty($user_token)){
            Response::setHeaderCode(401,'auth faild');
            Response::fail('auth faild');
        }else{
            $token = Hash::make($user_token->token);
            $token_key = str_replace('hash',$token,self::$token_key);
            Redis::set($token_key,$user_token->token,self::$expiration);
            Redis::inc($token_quota_key,1,self::$quota[0]);
        }
        Response::setHeaderCode();
        Response::success([
            'token'=>$token,
            'expirat_in'=>self::$expiration
        ],'','get token success');
    }

    /**
     * @param $token
     * @return mixed
     * Description 获取用户token
     */
    public static function getUserToken($token){
        $user_token = Redis::get($token);
        if(empty($user_token)){
            Response::setHeaderCode(401,'token is old');
            Response::fail('token is old');
        }
        return $user_token;
    }

    /**
     *Description 验证认证信息
     */
    public static function checkAuth(){
        dump(1);
        $request = new Request();
        $token = $request->input('token');
        if(empty($token) || empty(Redis::get($token))){
            Response::setHeaderCode(401,'auth faild');
            Response::fail('auth faild');
        }
    }
}