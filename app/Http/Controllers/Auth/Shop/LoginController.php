<?php

namespace App\Http\Controllers\Auth\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('common.role-login', [
            'role' => 'shop',
            'title' => '店舗ログイン',
            'bodyClass' => 'page-auth-login page-auth-login-shop',
            'guideMessage' => "店舗ログインです。\n入力して進んでください。",
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
        DB::table('shop_managers')
            ->where('id', auth()->guard('shop')->id())
            ->update(['last_login_at' => now(), 'updated_at' => now()]);

        return redirect()
            ->route('shop.home')
            ->with('message', '店舗としてログインしました。');
    }
}
