<?php


namespace App\Libs\sucaiz;


class File
{
    /**
     * @param $url
     * @return mixed
     * Description 获取远程文件大小
     */
    public static function getRemoteFileSize($url)
    {
        $header_array = get_headers($url, true);
        return $header_array['Content-Length'];
    }

    /**
     * @param $url
     * @return mixed
     * Description 获取远程文件文件名
     */
    public static function getRemoteFileName($url)
    {
        $url_info = parse_url($url);
        $info = pathinfo($url_info['path']);
        return $info['filename'];
    }

    /**
     * @param $url
     * @return mixed
     * Description 获取远程文件扩展名
     */
    public static function getRemoteFileExt($url){
        $url_info = parse_url($url);
        $info = pathinfo($url_info['path']);
        return $info['extension'];
    }

    /**
     * @param $url
     * @return bool
     * Description 检查判断远程文件是否存在
     */
    public static function checkRemoteUrl($url){
        $headers = @get_headers($url,1);
        if (strtolower($headers[0]) == 'http/1.1 404 not found') {
            return false;
        } else {
            return true;
        }
    }
}