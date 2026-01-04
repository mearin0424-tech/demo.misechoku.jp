<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogUserAgent
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

        $userAgent = $request->header('User-Agent');
        Log::info('User Agent: ' . $userAgent);
        
        // LINEアプリ内ブラウザの場合の特別な処理
        if (strpos($userAgent, 'Line/') !== false) {
            Log::info('Accessed by LINE in-app browser');
        }

        return $next($request);
    }
}
