<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/28 0028
 * Time: 14:07
 */

namespace App\Http\Controllers\Api;

use App\Libs\Sms;
use App\Model\User;
use Illuminate\Http\Request;

class RegisterController extends BaseController
{
    private $send_ip_key = 'send_sms_ip_num';
    private $send_phone_key = 'send_sms_phone';
    private $send_quota = [600, 5];
    private $send_code = 'SMS_133000964';
    private $send_sms_ttl = 600;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     *Description 发送注册短信方法
     */
    public function sendRegisterSms()
    {
        //设置过滤,防止接口被恶意使用
        $send_sms_key = str_replace('ip', $this->request->ip(), $this->send_ip_key);
        $send_num = Redis::get($send_sms_key);
        if (!empty($send_num) && $send_num >= $this->send_quota[1]) {
            Response::fail('您发送的次数太多啦');
        }
        Redis::inc($send_num, 1, $this->send_quota[0]);

        $phone = request('phone');
        if (empty($phone)) {
            Response::fail('请输入手机号');
        }
        $phone_rule = "/^1[345678]\d{9}$/";
        preg_match($phone_rule, $phone, $matches);
        if (!$matches) {
            Response::fail('手机号码格式不正确');
        }

        //判断手机号是否已经被使用
        $uid = User::getField(['phone' => $phone], 'id');
        if (!empty($uid)) {
            Response::fail('该手机号已经注册过啦，您可以直接登录');
        }

        $send_phone_key = str_replace('phone', $phone, $this->send_phone_key);
        $send_phone_status = Redis::get($send_phone_key);
        if (!empty($send_phone_status)) {
            Response::fail('您发送短信的频率太快啦...');
        }

        $code = Sms::getCode();
        Redis::set($send_phone_key, $code, $this->send_sms_ttl);

//        $res = Sms::sendSms($phone,
//            '素材站',
//            $this->send_code,
//            ['code' => $code],
//            [
//                'uid' => 0,
//                'title' => '手机短信激活码',
//                'content' => "您的验证码为$code ，请于10分钟内正确输入，如非本人操作，请忽略此短信。"
//            ]);
        $res = true;
        if ($res) {
            Response::success([], '', '发送成功,'.$code);
        } else {
            Response::fail('发送失败');
        }
    }

    /**
     * Description 会员账号注册方法
     */
    public function register()
    {
        $phone = request('phone');
        if (empty($phone)) {
            Response::fail('请填写手机号');
        }
        $phone_rule = "/^1[345786]\d{9}$/";
        preg_match($phone_rule, $phone, $matches);
        if (!$matches) {
            Response::fail('手机号格式不正确');
        }
        $code = request('code');
        if (empty($code)) {
            Response::fail('请输入手机短信验证码');
        }
        $pwd = request('pwd');
        if (empty($pwd)) {
            Response::fail('请输入账户密码');
        }

        //验证手机短信验证码
        $send_sms_key = str_replace('phone', $phone, $this->send_phone_key);
        $sms_code = Redis::get($send_sms_key);
        if ($sms_code != $code) {
            Response::fail('手机短信验证码不正确');
        }

        //检查手机号是否已经注册过
        $uid = User::getField(['phone' => $phone], 'id');
        if (!empty($uid)) {
            Response::fail('请更换手机号码,或者直接登录');
        }
        //组合数据添加到数据库
        $data = [
            'password' => UserController::getPwd($pwd),
            'type' => 1,
            'create_time' => time(),
            'status' => 1,
            'level' => 1,
            'experience' => 0,
            'gold' => 0,
            'integral' => 0,
            'comment_status' => 1,
            'index_status' => 1,
            'sex' => 1,
            'description' => '',
            'signature' => '',
            'face' => UserController::getFace(),
            'token'=>md5(time().$phone),
            'phone'=>$phone
        ];
        $res = User::add($data);
        if ($res) {
            Response::success(['token'=>$data['token']], '', '注册成功,请完善账户信息');
        } else {
            Response::fail('注册失败');
        }
    }

    /**
     *Description 完善账号信息
     */
    public function perfectAccount()
    {
        $data['nickname'] = request('nickname');
        if (empty($data['nickname'])) {
            Response::fail('请输入昵称');
        }
        if (mb_strlen($data['nickname'], 'UTF-8') > 20) {
            Response::fail('昵称不能超过20个字');
        }
        $data['realname'] = request('realname');
        if (empty($data['realname'])) {
            Response::fail('请输入真实姓名');
        }
        if (mb_strlen($data['realname'], 'UTF-8') > 20) {
            Response::fail('真实姓名不能超过20个字');
        }
        $uid = Auth::getUserId();
        if (!$uid) {
            Response::fail('会员账号不存在', '', 10011);
        }
        $data['alter_time'] = time();
        $data['status'] = 2;
        //更新信息
        $res = User::edit(['id' => $uid], $data);
        if ($res) {
            Response::success('修改信息成功');
        } else {
            Response::fail('修改信息失败');
        }
    }
}