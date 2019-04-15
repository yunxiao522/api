<?php


namespace App\Http\Controllers\Api;


use App\Model\Advert;
use Illuminate\Http\Request;

class AdvertController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Description 获取广告代码
     */
    public function getAdCode()
    {
        $id = $this->request->route('id');
        if (empty($id) || !is_numeric($id)) {
            Response::fail('参数错误');
        }
        $ad_code = Advert::getOne(['id' => $id, 'status' => 1], 'content');
        Response::success($ad_code, '', 'get data success');
    }
}