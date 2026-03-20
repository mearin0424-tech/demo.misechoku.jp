<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\CastProvider;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * 通知設定（キャスト／店舗共通・デモ用）
     */
    public function notification()
    {
        $isCast = request()->is('cast/*');
        $lineLinked = false;
        $lineLinkUrl = route('setting.line.link');

        if (auth()->guard('member')->check()) {
            $user = auth()->guard('member')->user();
            $lineLinked = CastProvider::query()
                ->where('cast_id', $user->getAuthIdentifier())
                ->where('provider', 'line')
                ->exists();
        } elseif (auth()->guard('shop')->check()) {
            $user = auth()->guard('shop')->user();
            $lineLinked = !empty($user->line_user_id);
        }

        return view('common.setting.notification', [
            'isCast' => $isCast,
            'lineLinked' => $lineLinked,
            'lineLinkUrl' => $lineLinkUrl,
            'isLoggedIn' => auth()->guard('member')->check() || auth()->guard('shop')->check(),
        ]);
    }

    /**
     * メールアドレス変更画面（デモ用）
     */
    public function accountEmail()
    {
        $isCast = request()->is('cast/*');
        return view('common.setting.account-email', compact('isCast'));
    }

    /**
     * パスワード変更画面（デモ用）
     */
    public function accountPassword()
    {
        $isCast = request()->is('cast/*');
        return view('common.setting.account-password', compact('isCast'));
    }

    /**
     * 退会手続き画面（デモ用）
     */
    public function accountWithdraw()
    {
        $isCast = request()->is('cast/*');
        return view('common.setting.account-withdraw', compact('isCast'));
    }

    /**
     * プラン設定（店舗専用・デモ用）
     */
    public function subscription()
    {
        // サイドバーからは店舗のみリンクされる想定
        return view('common.setting.subscription');
    }
}

