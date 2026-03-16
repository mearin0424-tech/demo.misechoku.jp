<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ShopAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if (auth()->guard('shop')->check()) {
            return $next($request);
        }

        return redirect()->route('login.demo')->with('message', 'ログインの有効期限が切れました。もう一度ログインしてください。');
        //return $next($request);
    }
}
