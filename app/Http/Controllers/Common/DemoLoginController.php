<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\ShopManager;
use App\Models\SystemAccount;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ★★★ テスト用（本番デプロイ時に無効化推奨）★★★
 * デモ用ログイン画面：1画面から複数ロール（cast/shop/admin）にワンクリックログインできる
 * 検証・デモ用の便宜的な入口。正式なログイン画面（cast.login / shop.login / admin.login）とは別扱い。
 *
 * 詳細: CLAUDE.md「テスト用機能」セクション参照。
 */
class DemoLoginController extends Controller
{
    /**
     * デモ用ログイン画面表示
     *
     * 管理者／キャスト／店舗（マネージャ）を選択して各トップへ遷移するための入口。
     */
    public function show(): View
    {
        return view('common.demo-login', [
            'roleGroups' => $this->buildRoleGroups(),
        ]);
    }

    /**
     * デモ用ログイン処理
     *
     * 実際のメールアドレス・パスワード認証は行わず、選択されたロールに応じて
     * 各トップページへリダイレクトする。
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:admin,cast,shop'],
            'account_id' => ['required', 'string'],
            'auth_channel' => ['required', 'in:standard,line'],
        ]);

        auth()->guard('member')->logout();
        auth()->guard('shop')->logout();
        auth()->guard('admin')->logout();

        $role = (string) $request->input('role');
        $accountId = (string) $request->input('account_id');
        $authChannel = (string) $request->input('auth_channel');

        return match ($role) {
            'admin' => $this->loginAdmin($request, $accountId, $authChannel),
            'cast' => $this->loginCast($request, $accountId, $authChannel),
            'shop' => $this->loginShop($request, $accountId, $authChannel),
            default => redirect()->route('login.demo'),
        };
    }

    private function loginAdmin(Request $request, string $accountId, string $authChannel): RedirectResponse
    {
        $admin = SystemAccount::query()
            ->where('is_active', true)
            ->find($accountId);

        // デモ用: IDで見つからない場合は先頭の有効アカウントか、デフォルト運営を取得
        if (!$admin) {
            $admin = SystemAccount::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->first();
        }
        if (!$admin) {
            $admin = $this->ensureDefaultAdminAccount();
        }
        if (!$admin) {
            return back()
                ->withErrors(['account_id' => '選択された管理運営者アカウントが見つかりません。'])
                ->withInput();
        }

        auth()->guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()
            ->route('admin.dashboard')
            ->with('message', $this->buildLoginMessage('管理運営者', $authChannel, $admin->name ?? $admin->email ?? $admin->id));
    }

    private function loginCast(Request $request, string $accountId, string $authChannel): RedirectResponse
    {
        $member = Cast::query()->where('status', 1)->find($accountId);

        if (!$member) {
            return back()
                ->withErrors(['account_id' => '選択されたキャストアカウントが見つかりません。'])
                ->withInput();
        }

        auth()->guard('member')->login($member);
        $request->session()->regenerate();

        try {
            $displayName = DB::table('cast_profiles')
                ->where('cast_id', $member->getAuthIdentifier())
                ->value('nickname')
                ?: $member->email
                ?: $member->id;
        } catch (QueryException) {
            $displayName = $member->email ?: $member->id;
        }

        return redirect()
            ->route('cast.home')
            ->with('message', $this->buildLoginMessage('キャスト', $authChannel, (string) $displayName));
    }

    private function loginShop(Request $request, string $accountId, string $authChannel): RedirectResponse
    {
        $manager = ShopManager::query()->where('status', 1)->find($accountId);

        if (!$manager) {
            return back()
                ->withErrors(['account_id' => '選択された店舗マネージャーアカウントが見つかりません。'])
                ->withInput();
        }

        auth()->guard('shop')->login($manager);
        $request->session()->regenerate();

        try {
            $shopName = DB::table('shop_profiles')
                ->where('shop_id', $manager->shop_id)
                ->value('shop_name')
                ?: $manager->name
                ?: $manager->email
                ?: $manager->id;
        } catch (QueryException) {
            $shopName = $manager->name ?: $manager->email ?: $manager->id;
        }

        return redirect()
            ->route('shop.home')
            ->with('message', $this->buildLoginMessage('店舗マネージャー', $authChannel, (string) $shopName));
    }

    private function buildLoginMessage(string $roleLabel, string $authChannel, string $displayName): string
    {
        $channelLabel = $authChannel === 'line' ? 'LINE' : '通常';

        return sprintf('%sログイン（デモ）で「%s」として入室しました。', $channelLabel . $roleLabel, $displayName);
    }

    private function buildRoleGroups(): array
    {
        return [
            [
                'key' => 'cast',
                'label' => 'キャスト',
                'eyebrow' => '',
                'icon' => 'fa-gem',
                'description' => '',
                'accounts' => $this->loadCastAccounts(),
                'register_url' => route('cast.register'),
                'register_label' => 'キャスト登録',
            ],
            [
                'key' => 'shop',
                'label' => '店舗',
                'eyebrow' => '',
                'icon' => 'fa-store',
                'description' => '',
                'accounts' => $this->loadShopAccounts(),
                'register_url' => route('shop.register'),
                'register_label' => '店舗登録',
            ],
            [
                'key' => 'admin',
                'label' => '運営',
                'eyebrow' => '',
                'icon' => 'fa-crown',
                'description' => '',
                'accounts' => $this->loadAdminAccounts(),
                'register_url' => null,
                'register_label' => '運営ログイン',
            ],
        ];
    }

    private function loadAdminAccounts(): array
    {
        try {
            $accounts = SystemAccount::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->get(['id', 'name', 'email'])
                ->map(fn (SystemAccount $user) => [
                    'id' => (string) $user->id,
                    'label' => trim(($user->name ?? '管理運営者') . ' / ' . ($user->email ?? $user->id)),
                ])
                ->all();

            if ($accounts !== []) {
                return $accounts;
            }
            // 1件もいない場合はデフォルト運営を作成してから再取得
            $this->ensureDefaultAdminAccount();
            $accounts = SystemAccount::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->get(['id', 'name', 'email'])
                ->map(fn (SystemAccount $user) => [
                    'id' => (string) $user->id,
                    'label' => trim(($user->name ?? '管理運営者') . ' / ' . ($user->email ?? $user->id)),
                ])
                ->all();

            return $accounts !== [] ? $accounts : $this->fallbackAdminAccounts();
        } catch (QueryException) {
            return $this->fallbackAdminAccounts();
        }
    }

    /**
     * デモ用: 運営アカウントが0件のときに1件だけ作成する
     */
    private function ensureDefaultAdminAccount(): ?SystemAccount
    {
        try {
            $admin = SystemAccount::query()->updateOrCreate(
                ['email' => 'admin@misechoku.jp'],
                [
                    'name' => '管理者アカウント1',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    'role' => SystemAccount::ROLE_ADMIN,
                    'is_active' => true,
                ]
            );
            return $admin;
        } catch (QueryException) {
            return null;
        }
    }

    private function loadShopAccounts(): array
    {
        try {
            $accounts = DB::table('shop_managers')
                ->leftJoin('shop_profiles', 'shop_managers.shop_id', '=', 'shop_profiles.shop_id')
                ->where('shop_managers.status', 1)
                ->orderBy('shop_managers.id')
                ->get([
                    'shop_managers.id',
                    'shop_managers.name',
                    'shop_managers.email',
                    'shop_profiles.shop_name',
                ])
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'label' => trim(($row->shop_name ?: '店舗') . ' / ' . ($row->name ?: $row->email ?: $row->id)),
                ])
                ->all();

            return $accounts !== [] ? $accounts : $this->fallbackShopAccounts();
        } catch (QueryException) {
            return $this->fallbackShopAccounts();
        }
    }

    private function loadCastAccounts(): array
    {
        try {
            $accounts = DB::table('casts')
                ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
                ->where('casts.status', 1)
                ->orderBy('casts.id')
                ->get([
                    'casts.id',
                    'casts.email',
                    'cast_profiles.nickname',
                    'cast_profiles.name',
                ])
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'label' => trim(($row->nickname ?: $row->name ?: 'キャスト') . ' / ' . ($row->email ?: $row->id)),
                ])
                ->all();

            return $accounts !== [] ? $accounts : $this->fallbackCastAccounts();
        } catch (QueryException) {
            return $this->fallbackCastAccounts();
        }
    }

    private function fallbackAdminAccounts(): array
    {
        return [
            [
                'id' => '1',
                'label' => '管理者アカウント1 / admin@misechoku.jp',
            ],
        ];
    }

    private function fallbackShopAccounts(): array
    {
        return [
            [
                'id' => 'm00000001',
                'label' => 'Club Luminous (ルミナス) / 佐藤 店長',
            ],
            [
                'id' => 'm00000002',
                'label' => 'Lounge Stella (ステラ) / 鈴木 オーナー',
            ],
        ];
    }

    private function fallbackCastAccounts(): array
    {
        return [
            [
                'id' => 'c00000001',
                'label' => 'みさき / cast01@example.com',
            ],
            [
                'id' => 'c00000002',
                'label' => 'あい / cast02@example.com',
            ],
            [
                'id' => 'c00000003',
                'label' => 'ユナ / cast03@example.com',
            ],
        ];
    }
}

