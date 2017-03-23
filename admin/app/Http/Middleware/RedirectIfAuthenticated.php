<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * 处理传入的请求
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return redirect('/');
            // 获取上一次访问的地址 
            // $return_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

            // urlencode 将字符串以 URL 编码,之所以要进行编码，是因为Url中有些字符会引起歧义。为避免意外发生，这里进行编码
            // return redirect('/login?return_url='.urlencode($return_url));
        }

        return $next($request);
    }
}
