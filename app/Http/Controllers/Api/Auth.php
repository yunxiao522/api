<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/21 0021
 * Time: 14:32
 */

namespace App\Http\Controllers\Api;

use App\Model\LogLogin;
use App\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Auth extends BaseController
{
    //认证token有效时间
    private static $expiration = 7200;
    //refresh_token有效时间
    private static $refresh_token_ttl = 2678400;
    //获取认证token限流信息，[单位时间内,可刷新的次数]
    public static $quota = [600, 10];
    public static $token_key = 'hash';
    public static $token_quota_key = 'user_quota';
    public static $refresh_token_key = 'refresh_token';
    public static $refresh_quota_key = 'refresh_quota_token';

    public static function start()
    {

    }

    public static function quota()
    {
        return false;
    }

    /**
     * Description 获取用户token,用于认证
     */
    public function getToken(Request $request)
    {
        $type = request('type');
        if (empty($type)) {
            $type = 'refresh_token';
        }
        switch ($type) {
            case 'pass':
                $user = $request->user;
                $pass = md5($request->input('pass'));
                $token_quota_key = str_replace('user', $user, self::$token_quota_key);
                $n = Redis::get($token_quota_key);
                break;
            case 'refresh_token':
                $refresh_token = request('refresh_token');
                if (empty($refresh_token)) {
                    Response::setHeaderCode(401, 'params error');
                    Response::fail('params error', '');
                }
                $token_quota_key = str_replace('token', $refresh_token, self::$refresh_quota_key);
                $n = Redis::get($token_quota_key);
                break;
        }
        //限流代码
        if (!empty($n) && self::quota() && $n > self::$quota[1]) {
            Response::setHeaderCode(403, 'refresh too fast');
            Response::send_msg('refresh too fast');
        }
        if ($type == 'pass') {
            //构建查询条件
            $where = ['password' => $pass];
            if (is_phone($user)) {
                $where['phone'] = $user;
            } else if (is_email($user)) {
                $where['email'] = $user;
            } else {
                $where['username'] = $user;
            }
            $user_token = User::getField($where, 'token');
            if (empty($user_token)) {
                Response::setHeaderCode(401, 'auth faild');
                Response::fail('auth faild');
            }
        } else if ($type == 'refresh_token') {
            $refresh_token_key = str_replace('token', $refresh_token, self::$refresh_token_key);
            $user_token = Redis::get($refresh_token_key);
            if (empty($user_token)) {
                Response::setHeaderCode(412, 'old refresh token');
                Response::fail('old refresh token', '', 10010);
            }
        }
        $token = Hash::make($user_token);
        $token_key = str_replace('hash', $token, self::$token_key);
        Redis::set($token_key, $user_token, self::$expiration);
        Redis::inc($token_quota_key, 1, self::$quota[0]);
        Response::setHeaderCode();
        Response::success([
            'token' => $token,
            'expirat_in' => self::$expiration
        ], '', 'get token success');
    }

    /**
     * @return mixed
     * Description 获取用户token
     */
    public static function getUserToken()
    {
        $token = self::getAuthStatus();
        if (!$token) {
            return false;
        }
        $user_token = Redis::get($token);
        return $user_token;
    }

    /**
     *Description 验证认证信息
     */
    public static function checkAuth($request, $next)
    {
        if (!self::getAuthStatus()) {
            Response::setHeaderCode(200, 'auth faild');
            Response::fail('auth faild', '', 10000);
        }
        return $next($request);
    }

    /**
     * @return array|Request|string
     * Descriptionn 获取验证结果
     */
    public static function getAuthStatus()
    {
        $token = request('token');
        if (empty($token) || empty(Redis::get($token))) {
            return false;
        }
        return $token;
    }

    /**
     *Description 获取用户id
     */
    public static function getUserId()
    {
        $token = self::getUserToken();
        if (empty($token)) {
            return false;
        }
        $where = [
            'token' => $token,
        ];
        return User::getField($where, 'id');
    }

    /**
     *Description 获取refresh_token
     */
    public function getRefreshToken()
    {
        $user = request('user');
        $pass = md5(request('pass'));
        $token_quota_key = str_replace('user', $user, self::$token_quota_key);
        $n = Redis::get($token_quota_key);
        //限流操作
        if (!empty($n) && self::quota() && $n > self::$quota[1]) {
            Response::setHeaderCode(403, 'refresh too fast');
            Response::send_msg('refresh too fast');
        }
        //根据账户类型构建查询条件
        $where = ['password' => $pass];
        if (is_phone($user)) {
            $where['phone'] = $user;
        } else if (is_email($user)) {
            $where['email'] = $user;
        } else {
            $where['username'] = $user;
        }
        $user_info = User::getOne($where, ['id','token', 'status', 'nickname']);
        //检查账号是否存在
        if (empty($user_info)) {
            Response::setHeaderCode(401, 'auth fail');
            Response::fail('auth fail','',10001);
        }
        //判断账号状态
        if (empty($user_info['nickname']) && 1 == $user_info['status']) {
            Response::success(['token'=>$user_info['token']],'The account information is incomplete', '', 10012);
        }
        $token = self::getMakeRefreshToken($user_info['token']);
        Redis::inc($token_quota_key, 1, self::$quota[0]);
        $method = empty(request('method'))? '' :request('method');
        //添加登录日志
        LogLogin::add([
            'uid'=>$user_info['id'],
            'login_time'=>time(),
            'login_ip'=>$this->request->ip(),
            'type'=>2,
            'browser'=>getBrowserInfo(),
            'method'=>$method
        ]);
        Response::setHeaderCode();
        Response::success([
            'refresh_token' => $token,
            'expirat_in' => self::$refresh_token_ttl
        ], '', 'get refresh_token success');
    }

    /**
     * @param $user_token
     * @return string
     * Description 生成保存会员账号的refresh_token。方便在外部生成获取
     */
    public static function getMakeRefreshToken($user_token){
        $token = Hash::make($user_token);
        $refresh_token_key = str_replace('token', $token, self::$refresh_token_key);
        Redis::set($refresh_token_key, $user_token, self::$refresh_token_ttl);
        return $token;
    }
}