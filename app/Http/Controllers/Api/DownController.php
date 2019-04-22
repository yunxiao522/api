<?php


namespace App\Http\Controllers\Api;


use App\Libs\sucaiz\File;
use App\Model\Article;
use App\Model\Column;
use App\Model\Down;
use App\Model\MyDown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Description 获取文件下载链接
     */
    public function getDownUrl(){
        $id = request('id');
        if(empty($id) || !is_numeric($id)){
            Response::fail('参数错误');
        }
        $p = request('p');
        if(!empty($p) && !is_numeric($p)){
            Response::fail('参数错误');
        }
        $url = request('url');
        if(empty($url)){
            Response::fail('参数错误');
        }
        //检查远程文件是否存在
        if(!File::checkRemoteUrl($url)){
            Response::fail('文件不存在');
        }
        //查询文档信息
        $article_info = Article::getOne(['id'=>$id],['token','column_id']);
        if(empty($article_info)){
            Response::fail('文档不存在');
        }
        DB::beginTransaction();
        //添加下载表信息
        $res = Down::add($id,$article_info['token'],$url,$p);
        if(!$res){
            Response::fail('获取下载地址失败');
        }
        //添加我的下载信息
        $uid = Auth::getUserId();
        if($uid){
            $file_size = File::getRemoteFileSize($url);
            $file_ext = File::getRemoteFileExt($url);
            $res = MyDown::add($id,$file_ext,$file_size,$url,$article_info['column_id']);
            if(!$res){
                DB::rollBack();
                Response::fail('获取下载地址失败');
            }
        }
        DB::commit();
        Response::success(['url'=>$url],'','get url success');
    }
}