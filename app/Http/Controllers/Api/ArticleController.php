<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 10:44
 */

namespace App\Http\Controllers\Api;

use App\Model\Article;
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
}