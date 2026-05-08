<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    /**
     * スワイプ画面からの「いいね」「キープ」をトグルする簡易API
     *
     * action: like / keep
     * item_type: cast / shop
     * item_id: 対象のID（キャストID or 店舗ID）
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:like,keep',
            'item_type' => 'required|string|in:cast,shop',
            'item_id' => 'required|string|max:20',
        ]);

        $action = $validated['action'];
        $itemType = $validated['item_type'];
        $itemId = $validated['item_id'];

        // 認証中の主体（店舗 or キャスト）を判定
        $castId = null;
        $shopId = null;
        $senderType = null;

        if (Auth::guard('shop')->check()) {
            // 店舗ポータル：manager 経由で shop_id がぶら下がっている想定
            $shopUser = Auth::guard('shop')->user();
            $shopId = (string) ($shopUser->shop_id ?? '');
            $senderType = Favorite::SENDER_SHOP;
            if ($itemType === 'cast') {
                $castId = $itemId;
            }
        } elseif (Auth::guard('member')->check()) {
            // キャストポータル：guard member の ID が cast_id
            $castUser = Auth::guard('member')->user();
            $castId = (string) ($castUser->id ?? '');
            $senderType = Favorite::SENDER_CAST;
            if ($itemType === 'shop') {
                $shopId = $itemId;
            }
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (empty($castId) && empty($shopId)) {
            return response()->json(['error' => 'Invalid target combination'], 422);
        }

        // キャスト -> 店舗 LIKE は廃止
        if ($action === 'like' && $senderType === Favorite::SENDER_CAST && $itemType === 'shop') {
            return response()->json(['error' => 'Cast to shop like is disabled'], 422);
        }

        $actionType = match ($action) {
            'keep' => Favorite::ACTION_KEEP,
            default => Favorite::ACTION_LIKE,
        };

        // 自分が同じ向きで打った既存レコード
        $existing = DB::table('favorites')
            ->where('action_type', $actionType)
            ->where('sender_type', $senderType)
            ->when(!empty($castId), fn ($q) => $q->where('cast_id', $castId))
            ->when(!empty($shopId), fn ($q) => $q->where('shop_id', $shopId))
            ->first();

        $now = now();
        $isActive = false;

        if ($action === 'like') {
            // LIKE は 1 日 1 回まで（連打抑制）。同方向の本日 LIKE が既にあれば加えない。
            $hasLikedToday = DB::table('favorites')
                ->where('action_type', Favorite::ACTION_LIKE)
                ->where('sender_type', $senderType)
                ->whereDate('created_at', $now->toDateString())
                ->when(!empty($castId), fn ($q) => $q->where('cast_id', $castId))
                ->when(!empty($shopId), fn ($q) => $q->where('shop_id', $shopId))
                ->exists();

            if (!$hasLikedToday) {
                DB::table('favorites')->insert([
                    'cast_id' => $castId,
                    'shop_id' => $shopId,
                    'action_type' => Favorite::ACTION_LIKE,
                    'sender_type' => $senderType,
                    'created_at' => $now,
                ]);
            }
            $isActive = true;
        } elseif ($existing) {
            DB::table('favorites')->where('id', $existing->id)->delete();
            $isActive = false;
        } else {
            DB::table('favorites')->insert([
                'cast_id' => $castId,
                'shop_id' => $shopId,
                'action_type' => $actionType,
                'sender_type' => $senderType,
                'created_at' => $now,
            ]);
            $isActive = true;
        }

        // 表示用の最新いいね数（受け手側に届いた LIKE をカウント。
        // LIKE は仕様上 sender_type='shop' のみ なので絞り込みも一貫。）
        $likeCount = null;
        if ($action === 'like') {
            $likeCount = (int) DB::table('favorites')
                ->where('action_type', Favorite::ACTION_LIKE)
                ->when($itemType === 'cast', fn ($q) => $q->where('cast_id', $itemId))
                ->when($itemType === 'shop', fn ($q) => $q->where('shop_id', $itemId))
                ->count();
        }

        return response()->json([
            'ok' => true,
            'action' => $action,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'is_active' => $isActive,
            'like_count' => $likeCount,
        ]);
    }
}
