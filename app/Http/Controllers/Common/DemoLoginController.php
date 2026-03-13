<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DemoLoginController extends Controller
{
    /**
     * デモ用ログイン画面表示
     *
     * 管理者／キャスト／店舗（マネージャ）を選択して各トップへ遷移するための入口。
     */
    public function show()
    {
        return view('common.demo-login');
    }

    /**
     * デモ用ログイン処理
     *
     * 実際のメールアドレス・パスワード認証は行わず、選択されたロールに応じて
     * 各トップページへリダイレクトする。
     */
    public function login(Request $request)
    {
        $request->validate([
            'role' => ['required', 'in:admin,cast,shop'],
        ]);

        switch ($request->input('role')) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'cast':
                return redirect()->route('cast.home');
            case 'shop':
                return redirect()->route('shop.home');
        }

        return redirect()->route('login.demo');
    }
}

