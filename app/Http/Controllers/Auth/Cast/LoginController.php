<?php

namespace App\Http\Controllers\Auth\Cast;

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
            'role' => 'cast',
            'title' => 'キャストログイン',
            'bodyClass' => 'page-auth-login page-auth-login-cast',
            'guideMessage' => "キャストログインです。\n入力して進んでください。",
            'eyebrow' => '',
            'heroTitle' => 'キャストログイン',
            'heroDescription' => '',
            'formAction' => route('cast.login.post'),
            'registerUrl' => route('cast.register'),
            'registerLabel' => '新規登録',
            'alternateUrl' => route('shop.login'),
            'alternateLabel' => '店舗',
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if (!auth()->guard('member')->attempt($request->only('email', 'password'))) {
            return back()
                ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。'])
                ->withInput();
        }

        $request->session()->regenerate();
        DB::table('casts')
            ->where('id', auth()->guard('member')->id())
            ->update(['last_login_at' => now(), 'updated_at' => now()]);

        return redirect()
            ->route('cast.home')
            ->with('message', 'キャストとしてログインしました。');
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->guard('member')->logout();
        auth()->guard('shop')->logout();
        auth()->guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login.demo')
            ->with('message', 'ログアウトしました。');
    }
}
