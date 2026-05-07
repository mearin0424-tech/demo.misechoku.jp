<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SuspendedController extends Controller
{
    /**
     * 停止中アカウント向けの案内ページ。
     * - キャスト（casts.status = 2）／店舗（shops.status = 2 経由 shop_managers）どちらでも表示可能
     * - 問合せフォームへのリンクを提示
     */
    public function show()
    {
        $userType = null;
        $displayName = '';
        $accountId = null;

        if (auth()->guard('member')->check()) {
            $cast = auth()->guard('member')->user();
            $accountId = (string) ($cast->id ?? '');
            $userType = 'cast';
            $profile = DB::table('cast_profiles')->where('cast_id', $accountId)->first();
            $displayName = $profile->nickname ?? ($profile->name ?? 'キャスト');
        } elseif (auth()->guard('shop')->check()) {
            $manager = auth()->guard('shop')->user();
            $accountId = (string) ($manager->shop_id ?? '');
            $userType = 'shop';
            $profile = DB::table('shop_profiles')->where('shop_id', $accountId)->first();
            $displayName = $profile->shop_name ?? '店舗';
        }

        return view('common.suspended', [
            'userType' => $userType,
            'displayName' => $displayName,
            'accountId' => $accountId,
        ]);
    }
}
