<?php

namespace App\Services;

use App\Models\CharacterGuideSetting;
use Illuminate\Support\Facades\Schema;

/**
 * オコジョガイド（character-guide）の画面別設定を取り扱うサービス。
 *
 * - 既知画面のカタログ（route_name => 画面ラベル）を持つ。
 * - DB の character_guide_settings から表示 ON/OFF とセリフを返す。
 * - 設定が未保存の画面については、カタログ既定の表示・空メッセージを返す。
 */
class CharacterGuideService
{
    /**
     * 編集対象としてカタログに掲載する画面一覧。
     * 各エントリは [route_name => ['label' => 表示名, 'group' => 区分, 'default_enabled' => bool]]
     */
    private const CATALOG = [
        // キャスト向け
        'cast.home'                       => ['label' => 'キャスト：ホーム（スワイプ）',     'group' => 'cast',   'default_enabled' => false],
        'cast.search.index'               => ['label' => 'キャスト：店舗検索（一覧/AI）',   'group' => 'cast',   'default_enabled' => false],
        'cast.shopprofile.show'           => ['label' => 'キャスト：店舗プロフィール',       'group' => 'cast',   'default_enabled' => false],
        'cast.mypage.index'               => ['label' => 'キャスト：マイページ',             'group' => 'cast',   'default_enabled' => false],
        'cast.profile.edit'               => ['label' => 'キャスト：プロフィール編集',       'group' => 'cast',   'default_enabled' => false],
        'cast.mypage.management'          => ['label' => 'キャスト：採用・入金管理',         'group' => 'cast',   'default_enabled' => false],
        'cast.interaction.index'          => ['label' => 'キャスト：つながり（LIKES）',      'group' => 'cast',   'default_enabled' => false],
        'cast.talk.index'                 => ['label' => 'キャスト：トーク一覧',             'group' => 'cast',   'default_enabled' => false],
        'cast.register'                   => ['label' => 'キャスト：新規登録',               'group' => 'cast',   'default_enabled' => false],

        // 店舗向け
        'shop.home'                       => ['label' => '店舗：ホーム',                     'group' => 'shop',   'default_enabled' => false],
        'shop.search.index'               => ['label' => '店舗：キャスト検索',               'group' => 'shop',   'default_enabled' => false],
        'shop.recruits.show'              => ['label' => '店舗：求人プレビュー（プロフィール）', 'group' => 'shop', 'default_enabled' => false],
        'shop.recruits.edit'              => ['label' => '店舗：求人票編集',                 'group' => 'shop',   'default_enabled' => false],
        'shop.mypage.index'               => ['label' => '店舗：マイページ',                 'group' => 'shop',   'default_enabled' => false],
        'shop.mypage.management'          => ['label' => '店舗：採用・入金管理',             'group' => 'shop',   'default_enabled' => false],
        'shop.interaction.index'          => ['label' => '店舗：つながり（LIKES）',          'group' => 'shop',   'default_enabled' => false],
        'shop.talk.index'                 => ['label' => '店舗：トーク一覧',                 'group' => 'shop',   'default_enabled' => false],
        'shop.register'                   => ['label' => '店舗：新規登録',                   'group' => 'shop',   'default_enabled' => false],
    ];

    /**
     * 指定ルートに対する表示設定を返す。
     *
     * @return array{enabled: bool, message: string, has_catalog: bool}
     */
    public function getForRoute(?string $routeName): array
    {
        if ($routeName === null || $routeName === '') {
            return ['enabled' => false, 'message' => '', 'has_catalog' => false];
        }

        $catalog = self::CATALOG[$routeName] ?? null;

        if (!Schema::hasTable('character_guide_settings')) {
            return [
                'enabled' => $catalog ? (bool) $catalog['default_enabled'] : false,
                'message' => '',
                'has_catalog' => $catalog !== null,
            ];
        }

        $row = CharacterGuideSetting::query()
            ->where('route_name', $routeName)
            ->first();

        if ($row) {
            return [
                'enabled' => (bool) $row->is_enabled,
                'message' => (string) ($row->message ?? ''),
                'has_catalog' => $catalog !== null,
            ];
        }

        return [
            'enabled' => $catalog ? (bool) $catalog['default_enabled'] : false,
            'message' => '',
            'has_catalog' => $catalog !== null,
        ];
    }

    /**
     * 運営管理画面用：カタログ画面とその現在の設定（未登録なら既定値）を返す。
     *
     * @return array<int, array{route_name: string, label: string, group: string, enabled: bool, message: string}>
     */
    public function getCatalogWithSettings(): array
    {
        $existing = collect();
        if (Schema::hasTable('character_guide_settings')) {
            $existing = CharacterGuideSetting::query()->get()->keyBy('route_name');
        }

        $rows = [];
        foreach (self::CATALOG as $route => $meta) {
            $row = $existing->get($route);
            $rows[] = [
                'route_name' => $route,
                'label'      => $meta['label'],
                'group'      => $meta['group'],
                'enabled'    => $row ? (bool) $row->is_enabled : (bool) $meta['default_enabled'],
                'message'    => $row ? (string) ($row->message ?? '') : '',
            ];
        }

        return $rows;
    }

    /**
     * カタログに含まれる route_name 一覧。
     *
     * @return string[]
     */
    public function getCatalogRouteNames(): array
    {
        return array_keys(self::CATALOG);
    }

    /**
     * 設定の一括保存。$inputs は ['<route_name>' => ['enabled' => bool, 'message' => string]] 形式。
     */
    public function saveAll(array $inputs): void
    {
        if (!Schema::hasTable('character_guide_settings')) {
            return;
        }

        foreach ($inputs as $route => $payload) {
            if (!array_key_exists($route, self::CATALOG)) {
                continue;
            }
            $meta = self::CATALOG[$route];
            $message = isset($payload['message']) ? (string) $payload['message'] : '';
            $enabled = !empty($payload['enabled']);

            CharacterGuideSetting::query()->updateOrCreate(
                ['route_name' => $route],
                [
                    'screen_label' => $meta['label'],
                    'is_enabled'   => $enabled,
                    'message'      => $message,
                ]
            );
        }
    }

    /**
     * グループ表示用ラベル。
     */
    public function getGroupLabels(): array
    {
        return [
            'cast' => 'キャスト向け画面',
            'shop' => '店舗向け画面',
        ];
    }
}
