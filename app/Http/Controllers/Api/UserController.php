<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/27 0027
 * Time: 13:11
 */

namespace App\Http\Controllers\Api;


use App\Model\User;
use App\Model\UserSms;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function sendPhoneCode(){
        $phone = request('phone');
        $uid = User::getField(['phone'=>$phone],'id');
        if(!empty($uid)){
            Response::fail('该手机号已经绑定过其他账号');
        }
    }

    //获取账号加密后的密码
    public static function getPwd($password){
        return md5($password);
    }

    //获取会员账号头像
    public static function getFace(){
        $face_number = rand(1, 24);
        $face_url = 'https://www.sucai.biz/upload/face/' . $face_number . '.jpg';
        return $face_url;
    }

    //获取会员账号信息
    public function getUserInfo(){
        $uid = Auth::getUserId();
        $user_info = User::getOne(['id'=>$uid],['nickname','face','level','email','qq','gold','experience','sex','description','signature','create_time']);
        $user_info->create_time = date('Y-m-d',$user_info->create_time);
        Response::success($user_info);
    }
}