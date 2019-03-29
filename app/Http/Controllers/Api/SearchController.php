<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/29 0029
 * Time: 11:49
 */

namespace App\Http\Controllers\Api;


use App\Model\Search;
use App\Model\SearchHistory;
use Illuminate\Http\Request;

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
            Response::success('清空成功');
        }else{
            Response::fail('清空失败');
        }
    }
}