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

    /**
     * Description 获取会员账号信息方法
     */
    public function getUserInfo(){
        $uid = Auth::getUserId();
        $user_info = User::getOne(['id'=>$uid],['nickname','face','level','email','qq','gold','experience','sex','description','signature','create_time']);
        $user_info->create_time = date('Y-m-d',$user_info->create_time);
        Response::success($user_info);
    }

    /**
     *Description 修改会员账号信息方法
     */
    public function editUserInfo(){
        $data = $this->checkForm();
        if(is_string($data)){
            Response::fail($data);
        };
        $uid = Auth::getUserId();
        $res = User::edit(['id'=>$uid],$data);
        if($res){
            Response::success('修改成功');
        }else{
            Response::fail('修改失败');
        }
    }

    /**
     * @return string
     * Description 验证修改账号信息表单
     */
    private function checkForm(){
        $nickname = request('nickname');
        if(!empty($nickname)){
            $data['nickname'] = $nickname;
            if(mb_strlen($nickname,'UTF-8')>20){
                return '昵称不能超过20个字符';
            }
        }
        $sex = request('sex');
        if(!empty($sex)){
            if(in_array($sex,User::$user_sex)){
                $data['sex'] = array_search($sex,User::$user_sex);
            }else{
                return '性别不正确';
            }
        }
        $qq = request('qq');
        if(!empty($qq)){
            if(is_numeric($qq) && mb_strlen($qq ,'UTF-8') <= 12){
                $data['qq'] = $qq;
            }else{
                return 'qq账号格式不正确';
            }
        }
        $data['alter_time'] = time();
        return $data;
    }

}