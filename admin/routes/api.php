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
	$followed = \App\Models\Follow::where('question_id', $request->get('question'))
		->where('user_id', $request->get('user'))
		->count();

	if($followed) {
		return response()->json(['followed' => true]);
	}
	return response()->json(['followed' => false]);
	
})->middleware('api');

// 关注 点击响应
Route::post('/question/follow', function (Request $request) {
	$followed = \App\Models\Follow::where('question_id', $request->get('question'))
		->where('user_id', $request->get('user'))
		->first();

	// 对查询结果进行判断  非空 删除记录 改变状态
	if($followed !== null) {
		$followed->delete();
		return response()->json(['followed' => false]);
	}
	// 否则 创建记录
	\App\Models\Follow::create([
		'question_id' => $request->get('question'),
		'user_id' => $request->get('user'),
	]);
	// 返回状态
	return response()->json(['followed' => true]);

})->middleware('api');