<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckRedirectAfterLogin
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

        Log::info('CheckRedirectAfterLogin: ');

        if (Auth::guard('member')->check() && $request->session()->has('redirect_after_login')) {
            $url = $request->session()->pull('redirect_after_login', '/');
            Log::info('Already logged in, redirecting to: ' . $url);
            return redirect($url);
        }

        return $next($request);
    }
}
