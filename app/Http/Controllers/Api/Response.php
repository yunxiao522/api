<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/21 0021
 * Time: 14:25
 */

namespace App\Http\Controllers\Api;


class Response extends BaseController
{
    //请求成功的状态提示信息
    private static $success = 'success';
    //请求失败的状态提示信息
    private static $fail = 'error';
    //返回信息格式
    private static $MsgType = ['json','xml'];
    //返回信息格式设置
    public static  $type = 'json';
    //返回请求成功
    public static function success($data = '',$url = '',$msg = '' ,$code = 0){
        $m = [
            'msg'=>$msg,
            'status'=>self::$success,
            'success'=>true,
            'url'=>$url,
            'code'=>$code,
            'data'=>$data
        ];
        self::send_msg($m);
    }

    //返回请求失败
    public static function fail($msg = '',$url = '',$code = 1,$data = []){
        $m = [
            'msg'=>$msg,
            'status'=>self::$fail,
            'success'=>false,
            'url'=>$url,
            'code'=>$code,
            'data'=>$data,
        ];
        self::send_msg($m);
    }

    /**
     * @param $data
     * @return false|string
     * Description 获取输出的字符串
     */
    public static function getResponseData($data){
        if(self::$type == 'json' || !in_array(self::$type,self::$MsgType)){
            return self::arrToJson($data);
        }elseif(self::$type == 'xml'){
            return self::arrToXml($data);
        }
    }

    /**
     * @param $arr
     * @return string
     * Description 数组转xml字符串
     */
    private static function arrToXml($arr){
        $xml = "<root>";
        foreach($arr as $key => $val){
            if(is_array($val)){
                $child = self::arrToXml($val);
                $xml .= "<$key>$child</$key>";
            }else{
                $xml.= "<$key><![CDATA[$val]]></$key>";
            }
        }
        $xml.="</root>";
        return $xml;
    }

    /**
     * @param $arr
     * @return false|string
     * Description 数组转json字符串
     */
    private static function arrToJson($arr){
        return json_encode($arr,JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $code
     * @param $msg
     * Description 设置消息的返回头部code信息
     */
    public static function setHeaderCode($code = 200 ,$msg = 'OK'){
        header("HTTP/1.1 $code $msg");
    }

    /**
     * @param $data
     * Description 向客户端返回消息
     */
    public static function send_msg($data){
        echo self::getResponseData($data);die;
    }
}