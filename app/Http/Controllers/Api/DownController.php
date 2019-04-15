<?php


namespace App\Http\Controllers\Api;


use App\Model\Article;
use App\Model\Column;
use App\Model\MyDown;
use Illuminate\Http\Request;

class DownController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Description 获取我的下载数据
     */
    public function getMyDown(){
        $list = MyDown::getList(['uid'=>Auth::getUserId()],'*',10,['create_time','desc']);
        //循环处理列表数据
        foreach($list['data'] as $key => $value){
            $list['data'][$key]['create_time'] = date('y-m-d H:i:s',$value['create_time']);
            if($value['file_type'] == 'zip'){
                $list['data'][$key]['file_url'] = 'http://image.sucai.biz/2019-02-21/a81d6543f0f9700ad0534189cb3de34a.png';
            }
            $list['data'][$key]['column'] = Column::getField(['id'=>$value['column_id']],'type_name');
            $list['data'][$key]['article'] = self::cut_str(Article::getField(['id'=>$value['article_id']],'title'),10);
        }
        Response::success($list,'','获取数据成功');
    }
}