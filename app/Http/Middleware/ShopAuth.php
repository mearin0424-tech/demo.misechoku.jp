<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->guard('shop')->check()) {
            return redirect()
                ->route('shop.login')
                ->with('message', 'ログインの有効期限が切れました。もう一度ログインしてください。');
        }

        // 停止中アカウントは案内ページ＋問合せフォーム＋ログアウトのみ許可。
        // ・shop_managers.status = 0 もしくは
        // ・親 shops.status = 2 のとき停止扱い
        $manager = auth()->guard('shop')->user();
        if ($this->isShopSuspended($manager) && !$this->isAllowedDuringSuspension($request)) {
            return redirect()->route('account.suspended');
        }

        return $next($request);
    }

    private function isShopSuspended($manager): bool
    {
        if (!$manager || empty($manager->shop_id)) {
            return false;
        }
        // 親 shops.status が 2（停止）の場合のみ停止扱い
        $shopStatus = (int) (DB::table('shops')->where('id', $manager->shop_id)->value('status') ?? 0);
        return $shopStatus === 2;
    }

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
