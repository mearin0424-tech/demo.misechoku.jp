<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * パスワードリセット共通コントローラ（キャスト・店舗共通）。
 *
 * 標準の Laravel Auth スカフォールドに準拠しつつ、
 * `casts.email` と `shop_managers.email` の両テーブルを対象にする。
 *
 * トークンは `password_reset_tokens` テーブル（PK: email）に平文で保存せず
 * ハッシュ化して格納。有効期限は 60 分（config/auth.php の passwords.users.expire）。
 *
 * フロー:
 *   1. GET  /password/forgot           → メール入力フォーム
 *   2. POST /password/forgot           → トークン生成 + メール送信（デモ環境はログ出力）
 *   3. GET  /password/reset?token=&email= → 新パスワード入力フォーム
 *   4. POST /password/reset            → トークン照合 → パスワード更新 → ログインへ
 */
class PasswordResetController extends Controller
{
    /** トークン有効期限（分） */
    private const TOKEN_EXPIRE_MINUTES = 60;

    /** メール入力フォーム表示 */
    public function showForgotForm(): View
    {
        return view('common.password.forgot', [
            'title'       => 'パスワードの再設定',
            'bodyClass'   => 'page-auth-login page-auth-forgot',
        ]);
    }

    /** メール送信処理：token を発行し reset URL をメール送付 */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = (string) $data['email'];

        // どのテーブルに存在するか判定（存在しない場合も同一メッセージを返して列挙を防ぐ）
        $guard = $this->resolveGuardByEmail($email);

        if ($guard !== null) {
            $rawToken = Str::random(64);
            $now = now();

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token'      => Hash::make($rawToken),
                    'created_at' => $now,
                ]
            );

            $resetUrl = route('password.reset.show', [
                'token' => $rawToken,
                'email' => $email,
            ]);

            $this->sendMail($email, $resetUrl);
        }

        // セキュリティ配慮：登録有無に関わらず同じメッセージを返す
        return redirect()->route('password.forgot.show')
            ->with('message', 'パスワード再設定用のURLをメールでお送りしました。60分以内にリンクを開いて新しいパスワードを設定してください。');
    }

    /** 新パスワード入力フォーム表示 */
    public function showResetForm(Request $request): View
    {
        return view('common.password.reset', [
            'title'     => '新しいパスワードの設定',
            'bodyClass' => 'page-auth-login page-auth-reset',
            'token'     => (string) $request->query('token', ''),
            'email'     => (string) $request->query('email', ''),
        ]);
    }

    /** パスワード更新処理 */
    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'email', 'max:255'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $row = DB::table('password_reset_tokens')->where('email', $data['email'])->first();
        if (!$row) {
            return back()->withErrors(['token' => 'トークンが無効か、既に使用されています。再度パスワードリセットを申請してください。'])->withInput($request->only('email'));
        }

        // 有効期限チェック
        $createdAt = $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null;
        if (!$createdAt || $createdAt->diffInMinutes(now()) > self::TOKEN_EXPIRE_MINUTES) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            return back()->withErrors(['token' => 'トークンの有効期限（60分）が切れています。もう一度パスワードリセットを申請してください。']);
        }

        // トークン照合（ハッシュ）
        if (!Hash::check($data['token'], $row->token)) {
            return back()->withErrors(['token' => 'トークンが正しくありません。'])->withInput($request->only('email'));
        }

        $guard = $this->resolveGuardByEmail($data['email']);
        if ($guard === null) {
            return back()->withErrors(['email' => 'このメールアドレスは登録されていません。']);
        }

        // パスワード更新
        [$table, $emailCol] = $guard === 'cast'
            ? ['casts', 'email']
            : ['shop_managers', 'email'];

        DB::table($table)
            ->where($emailCol, $data['email'])
            ->update([
                'password'   => Hash::make($data['password']),
                'updated_at' => now(),
            ]);

        // 使い捨て：トークン削除
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        $loginRoute = $guard === 'cast' ? 'cast.login' : 'shop.login';

        return redirect()->route($loginRoute)
            ->with('message', 'パスワードを更新しました。新しいパスワードでログインしてください。');
    }

    /**
     * email から所属を判定。casts が優先。
     * どちらにも無ければ null。
     */
    private function resolveGuardByEmail(string $email): ?string
    {
        if (DB::table('casts')->where('email', $email)->exists()) {
            return 'cast';
        }
        if (DB::table('shop_managers')->where('email', $email)->exists()) {
            return 'shop';
        }
        return null;
    }

    /**
     * リセット URL をメール送付。
     * デモ／開発環境で MAIL 設定が無い場合も画面遷移は止めず、代わりに log に URL を残す。
     */
    private function sendMail(string $email, string $resetUrl): void
    {
        $body = "ミセチョク パスワードの再設定リクエストを受け付けました。\n\n"
            . "下記の URL から新しいパスワードを設定してください（有効期限: 60分）。\n\n"
            . $resetUrl . "\n\n"
            . "このリクエストに心当たりが無い場合は、このメールを無視してください。\n"
            . "既存のパスワードは変更されません。";

        try {
            Mail::raw($body, function ($m) use ($email) {
                $m->to($email)->subject('【ミセチョク】パスワード再設定のご案内');
            });
        } catch (\Throwable $e) {
            // デモ環境用：メール送信失敗時は log に URL を吐いて手動確認できるようにする
            Log::warning('Password reset mail failed. Fallback URL: ' . $resetUrl . ' — ' . $e->getMessage());
        }
    }
}
