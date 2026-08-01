<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->guard('member')->check()) {
            return redirect()
                ->route('cast.login')
                ->with('message', 'ログインの有効期限が切れました。もう一度ログインしてください。');
        }

        // 停止中アカウントは停止案内ページ＋問合せフォーム＋ログアウトのみ許可
        $cast = auth()->guard('member')->user();
        $status = (int) ($cast->status ?? 0);
        if ($status === 2 && !$this->isAllowedDuringSuspension($request)) {
            return redirect()->route('account.suspended');
        }

        return $next($request);
    }

    /**
     * 停止中でも許可するルート名／パス。
     */
    private function isAllowedDuringSuspension(Request $request): bool
    {
        $name = optional($request->route())->getName();
        if (in_array($name, [
            'account.suspended',
            'pages.support.form',
            'pages.support.column',
            'pages.support.column.show',
            'pages.support.notices',
            'pages.support.notices.show',
            'pages.official.about',
            'pages.official.terms',
            'pages.official.privacy',
            'auth.logout',
        ], true)) {
            return true;
        }
        return false;
    }
}
