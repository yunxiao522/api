<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/28 0028
 * Time: 14:37
 */

namespace App\Libs;
use App\Libs\Aliyun\Sms\SignatureHelper;
use App\Http\Controllers\Api\Response;
use App\Model\UserSms;
use App\Libs\sucaiz\Config;

class Sms
{
    //获取短信验证码
    public static function getCode(){
        return rand(100000,999999);
    }

    public static function sendSms($phone,$singname,$templatescode,$templateparams = [],$data = []){
        set_time_limit(0);
        $params = array ();
        // *** 需用户填写部分 ***
        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = Config::get('cfg_aliyun_app_id');
        $accessKeySecret = Config::get('cfg_aliyun_app_key');
        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $phone;
        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $singname;
        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $templatescode;
        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = $templateparams;
        // fixme 可选: 设置发送短信流水号
//    $params['OutId'] = "12345";
        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
//    $params['SmsUpExtendCode'] = "1234567";
        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();
        $data['phone'] = $phone;
        $data['sms_code'] = $singname;
        $data['status'] = 3;
        $data['create_time'] = time();
        $sms_id = self::addSmsData($data);
        try{
            // 此处可能会抛出异常，注意catch
            $content = $helper->request(
                $accessKeyId,
                $accessKeySecret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                ))
            // fixme 选填: 启用https
            // ,true
            );
            dump($content);
        }catch (\Exception $exception){
            UserSms::edit(['id'=>$sms_id],['status'=>2]);
            Response::fail('发送失败');
        }

        if($content->Code == 'OK'){
            UserSms::edit(['id'=>$sms_id],['status'=>1]);
            return true;
        }else{
            UserSms::edit(['id'=>$sms_id],['status'=>2]);
            return false;
        }
    }

    /**
     * @param $data
     * @return mixed
     * Description 添加信息到用户短信发送记录表
     */
    public static function addSmsData($data){
        return UserSms::add($data);
    }
}