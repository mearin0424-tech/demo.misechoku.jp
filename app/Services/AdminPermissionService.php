<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 運営アカウントのロールごとの権限を管理する。
 *
 * - 権限カタログ（capabilities）はコードで定義
 * - ロール単位の許可リストは `admin_role_permissions` テーブルに保存
 *   （行が無ければ defaultPermissions() を使用）
 * - admin ロールは常に全権限（変更不可）
 */
class AdminPermissionService
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';

    /**
     * 権限カタログ。
     *
     * 構造:
     * [
     *   group_label => [
     *     ['key' => '...', 'label' => '...', 'description' => '...'],
     *     ...
     *   ],
     * ]
     *
     * @return array<string, array<int, array{key:string, label:string, description:string}>>
     */
    public function permissionCatalog(): array
    {
        return [
            'ダッシュボード' => [
                ['key' => 'dashboard.view', 'label' => 'ダッシュボード閲覧', 'description' => '全体サマリー画面を表示できる'],
            ],
            'オペレーション' => [
                ['key' => 'operations.invoices', 'label' => '請求書発行', 'description' => '請求書の作成・発行・送付'],
                ['key' => 'operations.deposits', 'label' => '入金確認・振込', 'description' => '入金確認、キャストへの振込'],
                ['key' => 'operations.verification', 'label' => '身分証・書類審査', 'description' => '本人確認・店舗書類の承認／差戻し'],
                ['key' => 'operations.inquiries', 'label' => '問合せ対応', 'description' => 'お問合せの閲覧・返信'],
            ],
            'コンテンツ' => [
                ['key' => 'content.notices', 'label' => 'お知らせ管理', 'description' => 'お知らせの作成・編集・削除'],
                ['key' => 'content.columns', 'label' => 'コラム管理', 'description' => 'コラム記事の作成・編集・削除'],
            ],
            'マスタ設定' => [
                ['key' => 'master.ngwords', 'label' => 'NGワード管理', 'description' => 'NGワードの追加・削除'],
                ['key' => 'master.masters', 'label' => 'マスタメンテナンス', 'description' => 'カタログ・選択肢のメンテナンス'],
            ],
            'アナリティクス' => [
                ['key' => 'analytics.sales', 'label' => '売上・ユーザー分析', 'description' => '売上推移・ユーザー数増減の閲覧'],
            ],
            'アカウント管理' => [
                ['key' => 'accounts.shops.view', 'label' => '店舗の閲覧', 'description' => '店舗一覧・詳細の閲覧（公開情報まで）'],
                ['key' => 'accounts.shops.manage', 'label' => '店舗の編集操作', 'description' => '求人公開／非公開などの操作'],
                ['key' => 'accounts.shops.private', 'label' => '店舗の非公開情報閲覧', 'description' => '連絡先・口座・運営メモなど（要パスワード再入力）'],
                ['key' => 'accounts.casts.view', 'label' => 'キャストの閲覧', 'description' => 'キャスト一覧・詳細の閲覧（公開情報まで）'],
                ['key' => 'accounts.casts.manage', 'label' => 'キャストの編集操作', 'description' => 'ステータスの変更など'],
                ['key' => 'accounts.casts.private', 'label' => 'キャストの非公開情報閲覧', 'description' => '本名・連絡先・口座など（要パスワード再入力）'],
                ['key' => 'accounts.admins', 'label' => '運営アカウント管理', 'description' => '運営アカウントとロール権限の管理（admin専用）'],
            ],
            '規約' => [
                ['key' => 'policies.manage', 'label' => '規約管理', 'description' => '利用規約・プライバシーポリシー等の編集'],
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function allPermissionKeys(): array
    {
        $keys = [];
        foreach ($this->permissionCatalog() as $group) {
            foreach ($group as $cap) {
                $keys[] = $cap['key'];
            }
        }
        return $keys;
    }

    /**
     * ロール一覧（label 付き）。
     *
     * @return array<string, array{label:string, description:string, locked:bool}>
     */
    public function rolesCatalog(): array
    {
        return [
            self::ROLE_ADMIN => [
                'label' => 'スーパー管理者（admin）',
                'description' => 'すべての権限を保有します。権限のカスタマイズはできません。',
                'locked' => true,
            ],
            self::ROLE_STAFF => [
                'label' => 'オペレーター（staff）',
                'description' => '日常運用を担当するロール。許可する機能をチェックボックスで選択できます。',
                'locked' => false,
            ],
        ];
    }

    /**
     * ロールごとのデフォルト権限。
     *
     * @return array<string, array<int, string>>
     */
    public function defaultPermissions(): array
    {
        $all = $this->allPermissionKeys();
        return [
            self::ROLE_ADMIN => $all,
            self::ROLE_STAFF => [
                'dashboard.view',
                'operations.invoices',
                'operations.deposits',
                'operations.verification',
                'operations.inquiries',
                'content.notices',
                'content.columns',
                'analytics.sales',
                'accounts.shops.view',
                'accounts.casts.view',
            ],
        ];
    }

    /**
     * 指定ロールが保有する権限キーの配列。
     *
     * @return array<int, string>
     */
    public function getRolePermissions(string $role): array
    {
        if ($role === self::ROLE_ADMIN) {
            return $this->allPermissionKeys();
        }

        if (Schema::hasTable('admin_role_permissions')) {
            $row = DB::table('admin_role_permissions')->where('role', $role)->first();
            if ($row && !empty($row->permissions)) {
                $decoded = json_decode((string) $row->permissions, true);
                if (is_array($decoded)) {
                    // 不明キーを除外
                    $valid = array_intersect($decoded, $this->allPermissionKeys());
                    return array_values($valid);
                }
            }
        }

        return $this->defaultPermissions()[$role] ?? [];
    }

    /**
     * @param array<int, string> $keys
     */
    public function setRolePermissions(string $role, array $keys): void
    {
        if ($role === self::ROLE_ADMIN) {
            // admin は全権限固定
            return;
        }
        $valid = array_values(array_intersect($keys, $this->allPermissionKeys()));
        $payload = json_encode($valid, JSON_UNESCAPED_UNICODE);

        if (!Schema::hasTable('admin_role_permissions')) {
            return;
        }
        DB::table('admin_role_permissions')->updateOrInsert(
            ['role' => $role],
            ['permissions' => $payload, 'updated_at' => now()]
        );
    }

    public function roleHasPermission(string $role, string $key): bool
    {
        return in_array($key, $this->getRolePermissions($role), true);
    }
}
