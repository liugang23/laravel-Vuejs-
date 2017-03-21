<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;

class EmailController extends Controller
{
	/**
     * 根据 token进行校验
	 */
    public function verify($token)
    {
    	$user = User::where('confirmation_token', $token)->first();
    	// 判断token 是否存在
    	if (is_null($user)) {
    		// 如果为空
    		flash('邮箱验证失败！', 'danger');
    		return redirect('/');
    	}

    	// 否则 更新激活状态为 1
    	$user->is_active = 1;
    	// 重置token
    	$user->confirmation_token = str_random(50);
    	$user->save();

    	// 执行登录
    	Auth::login($user);	
    	flash('邮箱验证成功！', 'success');
    	// 激活成功  跳转首页
    	return redirect('/home');
    }
}
