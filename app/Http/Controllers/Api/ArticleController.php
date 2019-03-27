<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 10:44
 */

namespace App\Http\Controllers\Api;

use App\Model\Article;
use App\Model\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ArticleController extends BaseController
{
    public $request;
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
}