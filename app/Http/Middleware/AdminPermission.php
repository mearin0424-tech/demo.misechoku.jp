<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * 管理画面の特定機能へのアクセス権限をチェックするミドルウェア。
 *
 * 使い方（routes/web.php）:
 *   Route::middleware(['admin.auth', 'admin.permission:operations.invoices'])->group(function () { ... });
 *
 * - 未ログインの場合は `admin.auth` 側でリダイレクト済み想定
 * - admin ロールはすべての権限を保有（SystemAccount::isAdmin()）
 * - 権限なしの場合は 403
 */
class AdminPermission
{
    public function handle(Request $request, Closure $next, string $permissionKey)
    {
        $user = auth()->guard('admin')->user();
        if (!$user) {
            return redirect()->route('admin.login')->with('message', 'ログインが必要です。');
        }

        if (method_exists($user, 'hasPermission') && $user->hasPermission($permissionKey)) {
            return $next($request);
        }

        // admin ロールは常に許可
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        abort(403, 'この機能への権限がありません。');
    }
}
