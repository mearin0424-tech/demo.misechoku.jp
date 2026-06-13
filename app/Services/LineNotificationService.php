<?php

namespace App\Services;

use App\Models\Cast;
use App\Models\CastProvider;
use App\Models\ShopManager;

/**
 * キャスト／店舗へのLINE通知送信をまとめて行うサービス。
 * Messaging API（pushMessage）で通知を送信します。
 */
class LineNotificationService
{
    public function __construct(
        protected LineMessageService $lineMessage
    ) {}

    /**
     * キャストにLINE通知を送る（連携済みLINEユーザーIDに送信）
     */
    public function sendToCast(string $castId, string $message): array
    {
        if (!Cast::query()->whereKey($castId)->exists()) {
            return ['success' => false, 'error' => 'cast_not_found'];
        }

        $lineUserId = CastProvider::query()
            ->where('cast_id', $castId)
            ->where('provider', 'line')
            ->value('provider_id');

        return $this->send($lineUserId, $message);
    }

    /**
     * 店舗マネージャーにLINE通知を送る
     */
    public function sendToShopManager(string $managerId, string $message): array
    {
        $manager = ShopManager::query()->find($managerId);
        if (!$manager) {
            return ['success' => false, 'error' => 'manager_not_found'];
        }

        $lineUserId = $manager->line_user_id ?? null;

        return $this->send($lineUserId, $message);
    }

    /**
     * LINEユーザーID（Messaging API）に送信
     */
    private function send(?string $lineUserId, string $message): array
    {
        if (!$lineUserId) {
            return ['success' => false, 'error' => 'line_not_linked'];
        }

        try {
            $response = $this->lineMessage->sendText($lineUserId, $message);
            // LineMessageService は ['success' => bool, 'error' => ?string] を返すよう刷新済み
            $success = is_array($response) && !empty($response['success']);
        } catch (\Throwable $e) {
            $success = false;
        }

        return [
            'success' => $success,
            'results' => ['messaging_api' => $success],
        ];
    }
}
