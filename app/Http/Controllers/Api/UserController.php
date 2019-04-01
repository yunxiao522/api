<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/27 0027
 * Time: 13:11
 */

namespace App\Http\Controllers\Api;


use App\Libs\Sms;
use App\Model\User;
use Illuminate\Http\Request;
use Validator;

class UserController extends BaseController
{
    public $sms_code_key = 'uid_phone_code';
    public $sms_code_ttl = 600;
    private $send_code = 'SMS_133000964';
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Description 发送手机验证码
     */
    public function sendPhoneCode(){
        //验证数据
        $validator = Validator::make($this->request->all(),[
            'phone'=>'required|phone'
        ],[
            'phone.required'=>'手机号码不能为空',
            'phone.phone'=>'手机号码格式不正确',
        ]);
        if($validator->fails()){
            Response::fail($validator->errors()->first());
        };
        $phone = request('phone');
        $uid = User::getField(['phone'=>$phone],'id');
        if(!empty($uid)){
            Response::fail('该手机号已经绑定过其他账号');
        }
        //获取手机短信code
        $code = Sms::getCode();
//        $res = Sms::sendSms($phone,
//            '素材站',
//            $this->send_code,
//            ['code'=>$code],
//            [
//                'uid' => Auth::getUserId(),
//                'title' => '手机短信激活码',
//                'content' => "您的验证码为$code ，请于10分钟内正确输入，如非本人操作，请忽略此短信。"
//            ]);
        //code存入redis
        $sms_code_key = str_replace(['uid','phone'],[$uid,$phone],$this->sms_code_key);
        Redis::set($sms_code_key,$code,$this->sms_code_ttl);
        if(true){
            Response::success(['code'=>$code],'','发送成功');
        }else{
            Response::fail('发送失败');
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
        $user_info = User::getOne(['id'=>$uid],['id','nickname','face','level','email','qq','gold','experience','sex','description','signature','create_time','phone']);
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
            Response::success([],'','修改成功');
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

    /**
     *Description 修改账号密码
     */
    public function editPassword(){
        $oldpwd = request('oldpwd');
        $newpwd = request('newpwd');
        $verifypwd = request('verifypwd');
        if(empty($oldpwd)){
            Response::fail('参数错误');
        }
        if(empty($newpwd)){
            Response::fail('参数错误');
        }
        if(empty($verifypwd)){
            Response::fail('参数错误');
        }
        if($newpwd != $verifypwd){
            Response::fail('两次输入的密码不一致');
        }
        $uid = Auth::getUserId();
        $user_pwd = User::getField(['id'=>$uid],'password');
        if($user_pwd != self::getPwd($oldpwd)){
            Response::fail('原密码不正确');
        }
        $res = User::edit(['id'=>$uid],['password'=>self::getPwd($newpwd)]);
        if($res){
            Response::success([],'','修改成功');
        }else{
            Response::fail('修改失败');
        }
    }

    /**
     * Description 修改手机号方法
     */
    public function editPhone(){
        $phone = request('phone');
        $code = request('code');
        //从redis中取出发送的短信验证码
        $key = str_replace(['phone','uid'],[$phone,Auth::getUserId()],$this->sms_code_key);
        $sms_code = Redis::get($key);
        if(empty($sms_code)){
            Response::fail('验证码已经过期');
        }
        if($code != $sms_code){
            Response::fail('验证码不正确');
        }
        $res = User::edit(['id'=>Auth::getUserId()],['phone'=>$phone],true);
        if($res){
            Response::success('换绑成功');
        }else{
            Response::fail('换绑失败');
        }
    }

    /*
     * Description 修改邮箱账号方法
     */
    public function editEmail(){
        $email = request('email');
        $code = request('code');

        $uid = Auth::getUserId();
        $res = User::edit(['id'=>$uid],['email'=>$email]);
        if($res){
            Response::success('','','修改成功');
        }else{
            Response::fail('修改失败');
        }
    }

}