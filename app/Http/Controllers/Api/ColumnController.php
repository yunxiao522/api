<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 16:56
 */

namespace App\Http\Controllers\Api;


use App\Model\Column;
use App\Model\Tag;
use Illuminate\Http\Request;

class ColumnController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Description 获取下级栏目列表
     */
    public function getSonLists(){
        $id = $this->request->route('id');
        if(empty($id) || !is_numeric($id)){
            return Response::fail('参数错误');
        }
        //组合条件查询列表数据
        $where = [
            'parent_id'=>$id
        ];
        $list = Column::getAll($where,['id','type_name','cover_img']);
        return Response::success($list,'','get data success');
    }

    /**
     * Description 获取栏目 的tag标签列表
     */
    public function getColumnTagList(){
        $column =request('column_id');
        if(empty($column) || !is_numeric($column)){
            Response::fail('参数错误');
        }
        $list = Tag::getALL(['column_id'=>$column],['id as tag_id','tag_name as name'],1000,['id','desc']);
        Response::success($list,'','get data success');
    }
}