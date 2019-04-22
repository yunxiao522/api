<?php


namespace App\Model;


class MyDown extends Base
{
    public $table = 'my_down';
    public static $pk = 'uid';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function addMyDown($uid,$article_id,$file_type,$file_size,$file_url,$column_id){
        //判断是否之前有下载过
        $where = ['file_url'=>$file_url];
        $count = self::getCount($where,'uid');
        if(empty($count)){
            return self::add([
                'article_id'=>$article_id,
                'file_type'=>$file_type,
                'file_size'=>$file_size,
                'file_url'=>$file_url,
                'create_time'=>time(),
                'column_id'=>$column_id,
                'uid'=>$uid
            ]);
        }
        return true;
    }
}