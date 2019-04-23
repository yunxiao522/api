<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 11:53
 */

namespace App\Http\Controllers\Api;


use App\Libs\sucaiz\Config;
use App\Model\Article;
use App\Model\Comment;
use App\Model\CommentOperate;
use App\Model\User;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends BaseController
{
    private $limit = 10;
    //获取评论列表时,二级评论显示的层数
    protected $reply_limit = 3;
    //评论列表的排序规则
    protected $order_type = [1 => ['id', 'desc'], 2 => ['id', 'asc']];
    //评论举报极值
    protected $inform_num;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->inform_num = Config::get('cfg_comment_inform_num');
    }

    /**
     * Description 获取评论列表数据
     */
    public function getList()
    {
        $id = request('id');
        if (empty($id) || !is_numeric($id)) {
            Response::fail('参数错误');
        }
        $type = request('type');
        if (empty($type) || !isset($this->order_type[$type])) {
            $type = $this->order_type[1];
        }
        $where = [
            'id' => $id,
            'is_delete' => 1,
            'is_audit' => 1,
            'draft' => 2
        ];
        //查询文档评论状态
        $article_info = Article::getOne($where, ['id', 'iscommend']);
        if (empty($article_info)) {
            Response::fail('文档不存在', '', 20001);
        }
        if ($article_info['icsommend'] == 2) {
            Response::success([], '', '文档被设置为禁止评论', 20005);
        }
        $list = Comment::getList(['aid' => $id, 'parent_id' => 0, ['inform', '<', $this->inform_num]], ['uid', 'face', 'content', 'create_time', 'id', 'tier', 'city', 'praiser', 'oppose'], $this->limit, $type);
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['reply'] = Comment::getAll(['ppid' => $value['id']], ['uid', 'face', 'content', 'create_time', 'id', 'tier', 'city', 'praiser', 'oppose'], $this->reply_limit);
            $list['data'][$key]['reply_count'] = 0;
            if (!empty($list['data'][$key]['reply'])) {
                $list['data'][$key]['reply_count'] = Comment::getCount(['ppid' => $value['id']], 'id');
                foreach ($list['data'][$key]['reply'] as $k => $v){
                    $list['data'][$key]['reply'][$k]= $this->dealCommentListInfo($v);
                }
            }
            $list['data'][$key] = $this->dealCommentListInfo($value);
        }
        Response::success($list, '', 'get data success');
    }

    /**
     * Description 获取回复评论列表
     */
    public function getReplyList()
    {
        $id = request('id');
        if (empty($id) || !is_numeric($id)) {
            Response::fail('参数错误');
        }
        $type = request('type');
        if (empty($type) || !isset($this->order_type[$type])) {
            $type = $this->order_type[1];
        }
        $aid = request('aid');
        if (empty($aid) || !is_numeric($aid)) {
            Response::fail('参数错误');
        }
        $where = [
            'id' => $aid,
            'is_delete' => 1,
            'is_audit' => 1,
            'draft' => 2
        ];
        //查询文档评论状态
        $article_info = Article::getOne($where, ['id', 'iscommend']);
        if (empty($article_info)) {
            Response::fail('文档不存在', '', 20001);
        }
        if ($article_info['icsommend'] == 2) {
            Response::success([], '', '文档被设置为禁止评论', 20005);
        }
        $list = Comment::getList(['aid' => $aid, 'ppid' => $id, ['inform', '<', $this->inform_num]], ['*'], $this->limit, $type);
        Response::success($list, '', 'get data success');
    }

    /**
     * @param $list_info
     * @return mixed
     * Description 处理评论列表数据信息
     */
    private function dealCommentListInfo($list_info){
        $list_info['user_info'] = User::getOne(['id' => $list_info['uid']], ['level', 'nickname']);
        $list_info['create_time'] = date('Y-m-d H:i:s', $list_info['create_time']);
        $oppose_status = $this->checkUserCommentOperateStatus($list_info['id'],2);
        $praiser_status = $this->checkUserCommentOperateStatus($list_info['id'],1);
        $list_info['operate_status'] = [
            'oppose'=>$oppose_status,
            'praiser'=>$praiser_status
        ];
        $list_info['oppose'] = $oppose_status ? '取消反对('.$list_info['oppose'].')' : '反对('.$list_info['oppose'].')';
        $list_info['praiser'] = $praiser_status ? '取消支持('.$list_info['praiser'].')' : '支持('.$list_info['praiser'].')';
        return $list_info;
    }

    /**
     * Description 评论支持操作
     */
    public function praiser()
    {
        $validator = Validator::make($this->request->all(), [
            'id' => 'required|numeric'
        ], [
            'id.required' => '非法访问',
            'id.numeric' => '参数错误'
        ]);
        if ($validator->fails()) {
            Response::fail($validator->errors()->first());
        }
        $res = $this->operate(request('id'), 1);
        if(is_string($res)){
            Response::fail($res);
        }
        Response::success($res,'','投票成功');
    }

    /**
     * Description 评论反对操作
     */
    public function oppose()
    {
        $validator = Validator::make($this->request->all(), [
            'id' => 'required|numeric'
        ], [
            'id.required' => '非法访问',
            'id.numeric' => '参数错误'
        ]);
        if ($validator->fails()) {
            Response::fail($validator->errors()->first());
        }
        $res = $this->operate(request('id'), 2);
        if(is_string($res)){
            Response::fail($res);
        }
        Response::success($res,'','投票成功');
    }

    /**
     * @param $comment_id 评论id
     * @param $type 操作类型
     * @return array|string 操作结果
     * Description 评论操作方法
     */
    private function operate($comment_id, $type)
    {

        if ($type == 1) {
            $contra_type = 2;
            $field = 'praiser';
        } else if ($type == 2) {
            $contra_type = 1;
            $field = 'oppose';
        }

        $uid = Auth::getUserId();
        //查询是否有过其他操作
        $where = [
            'comment_id' => $comment_id,
            'type' => $contra_type,
            'uid' => $uid
        ];
        $count = CommentOperate::getField($where, 'uid');
        if (!empty($count)) {
            return'你已经投过票了';
        }
        //判断是否有过相同类型操作
        $where = [
            'comment_id' => $comment_id,
            'type' => $type,
            'uid' => $uid
        ];
        $count = CommentOperate::getField($where, 'uid');
        DB::beginTransaction();
        if (!empty($count)) {
            //删除对应评论操作
            $res = CommentOperate::del($where);
            if (!$res) {
                DB::rollBack();
                return '投票失败';
            }
            //减少对应评论状态的值
            $res = Comment::decr(['id' => $comment_id], $field, 1);
            if (!$res) {
                DB::rollBack();
                return '投票失败';
            }
            $class = 2;
        } else {
            $res = CommentOperate::add([
                'uid' => $uid,
                'comment_id' => $comment_id,
                'type' => $type,
                'create_time' => time()
            ]);
            if (!$res) {
                DB::rollBack();
                return '投票失败';
            }
            //更新评论表字段值
            $res = Comment::incr(['id' => $comment_id], $field, 1);
            if (!$res) {
                DB::rollBack();
                return '投票失败';
            }
            $class = 1;
        }
        $num = Comment::getField(['id' => $comment_id], $field);
        DB::commit();
        return ['num' => $num, 'type' => $class];
    }

    /**
     * Description 发布评论
     */
    public function push()
    {

    }

    /**
     *Description 获取我的评论
     */
    public function getMyComment()
    {
        $type = request('type');
        $limit = request('limit');
        //验证处理数据
        if (empty($type) || !isset(Comment::$comment_type[$type])) {
            Response::success([], '', '数据为空');
        }
        $limit = !empty($limit) && is_numeric($limit) ? $limit : $this->limit;
        //组合查询条件
        $where['status'] = 1;
        $where[] = ['inform', '<', 5];
        $where['uid'] = Auth::getUserId();
        switch ($type) {
            case 1:
                $where = [
                    'uid' => Auth::getUserId(),
                    'parent_id' => 0,
                ];
                $list = Comment::getList($where, '*', $limit);
                break;
            case 2:
                $where = [
                    'uid' => Auth::getUserId(),
                    ['parent_id', '<>', 0],
                ];
                $list = Comment::getList($where, '*', $limit);
                break;
            case 3:
                $my_comment_list = Comment::getAll($where, 'id', 100000);
                $comment_ids = array_column($my_comment_list, 'id');
                unset($where['uid']);
                $where[] = ['uid', '<>', Auth::getUserId()];
                $whereIn = [
                    ['parent_id', 'in', $comment_ids]
                ];
                $list = Comment::getListIn($where, $whereIn, '*', $limit);
                break;
        }
        //循环处理列表数据
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $list['data'][$key]['article_info']['title'] = Article::getField(['id' => $value['aid']], 'title');
            $list['data'][$key]['article_info']['url'] = $this->getArticleUrl($value['aid'], true, true, true);
            $list['data'][$key]['oppose'] = $this->checkUserCommentOperateStatus($value['id'], 2) ? '取消(' . $value['oppose'] . ')' : '反对(' . $value['oppose'] . ')';
            $list['data'][$key]['praiser'] = $this->checkUserCommentOperateStatus($value['id'], 1) ? '取消(' . $value['praiser'] . ')' : '支持(' . $value['praiser'] . ')';
            if ($type == 3) {
                $list['data'][$key]['nickname'] = User::getField(['id' => $value['uid']], 'nickname');
                $list['data'][$key]['main'] = Comment::getOne(['id' => $value['parent_id']], '*');
                $list['data'][$key]['main']['create_time'] = date('Y-m-d H:i:s', $list['data'][$key]['main']['create_time']);
                $list['data'][$key]['main']['oppose'] = $this->checkUserCommentOperateStatus($list['data'][$key]['main']['id'], 2) ? '取消(' . $list['data'][$key]['main']['oppose'] . ')' : '反对(' . $list['data'][$key]['main']['oppose'] . ')';
                $list['data'][$key]['main']['praiser'] = $this->checkUserCommentOperateStatus($list['data'][$key]['main']['id'], 1) ? '取消(' . $list['data'][$key]['main']['praiser'] . ')' : '支持(' . $list['data'][$key]['main']['praiser'] . ')';
            }
        }
        Response::success($list, '', '获取数据成功');
    }

    /**
     * @param $comment_id
     * @param $type
     * @return bool
     * Description 获取评论的投票状态
     */
    private function checkUserCommentOperateStatus($comment_id, $type)
    {
        //组合查询条件
        $where = [
            'comment_id' => $comment_id,
            'type' => $type,
            'uid' => Auth::getUserId()
        ];
        //查询符合条件的操作
        $count = CommentOperate::getCount($where, 'uid');
        if (!empty($count)) {
            return true;
        } else {
            return false;
        }
    }
}