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

class ArticleController
{
    public $request;
    public function __construct()
    {

    }

    public function getTitle(){
        dump(Route::input('id'));
    }
}