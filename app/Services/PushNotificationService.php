<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    public function sendToUser(string $userType, string $userId, string $title, string $body, ?string $url = null): array
    {
        $publicKey = config('services.push.vapid_public');
        $privateKey = config('services.push.vapid_private');
        $subject = config('services.push.subject');

        if (empty($publicKey) || empty($privateKey)) {
            return ['success' => false, 'sent' => 0, 'failed' => 0, 'error' => 'vapid_not_configured'];
        }

        $subscriptions = DB::table('push_subscriptions')
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->get();

        if ($subscriptions->isEmpty()) {
            return ['success' => false, 'sent' => 0, 'failed' => 0, 'error' => 'subscription_not_found'];
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?: url('/'),
            'badge' => 1,
        ]);

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);

        $sent = 0;
        $failed = 0;

        foreach ($subscriptions as $row) {
            try {
                $subscription = Subscription::create([
                    'endpoint' => $row->endpoint,
                    'keys' => [
                        'p256dh' => $row->public_key,
                        'auth' => $row->auth_token,
                    ],
                ]);
                $result = $webPush->sendOneNotification($subscription, $payload);
                if (method_exists($result, 'isSuccess') && $result->isSuccess()) {
                    $sent++;
                } else {
                    $failed++;
                    if (method_exists($result, 'isSubscriptionExpired') && $result->isSubscriptionExpired()) {
                        DB::table('push_subscriptions')->where('id', $row->id)->delete();
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Push send error: ' . $e->getMessage());
                $failed++;
            }
        }

        return ['success' => $sent > 0, 'sent' => $sent, 'failed' => $failed];
    }
}
