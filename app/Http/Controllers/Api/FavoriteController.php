<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        // action_type の割り当て（今後 Footprint 等を拡張する余地を残す）
        // 1: KEEP, 3: LIKE
        $actionType = $action === 'like' ? 3 : 1;

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

        if ($existing) {
            DB::table('favorites')->where('id', $existing->id)->delete();
            $isActive = false;
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
                    ->where('action_type', 3)
                    ->count();
            } else {
                $likeCount = (int) DB::table('favorites')
                    ->where('shop_id', $itemId)
                    ->where('action_type', 3)
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

