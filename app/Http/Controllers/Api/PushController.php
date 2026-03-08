<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushController extends Controller
{
    /**
     * 公開鍵を返す（フロントで pushManager.subscribe に渡す）
     */
    public function vapidPublicKey(): JsonResponse
    {
        $key = config('services.push.vapid_public');
        if (empty($key)) {
            return response()->json(['error' => 'VAPID not configured'], 503);
        }
        return response()->json(['publicKey' => $key]);
    }

    /**
     * ブラウザの Push 購読情報を保存
     */
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

        try {
            $now = now();
            $exists = DB::table('push_subscriptions')->where('endpoint', $endpoint)->exists();
            if ($exists) {
                DB::table('push_subscriptions')->where('endpoint', $endpoint)->update([
                    'public_key' => $keys['p256dh'] ?? null,
                    'auth_token' => $keys['auth'] ?? null,
                    'user_agent' => $request->userAgent(),
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('push_subscriptions')->insert([
                    'endpoint' => $endpoint,
                    'public_key' => $keys['p256dh'] ?? null,
                    'auth_token' => $keys['auth'] ?? null,
                    'user_agent' => $request->userAgent(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            return response()->json(['ok' => true], 201);
        } catch (\Throwable $e) {
            Log::warning('Push subscribe error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save subscription'], 500);
        }
    }

    /**
     * テスト通知を送信（保存済みの全購読に1件送る）
     */
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

        $subscriptions = DB::table('push_subscriptions')->get();
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
}
