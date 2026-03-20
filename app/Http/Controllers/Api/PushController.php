<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushController extends Controller
{
    public function vapidPublicKey(): JsonResponse
    {
        $key = config('services.push.vapid_public');
        if (empty($key)) {
            return response()->json(['error' => 'VAPID not configured'], 503);
        }

        return response()->json(['publicKey' => $key]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string|max:500',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $endpoint = $request->input('endpoint');
        $keys = $request->input('keys');
        [$userType, $userId] = $this->resolveActor();

        try {
            $now = now();
            $payload = [
                'public_key' => $keys['p256dh'] ?? null,
                'auth_token' => $keys['auth'] ?? null,
                'user_agent' => $request->userAgent(),
                'updated_at' => $now,
            ];
            if ($userType && $userId) {
                $payload['user_type'] = $userType;
                $payload['user_id'] = $userId;
            }

            $exists = DB::table('push_subscriptions')->where('endpoint', $endpoint)->exists();
            if ($exists) {
                DB::table('push_subscriptions')->where('endpoint', $endpoint)->update($payload);
            } else {
                $payload['endpoint'] = $endpoint;
                $payload['created_at'] = $now;
                DB::table('push_subscriptions')->insert($payload);
            }

            return response()->json(['ok' => true], 201);
        } catch (\Throwable $e) {
            Log::warning('Push subscribe error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save subscription'], 500);
        }
    }

    public function sendTest(Request $request): JsonResponse
    {
        $publicKey = config('services.push.vapid_public');
        $privateKey = config('services.push.vapid_private');
        $subject = config('services.push.subject');

        if (empty($publicKey) || empty($privateKey)) {
            return response()->json([
                'ok' => false,
                'message' => 'VAPID キーが未設定です。.env に VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY を設定し、php artisan push:vapid で生成してください。',
            ], 503);
        }

        [$userType, $userId] = $this->resolveActor();
        $query = DB::table('push_subscriptions');
        if ($userType && $userId) {
            $query->where('user_type', $userType)->where('user_id', $userId);
        }

        $subscriptions = $query->get();
        if ($subscriptions->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => '購読がありません。先に「通知を有効にする」を押してからテストしてください。',
            ], 404);
        }

        $payload = json_encode([
            'title' => 'ミセチョク',
            'body' => 'テスト通知です。',
            'url' => url('/shop/home'),
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

        return response()->json([
            'ok' => true,
            'message' => "送信: {$sent} 件、失敗: {$failed} 件",
            'sent' => $sent,
            'failed' => $failed,
        ]);
    }

    private function resolveActor(): array
    {
        if (auth()->guard('member')->check()) {
            return ['cast', (string) auth()->guard('member')->id()];
        }
        if (auth()->guard('shop')->check()) {
            return ['shop_manager', (string) auth()->guard('shop')->id()];
        }

        return [null, null];
    }
}
