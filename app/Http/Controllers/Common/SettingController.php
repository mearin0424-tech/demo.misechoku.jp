<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * 通知設定（キャスト／店舗共通・デモ用）
     */
    public function notification()
    {
        $isCast = request()->is('cast/*');
        return view('common.setting.notification', compact('isCast'));
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

