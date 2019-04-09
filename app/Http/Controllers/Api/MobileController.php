<?php


namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;

class MobileController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }


    public function getMobileTag(){
        $column_id = request('column_id');
    }
}