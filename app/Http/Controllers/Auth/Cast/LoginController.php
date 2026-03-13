<?php

namespace App\Http\Controllers\Auth\Cast;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('common.role-login', [
            'role' => 'cast',
            'title' => 'キャストログイン',
            'bodyClass' => 'page-auth-login page-auth-login-cast',
            'guideMessage' => "キャスト用ログインだよ。\nデモでは入力後すぐに画面体験へ進めるよ。",
            'eyebrow' => 'CAST LOGIN',
            'heroTitle' => '理想のお店とつながるログイン入口。',
            'heroDescription' => '応募、検索、マイページ体験につながるキャスト向けログイン画面です。',
            'formAction' => route('cast.login.post'),
            'registerUrl' => route('cast.register'),
            'registerLabel' => 'キャスト新規登録はこちら',
            'alternateUrl' => route('shop.login'),
            'alternateLabel' => '店舗ログインはこちら',
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        return redirect()
            ->route('cast.home')
            ->with('message', 'デモ用ログインとしてキャスト画面へ移動しました。');
    }

    public function logout(): RedirectResponse
    {
        return redirect()
            ->route('login.demo')
            ->with('message', 'ログアウトしました。');
    }
}
