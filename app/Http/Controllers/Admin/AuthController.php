<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * 管理者ログイン画面
     *
     * まずはダミーフォームのみ用意し、実際の認証実装は別タスクで行う前提とする。
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * ダミーログイン処理
     *
     * 現時点ではバリデーションのみ行い、常にダッシュボードへ遷移させる。
     * 実際の認証（Guard 連携）は今後の実装とする。
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // TODO: 実際の管理者認証ロジックをここに実装する

        return redirect()->route('bk.dashboard')
            ->with('status', 'ダミーログインとしてダッシュボードへ遷移しました（実際の認証は未実装）。');
    }

    /**
     * ダミーログアウト
     */
    public function logout()
    {
        // TODO: 実際のログアウト処理を追加する
        return redirect()->route('bk.login')->with('status', 'ログアウトしました（ダミー処理）。');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * 管理者ログイン画面
     *
     * まずはダミーフォームのみ用意し、実際の認証実装は別タスクで行う前提とする。
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * ダミーログイン処理
     *
     * 現時点ではバリデーションのみ行い、常にダッシュボードへ遷移させる。
     * 実際の認証（Guard 連携）は今後の実装とする。
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // TODO: 実際の管理者認証ロジックをここに実装する

        return redirect()->route('bk.dashboard')
            ->with('status', 'ダミーログインとしてダッシュボードへ遷移しました（実際の認証は未実装）。');
    }

    /**
     * ダミーログアウト
     */
    public function logout()
    {
        // TODO: 実際のログアウト処理を追加する
        return redirect()->route('bk.login')->with('status', 'ログアウトしました（ダミー処理）。');
    }
}

