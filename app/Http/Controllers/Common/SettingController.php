<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\CastProvider;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\Request;

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
        $lineLinked = false;
        $lineLinkUrl = route('setting.line.link');

        [$actorType, $actorId] = $this->resolveActor();

        if (auth()->guard('member')->check()) {
            $user = auth()->guard('member')->user();
            $lineLinked = CastProvider::query()
                ->where('cast_id', $user->getAuthIdentifier())
                ->where('provider', 'line')
                ->exists();
        } elseif (auth()->guard('shop')->check()) {
            $user = auth()->guard('shop')->user();
            $lineLinked = !empty($user->line_user_id);
        }

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
            'lineLinked' => $lineLinked,
            'lineLinkUrl' => $lineLinkUrl,
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

    public function accountEmail()
    {
        $isCast = request()->is('cast/*');
        return view('common.setting.account-email', compact('isCast'));
    }

    public function accountPassword()
    {
        $isCast = request()->is('cast/*');
        return view('common.setting.account-password', compact('isCast'));
    }

    public function accountWithdraw()
    {
        $isCast = request()->is('cast/*');
        return view('common.setting.account-withdraw', compact('isCast'));
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
}
