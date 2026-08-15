<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\CastProvider;
use App\Models\ShopManager;
use App\Services\LineLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LineLoginController extends Controller
{
    public function __construct(
        protected LineLoginService $lineLogin
    ) {}

    /**
     * LINE認証へリダイレクト（ロールは state で渡す）
     */
    public function redirect(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:cast,shop'],
        ]);

        $role = $request->input('role');
        $state = $role . '.' . Str::random(32);
        session()->put('line_login_state', $state);

        $redirectUri = $this->resolveLineRedirectUri($request);
        if (!config('services.line.redirect_is_explicit')) {
            session()->put('line_oauth_redirect_uri', $redirectUri);
        }

        $url = $this->lineLogin->getAuthorizationUrl($state, $redirectUri);

        return redirect()->away($url);
    }

    /**
     * 設定画面から「LINEと連携」：ログイン済みユーザーがLINEアカウントを紐づける
     */
    public function redirectLink(Request $request): RedirectResponse
    {
        $guard = $this->currentLineGuard();
        $userId = $this->currentLineUserId();
        if (!$guard || !$userId) {
            return redirect()->route('login.demo')
                ->withErrors(['line' => 'ログイン後に設定から連携してください。']);
        }

        $state = 'link_' . $guard . '.' . Str::random(32);
        session()->put('line_login_state', $state);
        session()->put('line_link_guard', $guard);
        session()->put('line_link_user_id', $userId);

        $redirectUri = $this->resolveLineRedirectUri($request);
        if (!config('services.line.redirect_is_explicit')) {
            session()->put('line_oauth_redirect_uri', $redirectUri);
        }

        $url = $this->lineLogin->getAuthorizationUrl($state, $redirectUri);

        return redirect()->away($url);
    }

    /**
     * LINEコールバック：トークン取得 → プロフィール取得 → アカウント紐づけでログイン
     */
    public function callback(Request $request): RedirectResponse
    {
        $code = $request->input('code');
        $state = $request->input('state');

        if (!$code || !$state) {
            return redirect()->route('login.demo')
                ->withErrors(['line' => 'LINE認証に失敗しました。再度お試しください。']);
        }

        $savedState = session()->pull('line_login_state');
        if ($savedState !== $state) {
            return $this->redirectToLoginOrSetting('セッションが無効です。再度お試しください。');
        }

        $isLink = str_starts_with($state, 'link_');
        $role = $isLink ? Str::before(Str::after($state, 'link_'), '.') : Str::before($state, '.');

        if ($isLink) {
            $guard = session()->pull('line_link_guard');
            $linkUserId = session()->pull('line_link_user_id');
            if (!$guard || !$linkUserId || !in_array($role, ['cast', 'shop'], true)) {
                return $this->redirectToLoginOrSetting('不正なリクエストです。');
            }
        } elseif (!in_array($role, ['cast', 'shop'], true)) {
            return redirect()->route('login.demo')
                ->withErrors(['line' => '不正なリクエストです。']);
        }

        $redirectUri = config('services.line.redirect_is_explicit')
            ? config('services.line.redirect')
            : (session()->pull('line_oauth_redirect_uri') ?: config('services.line.redirect'));

        try {
            $tokenData = $this->lineLogin->exchangeCode($code, $redirectUri);
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new \RuntimeException('No access_token in response');
            }
            $profile = $this->lineLogin->getProfile($accessToken);
            $lineUserId = $profile['userId'] ?? null;
            if (!$lineUserId) {
                throw new \RuntimeException('No userId in profile');
            }
        } catch (\Throwable $e) {
            return $this->redirectToLoginOrSetting('LINE認証の取得に失敗しました。');
        }

        if ($isLink) {
            return $this->saveLineLink($role, $linkUserId, $lineUserId);
        }

        if ($role === 'cast') {
            return $this->loginCast($request, $lineUserId);
        }

        return $this->loginShop($request, $lineUserId);
    }

    private function redirectToLoginOrSetting(string $message): RedirectResponse
    {
        if (session()->has('line_link_guard')) {
            return redirect()->route('setting.notification')->withErrors(['line' => $message]);
        }
        return redirect()->route('login.demo')->withErrors(['line' => $message]);
    }

    private function saveLineLink(string $role, string $userId, string $lineUserId): RedirectResponse
    {
        $notificationRoute = 'setting.notification';
        $redirectTo = redirect()->route($notificationRoute)->with('message', 'LINEと連携しました。');

        if ($role === 'cast') {
            CastProvider::updateOrCreate(
                ['cast_id' => $userId, 'provider' => 'line'],
                ['provider_id' => $lineUserId]
            );
            return $redirectTo;
        }

        ShopManager::query()->where('id', $userId)->update(['line_user_id' => $lineUserId]);
        return $redirectTo;
    }

    private function currentLineGuard(): ?string
    {
        if (auth()->guard('member')->check()) {
            return 'cast';
        }
        if (auth()->guard('shop')->check()) {
            return 'shop';
        }
        return null;
    }

    private function currentLineUserId(): ?string
    {
        if (auth()->guard('member')->check()) {
            return (string) auth()->guard('member')->id();
        }
        if (auth()->guard('shop')->check()) {
            return (string) auth()->guard('shop')->id();
        }
        return null;
    }

    private function loginCast(Request $request, string $lineUserId): RedirectResponse
    {
        $provider = CastProvider::query()
            ->where('provider', 'line')
            ->where('provider_id', $lineUserId)
            ->first();

        if (!$provider) {
            return redirect()->route('cast.login')
                ->withErrors(['line' => 'このLINEアカウントはまだ連携されていません。ログイン後、設定＞通知設定から「LINEと連携」してください。']);
        }

        $cast = Cast::query()->where('status', 1)->find($provider->cast_id);
        if (!$cast) {
            return redirect()->route('cast.login')
                ->withErrors(['line' => 'このアカウントは利用できません。']);
        }

        auth()->guard('member')->login($cast);
        $request->session()->regenerate();
        // last_login_at is updated centrally by App\Listeners\UpdateLastLoginAt.

        return redirect()
            ->route('cast.home')
            ->with('message', 'LINEでログインしました。');
    }

    private function loginShop(Request $request, string $lineUserId): RedirectResponse
    {
        $manager = ShopManager::query()
            ->where('status', 1)
            ->where('line_user_id', $lineUserId)
            ->first();

        if (!$manager) {
            return redirect()->route('shop.login')
                ->withErrors(['line' => 'このLINEアカウントはまだ連携されていません。ログイン後、設定＞通知設定から「LINEと連携」してください。']);
        }

        auth()->guard('shop')->login($manager);
        $request->session()->regenerate();
        // last_login_at is updated centrally by App\Listeners\UpdateLastLoginAt.

        return redirect()
            ->route('shop.home')
            ->with('message', 'LINEでログインしました。');
    }

    /**
     * LINE に渡す redirect_uri（認可URLとトークン交換で完全一致させる）
     * LINE_REDIRECT_URI 未設定時は実リクエストのスキーム・ホストに合わせる（APP_URL ずれによる 400 対策）
     */
    private function resolveLineRedirectUri(Request $request): string
    {
        if (config('services.line.redirect_is_explicit')) {
            return config('services.line.redirect');
        }

        $root = rtrim($request->getSchemeAndHttpHost() . $request->getBasePath(), '/');

        return $root . '/login/line/callback';
    }
}
