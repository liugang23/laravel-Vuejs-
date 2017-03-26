<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Notifications\NewUserFollowNotification;
use Auth;
use Illuminate\Support\Facades\Notification;

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
    	$userToFollow = $this->user->getUserId(request('user'));

    	$followed = Auth::guard('api')->user()
    					 ->followThisUser($userToFollow->id);

    	// attached 大于0  关注
    	if (count($followed['attached']) > 0) {
    		// 发送私信
            // 通知可以通过两种方式发送：使用Notifiabletrait提供的notify方法或者使用Notification门面
            // 记住，你可以在任何模型中使用Illuminate\Notifications\Notifiabletrait，不限于只在User模型中使用
            // $userToFollow->notify(new NewUserFollowNotification());
            // 使用Notification门面
            Notification::send($userToFollow, new NewUserFollowNotification());
    		$userToFollow->increment('followers_count');

    		return response()->json(['followed' => true]);
    	}
        // 修改私信状态
        \DB::table('notifications')->update(['state'=>'F']);
    	$userToFollow->decrement('followers_count');

    	return response()->json(['followed' => false]);

    }
}
