<?php
namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class FollowController extends Controller
{
	public function __construct()
    {
        // 登录限制
        $this->middleware('auth');
    }

    public function follow($question)
    {
        Auth::user()->follows($question);

        return back();
    }
}