<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * おしらせ（個人宛通知）の既読操作 API。
 */
class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    /**
     * 1件既読化。
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        [$type, $userId] = $this->resolve();
        if ($type === null) {
            return response()->json(['success' => false, 'error' => 'unauthenticated'], 401);
        }
        $ok = $this->notifications->markRead($id, $type, $userId);
        return response()->json([
            'success' => $ok,
            'unread_count' => $this->notifications->unreadCount($type, $userId),
        ]);
    }

    /**
     * Mark a single notification as read then redirect to its destination URL.
     * Guarantees notifications.read_at is updated before navigation completes,
     * avoiding races caused by client-side sendBeacon on link click.
     */
    public function visit(Request $request, int $id): RedirectResponse
    {
        [$type, $userId] = $this->resolve();
        if ($type === null) {
            return redirect('/');
        }

        $notification = \App\Models\Notification::query()
            ->forUser($type, $userId)
            ->whereKey($id)
            ->first();

        if (!$notification) {
            return redirect('/');
        }

        if ($notification->isUnread()) {
            $notification->markRead();
        }

        return redirect($this->safeRedirectTarget($notification->url));
    }

    /**
     * Restrict redirect target to same-origin URLs (relative path or matching host)
     * to prevent open redirect via crafted notification.url values.
     */
    private function safeRedirectTarget(?string $url): string
    {
        $url = is_string($url) ? trim($url) : '';
        if ($url === '') {
            return '/';
        }
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }
        $parsed = parse_url($url);
        if (!is_array($parsed) || empty($parsed['host'])) {
            return '/';
        }
        $appHost = parse_url(config('app.url', ''), PHP_URL_HOST);
        if ($appHost && strcasecmp($parsed['host'], $appHost) === 0) {
            return $url;
        }
        if (strcasecmp($parsed['host'], (string) request()->getHost()) === 0) {
            return $url;
        }
        return '/';
    }

    /**
     * 全件既読化。
     */
    public function markAllRead(Request $request): JsonResponse
    {
        [$type, $userId] = $this->resolve();
        if ($type === null) {
            return response()->json(['success' => false, 'error' => 'unauthenticated'], 401);
        }
        $marked = $this->notifications->markAllRead($type, $userId);
        return response()->json([
            'success' => true,
            'marked'  => $marked,
            'unread_count' => $this->notifications->unreadCount($type, $userId),
        ]);
    }

    /**
     * 未読件数のみ取得（ベルバッジ更新用）。
     */
    public function unreadCount(): JsonResponse
    {
        [$type, $userId] = $this->resolve();
        if ($type === null) {
            return response()->json(['success' => false, 'error' => 'unauthenticated'], 401);
        }
        return response()->json([
            'success' => true,
            'unread_count' => $this->notifications->unreadCount($type, $userId),
        ]);
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function resolve(): array
    {
        if ($u = auth()->guard('member')->user()) {
            return ['cast', (string) $u->getKey()];
        }
        if ($u = auth()->guard('shop')->user()) {
            return ['shop_manager', (string) $u->getKey()];
        }
        if ($u = auth()->guard('admin')->user()) {
            return ['admin', (string) $u->getKey()];
        }
        return [null, null];
    }
}
