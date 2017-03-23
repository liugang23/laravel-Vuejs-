<?php
namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class FollowController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');// 用户登录限制
	}


    public function follow($question)
    {
    	Auth::user()->followThis($question);

    	return back();
    }
}
