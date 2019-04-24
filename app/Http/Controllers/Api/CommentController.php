<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 11:53
 */

namespace App\Http\Controllers\Api;


use App\Libs\sucaiz\Config;
use App\Libs\sucaiz\Ip;
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
    //评论楼层存储在redis中的key格式
    protected $comment_tier_key = 'comment_tier_article_pid_key';
    //评论楼层储存在redis中的有效时间
    protected $comment_tier_key_ttl = 86400;

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
            $type = 1;
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
        $list = Comment::getList(['aid' => $id, 'parent_id' => 0, ['inform', '<', $this->inform_num]], ['uid', 'face', 'content', 'create_time', 'id', 'tier', 'city', 'praiser', 'oppose'], $this->limit, $this->order_type[$type]);
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key] = $this->dealCommentListInfo($value);
            //获取二级评论
            $list['data'][$key]['reply'] = Comment::getAll(['ppid' => $value['id']], ['uid', 'face', 'content', 'create_time', 'id', 'tier', 'city', 'praiser', 'oppose'], $this->reply_limit);
            $list['data'][$key]['reply_count'] = 0;
            if (!empty($list['data'][$key]['reply'])) {
                $list['data'][$key]['reply_count'] = Comment::getCount(['ppid' => $value['id']], 'id');
                foreach ($list['data'][$key]['reply'] as $k => $v) {
                    $list['data'][$key]['reply'][$k] = $this->dealCommentListInfo($v);
                }
            }
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
    private function dealCommentListInfo($list_info)
    {
        $list_info['user_info'] = User::getOne(['id' => $list_info['uid']], ['level', 'nickname']);
        $list_info['create_time'] = date('Y-m-d H:i:s', $list_info['create_time']);
        $oppose_status = $this->checkUserCommentOperateStatus($list_info['id'], 2);
        $praiser_status = $this->checkUserCommentOperateStatus($list_info['id'], 1);
        $list_info['operate_status'] = [
            'oppose' => $oppose_status,
            'praiser' => $praiser_status
        ];
        $list_info['oppose'] = $oppose_status ? '取消反对(' . $list_info['oppose'] . ')' : '反对(' . $list_info['oppose'] . ')';
        $list_info['praiser'] = $praiser_status ? '取消支持(' . $list_info['praiser'] . ')' : '支持(' . $list_info['praiser'] . ')';
        $list_info['face'] = strpos($list_info['face'], 'http') === false ? config::get('cfg_hostsite') . $list_info['face'] : $list_info['face'];
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
        if (is_string($res)) {
            Response::fail($res);
        }
        Response::success($res, '', '投票成功');
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
        if (is_string($res)) {
            Response::fail($res);
        }
        Response::success($res, '', '投票成功');
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
            return '你已经投过票了';
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
        $uid = Auth::getUserId();
        $aid = request('aid');
        if (empty($aid) || !is_numeric($aid)) {
            Response::fail('参数错误');
        }
        $content = request('content');
        if (empty($content)) {
            Response::fail('评论内容不能为空');
        }
        if(mb_strlen($content,'UTF-8')>500){
            Response::fail('评论的字数太多了');
        }
        $pid = request('pid');
        if (!empty($pid) && !is_numeric($pid)) {
            Response::fail('参数错误');
        }
        $pid = empty($pid) ? 0 : $pid;
        $ppid = 0;
        if (!empty($pid)) {
            $ppid = Comment::getField(['id' => $pid], 'ppid');
            if ($ppid === '') {
                Response::fail('回复的评论不存在');
            }else if($ppid === 0){
                $ppid = $pid;
            }
        }
        //处理设备型号信息
        $device = request('device');
        $device = $this->dealCommentDeviceInfo($device);
        //检查评论内容
        $content = $this->checkContent($content);
        if($content === false){
            Response::fail('发表失败');
        }
        //获取判断文档信息
        $article_info = Article::getOne(['id' => $aid, 'is_delete' => 1, 'is_audit' => 1, 'draft' => 2], ['id', 'iscommend']);
        if (empty($article_info)) {
            Response::fail('文档不存在');
        }
        if ($article_info['iscomment'] == 2) {
            Response::fail('文档不允许发表评论');
        }
        //获取判断用户账户发言状态
        $user_info = User::getOne(['id' => $uid], ['face', 'comment_status']);
        if ($user_info['comment_status'] == 2) {
            Response::fail('账号被禁言');
        }
        DB::beginTransaction();
        //添加评论表数据
        $comment_id = Comment::add([
            'aid'=>$aid,
            'face'=>$user_info['face'],
            'uid'=>$uid,
            'content'=>$content,
            'tier'=>0,
            'ppid'=>$ppid,
            'parent_id'=>$pid,
            'praiser'=>0,
            'oppose'=>0,
            'inform'=>0,
            'status'=>1,
            'create_time'=>time(),
            'comment_ip'=>$this->request->ip(),
            'device'=>$device,
            'city'=>''
        ]);
        if(!$comment_id){
            Response::fail('发表失败');
        }
        //更新评论列表内容
        $res = Comment::edit(['id'=>$comment_id],[
            'city' => Ip::getIpCity($this->request->ip()),
        ]);
        if($res === false){
            DB::rollBack();
            Response::fail('发表失败');
        }
        //修改文档评论数量
        Article::incr(['id'=>$aid],'comment_num');
        //更新评论楼层数
        Comment::edit(['id'=>$comment_id],['tier'=>$this->computeCommentTier($aid,$ppid)]);
        DB::commit();
        Response::success([],'','发表成功');
    }

    /**
     * @param $aid
     * @param $pid
     * @return mixed|string
     * Description 发表评论时对评论楼层的处理
     */
    private function computeCommentTier($aid, $pid)
    {
        $tier_key = str_replace(['article', 'pid'], [$aid, $pid], $this->comment_tier_key);
        $tier = Redis::get($tier_key);
        if (empty($tier)) {
            $tier = Comment::getTier(['aid' => $aid, 'ppid' => $pid]);
            if (empty($tier)) {
                $tier = 0;
            }
            Redis::set($tier_key, $tier + 1, $this->comment_tier_key_ttl);
            $tier ++;
        } else {
            $tier++;
            Redis::inc($tier_key,1,$this->comment_tier_key_ttl);
        }
        return $tier;
    }

    /**
     * @param $aid
     * @param $pid
     * Description 更新缓存中的评论楼层数
     */
    private function updateCacheTier($aid, $pid)
    {
        $tier_key = str_replace(['article', 'pid'], [$aid, $pid], $this->comment_tier_key);
        Redis::inc($tier_key, 1, $this->comment_tier_key_ttl);
    }

    /**
     * @param $device
     * @return mixed
     * Description 处理评论设备信息
     */
    private function dealCommentDeviceInfo($device)
    {
        return $device;
    }

    /**
     * @param $content
     * @return bool
     * Description 检查评论内容中是否存在违规关键字
     */
    private function checkContent($content){
        Redis::$db = 1;
        $comment_key = Redis::get('comment_key');
        $comment_key_arr = json_decode($comment_key,true);
        foreach ($comment_key_arr as $value){
            if(strpos($content,$value) !== false){
                return false;
            }
        }
        return $content;
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