<?php

namespace App\Http\Middleware;

use App\Models\ShopManager;
use Closure;
use Illuminate\Http\Request;

/**
 * 店舗ログインのうち「オーナー(role=1)」のみ通す middleware。
 *
 * ShopAuth の後段で使う想定。オーナー限定の操作（店舗プロフィール編集・
 * 求人票編集・許可証提出・Premium 契約・銀行口座・入金確認・スタッフ管理・
 * 案件の入金確認/振込等）に付与する。
 *
 * 権限判定の仕様:
 *   - role が ShopManager::ROLE_OWNER (=1) なら通過
 *   - それ以外（未ログイン含む）は 403
 *
 * 使い方（ルート側）:
 *   Route::middleware(['shop.auth', 'shop.owner'])->group(function () { ... });
 */
class ShopOwner
{
    public function handle(Request $request, Closure $next)
    {
        $manager = auth()->guard('shop')->user();

        if (!$manager || (int) $manager->role !== ShopManager::ROLE_OWNER) {
            abort(403, 'この操作にはオーナー権限が必要です。');
        }

        return $next($request);
    }
}
