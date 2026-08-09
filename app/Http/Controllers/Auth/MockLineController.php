<?php

// ### demo function and data for test ###
//
// MockLineController lets external testers exercise LINE Login / Link without
// going through a real LINE OAuth callback. It is gated by config('demo.enabled')
// and config('demo.mock_line') — production MUST have DEMO_MODE=false.
//
// The mock uses provider_id = "mock:" . $mockUserId, so real LINE providers
// (which contain LINE's opaque userId format) never collide with mock IDs.

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\CastProvider;
use App\Models\ShopManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;

class MockLineController extends Controller
{
    /**
     * Show a small form: role selector + arbitrary "LINE user id".
     * Only reachable when demo mode is enabled.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (!$this->enabled()) {
            return redirect()->route('login.demo');
        }

        return view('common.mock-line', [
            'mode'         => 'login',
            'defaultRole'  => $request->query('role', 'cast'),
            'defaultLineId'=> $request->query('user_id', 'mock-' . Str::lower(Str::random(10))),
        ]);
    }

    /**
     * Simulate the LINE callback for login.
     * If the given (role, mockUserId) is not linked to any account,
     * we bounce back to the corresponding login page with a friendly hint.
     */
    public function login(Request $request): RedirectResponse
    {
        if (!$this->enabled()) {
            return redirect()->route('login.demo');
        }

        $data = $request->validate([
            'role'    => ['required', 'in:cast,shop'],
            'user_id' => ['required', 'string', 'max:120'],
        ]);

        $role       = $data['role'];
        $lineUserId = 'mock:' . trim($data['user_id']);

        if ($role === 'cast') {
            $provider = CastProvider::query()
                ->where('provider', 'line')
                ->where('provider_id', $lineUserId)
                ->first();

            if (!$provider) {
                return redirect()->route('cast.login')->withErrors([
                    'line' => 'このモック LINE ID (' . $lineUserId . ') に紐づくアカウントがありません。'
                        . '先にキャストで登録 → 設定 > 通知設定 > LINE と連携（モック）で連携してください。',
                ]);
            }

            $cast = Cast::query()->where('status', 1)->find($provider->cast_id);
            if (!$cast) {
                return redirect()->route('cast.login')->withErrors(['line' => 'このアカウントは利用できません。']);
            }

            auth()->guard('member')->login($cast);
            $request->session()->regenerate();
            $cast->update(['last_login_at' => now()]);

            return redirect()->route('cast.home')->with('message', '[デモ] モック LINE でログインしました。');
        }

        // role === 'shop'
        $manager = ShopManager::query()
            ->where('status', 1)
            ->where('line_user_id', $lineUserId)
            ->first();

        if (!$manager) {
            return redirect()->route('shop.login')->withErrors([
                'line' => 'このモック LINE ID (' . $lineUserId . ') に紐づくアカウントがありません。'
                    . '先に店舗で登録 → 設定 > 通知設定 > LINE と連携（モック）で連携してください。',
            ]);
        }

        auth()->guard('shop')->login($manager);
        $request->session()->regenerate();
        $manager->update(['last_login_at' => now()]);

        return redirect()->route('shop.home')->with('message', '[デモ] モック LINE でログインしました。');
    }

    /**
     * Link the currently logged-in cast/shop with a mock LINE user id.
     * If no user_id is given we auto-generate "mock:<current-id>" for a
     * deterministic pairing between the real account and its mock LINE identity.
     */
    public function link(Request $request): RedirectResponse
    {
        if (!$this->enabled()) {
            return redirect()->route('login.demo');
        }

        [$guard, $userId] = $this->currentActor();
        if (!$guard || !$userId) {
            return redirect()->route('login.demo')->withErrors([
                'line' => 'ログイン後にご利用ください。',
            ]);
        }

        $mockUserId = trim((string) $request->input('user_id', ''));
        if ($mockUserId === '') {
            $mockUserId = 'mock-' . $userId;
        }
        $providerId = 'mock:' . $mockUserId;

        if ($guard === 'cast') {
            CastProvider::updateOrCreate(
                ['cast_id' => $userId, 'provider' => 'line'],
                ['provider_id' => $providerId],
            );
        } else {
            ShopManager::query()->where('id', $userId)->update(['line_user_id' => $providerId]);
        }

        return redirect()->route('setting.notification')->with(
            'message',
            '[デモ] モック LINE を連携しました。LINE User ID = ' . $mockUserId
                . '（ログアウト後、モック LINE ログインで同じ ID を入力すると入れます）',
        );
    }

    private function enabled(): bool
    {
        return (bool) config('demo.enabled') && (bool) config('demo.mock_line');
    }

    /**
     * @return array{0: ?string, 1: ?string}  [guard, id]
     */
    private function currentActor(): array
    {
        if (auth()->guard('member')->check()) {
            return ['cast', (string) auth()->guard('member')->id()];
        }
        if (auth()->guard('shop')->check()) {
            return ['shop', (string) auth()->guard('shop')->id()];
        }
        return [null, null];
    }
}
