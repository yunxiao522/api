<?php


namespace App\Libs;

use App\Libs\sucaiz\Config;
use App\Model\UserEmail;
use App\Libs\mailer\PHPMailer;

class Email
{
    public static function getCode()
    {
        $code = rand(100000,999999);
        return $code;
    }

    public static function sendEmail($address, $addressname, $title, $content, $data = [])
    {

        set_time_limit(0);    //设置不超时，程序一直运行。
        //实例化PHPMailer核心类
        $mail = new PHPMailer();
        //使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        //smtp需要鉴权
        $mail->SMTPAuth = true;
        //smtp服务器地址
        $mail->Host = Config::get('cfg_smtp_host');
        //使用ssl加密方式登录鉴权
        $mail->STMPSecure = 'ssl';
        //ssl连接服务器使用的服务器端口
        $mail->Port = Config::get('cfg_smtp_port');
        //设置smtp的hello消息头，
//    $mail->Helo = 'Hello smtp.qq.com server';
        //设置发送邮件的编码
        $mail->CharSet = 'UTF-8';
        //设置发件人昵称
        $mail->FromName = Config::get('cfg_smtp_formname');
        //smtp登录的账号
        $mail->Username = Config::get('cfg_smtp_user');
        //smtp登录的密码
        $mail->Password = Config::get('cfg_smtp_password');
        //设置发件人邮箱地址
        $mail->From = Config::get('cfg_smtp_from');
        //邮件正文是否为html编码
        $mail->isHTML(true);
        //设置收件人邮箱地址
        $mail->addAddress($address, $addressname);
        //设置该邮件的主题
        $mail->Subject = $title;
        //添加邮件正文
        $mail->Body = $content;
        $email_id = self::addEmailData([
            'address' => $address,
            'uid' => $data['uid'],
            'title' => $data['title'],
            'content' => $content,
            'create_time' => time(),
            'status' => 3
        ]);
        //发送结果
        $status = $mail->send();
        if ($status) {
            UserEmail::edit(['id' => $email_id], ['status' => 1]);
            return true;
        } else {
            UserEmail::edit(['id' => $email_id], ['status' => 2]);
            return false;
        }
    }

    public static function addEmailData($data)
    {
        return UserEmail::add($data);
    }
}