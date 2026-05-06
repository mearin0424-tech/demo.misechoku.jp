<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InteractionController extends Controller
{
    private const ACTION_TYPE_KEEP = 1;
    private const ACTION_TYPE_FOOTPRINT = 2;
    private const ACTION_TYPE_LIKE = 3;

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
            // キャスト側：お店のキープ・ライク・足あと
            $castUser = Auth::guard('member')->user();
            $castId = $castUser ? (string) $castUser->id : null;

            $keepCasts = [];
            $receivedLikeCasts = [];
            $sentLikeCasts = [];
            $footprintCasts = [];

            if ($castId && Schema::hasTable('favorites')) {
                // 自分がキープした店舗（cast -> shop, action_type=1）
                $keepRows = DB::table('favorites')
                    ->join('shops', 'favorites.shop_id', '=', 'shops.id')
                    ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
                    ->where('favorites.cast_id', $castId)
                    ->where('favorites.action_type', self::ACTION_TYPE_KEEP)
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

                // 受け取ったLIKE（shop -> cast, action_type=3）
                $receivedRows = DB::table('favorites')
                    ->join('shops', 'favorites.shop_id', '=', 'shops.id')
                    ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
                    ->where('favorites.cast_id', $castId)
                    ->where('favorites.action_type', self::ACTION_TYPE_LIKE)
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

                $footprintRows = DB::table('favorites')
                    ->join('shops', 'favorites.shop_id', '=', 'shops.id')
                    ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
                    ->where('favorites.cast_id', $castId)
                    ->where('favorites.action_type', self::ACTION_TYPE_FOOTPRINT)
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
                foreach ($footprintRows as $row) {
                    $footprintCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: '店舗',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $this->assetPathForStored($row->main_image_path ?? null),
                        'visited_at' => $this->formatInteractionAt($row->created_at),
                    ];
                }
            }

            $profileRoute = 'cast.recruit.show';
            $showReceivedLike = true;
        } else {
            // お店側：キャストのキープ・ライク・足あと
            $shopUser = Auth::guard('shop')->user();
            $shopId = $shopUser ? (string) $shopUser->shop_id : null;

            $keepCasts = [];
            $receivedLikeCasts = [];
            $sentLikeCasts = [];
            $footprintCasts = [];

            if ($shopId && Schema::hasTable('favorites')) {
                // 店舗がキープしたキャスト（shop -> cast, action_type=1）
                $keepRows = DB::table('favorites')
                    ->join('casts', 'favorites.cast_id', '=', 'casts.id')
                    ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
                    ->where('favorites.shop_id', $shopId)
                    ->where('favorites.action_type', self::ACTION_TYPE_KEEP)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'casts.id',
                        'cast_profiles.nickname as name',
                        'cast_profiles.birthday',
                        'cast_profiles.pref',
                        'cast_profiles.city',
                        'cast_profiles.profession',
                        DB::raw("(SELECT ci.image_path FROM cast_images ci WHERE ci.cast_id = casts.id AND ci.type = 1 ORDER BY ci.is_main DESC, ci.main_order IS NULL, ci.main_order, ci.id LIMIT 1) as main_image_path"),
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

                // 店舗が送ったLIKE（shop -> cast, action_type=3）
                $sentRows = DB::table('favorites')
                    ->join('casts', 'favorites.cast_id', '=', 'casts.id')
                    ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
                    ->where('favorites.shop_id', $shopId)
                    ->where('favorites.action_type', self::ACTION_TYPE_LIKE)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'casts.id',
                        'cast_profiles.nickname as name',
                        'cast_profiles.birthday',
                        'cast_profiles.pref',
                        'cast_profiles.city',
                        'cast_profiles.profession',
                        DB::raw("(SELECT ci.image_path FROM cast_images ci WHERE ci.cast_id = casts.id AND ci.type = 1 ORDER BY ci.is_main DESC, ci.main_order IS NULL, ci.main_order, ci.id LIMIT 1) as main_image_path"),
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

                $footprintRows = DB::table('favorites')
                    ->join('casts', 'favorites.cast_id', '=', 'casts.id')
                    ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
                    ->where('favorites.shop_id', $shopId)
                    ->where('favorites.action_type', self::ACTION_TYPE_FOOTPRINT)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'casts.id',
                        'cast_profiles.nickname as name',
                        'cast_profiles.birthday',
                        'cast_profiles.pref',
                        'cast_profiles.city',
                        'cast_profiles.profession',
                        DB::raw("(SELECT ci.image_path FROM cast_images ci WHERE ci.cast_id = casts.id AND ci.type = 1 ORDER BY ci.is_main DESC, ci.main_order IS NULL, ci.main_order, ci.id LIMIT 1) as main_image_path"),
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($footprintRows as $row) {
                    $age = $row->birthday ? \Carbon\Carbon::parse($row->birthday)->age : null;
                    $footprintCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: 'ゲスト',
                        'age' => $age,
                        'profession' => $row->profession ?? '',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $this->assetPathForStored($row->main_image_path ?? null),
                        'visited_at' => $this->formatInteractionAt($row->created_at),
                    ];
                }
            }

            $profileRoute = 'shop.castprofileview.show';
            $showReceivedLike = false;
        }

        return view('shops.interaction.index', [
            'pageId' => 'connection',
            'keepCasts' => $keepCasts,
            'receivedLikeCasts' => $receivedLikeCasts,
            'sentLikeCasts' => $sentLikeCasts,
            'footprintCasts' => $footprintCasts,
            'profileRoute' => $profileRoute,
            'showReceivedLike' => $showReceivedLike,
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