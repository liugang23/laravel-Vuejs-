<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Auth;

class FollowersController extends Controller
{
	protected $user;
	/**
	 * FollowersController constructor
	 * @param $user
	 */
	public function __construct(UserRepository $user)
	{
		$this->user = $user;
	}

    public function index($id)
    {
    	$user = $this->user->getUserId($id);
    	// 查询用户是否存在于 用户-用户关注表中
    	// 从数据表中取得单一数据列的单一字段
    	$followers = $user->followersUser()->pluck('follower_id')->toArray();
    	// 对查询结果进行判断
    	if(in_array(Auth::guard('api')->user()->id, $followers)) {
    		return response()->json(['followed'=>true]);
    	}

    	return response()->json(['followed'=>false]);
    }

    public function follow(Request $request)
    {
    	// return $request->get('user');
    	$userToFollow = $this->user->getUserId(request('user'));
    	$followed = Auth::guard('api')->user()
    					->followThisUser($userToFollow->id);
    	// attached 大于0  关注
    	if (count($followed['attached']) > 0) {
    		// 发送私信

    		$userToFollow->increment('followers_count');

    		return response()->json(['followed' => true]);
    	}

    	$userToFollow->decrement('followers_count');

    	return response()->json(['followed' => false]);

    }
}
