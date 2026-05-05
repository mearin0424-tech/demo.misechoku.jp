<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    private const ACTION_TYPE_KEEP = 1;
    private const ACTION_TYPE_FOOTPRINT = 2;
    private const ACTION_TYPE_LIKE = 3;

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
            'action' => 'required|string|in:like,keep,footprint',
            'item_type' => 'required|string|in:cast,shop',
            'item_id' => 'required|string|max:20',
        ]);

        $action = $validated['action'];
        $itemType = $validated['item_type'];
        $itemId = $validated['item_id'];

        // 認証中の主体（店舗 or キャスト）を判定
        $castId = null;
        $shopId = null;

        if (Auth::guard('shop')->check()) {
            // 店舗ポータル：manager 経由で shop_id がぶら下がっている想定
            $shopUser = Auth::guard('shop')->user();
            $shopId = (string) ($shopUser->shop_id ?? '');
            if ($itemType === 'cast') {
                $castId = $itemId;
            }
        } elseif (Auth::guard('member')->check()) {
            // キャストポータル：guard member の ID が cast_id
            $castUser = Auth::guard('member')->user();
            $castId = (string) ($castUser->id ?? '');
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
        if ($action === 'like' && Auth::guard('member')->check() && $itemType === 'shop') {
            return response()->json(['error' => 'Cast to shop like is disabled'], 422);
        }

        // action_type の割り当て
        // 1: KEEP, 2: FOOTPRINT, 3: LIKE
        $actionType = match ($action) {
            'keep' => self::ACTION_TYPE_KEEP,
            'footprint' => self::ACTION_TYPE_FOOTPRINT,
            default => self::ACTION_TYPE_LIKE,
        };

        $query = DB::table('favorites')->where('action_type', $actionType);
        if (!empty($castId)) {
            $query->where('cast_id', $castId);
        }
        if (!empty($shopId)) {
            $query->where('shop_id', $shopId);
        }

        $existing = $query->first();

        $now = now();
        $isActive = false;

        if ($action === 'like') {
            $todayLikeQuery = DB::table('favorites')
                ->where('action_type', self::ACTION_TYPE_LIKE)
                ->whereDate('created_at', $now->toDateString());
            if (!empty($castId)) {
                $todayLikeQuery->where('cast_id', $castId);
            }
            if (!empty($shopId)) {
                $todayLikeQuery->where('shop_id', $shopId);
            }
            $hasLikedToday = $todayLikeQuery->exists();

            if (!$hasLikedToday) {
                DB::table('favorites')->insert([
                    'cast_id' => $castId,
                    'shop_id' => $shopId,
                    'action_type' => self::ACTION_TYPE_LIKE,
                    'created_at' => $now,
                ]);
            }
            $isActive = true;
        } elseif ($existing && $action !== 'footprint') {
            DB::table('favorites')->where('id', $existing->id)->delete();
            $isActive = false;
        } elseif ($existing && $action === 'footprint') {
            DB::table('favorites')->where('id', $existing->id)->update([
                'created_at' => $now,
            ]);
            $isActive = true;
        } else {
            DB::table('favorites')->insert([
                'cast_id' => $castId,
                'shop_id' => $shopId,
                'action_type' => $actionType,
                'created_at' => $now,
            ]);
            $isActive = true;
        }

        // 表示用の最新いいね数（LIKEのみカウントを返す）
        $likeCount = null;
        if ($action === 'like') {
            if ($itemType === 'cast') {
                $likeCount = (int) DB::table('favorites')
                    ->where('cast_id', $itemId)
                    ->where('action_type', self::ACTION_TYPE_LIKE)
                    ->count();
            } else {
                $likeCount = (int) DB::table('favorites')
                    ->where('shop_id', $itemId)
                    ->where('action_type', self::ACTION_TYPE_LIKE)
                    ->count();
            }
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

