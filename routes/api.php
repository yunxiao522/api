<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('/v1')->group(function(){
    Route::middleware("apiauth::checkAuth")->namespace('base',function (){
        return 1;
    });
    //访问记录
    Route::post('/visit','Api\Visit@visit');
    //获取文档标题
    Route::get('/getArticleTitle/{id}',function ($id){
        $article = new \App\Http\Controllers\Api\ArticleController();
        return $article->getTitle($id);
    });
});
//获取验证令牌
Route::any('/getToken','Api\Auth@getToken');