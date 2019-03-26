<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 11:53
 */

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;

class CommentController extends BaseController
{
    private $limit = 20;
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getList(){
        $id = $this->request->route('id');
        $page = $this->request->route('page')?$this->request->route('page'):1;
        $limit = $this->request->route('limit')?$this->request->route('limit'):$this->limit;
        dump($id,$page,$limit);

    }
}