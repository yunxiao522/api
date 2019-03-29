<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/29 0029
 * Time: 11:49
 */

namespace App\Http\Controllers\Api;


use App\Model\Search;
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
}