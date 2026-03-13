<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use App\Models\Member;
use App\Models\User;
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

        auth()->guard('member')->logout();
        auth()->guard('shop')->logout();
        auth()->guard()->logout();

        switch ($request->input('role')) {
            case 'admin':
                $admin = User::query()->orderBy('id')->first();
                if ($admin) {
                    auth()->guard()->login($admin);
                    $request->session()->regenerate();
                }
                return redirect()->route('admin.dashboard');
            case 'cast':
                $member = Member::query()->where('status', 1)->orderBy('id')->first();
                if ($member) {
                    auth()->guard('member')->login($member);
                    $request->session()->regenerate();
                }
                return redirect()->route('cast.home');
            case 'shop':
                $manager = Manager::query()->where('status', 1)->orderBy('id')->first();
                if ($manager) {
                    auth()->guard('shop')->login($manager);
                    $request->session()->regenerate();
                }
                return redirect()->route('shop.home');
        }

        return redirect()->route('login.demo');
    }
}

