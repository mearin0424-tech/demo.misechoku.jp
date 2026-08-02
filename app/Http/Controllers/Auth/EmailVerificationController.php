<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * メール認証（cast / shop 両対応）。
 *
 * 認証は「ソフトウォール」：未認証でもログインは可能だが、
 * app-v2 レイアウトで「メール未認証」バナーが出続ける。
 * 認証済みユーザーは casts.email_verified_at / shop_managers.email_verified_at に日時が入る。
 *
 * フロー:
 *   1. 登録時（RegistrationController）or ユーザー操作で send() を呼ぶ → 署名付きURLをメール送付
 *   2. ユーザーがリンクを開く → verify() が signed URL を検証し verified_at 更新
 */
class EmailVerificationController extends Controller
{
    /** 認証メール再送信リクエスト（現在ログインしている cast/shop 向け） */
    public function send(Request $request): RedirectResponse
    {
        [$type, $id, $email] = $this->resolveActor();
        if ($type === null || empty($email)) {
            return back()->withErrors(['メール認証はログイン後にご利用ください。']);
        }

        // 既に認証済みなら何もしない
        [$table, $idCol] = $type === 'cast' ? ['casts', 'id'] : ['shop_managers', 'id'];
        $current = DB::table($table)->where($idCol, $id)->first();
        if ($current && !empty($current->email_verified_at)) {
            return back()->with('message', 'このメールアドレスは既に認証済みです。');
        }

        $this->dispatchVerifyMail($type, $id, $email);

        return back()->with('message', $email . ' 宛に認証メールを送信しました。メール内のリンクを開いて認証を完了してください（有効期限 60 分）。');
    }

    /** URL 署名検証 → email_verified_at 更新 */
    public function verify(Request $request, string $type, string $id): RedirectResponse
    {
        // 署名検証（有効期限つき署名 URL）
        if (!$request->hasValidSignature()) {
            return redirect()->route('login.demo')->withErrors(['メール認証リンクが無効か、有効期限が切れています。ログイン後に再送信してください。']);
        }
        if (!in_array($type, ['cast', 'shop'], true)) {
            abort(404);
        }

        [$table, $idCol, $loginRoute] = $type === 'cast'
            ? ['casts', 'id', 'cast.login']
            : ['shop_managers', 'id', 'shop.login'];

        $updated = DB::table($table)
            ->where($idCol, $id)
            ->whereNull('email_verified_at')
            ->update([
                'email_verified_at' => now(),
                'updated_at'        => now(),
            ]);

        $msg = $updated > 0
            ? 'メールアドレスの認証が完了しました。ログインしてサービスをご利用ください。'
            : 'メールアドレスは既に認証済みです。ログインしてご利用ください。';

        return redirect()->route($loginRoute)->with('message', $msg);
    }

    /**
     * 認証メール送信。ユーザー登録時 / 再送時に呼ぶ。
     */
    public static function dispatchVerifyMail(string $type, string $id, string $email): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'auth.email.verify',
            now()->addMinutes(60),
            ['type' => $type, 'id' => $id]
        );

        $body = "ミセチョク メールアドレスの認証をお願いします。\n\n"
            . "下記の URL を開くと認証が完了します（有効期限: 60分）:\n\n"
            . $signedUrl . "\n\n"
            . "このメールに心当たりが無い場合は無視してください。";

        try {
            Mail::raw($body, function ($m) use ($email) {
                $m->to($email)->subject('【ミセチョク】メールアドレスの認証');
            });
        } catch (\Throwable $e) {
            Log::warning('Email verify mail failed. URL: ' . $signedUrl . ' — ' . $e->getMessage());
        }
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string} [type, id, email]
     */
    private function resolveActor(): array
    {
        if (auth()->guard('member')->check()) {
            $u = auth()->guard('member')->user();
            return ['cast', (string) $u->id, (string) ($u->email ?? '')];
        }
        if (auth()->guard('shop')->check()) {
            $u = auth()->guard('shop')->user();
            return ['shop', (string) $u->id, (string) ($u->email ?? '')];
        }
        return [null, null, null];
    }
}
