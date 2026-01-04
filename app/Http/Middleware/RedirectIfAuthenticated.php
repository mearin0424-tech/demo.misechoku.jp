<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                if ($guard === 'shop') {
                    return redirect('/shop/index');
                }

                if ($guard === 'member') {

                    Log::INFO('■デバッグログ＞＞RedirectIfAuthenticated＞＞handle＞＞'.$request->session()->has('redirect_after_login'));

                    if ($request->session()->exists('redirect_after_login')) {

                        Log::INFO('■デバッグログ＞＞RedirectIfAuthenticated＞＞handle＞＞redirect_after_login');
                        $url = $request->session()->get('redirect_after_login', '/');
                        Log::info('Already logged in, redirecting to: ' . $url);

                       // セッションにリダイレクト先が設定されていればそこへリダイレクト
                       return redirect($url);
                    }

                     return redirect('/member');  // デフォルトのリダイレクト先
                    //return redirect('/member');
                }
                //return redirect(RouteServiceProvider::HOME);
                return redirect('/admin');

            }
        }

        return $next($request);
    }
}
