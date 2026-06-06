<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InteractionController extends Controller
{

    private function formatInteractionAt(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function index()
    {
        $isCastPortal = request()->is('cast/*');

        if ($isCastPortal) {
            // キャスト側：お店のキープ・ライク
            $castUser = Auth::guard('member')->user();
            $castId = $castUser ? (string) $castUser->id : null;

            $keepCasts = [];
            $receivedLikeCasts = [];
            $sentLikeCasts = [];

            if ($castId && Schema::hasTable('favorites')) {
                // 自分がキープした店舗（cast 発信の KEEP）
                $keepRows = DB::table('favorites')
                    ->join('shops', 'favorites.shop_id', '=', 'shops.id')
                    ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
                    ->where('favorites.cast_id', $castId)
                    ->where('favorites.action_type', Favorite::ACTION_KEEP)
                    ->where('favorites.sender_type', Favorite::SENDER_CAST)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'shops.id',
                        'shop_profiles.shop_name as name',
                        'shop_profiles.pref',
                        'shop_profiles.city',
                        DB::raw("(SELECT si.image_path FROM shop_images si WHERE si.shop_id = shops.id ORDER BY si.is_main DESC, si.main_order IS NULL, si.main_order, si.id LIMIT 1) as main_image_path"),
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($keepRows as $row) {
                    $keepCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: '店舗',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $this->assetPathForStored($row->main_image_path ?? null),
                        'updated_at' => $this->formatInteractionAt($row->created_at),
                    ];
                }

                // 受け取ったLIKE（shop 発信の LIKE がこのキャスト宛に来ているもの）
                $receivedRows = DB::table('favorites')
                    ->join('shops', 'favorites.shop_id', '=', 'shops.id')
                    ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
                    ->where('favorites.cast_id', $castId)
                    ->where('favorites.action_type', Favorite::ACTION_LIKE)
                    ->where('favorites.sender_type', Favorite::SENDER_SHOP)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'shops.id',
                        'shop_profiles.shop_name as name',
                        'shop_profiles.pref',
                        'shop_profiles.city',
                        DB::raw("(SELECT si.image_path FROM shop_images si WHERE si.shop_id = shops.id ORDER BY si.is_main DESC, si.main_order IS NULL, si.main_order, si.id LIMIT 1) as main_image_path"),
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($receivedRows as $row) {
                    $receivedLikeCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: '店舗',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $this->assetPathForStored($row->main_image_path ?? null),
                        'created_at' => $this->formatInteractionAt($row->created_at),
                        'is_match' => false,
                    ];
                }
            }

            $profileRoute = 'cast.recruit.show';
            $showReceivedLike = true;
        } else {
            // お店側：キャストのキープ・ライク
            $shopUser = Auth::guard('shop')->user();
            $shopId = $shopUser ? (string) $shopUser->shop_id : null;

            $keepCasts = [];
            $receivedLikeCasts = [];
            $sentLikeCasts = [];

            if ($shopId && Schema::hasTable('favorites')) {
                // 店舗がキープしたキャスト（shop 発信の KEEP）
                $keepRows = DB::table('favorites')
                    ->join('casts', 'favorites.cast_id', '=', 'casts.id')
                    ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
                    ->where('favorites.shop_id', $shopId)
                    ->where('favorites.action_type', Favorite::ACTION_KEEP)
                    ->where('favorites.sender_type', Favorite::SENDER_SHOP)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'casts.id',
                        'cast_profiles.nickname as name',
                        'cast_profiles.birthday',
                        'cast_profiles.pref',
                        'cast_profiles.city',
                        'cast_profiles.profession',
                        DB::raw("(SELECT ci.image_path FROM cast_images ci WHERE ci.cast_id = casts.id ORDER BY ci.is_main DESC, ci.main_order IS NULL, ci.main_order, ci.id LIMIT 1) as main_image_path"),
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($keepRows as $row) {
                    $age = $row->birthday ? \Carbon\Carbon::parse($row->birthday)->age : null;
                    $keepCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: 'ゲスト',
                        'age' => $age,
                        'profession' => $row->profession ?? '',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $this->assetPathForStored($row->main_image_path ?? null),
                        'updated_at' => $this->formatInteractionAt($row->created_at),
                    ];
                }

                // 店舗が送った LIKE（shop 発信の LIKE）
                $sentRows = DB::table('favorites')
                    ->join('casts', 'favorites.cast_id', '=', 'casts.id')
                    ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
                    ->where('favorites.shop_id', $shopId)
                    ->where('favorites.action_type', Favorite::ACTION_LIKE)
                    ->where('favorites.sender_type', Favorite::SENDER_SHOP)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'casts.id',
                        'cast_profiles.nickname as name',
                        'cast_profiles.birthday',
                        'cast_profiles.pref',
                        'cast_profiles.city',
                        'cast_profiles.profession',
                        DB::raw("(SELECT ci.image_path FROM cast_images ci WHERE ci.cast_id = casts.id ORDER BY ci.is_main DESC, ci.main_order IS NULL, ci.main_order, ci.id LIMIT 1) as main_image_path"),
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($sentRows as $row) {
                    $age = $row->birthday ? \Carbon\Carbon::parse($row->birthday)->age : null;
                    $sentLikeCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: 'ゲスト',
                        'age' => $age,
                        'profession' => $row->profession ?? '',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $this->assetPathForStored($row->main_image_path ?? null),
                        'created_at' => $this->formatInteractionAt($row->created_at),
                        'is_match' => false,
                    ];
                }
            }

            $profileRoute = 'shop.castprofileview.show';
            $showReceivedLike = false;
        }

        // レコメンド（条件が似ているお店／キャスト）
        $recommendation = app(\App\Services\RecommendationService::class);
        if ($isCastPortal) {
            $castUser = Auth::guard('member')->user();
            $castIdForReco = $castUser ? (string) $castUser->id : null;
            $recommendItems = $castIdForReco ? $recommendation->recommendShopsForCast($castIdForReco, 6) : [];
            $recommendType = 'shop';
            $recommendLogic = \App\Services\RecommendationService::castRecommendLogicLines();
            $recommendDetailRoute = 'cast.recruit.show';
        } else {
            $shopUser = Auth::guard('shop')->user();
            $shopIdForReco = $shopUser ? (string) $shopUser->shop_id : null;
            $recommendItems = $shopIdForReco ? $recommendation->recommendCastsForShop($shopIdForReco, 6) : [];
            $recommendType = 'cast';
            $recommendLogic = \App\Services\RecommendationService::shopRecommendLogicLines();
            $recommendDetailRoute = 'shop.castprofileview.show';
        }

        return view('shops.interaction.index', [
            'pageId' => 'connection',
            'isCastPortal' => $isCastPortal,
            'keepCasts' => $keepCasts,
            'receivedLikeCasts' => $receivedLikeCasts,
            'sentLikeCasts' => $sentLikeCasts,
            'profileRoute' => $profileRoute,
            'showReceivedLike' => $showReceivedLike,
            'recommendItems' => $recommendItems,
            'recommendType' => $recommendType,
            'recommendLogic' => $recommendLogic,
            'recommendDetailRoute' => $recommendDetailRoute,
        ]);
    }

    private function assetPathForStored(?string $path): string
    {
        if (empty($path)) {
            return asset('assets/images/common/no-image.png');
        }

        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }

        if (str_starts_with($path, 'public/')) {
            return asset('storage/' . substr($path, 7));
        }

        return asset(ltrim($path, '/'));
    }
}
