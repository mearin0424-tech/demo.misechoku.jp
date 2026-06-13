<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * おしらせ（インボックス）の作成・取得・既読操作を一手に扱う。
 *
 * 利用例：
 *   $notif->createForCast($castId, 'talk.message_received',
 *       'ABC店からメッセージ', '新しいメッセージが届いています', $url);
 */
class NotificationService
{
    public function __construct(
        private readonly ?PushNotificationService $push = null,
        private readonly ?LineNotificationService $line = null,
    ) {
    }

    // =================================================================
    // 作成
    // =================================================================

    /**
     * @param  array<string, mixed>|null $payload
     */
    public function createForCast(string $castId, string $type, string $title, ?string $body = null, ?string $url = null, ?array $payload = null, bool $alsoPush = true): ?Notification
    {
        return $this->create(Notification::USER_CAST, $castId, $type, $title, $body, $url, $payload, $alsoPush);
    }

    /**
     * @param  array<string, mixed>|null $payload
     */
    public function createForShopManager(string $managerId, string $type, string $title, ?string $body = null, ?string $url = null, ?array $payload = null, bool $alsoPush = true): ?Notification
    {
        return $this->create(Notification::USER_SHOP, $managerId, $type, $title, $body, $url, $payload, $alsoPush);
    }

    /**
     * 店舗（shops.id）の全マネージャー宛にまとめて作成。
     *
     * @param  array<string, mixed>|null $payload
     * @return array<int, Notification>
     */
    public function createForShop(string $shopId, string $type, string $title, ?string $body = null, ?string $url = null, ?array $payload = null, bool $alsoPush = true): array
    {
        if (!Schema::hasTable('shop_managers')) {
            return [];
        }
        $managerIds = \DB::table('shop_managers')
            ->where('shop_id', $shopId)
            ->where('status', 1)
            ->pluck('id')
            ->all();

        $created = [];
        foreach ($managerIds as $mid) {
            $n = $this->createForShopManager((string) $mid, $type, $title, $body, $url, $payload, $alsoPush);
            if ($n) $created[] = $n;
        }
        return $created;
    }

    /**
     * 全 admin マネージャー宛にまとめて作成。
     *
     * @param  array<string, mixed>|null $payload
     * @return array<int, Notification>
     */
    public function createForAllAdmins(string $type, string $title, ?string $body = null, ?string $url = null, ?array $payload = null, bool $alsoPush = false): array
    {
        if (!Schema::hasTable('admins')) {
            return [];
        }
        $adminIds = \DB::table('admins')
            ->where('is_active', 1)
            ->pluck('id')
            ->all();

        $created = [];
        foreach ($adminIds as $aid) {
            $n = $this->create(Notification::USER_ADMIN, (string) $aid, $type, $title, $body, $url, $payload, $alsoPush);
            if ($n) $created[] = $n;
        }
        return $created;
    }

    /**
     * 低レイヤの作成API。
     */
    private function create(string $userType, string $userId, string $type, string $title, ?string $body, ?string $url, ?array $payload, bool $alsoPush): ?Notification
    {
        if (!Schema::hasTable('notifications')) {
            return null; // マイグレ未適用環境では黙ってスキップ
        }
        try {
            $n = Notification::create([
                'user_type' => $userType,
                'user_id'   => $userId,
                'type'      => $type,
                'title'     => mb_strimwidth($title, 0, 255, '…'),
                'body'      => $body !== null ? mb_strimwidth($body, 0, 1000, '…') : null,
                'url'       => $url,
                'payload'   => $payload,
            ]);
        } catch (Throwable) {
            return null;
        }

        if ($alsoPush) {
            $this->tryDispatchPush($n);
        }

        return $n;
    }

    /**
     * 既存の Push サービスを介して PWA Push を試みる（失敗しても通知作成は成功とする）。
     */
    private function tryDispatchPush(Notification $n): void
    {
        if (!$this->push || !Schema::hasTable('push_subscriptions')) {
            return;
        }
        try {
            $this->push->sendToUser(
                $n->user_type === Notification::USER_CAST ? 'cast'
                    : ($n->user_type === Notification::USER_SHOP ? 'shop' : 'admin'),
                $n->user_id,
                $n->title,
                $n->body ?? '',
                $n->url ?? null
            );
            $n->forceFill(['dispatched_push_at' => now()])->save();
        } catch (Throwable) {
            // Push 失敗は致命的ではない
        }
    }

    // =================================================================
    // 取得
    // =================================================================

    /**
     * ユーザーの未読件数。
     */
    public function unreadCount(string $userType, string $userId): int
    {
        if (!Schema::hasTable('notifications')) {
            return 0;
        }
        return (int) Notification::query()
            ->forUser($userType, $userId)
            ->unread()
            ->count();
    }

    /**
     * ユーザーの最新通知（ヘッダーpopover用）。
     *
     * @return \Illuminate\Support\Collection<int, Notification>
     */
    public function latestForUser(string $userType, string $userId, int $limit = 20): Collection
    {
        if (!Schema::hasTable('notifications')) {
            return collect();
        }
        return Notification::query()
            ->forUser($userType, $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    // =================================================================
    // 既読
    // =================================================================

    public function markRead(int $notificationId, string $userType, string $userId): bool
    {
        $n = Notification::query()
            ->forUser($userType, $userId)
            ->whereKey($notificationId)
            ->first();
        if (!$n) return false;
        return $n->markRead();
    }

    public function markAllRead(string $userType, string $userId): int
    {
        return Notification::query()
            ->forUser($userType, $userId)
            ->unread()
            ->update(['read_at' => now()]);
    }
}
