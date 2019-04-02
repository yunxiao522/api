<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 11:53
 */

namespace App\Http\Controllers\Api;


use App\Model\Article;
use App\Model\Comment;
use App\Model\CommentOperate;
use App\Model\User;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $limit = $this->request->route('limit') && $this->request->route('limit') <= $this->limit?$this->request->route('limit'):$this->limit;
        $list = Comment::getList([
            'status'=>1,
            'aid'=>$id
        ],'*',$limit,['create_time','desc']);
        dump($list);
    }

    /**
     * Description 评论支持操作
     */
    public function praiser(){
        $validator = Validator::make($this->request->all(),[
            'id'=>'required|numeric'
        ],[
            'id.required'=>'非法访问',
            'id.numeric'=>'参数错误'
        ]);
        if($validator->fails()){
            Response::fail($validator->errors()->first());
        }
        $this->operate(request('id'),1);
    }

    /**
     * Description 评论反对操作
     */
    public function oppose(){
        $validator = Validator::make($this->request->all(),[
            'id'=>'required|numeric'
        ],[
            'id.required'=>'非法访问',
            'id.numeric'=>'参数错误'
        ]);
        if($validator->fails()){
            Response::fail($validator->errors()->first());
        }
        $this->operate(request('id'),2);
    }

    /**
     * @param $comment_id
     * @param $type
     * Description 评论操作方法
     */
    private function operate($comment_id,$type){

        if($type == 1){
            $contra_type = 2;
            $field = 'praiser';
        }else if($type == 2){
            $contra_type = 1;
            $field = 'oppose';
        }

        $uid = Auth::getUserId();
        //查询是否有过其他操作
        $where = [
            'comment_id'=>$comment_id,
            'type'=>$contra_type,
            'uid'=>$uid
        ];
        $count = CommentOperate::getField($where,'uid');
        if(!empty($count)){
            Response::fail('你已经投过票了');
        }
        //判断是否有过相同类型操作
        $where = [
            'comment_id'=>$comment_id,
            'type'=>$type,
            'uid'=>$uid
        ];
        $count = CommentOperate::getField($where,'uid');
        DB::beginTransaction();
        if(!empty($count)){
            //删除对应评论操作
            $res = CommentOperate::del($where);
            if(!$res){
                DB::rollBack();
                Response::fail('投票失败');
            }
            //减少对应评论状态的值
            $res = Comment::decr(['id'=>$comment_id],$field,1);
            if(!$res){
                DB::rollBack();
                Response::fail('投票失败');
            }
            $class = 2;
        }else{
            $res = CommentOperate::add([
                'uid'=>$uid,
                'comment_id'=>$comment_id,
                'type'=>$type,
                'create_time'=>time()
            ]);
            if(!$res){
                DB::rollBack();
                Response::fail('投票失败');
            }
            //更新评论表字段值
            $res = Comment::incr(['id'=>$comment_id],$field,1);
            if(!$res){
                DB::rollBack();
                Response::fail('投票失败');
            }
            $class = 1;
        }
        $num = Comment::getField(['id'=>$comment_id],$field);
        DB::commit();
        Response::success(['num'=>$num,'type'=>$class],'','投票成功');
    }

    /**
     * Description 发布评论
     */
    public function push(){

    }

    /**
     *Description 获取我的评论
     */
    public function getMyComment(){
        $type = request('type');
        $limit = request('limit');
        //验证处理数据
        if(empty($type) || !isset(Comment::$comment_type[$type])){
            Response::success([],'','数据为空');
        }
        $limit = !empty($limit) && is_numeric($limit) ? $limit : $this->limit;
        //组合查询条件
        $where['status'] = 1;
        $where[] = ['inform','<',5];
        $where['uid'] = Auth::getUserId();
        switch ($type){
            case 1:
                $where = [
                    'uid'=>Auth::getUserId(),
                    'parent_id'=>0,
                ];
                $list = Comment::getList($where,'*',$limit);
                break;
            case 2:
                $where = [
                    'uid'=>Auth::getUserId(),
                    ['parent_id','<>',0],
                ];
                $list = Comment::getList($where,'*',$limit);
                break;
            case 3:
                $my_comment_list = Comment::getAll($where,'id',100000);
                $comment_ids = array_column($my_comment_list,'id');
                unset($where['uid']);
                $where[] = ['uid','<>',Auth::getUserId()];
                $whereIn = [
                    ['parent_id','in',$comment_ids]
                ];
                $list = Comment::getListIn($where,$whereIn,'*',$limit);
                break;
        }
        //循环处理列表数据
        foreach($list['data'] as $key => $value){
            $list['data'][$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $list['data'][$key]['article_info']['title'] = Article::getField(['id'=>$value['article_id']],'title');
            $list['data'][$key]['article_info']['url'] = $this->getArticleUrl($value['aid'],0,true,true);
            $list['data'][$key]['oppose'] = $this->checkUserCommentOperateStatus($value['id'],2) ? '取消('.$value['oppose'].')' : '反对('.$value['oppose'].')';
            $list['data'][$key]['praiser'] = $this->checkUserCommentOperateStatus($value['id'],1) ? '取消('.$value['praiser'].')' : '支持('.$value['praiser'].')';
            if($type == 3){
                $list['data'][$key]['nickname'] = User::getField(['id'=>$value['uid']],'nickname');
                $list['data'][$key]['main'] = Comment::getOne(['id'=>$value['parent_id']],'*');
                $list['data'][$key]['main']['create_time'] = date('Y-m-d H:i:s',$list['data'][$key]['main']['create_time']);
                $list['data'][$key]['main']['oppose'] = $this->checkUserCommentOperateStatus($list['data'][$key]['main']['id'],2) ? '取消('.$list['data'][$key]['main']['oppose'].')' : '反对('.$list['data'][$key]['main']['oppose'].')';
                $list['data'][$key]['main']['praiser'] = $this->checkUserCommentOperateStatus($list['data'][$key]['main']['id'],1) ? '取消('.$list['data'][$key]['main']['praiser'].')' : '支持('.$list['data'][$key]['main']['praiser'].')';
            }
        }
        Response::success($list,'','获取数据成功');
    }

    /**
     * @param $comment_id
     * @param $type
     * @return bool
     * Description 获取评论的投票状态
     */
    private function checkUserCommentOperateStatus($comment_id,$type){
        //组合查询条件
        $where = [
            'comment_id'=>$comment_id,
            'type'=>$type,
            'uid'=>Auth::getUserId()
        ];
        //查询条件
        $count = CommentOperate::getCount($where,'uid');
        if(!empty($count)){
            return true;
        }else{
            return false;
        }
    }
}