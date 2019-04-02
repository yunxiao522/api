<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/27 0027
 * Time: 17:04
 */

namespace App\Http\Controllers\Api;

use App\Model\Article;
use App\Model\ArticleImages;
use App\Model\Column;
use App\Model\MyLike;
use Illuminate\Http\Request;

class LikeController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Description 收藏操作,文档、评论的添加收藏,取消收藏
     */
    public function collection()
    {
        $id = $this->request->route('id');
        $p = request('p');
        if (!empty($p) && !is_numeric($p) && $p != 'undefined') {
            return Response::fail('参数错误');
        }
        $p = empty($p) || $p == 'undefined' ? 0 : $p;
        $type = request('type');
        $type = empty($type) ? 1 : $type;

        if(!isset(MyLike::$like_type[$type])){
            return Response::fail('参数错误');
        }
        //查询是否已经有过收藏操作
        $like_id = MyLike::getField([
            'uid'=>Auth::getUserId(),
            'article_id'=>$id,
            'alone'=>$p,
            'type'=>$type
        ],'id');
        if(!empty($like_id)){
            //删除收藏操作
            $res = MyLike::del(['id'=>$like_id]);
            if($res){
                return Response::success([],'','取消收藏成功');
            }else{
                return Response::fail('取消收藏失败');
            }
        }
        $article_info = Article::getOne([
            'id'=>$id,
            'is_delete'=>1,
            'is_audit'=>1,
            'draft'=>2
        ],['channel','column_id']);
        if(empty($article_info)){
            return Response::fail('文档不存在');
        }
        if($article_info['channel'] == 2){
            //查询图集图片数量
            $img_num = ArticleImages::getField(['article_id'=>$id],'imgnum');
            if(($p + 1) > $img_num || $p <0){
                return Response::fail('参数错误');
            }
        }
        $res = MyLike::add([
            'uid'=>Auth::getUserId(),
            'article_id'=>$id,
            'alone'=>$p,
            'create_time'=>time(),
            'type'=>1,
            'channel'=>$article_info['column_id'],
            'class_id'=>1
        ]);
        if($res){
            return Response::success([],'','收藏成功');
        }else{
            return Response::fail('收藏失败');
        }
    }

    /**
     * Description 获取我的收藏数据
     */
    public function getMyLike(){
        $type = request('type');
        if(empty($type) || !isset(MyLike::$like_class[$type])){
            Response::fail('参数错误');
        }
        $column_son_list = Column::getAll(['parent_id'=>MyLike::$like_class[$type]['column_id']],'id',1000);
        $column_son_list = array_column($column_son_list,'id');
        $column_son_list[] = MyLike::$like_class[$type]['column_id'];
        //获取收藏列表数据
        $where  = [
            'uid'=>Auth::getUserId()
        ];
        $whereIn = ['channel',$column_son_list];
        $list = MyLike::getListIn($where,$whereIn,['article_id','alone','id','channel']);
        //循环列表数据
        foreach($list['data'] as $key => $value){
            //匹配图像正则方法
            $src_rule = "/(href|src)=([\"|']?)([^\"'>]+.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG))/i";
            //根据$type值获取文档信息
            if($type == 1 || $type == 3){
                $img = ArticleImages::getField(['article_id'=>$value['article_id']],'imgurls');
                $img_arr = explode(',',$img);
                $img_url = $img_arr[$value['alone']];
                preg_match($src_rule,$img_url,$match);
                $list['data'][$key]['img_url'] = $match[3];
                $title = Article::getField(['id'=>$value['article_id']],'title');
                $list['data'][$key]['title'] = $title;
            }else if($type == 2){
                $article_info = Article::getOne(['id'=>$value['article_id']],['title','id','litpic','pubdate']);
                $article_info['pubdate'] = date('Y-m-d',$article_info['pubdate']);
                $article_info['column'] = Column::getField(['id'=>$value['channel']],'type_name');
                $list['data'][$key]['article_info'] = $article_info;
            }
        }
        Response::success($list,'','获取数据成功');
    }


}