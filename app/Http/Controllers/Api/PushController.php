<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PushController extends Controller
{
    /**
     * ### demo function and data for test ###
     * Send a canned Web Push notification to the currently logged-in user.
     * Gated by config('demo.enabled') && config('demo.test_push').
     * Requires the caller to have already granted browser permission and
     * completed /api/push/subscribe (i.e. push_subscriptions row exists).
     */
    public function testSend(Request $request, PushNotificationService $push): JsonResponse
    {
        if (!config('demo.enabled') || !config('demo.test_push')) {
            return response()->json(['error' => 'demo mode disabled'], 403);
        }

        [$type, $id] = $this->resolveActor();
        if (!$type || !$id) {
            return response()->json(['error' => 'login required'], 401);
        }

        $result = $push->sendToUser(
            $type,
            $id,
            '[デモ] テスト通知',
            'ミセチョクのテスト通知です（' . now()->format('H:i:s') . '）',
            url('/'),
        );

        return response()->json($result, $result['success'] ? 200 : 202);
    }


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
