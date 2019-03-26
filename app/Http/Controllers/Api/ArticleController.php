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

class ArticleController
{
    public $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getTitle(){
        $id = $this->request->route('id');
        $title = Article::getField(['id'=>$id],'title');
        return Response::success(['title'=>$title]);
    }
}