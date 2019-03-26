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
    Route::get('/article/getTitle/{id}','Api\ArticleController@getTitle');
    //获取文档评论列表
    Route::get('/comment/getList/{id}/{page?}/{limit?}','Api\CommentController@getList');
    //获取文档列表
    Route::get('/article/getList/{type}','Api\ArticleController@getList');
});
//获取验证令牌
Route::any('/getToken','Api\Auth@getToken');