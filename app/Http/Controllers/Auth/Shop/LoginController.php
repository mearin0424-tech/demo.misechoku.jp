<?php

namespace App\Http\Controllers\Auth\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('common.role-login', [
            'role' => 'shop',
            'title' => '店舗ログイン',
            'bodyClass' => 'page-auth-login page-auth-login-shop',
            'eyebrow' => '',
            'heroTitle' => '店舗ログイン',
            'heroDescription' => '',
            'formAction' => route('shop.login.post'),
            'registerUrl' => route('shop.register'),
            'registerLabel' => '新規登録',
            'alternateUrl' => route('cast.login'),
            'alternateLabel' => 'キャスト',
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if (!auth()->guard('shop')->attempt($request->only('email', 'password'))) {
            return back()
                ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。'])
                ->withInput();
        }

        $request->session()->regenerate();
        // last_login_at is updated centrally by App\Listeners\UpdateLastLoginAt
        // via the Illuminate\Auth\Events\Login event fired by attempt().

        return redirect()
            ->route('shop.home')
            ->with('message', '店舗としてログインしました。');
    }
}
