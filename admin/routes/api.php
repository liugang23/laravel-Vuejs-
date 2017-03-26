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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/topics', function (Request $request) {
	$topics = \App\Models\topic::select(['id', 'name'])
			->where('name', 'like', '%'.$request->query('q').'%')
			->get();
    return $topics;
})->middleware('api');


// 关注组件 勾子调用
Route::post('/question/follower', function (Request $request) {
	$user = Auth::guard('api')->user();
	$followed = $user->followed($request->get('question'));

	if($followed) {
		return response()->json(['followed' => true]);
	}
	return response()->json(['followed' => false]);
	
})->middleware('auth:api');

// 关注 点击响应
Route::post('/question/follow', function (Request $request) {
	$user = Auth::guard('api')->user();
	$question = \App\Models\Question::find($request->get('question'));
	$followed = $user->followThis($question->id);

	// $followed 返回两数组结果集 detached attached
	// 对查询结果进行判断  非空 删除记录 改变状态
	if(count($followed['detached']) > 0) {
        $question->decrement('followers_count');
		return response()->json(['followed' => false]);
	}

	// 否则 创建记录
	$question->increment('followers_count');
	// 返回状态
	return response()->json(['followed' => true]);

})->middleware('auth:api');

// 用户关注用户 初始化
Route::get('/user/followers/{id}', 'Api\FollowersController@index');
// 用户 关注\取消关注  用户 操作
Route::post('/user/follow', 'Api\FollowersController@follow');



