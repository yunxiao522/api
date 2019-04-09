<?php


namespace App\Http\Controllers\Api;


use App\Model\MobileTag;
use Illuminate\Http\Request;

class MobileController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Description 获取栏目tag列表数据
     */
    public function getMobileTag(){
        $column_id = request('column_id');
        if(empty($column_id) || !is_numeric($column_id)){
            Response::fail('参数错误');
        }
        $list = MobileTag::getALL(['column_id'=>$column_id,'status'=>1],['id','litpic','tag_id','name']);
        Response::success($list,'','get data success');
    }
}