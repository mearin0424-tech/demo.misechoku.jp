<?php

namespace App\Support;

/**
 * 管理画面問い合わせ一覧（DB未接続時のモック）
 */
class AdminMockInquiries
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'id' => 1,
                'from_type' => '店舗',
                'from_name' => 'サンプル店舗A',
                'subject' => '請求内容の確認について',
                'status' => '未対応',
                'created_at' => now()->subDay(),
            ],
            [
                'id' => 2,
                'from_type' => 'キャスト',
                'from_name' => 'キャストB',
                'subject' => 'ログインできない',
                'status' => '対応中',
                'created_at' => now()->subDays(2),
            ],
        ];
    }
}
