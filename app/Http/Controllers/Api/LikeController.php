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
use App\Model\MyLike;
use Illuminate\Http\Request;

class LikeController extends BaseController
{
    private $like_type = [1=>'文档',2=>'评论'];
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
        if (!empty($p) && !is_numeric($p)) {
            return Response::fail('参数错误');
        }
        $p = empty($p) || $p == 'undefined' ? 0 : $p;
        $type = request('type');
        $type = empty($type) ? 1 : $type;
        if(!isset($this->like_type[$type])){
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
                return Response::success('取消收藏成功','',2);
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
            return Response::success('收藏成功 ','',1);
        }else{
            return Response::fail('收藏失败');
        }
    }
}