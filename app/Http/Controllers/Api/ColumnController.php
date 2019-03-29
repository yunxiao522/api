<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 16:56
 */

namespace App\Http\Controllers\Api;


use App\Model\Column;
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
        $list = Column::getAll($where,['id','type_name']);
        return Response::success($list,'获取数据成功');
    }
}