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
            'guideMessage' => "店舗用ログインだよ。\nデモでは入力後すぐに店舗ホームへ進めるよ。",
            'eyebrow' => 'SHOP LOGIN',
            'heroTitle' => 'お店の魅力を届けるためのログイン入口。',
            'heroDescription' => '求人管理、ホーム、マイページ体験につながる店舗向けログイン画面です。',
            'formAction' => route('shop.login.post'),
            'registerUrl' => route('shop.register'),
            'registerLabel' => '店舗新規登録はこちら',
            'alternateUrl' => route('cast.login'),
            'alternateLabel' => 'キャストログインはこちら',
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
