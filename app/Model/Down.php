<?php


namespace App\Model;


class Down extends Base
{
    public $table = 'down';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function add($aid,$token,$source_file,$page){
        //查询是否已经下载过该文件
        $where = ['article_id'=>$aid,'page'=>$page];
        $down_info = self::getOne($where,['id','num']);
        //已经下载过则更新下载内容
        if(!empty($down_info)){
            return self::edit(['id'=>$down_info['id']],['num'=>$down_info['num']+1,'end_time'=>time()]);
        }else{
            return parent::add([
                'article_id'=>$aid,
                'num'=>0,
                'token'=>$token,
                'source_file'=>$source_file,
                'page'=>$page,
                'end_time'=>time()
            ]);
        }
    }
}