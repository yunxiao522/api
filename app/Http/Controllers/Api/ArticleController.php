<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/26 0026
 * Time: 10:44
 */

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;

class ArticleController extends BaseController
{
    public $request;
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getTitle(){
        dump($this->request);
    }
}