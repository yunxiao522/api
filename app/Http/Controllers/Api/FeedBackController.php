<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/27 0027
 * Time: 14:38
 */

namespace App\Http\Controllers\Api;


use App\Model\FeedBack;
use Illuminate\Http\Request;

class FeedBackController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * @return false|string
     * Description 发表意见反馈方法
     */
    public function push(){
        $data = $this->chechForm();
        if(is_string($data)){
            return Response::fail($data);
        }
        //添加数据到数据库
        $res = FeedBack::add($data);
        if($res){
            return Response::success('反馈成功');
        }else{
            return Response::fail('反馈失败');
        }
    }

    /**
     * @return string
     * Description 验证意见反馈表单方法
     */
    private function chechForm(){
        $data['title'] = request('title');
        if (empty($data['title'])){
            return '反馈的标题不能为空';
        }
        $data['content'] = request('content');
        if(empty($data['content'])){
            return '反馈的内容不能空';
        }
        $user_id = Auth::getUserId();
        $data['create_time'] = time();
        $data['uid'] = !$user_id?0:$user_id;
        $data['status'] = 1;
        return $data;
    }
}