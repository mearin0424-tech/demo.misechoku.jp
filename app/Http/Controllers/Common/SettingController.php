<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\CastProvider;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function __construct(private readonly NotificationPreferenceService $preferenceService)
    {
    }

    /**
     * 通知設定（キャスト／店舗共通）
     */
    public function notification()
    {
        $isCast = request()->is('cast/*');

        [$actorType, $actorId] = $this->resolveActor();

        $prefs = ($actorType && $actorId)
            ? $this->preferenceService->get($actorType, $actorId)
            : [
                'push_enabled' => true,
                'line_enabled' => true,
                'interview_reminder_enabled' => true,
                'deadline_reminder_enabled' => true,
            ];

        return view('common.setting.notification', [
            'isCast' => $isCast,
            'isLoggedIn' => auth()->guard('member')->check() || auth()->guard('shop')->check(),
            'notificationPrefs' => $prefs,
        ]);
    }

    /**
     * 通知設定を保存
     */
    public function updateNotification(Request $request)
    {
        [$actorType, $actorId] = $this->resolveActor();
        if (!$actorType || !$actorId) {
            return redirect()->route('setting.notification')->withErrors(['ログイン後に設定してください。']);
        }

        $prefs = [
            'push_enabled' => $request->boolean('push_enabled'),
            'line_enabled' => $request->boolean('line_enabled'),
            'interview_reminder_enabled' => $request->boolean('interview_reminder_enabled'),
            'deadline_reminder_enabled' => $request->boolean('deadline_reminder_enabled'),
        ];

        $this->preferenceService->save($actorType, $actorId, $prefs);

        return redirect()->route('setting.notification')->with('message', '通知設定を更新しました。');
    }

    public function account()
    {
        $isCast = request()->is('cast/*');
        $lineLinked = false;
        $lineLinkUrl = route('setting.line.link');
        $currentEmail = '';

        if (auth()->guard('member')->check()) {
            $user = auth()->guard('member')->user();
            $currentEmail = (string) ($user->email ?? '');
            $lineLinked = CastProvider::query()
                ->where('cast_id', $user->getAuthIdentifier())
                ->where('provider', 'line')
                ->exists();
        } elseif (auth()->guard('shop')->check()) {
            $user = auth()->guard('shop')->user();
            $currentEmail = (string) ($user->email ?? '');
            $lineLinked = !empty($user->line_user_id);
        }

        return view('common.setting.account', compact('isCast', 'lineLinked', 'lineLinkUrl', 'currentEmail'));
    }

    /**
     * メールアドレス変更
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        [$actorType, $actorId] = $this->resolveActor();
        if (!$actorType || !$actorId) {
            return redirect()->route('setting.account')->withErrors(['ログイン後に設定してください。']);
        }

        [$table, $idColumn] = $this->resolveAccountTable($actorType);
        if (!$table) {
            return redirect()->route('setting.account')->withErrors(['対象アカウントが見つかりません。']);
        }

        $data = $request->validate([
            'new_email' => ['required', 'email', 'max:255', Rule::unique($table, 'email')->ignore($actorId, $idColumn)],
            'current_password' => ['required', 'string'],
        ], [
            'new_email.unique' => 'このメールアドレスは既に登録されています。',
        ]);

        $row = DB::table($table)->where($idColumn, $actorId)->first();
        if (!$row || !Hash::check($data['current_password'], (string) $row->password)) {
            return back()->withErrors(['current_password' => '現在のパスワードが正しくありません。'])->withInput();
        }

        DB::table($table)
            ->where($idColumn, $actorId)
            ->update(['email' => $data['new_email'], 'updated_at' => now()]);

        return redirect()->route('setting.account')->with('message', 'メールアドレスを更新しました。');
    }

    /**
     * パスワード変更
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        [$actorType, $actorId] = $this->resolveActor();
        if (!$actorType || !$actorId) {
            return redirect()->route('setting.account')->withErrors(['ログイン後に設定してください。']);
        }

        [$table, $idColumn] = $this->resolveAccountTable($actorType);
        if (!$table) {
            return redirect()->route('setting.account')->withErrors(['対象アカウントが見つかりません。']);
        }

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.confirmed' => '新しいパスワード（確認用）が一致しません。',
            'new_password.min' => 'パスワードは8文字以上で入力してください。',
        ]);

        $row = DB::table($table)->where($idColumn, $actorId)->first();
        if (!$row || !Hash::check($data['current_password'], (string) $row->password)) {
            return back()->withErrors(['current_password' => '現在のパスワードが正しくありません。'])->withInput();
        }

        if (Hash::check($data['new_password'], (string) $row->password)) {
            return back()->withErrors(['new_password' => '新しいパスワードは現在のパスワードと異なるものを入力してください。'])->withInput();
        }

        DB::table($table)
            ->where($idColumn, $actorId)
            ->update([
                'password' => Hash::make($data['new_password']),
                'updated_at' => now(),
            ]);

        return redirect()->route('setting.account')->with('message', 'パスワードを更新しました。');
    }

    /**
     * LINE 連携を解除
     */
    public function unlinkLine(Request $request): RedirectResponse
    {
        [$actorType, $actorId] = $this->resolveActor();
        if (!$actorType || !$actorId) {
            return redirect()->route('setting.account')->withErrors(['ログイン後に設定してください。']);
        }

        if ($actorType === 'cast') {
            CastProvider::query()
                ->where('cast_id', $actorId)
                ->where('provider', 'line')
                ->delete();
        } elseif ($actorType === 'shop_manager') {
            if (Schema::hasColumn('shop_managers', 'line_user_id')) {
                DB::table('shop_managers')
                    ->where('id', $actorId)
                    ->update(['line_user_id' => null, 'updated_at' => now()]);
            }
        }

        return redirect()->route('setting.account')->with('message', 'LINE連携を解除しました。');
    }

    /**
     * 退会（ソフトデリート）
     */
    public function withdraw(Request $request): RedirectResponse
    {
        [$actorType, $actorId] = $this->resolveActor();
        if (!$actorType || !$actorId) {
            return redirect()->route('setting.account')->withErrors(['ログイン後に設定してください。']);
        }

        $request->validate([
            'agreement' => ['accepted'],
            'current_password' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [
            'agreement.accepted' => '退会内容に同意のチェックを入れてください。',
        ]);

        [$table, $idColumn] = $this->resolveAccountTable($actorType);
        if (!$table) {
            return redirect()->route('setting.account')->withErrors(['対象アカウントが見つかりません。']);
        }

        $row = DB::table($table)->where($idColumn, $actorId)->first();
        if (!$row || !Hash::check($request->input('current_password'), (string) $row->password)) {
            return back()->withErrors(['current_password' => '現在のパスワードが正しくありません。'])->withInput();
        }

        if (!Schema::hasColumn($table, 'deleted_at')) {
            return back()->withErrors(['退会処理が現在ご利用いただけません。お手数ですが運営までお問い合わせください。']);
        }

        DB::table($table)
            ->where($idColumn, $actorId)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        // ログアウト
        if ($actorType === 'cast') {
            Auth::guard('member')->logout();
        } else {
            Auth::guard('shop')->logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.demo')->with('message', '退会手続きが完了しました。ご利用ありがとうございました。');
    }

    public function subscription()
    {
        return view('common.setting.subscription');
    }

    private function resolveActor(): array
    {
        if (auth()->guard('member')->check()) {
            return ['cast', (string) auth()->guard('member')->id()];
        }

        if (auth()->guard('shop')->check()) {
            return ['shop_manager', (string) auth()->guard('shop')->id()];
        }

        return [null, null];
    }

    /**
     * アクター種別から、アカウントテーブルと主キーカラムを返す。
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function resolveAccountTable(string $actorType): array
    {
        if ($actorType === 'cast') {
            return ['casts', 'id'];
        }
        if ($actorType === 'shop_manager') {
            return ['shop_managers', 'id'];
        }
        return [null, null];
    }
}
