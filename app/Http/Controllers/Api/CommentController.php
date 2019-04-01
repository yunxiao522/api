<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 11:53
 */

namespace App\Http\Controllers\Api;


use App\Model\Comment;
use App\Model\CommentOperate;
use Dotenv\Validator;
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
            if($res){
                DB::rollBack();
                Response::fail('投票失败');
            }
            $class = 1;
        }
        $num = Comment::getField(['id'=>$comment_id],$field);
        DB::commit();
        Response::success(['num'=>$num,'type'=>$class],'','投票成功');
    }
}