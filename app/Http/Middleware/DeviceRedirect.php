<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DeviceRedirect
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

        // スマホの場合（User-Agentをもとに判断）
        if ($this->isMobile($userAgent)) {
            // スマホ用ログイン後のページへリダイレクト
            return redirect()->route('shop.top');
        }

        // PCの場合はPC用ログイン後のページへリダイレクト
        return redirect()->route('shop.index');
    }

    /**
     * User-Agent でスマホかどうかを判定する
     * @param string $userAgent
     * @return bool
     */
    private function isMobile($userAgent)
    {
        // iPhone, Android等のスマホの判定
        return preg_match('/iPhone|Android.+Mobile|Windows Phone/i', $userAgent);
    }
}
