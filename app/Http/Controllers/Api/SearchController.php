<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/29 0029
 * Time: 11:49
 */

namespace App\Http\Controllers\Api;


use App\Model\Article;
use App\Model\Base;
use App\Model\Column;
use App\Model\ColumnType;
use App\Model\Search;
use App\Model\SearchColumn;
use App\Model\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Description 获取热门搜索
     */
    public function getHotSearch(){
        $list = Search::getList([],'keyword',10,['num','desc']);
        Response::success($list);
    }

    /**
     *Description 获取搜索记录
     */
    public function getHistorySearch(){
        $uid = Auth::getUserId();
        $history_list = SearchHistory::getList(['uid'=>$uid],'keyword',10);
        Response::success($history_list);
    }

    /**
     * Description 清空搜索历史记录
     */
    public function delHistorySearch(){
        $uid = Auth::getUserId();
        $res = SearchHistory::del(['uid'=>$uid]);
        if($res){
            Response::success([],'','清空成功');
        }else{
            Response::fail('清空失败');
        }
    }

    public function search(){
        $keyword = request('keyword');
        $type = request('type');
        $uid = Auth::getUserId();
        if(!$uid){
            $uid = 0;
        }
        //添加搜索记录
        $res = SearchHistory::addHistory($keyword,$uid);
        if(!$res){
            Response::fail('获取数据失败');
        }
        $column_id = SearchColumn::getField(['id'=>$type],'cid');
        if(empty($column_id)){
            $column_id = 54;
        }
        $column_list = Column::getAll([],['id','parent_id']);
        $column_son_list = self::getSonList($column_id,$column_list);
        $where = [
            ['title','like','%'.$keyword.'%']
        ];
        $whereIn = [
            'column_id',$column_son_list
        ];
        $list = Article::getListIn($where,$whereIn,[
            'id',
            'title',
            'litpic',
            'pubdate',
            'channel',
            'column_id'
        ]);
        //循环查询扩展表信息
        foreach($list['data'] as $key => $value){
            $table = ColumnType::getField(['id'=>$value['channel']],'table_name');
            $list['data'][$key]['type'] = $type;
            $list['data'][$key]['extend'] = DB::table($table)->where(['article_id'=>$value['id']])->first();
            $list['data'][$key]['pubdate'] = date('Y-m-d',$value['pubdate']);
            $list['data'][$key]['column'] = Column::getField(['id'=>$value['column_id']],'type_name');
            $list['data'][$key]['title'] = self::cut_str($value['title'],14);
        }
        Response::success($list);
    }
}