<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 11:53
 */

namespace App\Http\Controllers\Api;


use App\Model\Comment;
use Illuminate\Http\Request;

class CommentController extends BaseController
{
    private $limit = 20;
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Description 获取评论列表数据
     */
    public function getList(){
        $id = $this->request->route('id');
        $page = $this->request->route('page') ? $this->request->route('page'):1;
        $limit = $this->request->route('limit') && $this->request->route('limit') <= $this->limit?$this->request->route('limit'):$this->limit;
        $list = Comment::getList([
            'status'=>1,
            'article_id'=>$id
        ],'*',[$page,$limit],['create_time','desc']);
        return Response::success($list);
    }
}