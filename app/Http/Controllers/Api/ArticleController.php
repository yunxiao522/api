<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 10:44
 */

namespace App\Http\Controllers\Api;

use App\Model\Admin;
use App\Model\Article;
use App\Model\ArticleImages;
use App\Model\Base;
use App\Model\Column;
use App\Model\ColumnType;
use App\Model\MyLike;
use App\Model\Tag;
use App\Model\TagList;
use App\Model\User;
use Illuminate\Http\Request;

class ArticleController extends BaseController
{
    public $request;
    private $article_info;
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Description 获取文档标题
     */
    public function getTitle(){
        $id = $this->request->route('id');
        $title = Article::getField(['id'=>$id],'title');
        return Response::success(['title'=>$title]);
    }

    /**
     * @return false|string
     * Description 获取文档列表数据
     */
    public function getList()
    {
        $type = $this->request->type;
        if (empty($type)) {
            $type = 'undefined';
        }
        $where = [];
        $whereIn = [];
        //组合查询条件
        if ($type != "undefined") {
            if ($type == 24) {
                $column_arr = Column::getAll(['parent_id' => $type], 'id',100);
                $column_arr = array_column($column_arr, 'id');
                array_push($column_arr, 24);
                $whereIn = [
                    'column_id', $column_arr
                ];
            } elseif($type == 54){
                $column_arr = Column::getAll(['parent_id' => 54], 'id',100);
                $column_arr = array_column($column_arr, 'id');
                array_push($column_arr, 54);
                $whereIn = [
                    'column_id', $column_arr
                ];
            } else {
                $where = [
                    'column_id' => $type
                ];
            }
        }
        $where['is_delete'] = 1;
        $where['is_audit'] = 1;
        $where['draft'] = 2;
        $list = Article::getListIn($where,$whereIn, ['id','litpic','pubdate','title'], 15 , ['id','desc']);
        //循环列表数据
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['pubdate'] = date('Y-m-d', $value['pubdate']);
        }
        return Response::success($list);
    }

    public function getInfo(){
        $id = $this->request->route('id');
        $p = request('p');
        if(empty($p) || !is_numeric($p) || $p <0){
            $p = 0;
        }
        //获取基本数据
        $data = $this->getBaseInfo($id);
        if(is_string($data)){
            return Response::fail($data);
        }
        //获取文档附加表内数据
        //处理文档附加表的数据
        switch ($this->article_info['channel']){
            case 2:
                $article_extend_info = ArticleImages::getOne(['article_id'=>$id],'*');
                $data['prev_p'] = $p == 0?0:$p-1;
                $data['next_p'] = $p > $article_extend_info['imgnum'] ? $article_extend_info['imgnum']:$p+1;
                //验证分页数据,防止出错
                if($article_extend_info['imgnum'] < ($p+1)){
                    return Response::fail('没有 更多了','',1001);
                }
                $imgurls = explode(',',$article_extend_info['imgurls']);
                $src_rule = "/(href|src)=([\"|']?)([^\"'>]+.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG))/i";
                preg_match($src_rule, $imgurls[$p], $match);
                $data['img'] = $match[3];
                break;
        }
        $data['column'] = Column::getField(['id'=>$this->article_info['column_id']],'type_name');
        $data['description'] = $this->article_info['description'];
        $data['tag'] = $this->getArticleTagInfo();
        $data['hot_tag'] = $this->getArticleHotTag();
        $data['like_status'] = $this->getArticleLikeStatus($id,$p);
        return Response::success($data,'get data success');
    }

    /**
     * @param $article_id
     * @return string
     * Description 获取文档基础数据
     */
    private function getBaseInfo($article_id){
        //组合查询条件
        $where = [
            'id' => $article_id,
            'is_delete' => 1,
            'is_audit' => 1,
            'draft' => 2
        ];
        $this->article_info = Article::getOne($where, '*');
        if (empty($this->article_info)) {
            return '文档不存在';
        }
        //组合需要的数据
        $data['title'] = $this->article_info['title'];
        $data['pubdate'] = date('Y-m-d H:i:s', $this->article_info['pubdate']);
        $data['comment_num'] = $this->article_info['comment_num'];
        //获取作者信息
        if ($this->article_info['user_type'] == 1) {
            $data['user_info'] = User::getOne(['id' => $this->article_info['userid']], ['nickname','face']);
        } else if ($this->article_info['user_type'] == 2) {
            $data['user_info'] = Admin::getOne(['id' => $this->article_info['userid']], [['nick_name as nickname'],'face','real_name']);
        }
        $data['source'] = $this->article_info['source'];
        $data['author'] = $this->article_info['author'];
        $data['random_article'] = $this->getArticleRandom(14);
        //处理上下篇文档数据
        $data['prev'] = $this->getPrevArticleInfo();
        $data['next'] = $this->getNextArticleInfo();
        return $data;
    }

    /**
     * @return array
     * Description 获取文档tag数据
     */
    private function getArticleTagInfo(){
        $where = [
            'article_id'=>$this->article_info['id']
        ];
        $tag_list_ids = TagList::getALL($where,['tag_id']);
        $tag_list = [];
        foreach ($tag_list_ids as $value){
            $tag_list[] = Tag::getOne(['id'=>$value['tag_id']],['id','tag_name']);
        }
        return $tag_list;
    }

    /**
     * @return mixed
     * Desccription 获取文档相关的热门标签
     */
    private function getArticleHotTag(){
        $where = [
            'column_id'=>$this->article_info['column_id']
        ];
        $list = Tag::getAll($where,['id','tag_name'],15,['weekcc','desc']);
        return $list;
    }

    /**
     * @param int $num
     * @return array
     * Description 获取随机文档数据
     */
    private function getArticleRandom($num = 5){
        $where = [
            'column_id'=>$this->article_info['column_id'],
        ];
        $article_list_ids = Article::getALL($where,['id'],100000);
        $article_list_ids = array_column($article_list_ids,'id');
        $article_list = array_rand($article_list_ids,$num);
        foreach ($article_list as $key=>$value){
            $id_list[] = $article_list_ids[$value];
        }
        $list = [];
        foreach ($id_list as $key => $value){
            $where = [
                'id'=>$value,
                'is_delete'=>1,
                'is_audit'=>1,
                'draft'=>2
            ];
            $list[] = Article::getOne($where,['id','title','litpic','column_id']);
        }
        return $list;
    }

    /**
     * @param $article_id
     * @param int $p
     * @return bool
     * Description 查询文档收藏状态
     */
    public function getArticleLikeStatus($article_id,$p = 0){
        $user_id = Auth::getUserId();
        if(!$user_id){
            return false;
        }
        $where = [
            'uid'=>$user_id,
            'article_id'=>$article_id,
            'alone'=>$p,
            'type'=>1
        ];
        $res = MyLike::getField($where,'id');
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @return array|mixed
     * Description 获取上一篇文档数据
     */
    public function getPrevArticleInfo(){
        $where = [
            ['id','<',$this->article_info['id']],
            'column_id'=>$this->article_info['id']
        ];
        $article_info = Article::getOne($where,['id','litpic','title'],['id','asc']);
        return empty($article_info)?['id'=>0]:$article_info;
    }

    /**
     * @return array|mixed
     * Description 获取下一篇文档数据
     */
    public function getNextArticleInfo(){
        $where = [
            ['id','>',$this->article_info['id']],
            'column_id'=>$this->article_info['id']
        ];
        $article_info = Article::getOne($where,['id','litpic','title'],['id','desc']);
        return empty($article_info)?['id'=>0]:$article_info;
    }

    public function checkPower(){

    }
}