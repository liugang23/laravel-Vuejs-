<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    // 测试数据库
    public function index()
    {
        $test = \DB::table('test')->get();
        return $test;
    }

}
